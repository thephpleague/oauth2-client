<?php

namespace League\OAuth2\Client\Provider;

use Guzzle\Service\Client as GuzzleClient;
use League\OAuth2\Client\Token\AccessToken as AccessToken;
use League\OAuth2\Client\Token\Authorize as AuthorizeToken;
use League\OAuth2\Client\Exception\IDPException as IDPException;

abstract class IdentityProvider
{
    /**
     * Client ID.
     * @var string
     */
    public $clientId = '';

    /**
     * Client Secret.
     * @var string
     */
    public $clientSecret = '';

    /**
     * Redirect URI.
     * @var string
     */
    public $redirectUri = '';

    /**
     * Provider name.
     * @var String
     */
    public $name = '';

    /**
     * Unique Identifer key.
     * @var string
     */
    public $uidKey = 'uid';

    /**
     * Scopes to request.
     * @var array
     */
    public $scopes = array();

    /**
     * HTTP Method used to gain access token.
     * @var string
     */
    public $method = 'post';

    /**
     * Seperator for scopes list.
     * @var string
     */
    public $scopeSeperator = ',';

    /**
     * Expected response type to decode access token.
     * @var string
     */
    public $responseType = 'json';

    /**
     * Cache area if token has already been accessed.
     * @var Array
     */
    protected $cachedUserDetailsResponse;

    /**
     * Constructor, maps options to properties.
     * @param array $options options to be mapped to class properties.
     */
    public function __construct($options = array())
    {
        foreach ($options as $option => $value) {
            if (isset($this->{$option})) {
                $this->{$option} = $value;
            }
        }
    }

    /**
     * Get authorization endpoint.
     * @return String Authorization Endpoint.
     */
    abstract public function urlAuthorize();

    /**
     * Get Access Token endpoint
     * @return String url used to gain access token.
     */
    abstract public function urlAccessToken();

