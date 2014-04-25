<?php

namespace League\OAuth2\Client\Provider;

use Guzzle\Service\Client as GuzzleClient;
use Guzzle\Http\Exception\BadResponseException;
use League\OAuth2\Client\Token\AccessToken as AccessToken;
use League\OAuth2\Client\Exception\IDPException as IDPException;
use League\OAuth2\Client\Grant\GrantInterface;

abstract class IdentityProvider
{
    public $clientId = '';

    public $clientSecret = '';

    public $redirectUri = '';

    public $name;

    public $uidKey = 'uid';

    public $scopes = array();

    public $method = 'post';

    public $scopeSeparator = ',';

    public $responseType = 'json';

    public $headers = null;

    protected $httpClient;

   /** @var int This represents: PHP_QUERY_RFC1738. The default encryption type for the http_build_query setup */
    protected $httpBuildEncType = 1;

    public function __construct($options = array())
    {
        foreach ($options as $option => $value) {
            if (isset($this->{$option})) {
                $this->{$option} = $value;
            }
        }

        $this->setHttpClient(new GuzzleClient);
    }

    public function setHttpClient(GuzzleClient $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    public function getHttpClient()
    {
        $client = clone $this->httpClient;

        return $client;
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

        $params = array(
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
            'scope' => is_array($this->scopes) ? implode($this->scopeSeparator, $this->scopes) : $this->scopes,
            'response_type' => isset($options['response_type']) ? $options['response_type'] : 'code',
            'approval_prompt' => 'auto'
        );

        return $this->urlAuthorize() . '?' . $this->httpBuildQuery($params, '', '&', PHP_QUERY_RFC1738);
    }

    // @codeCoverageIgnoreStart
    public function authorize($options = array())
    {
        header('Location: ' . $this->getAuthorizationUrl($options));
        exit;
    }
    // @codeCoverageIgnoreEnd

    public function getAccessToken($grant = 'authorization_code', $params = array())
    {
        if (is_string($grant)) {
            $grant = 'League\\OAuth2\\Client\\Grant\\'.ucfirst(str_replace('_', '', $grant));
            if ( ! class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }
            $grant = new $grant;
        } elseif (! $grant instanceof GrantInterface) {
            throw new \InvalidArgumentException(get_class($grant) . ' is not an instance of League\OAuth2\Client\Grant\GrantInterface');
        }

        $defaultParams = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => $grant,
        );

        $requestParams = $grant->prepRequestParams($defaultParams, $params);

        try {
            switch (strtoupper($this->method)) {
                case 'GET':
                    // @codeCoverageIgnoreStart
                    // No providers included with this library use get but 3rd parties may
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->urlAccessToken() . '?' . $this->httpBuildQuery($requestParams, '', '&', PHP_QUERY_RFC1738));
                    $request = $client->send();
                    $response = $request->getBody();
                    break;
                    // @codeCoverageIgnoreEnd
                case 'POST':
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->urlAccessToken());
                    $request = $client->post(null, null, $requestParams)->send();
                    $response = $request->getBody();
                    break;
                // @codeCoverageIgnoreStart
                default:
                    throw new \InvalidArgumentException('Neither GET nor POST is specified for request');
                // @codeCoverageIgnoreEnd
            }
        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $raw_response = explode("\n", $e->getResponse());
            $response = end($raw_response);
            // @codeCoverageIgnoreEnd
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
            // @codeCoverageIgnoreStart
            throw new IDPException($result);
            // @codeCoverageIgnoreEnd
        }

        return $grant->handleResponse($result);
    }

    public function getUserDetails(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token);

        return $this->userDetails(json_decode($response), $token);
    }

    public function getUserUid(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userUid(json_decode($response), $token);
    }

    public function getUserEmail(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userEmail(json_decode($response), $token);
    }

    public function getUserScreenName(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userScreenName(json_decode($response), $token);
    }

    /**
     * Build HTTP the HTTP query, handling PHP version control options
     *
     * @param  array        $params
     * @param  integer      $numeric_prefix
     * @param  string       $arg_separator
     * @param  null|integer $enc_type
     * @return string
     * @codeCoverageIgnoreStart
     */
    protected function httpBuildQuery($params, $numeric_prefix = 0, $arg_separator = '&', $enc_type = null)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            if ($enc_type === null) {
                $enc_type = $this->httpBuildEncType;
            }
            $url = http_build_query($params, $numeric_prefix, $arg_separator, $enc_type);
        } else {
            $url = http_build_query($params, $numeric_prefix, $arg_separator);
        }

        return $url;
    }

    protected function fetchUserDetails(AccessToken $token)
    {
        $url = $this->urlUserDetails($token);

        try {

            $client = $this->getHttpClient();
            $client->setBaseUrl($url);

            if ($this->headers) {
                $client->setDefaultOption('headers', $this->headers);
            }

            $request = $client->get()->send();
            $response = $request->getBody();

        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $raw_response = explode("\n", $e->getResponse());
            throw new IDPException(end($raw_response));
            // @codeCoverageIgnoreEnd
        }

        return $response;
    }
}
