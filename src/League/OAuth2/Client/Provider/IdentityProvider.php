<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken as AccessToken;
use League\OAuth2\Client\Token\Authorize as AuthorizeToken;
use League\OAuth2\Client\Exception\IDPException as IDPException;
use League\OAuth2\Client\HttpClient\HttpClientInterface;

abstract class IdentityProvider
{
    public $clientId = '';

    public $clientSecret = '';

    public $redirectUri = '';

    public $name;

    public $uidKey = 'uid';

    public $scopes = array();

    public $method = 'post';

    public $scopeSeperator = ',';

    public $responseType = 'json';

    protected $cachedUserDetailsResponse;

    private $httpClient;

    public function __construct(HttpClientInterface $httpClient, $options = array())
    {
        $this->httpClient = $httpClient;

        foreach ($options as $option => $value) {
            if (isset($this->{$option})) {
                $this->{$option} = $value;
            }
        }
    }

    abstract public function urlAuthorize();

    abstract public function urlAccessToken();

    abstract public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token);

    abstract public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token);

    public function getScopes()
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;
    }

    public function getAuthorizationUrl($options = array())
    {
        $state = md5(uniqid(rand(), true));

        // PHPUnit will declare: "Cannot modify header information - headers already sent by ..."
        //setcookie($this->name.'_authorize_state', $state);

        $params = array(
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => is_array($this->scopes) ? implode($this->scopeSeperator, $this->scopes) : $this->scopes,
            'response_type' => isset($options['response_type']) ? $options['response_type'] : 'code',
            'state' => $state
        );

        // google force-recheck this option
        if (isset($this->approval_prompt)) {
            $params['approval_prompt'] = $this->approval_prompt;
        }

        // google need this option to obtain refersh token
        if (isset($this->access_type)) {
            $params['access_type'] = $this->access_type;
        }

        // google provide this options as a hit to the authentication server
        if (isset($this->login_hint)) {
            $params['login_hint'] = $this->login_hint;
        }

        return $this->urlAuthorize() . '?' . http_build_query($params);
    }

    public function authorize($options = array())
    {
        header('Location: ' . $this->getAuthorizationUrl($options));
        exit;
    }

    public function getAccessToken($grant = 'authorization_code', $params = array())
    {
        if (is_string($grant)) {
            $grant = 'League\\OAuth2\\Client\\Grant\\'.ucfirst(str_replace('_', '', $grant));
            if (!class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }
            $grant = new $grant;
        } elseif (!$grant instanceof Grant\GrantInterface) {
            throw new \InvalidArgumentException($grant
                . ' is not an instance of League\OAuth2\Client\Grant\GrantInterface');
        }

        $defaultParams = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => $grant,
        );

        $requestParams = $grant->prepRequestParams($defaultParams, $params);

        switch ($this->method) {
            case 'get':
                $response = $this->httpClient->get($this->urlAccessToken() . '?'. http_build_query($requestParams));
            break;
            case 'post':
                $response = $this->httpClient->post($this->urlAccessToken(), null, $requestParams);
                break;
        }

        if (is_array($response) && (isset($response['error']) || isset($response['message']))) {
            throw new IDPException($response);
        }

        switch ($this->responseType) {
            case 'json':
                $result = json_decode($response, true);
                break;
            case 'string':
                parse_str($response, $result);
                break;
        }

        return $grant->handleResponse($result);
    }

    public function getUserDetails(AccessToken $token, $force = false)
    {
        $response = $this->fetchUserDetails($token);

        return $this->userDetails(json_decode($response), $token);
    }

    public function getUserUid(AccessToken $token, $force = false)
    {
        $response = $this->fetchUserDetails($token, $force);

        return $this->userUid(json_decode($response), $token);
    }

    public function getUserEmail(AccessToken $token, $force = false)
    {
        $response = $this->fetchUserDetails($token, $force);

        return $this->userEmail(json_decode($response), $token);
    }

    public function getUserScreenName(AccessToken $token, $force = false)
    {
        $response = $this->fetchUserDetails($token, $force);

        return $this->userScreenName(json_decode($response), $token);
    }

    public function fetchUserDetails(AccessToken $token, $force = false)
    {
        if (!$this->cachedUserDetailsResponse || $force == true) {

            $url = $this->urlUserDetails($token);

            $response = $this->httpClient->get($url);
            if (is_array($response) && (isset($response['error']) || isset($response['message']))) {
                throw new IDPException($response);
            } else {
                $this->cachedUserDetailsResponse = $response;
            }
        }

        return $this->cachedUserDetailsResponse;
    }
}
