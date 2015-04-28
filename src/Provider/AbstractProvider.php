<?php

namespace League\OAuth2\Client\Provider;

use Closure;
use Ivory\HttpAdapter\CurlHttpAdapter;
use Ivory\HttpAdapter\HttpAdapterException;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\Message\RequestInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Grant\GrantInterface;
use League\OAuth2\Client\Token\AccessToken;
use UnexpectedValueException;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    public $clientId = '';

    /**
     * @var string
     */
    public $clientSecret = '';

    /**
     * @var string
     */
    public $redirectUri = '';

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $uidKey = 'uid';

    /**
     * @var array
     */
    public $scopes = [];

    /**
     * @var string
     */
    public $method = 'post';

    /**
     * @var string
     */
    public $scopeSeparator = ',';

    /**
     * @var string
     */
    public $responseType = 'json';

    /**
     * @var array
     */
    public $headers = [];

    /**
     * @var string
     */
    public $authorizationHeader;

    /**
     * @var HttpAdapterInterface
     */
    protected $httpClient;

    /**
     * @var Closure
     */
    protected $redirectHandler;

    /**
     * @var int This represents: PHP_QUERY_RFC1738, which is the default value for php 5.4
     *          and the default encryption type for the http_build_query setup
     */
    protected $httpBuildEncType = 1;

    /**
     * @param array $options
     * @param array $collaborators
     */
    public function __construct($options = [], array $collaborators = [])
    {
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->{$option} = $value;
            }
        }

        if (empty($collaborators['httpClient'])) {
            $collaborators['httpClient'] = new CurlHttpAdapter();
        }
        $this->setHttpClient($collaborators['httpClient']);
    }

    public function setHttpClient(HttpAdapterInterface $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    public function getHttpClient()
    {
        $client = $this->httpClient;

        return $client;
    }

    // Implementing these interfaces methods should not be required, but not
    // doing so will break HHVM because of https://github.com/facebook/hhvm/issues/5170
    // Once HHVM is working, delete the following abstract methods.
    abstract public function urlAuthorize();
    abstract public function urlAccessToken();
    abstract public function urlUserDetails(AccessToken $token);
    abstract public function userDetails($response, AccessToken $token);
    abstract public function errorCheck(array $result);
    // End of methods to delete.

    public function getScopes()
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;
    }

    public function getAuthorizationUrl(array $options = [])
    {
        $this->state = isset($options['state']) ? $options['state'] : md5(uniqid(rand(), true));

        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => $this->state,
            'scope' => is_array($this->scopes) ? implode($this->scopeSeparator, $this->scopes) : $this->scopes,
            'response_type' => isset($options['response_type']) ? $options['response_type'] : 'code',
            'approval_prompt' => isset($options['approval_prompt']) ? $options['approval_prompt'] : 'auto',
        ];

        return $this->urlAuthorize().'?'.$this->httpBuildQuery($params, '', '&');
    }

    // @codeCoverageIgnoreStart
    public function authorize(array $options = [])
    {
        $url = $this->getAuthorizationUrl($options);
        if ($this->redirectHandler) {
            $handler = $this->redirectHandler;
            return $handler($url, $this);
        }
        // @codeCoverageIgnoreStart
        header('Location: ' . $url);
        exit;
        // @codeCoverageIgnoreEnd
    }

    public function getAccessToken($grant = 'authorization_code', array $params = [])
    {
        if (is_string($grant)) {
            // PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
            $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $grant)));
            $grant = 'League\\OAuth2\\Client\\Grant\\'.$className;
            if (! class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }
            $grant = new $grant();
        } elseif (! $grant instanceof GrantInterface) {
            $message = get_class($grant).' is not an instance of League\OAuth2\Client\Grant\GrantInterface';
            throw new \InvalidArgumentException($message);
        }

        $defaultParams = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => $grant,
        ];

        $requestParams = $grant->prepRequestParams($defaultParams, $params);

        try {
            $client = $this->getHttpClient();
            switch (strtoupper($this->method)) {
                case 'GET':
                    // @codeCoverageIgnoreStart
                    // No providers included with this library use get but 3rd parties may
                    $httpResponse = $client->get($this->urlAccessToken(), [
                        'headers' => $this->getHeaders(),
                        'query' => $requestParams,
                    ]);
                    $response = (string) $httpResponse->getBody();
                    break;
                    // @codeCoverageIgnoreEnd
                case 'POST':
                    $httpResponse = $client->post($this->urlAccessToken(), [
                        'headers' => $this->getHeaders(),
                        'body' => $requestParams,
                    ]);
                    $response = (string) $httpResponse->getBody();
                    break;
                // @codeCoverageIgnoreStart
                default:
                    throw new \InvalidArgumentException('Neither GET nor POST is specified for request');
                // @codeCoverageIgnoreEnd
            }
        } catch (HttpAdapterException $e) {
            $response = (string) $e->getResponse()->getBody();
        }

        $result = $this->parseResponse($response);

        // @codeCoverageIgnoreStart
        $this->errorCheck($result);
        // @codeCoverageIgnoreEnd

        $result = $this->prepareAccessTokenResult($result);

        return $grant->handleResponse($result);
    }

    /**
     * Get an authenticated request instance.
     *
     * Creates a PSR-7 compatible request instance that can be modified.
     * Often used to create calls against an API that requires authentication.
     *
     * @param  string $method
     * @param  string $url
     * @param  AccessToken $token
     * @return RequestInterface
     */
    public function getAuthenticatedRequest($method, $url, AccessToken $token)
    {
        $factory = $this->getHttpClient()
            ->getConfiguration()
            ->getMessageFactory();

        $request = $factory->createRequest($url, $method);
        $request->addHeaders($this->getHeaders($token));
        return $request;
    }

    /**
     * Get a response for a request instance.
     *
     * Processes the response according to provider response type.
     *
     * @param  RequestInterface $request
     * @return mixed
     */
    public function getResponse(RequestInterface $request)
    {
        try {
            $client = $this->getHttpClient();

            $httpResponse = $client->sendRequest($request);

            $response = (string) $httpResponse->getBody();
        } catch (HttpAdapterException $e) {
            // @codeCoverageIgnoreStart
            $response = (string) $e->getResponse()->getBody();
            // @codeCoverageIgnoreEnd
        }

        $result = $this->parseResponse($response);

        // @codeCoverageIgnoreStart
        $this->errorCheck($result);
        // @codeCoverageIgnoreEnd

        return $result;
    }

    /**
     * Parse the response, according to the provider response type.
     *
     * @param  string $response
     * @return array
     */
    protected function parseResponse($response)
    {
        $result = [];

        switch ($this->responseType) {
            case 'json':
                $result = json_decode($response, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new UnexpectedValueException('Unable to parse client response');
                }

                break;
            case 'string':
                parse_str($response, $result);

                break;
        }

        return $result;
    }

    /**
     * Prepare the access token response for the grant. Custom mapping of
     * expirations, etc should be done here.
     *
     * @param  array $result
     * @return array
     */
    protected function prepareAccessTokenResult(array $result)
    {
        $this->setResultUid($result);
        return $result;
    }

    /**
     * Sets any result keys we've received matching our provider-defined uidKey to the key "uid".
     *
     * @param array $result
     */
    protected function setResultUid(array &$result)
    {
        // If we're operating with the default uidKey there's nothing to do.
        if ($this->uidKey === "uid") {
            return;
        }

        if (isset($result[$this->uidKey])) {
            // The AccessToken expects a "uid" to have the key "uid".
            $result['uid'] = $result[$this->uidKey];
        }
    }

    public function getUserDetails(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token);

        return $this->userDetails($response, $token);
    }

    public function getUserUid(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userUid($response, $token);
    }

    public function getUserEmail(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userEmail($response, $token);
    }

    public function getUserScreenName(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userScreenName($response, $token);
    }

    public function userUid($response, AccessToken $token)
    {
        if (!empty($response['id'])) {
            return $response['id'];
        }
    }

    public function userEmail($response, AccessToken $token)
    {
        if (!empty($response['email'])) {
            return $response['email'];
        }
    }

    public function userScreenName($response, AccessToken $token)
    {
        if (!empty($response['name'])) {
            return $response['name'];
        }
    }

    /**
     * Build HTTP the HTTP query, handling PHP version control options
     *
     * @param  array        $params
     * @param  integer      $numeric_prefix
     * @param  string       $arg_separator
     * @param  null|integer $enc_type
     *
     * @return string
     * @codeCoverageIgnoreStart
     */
    protected function httpBuildQuery($params, $numeric_prefix = 0, $arg_separator = '&', $enc_type = null)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !defined('HHVM_VERSION')) {
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

        $request = $this->getAuthenticatedRequest(Request::METHOD_GET, $url, $token);

        return $this->getResponse($request);
    }

    protected function getAuthorizationHeaders($token)
    {
        $headers = [];
        if ($this->authorizationHeader) {
            $headers['Authorization'] = $this->authorizationHeader . ' ' . $token;
        }
        return $headers;
    }

    public function getHeaders($token = null)
    {
        $headers = $this->headers;
        if ($token) {
            $headers = array_merge($headers, $this->getAuthorizationHeaders($token));
        }
        return $headers;
    }

    public function setRedirectHandler(Closure $handler)
    {
        $this->redirectHandler = $handler;
    }
}