    /**
     * Get authenticated user properties
     * @param  League\OAuth2\Client\Token\AccessToken $token Authorized Token
     * @return String                                    URL To access user data.
     */
    abstract public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token);

    /**
     * Decode the response for urlUserDetails url into a User object.
     * @param  Mixed                                  $response Decoded response for user data request
     * @param  League\OAuth2\Client\Token\AccessToken $token    Authorized Access Token
     * @return League\OAuth2\Client\Provider\User               Populated user object
     */
    abstract public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token);

    /**
     * Return scopes list
     * @return Array Scopes list.
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Set required scopes
     * @param array $scopes Array of scopes
     */
    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * Generates an authorization url to redirect the useragent
     * @param  array  $options Extra options fpr the request
     * @return String          Authorization url
     */
    public function getAuthorizationUrl($options = array())
    {
        /**
         * Generate a new state hash to protect from CSRF
         * @var String
         */
        $state = md5(uniqid(rand(), true));

        /**
         * Store the state in the useragents cookie.
         */
        setcookie($this->name . '_authorize_state', $state);

        /**
         * Build request parameters
         * @var array
         */
        $params = array(
            'client_id'       => $this->clientId,
            'redirect_uri'    => $this->redirectUri,
            'state'           => $state,
            'scope'           => is_array($this->scopes) ? implode($this->scopeSeperator, $this->scopes) : $this->scopes,
            'response_type'   => isset($options['response_type']) ? $options['response_type'] : 'code',
            'approval_prompt' => 'auto'
        );

        return $this->urlAuthorize() . '?' . http_build_query($params);
    }

    /**
     * Begin the authorization process, this method will redirect the
     * useragent to the authorization server.
     * @param  array  $options Extra options for the authorizaion url
     * @return void
     */
    public function authorize($options = array())
    {
        header('Location: ' . $this->getAuthorizationUrl($options));
        exit;
    }

    /**
     * Requests an access token for hte grant
     * @param  string $grant  Grant Type
     * @param  array  $params Extra parameters for the request
     */
    public function getAccessToken($grant = 'authorization_code', $params = array())
    {
        if (is_string($grant)) {
            $grant = 'League\\OAuth2\\Client\\Grant\\'.ucfirst(str_replace('_', '', $grant));
            if ( ! class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }

            $grant = new $grant;
        } elseif ( ! $grant instanceof Grant\GrantInterface) {
            throw new \InvalidArgumentException($grant.' is not an instance of League\OAuth2\Client\Grant\GrantInterface');
        }

        $defaultParams = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => $grant,
        );

        $requestParams = $grant->prepRequestParams($defaultParams, $params);

        try {
            switch ($this->method) {
                case 'get':
                    $client = new GuzzleClient($this->urlAccessToken() . '?' . http_build_query($requestParams));
                    $request = $client->send();
                    $response = $request->getBody();
                    break;
                case 'post':
                    $client = new GuzzleClient($this->urlAccessToken());
                    $request = $client->post(null, null, $requestParams)->send();
                    $response = $request->getBody();
                    break;
            }
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $raw_response = explode("\n", $e->getResponse());
            $response = end($raw_response);
        }

        switch ($this->responseType) {
            case 'json':
                $result = json_decode($response, true);
                break;
            case 'string':
                parse_str($response, $result);
                break;
        }

        if (isset($result['error']) && ! empty($result['error'])) {
            throw new IDPException($result);
        }

        return $grant->handleResponse($result);
    }

    /**
     * Fetch the user account information
     * @param  League\OAuth2\Client\Token\AccessToken $token Access Token object.
     * @param  boolean     $force                            Bypass resource cache.
     * @return eague\OAuth2\Client\Provider\User             Populated user object.
     */
    public function getUserDetails(AccessToken $token, $force = false)
    {
        return $this->userDetails(json_decode($this->fetchUserDetails($token, $force)), $token);
    }

    /**
     * Get the users Unique Identifier from the resource server.
     * @param  League\OAuth2\Client\Token\AccessToken $token Access Token object.
     * @param  boolean     $force                            Bypass resource cache.
     * @return String                                        User's UID
     */
    public function getUserUid(AccessToken $token, $force = false)
    {
        $response = $this->fetchUserDetails($token, $force);

        return $this->userUid(json_decode($response), $token);
    }

    /**
     * Get the users Email Address from the resource server.
     * @param  League\OAuth2\Client\Token\AccessToken $token Access Token object.
     * @param  boolean     $force                            Bypass resource cache.
     * @return String                                        User's UID
     */
    public function getUserEmail(AccessToken $token, $force = false)
    {
        $response = $this->fetchUserDetails($token, $force);

        return $this->userEmail(json_decode($response), $token);
    }

    /**
     * Get the users Screen Name from the resource server.
     * @param  League\OAuth2\Client\Token\AccessToken $token Access Token object.
     * @param  boolean     $force                            Bypass resource cache.
     * @return String                                        User's UID
     */
    public function getUserScreenName(AccessToken $token, $force = false)
    {
        $response = $this->fetchUserDetails($token, $force);

        return $this->userScreenName(json_decode($response), $token);
    }

    /**
     * Get all the users details from the resource server.
     * @param  League\OAuth2\Client\Token\AccessToken $token Access Token object.
     * @param  boolean     $force                            Bypass resource cache.
     * @return String                                        User's UID
     */
    protected function fetchUserDetails(AccessToken $token, $force = false)
    {
        if ( ! $this->cachedUserDetailsResponse || $force == true) {

            $url = $this->urlUserDetails($token);

            try {

                $client = new GuzzleClient($url);
                $request = $client->get()->send();
                $response = $request->getBody();
                $this->cachedUserDetailsResponse = $response;

            } catch (\Guzzle\Http\Exception\BadResponseException $e) {

                $raw_response = explode("\n", $e->getResponse());
                throw new IDPException(end($raw_response));

            }
        }

        return $this->cachedUserDetailsResponse;
    }

}
