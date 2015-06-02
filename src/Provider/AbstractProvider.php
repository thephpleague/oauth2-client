<?php

namespace League\OAuth2\Client\Provider;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Grant\GrantInterface;
use League\OAuth2\Client\Token\AccessToken;
use RandomLib\Factory as RandomFactory;
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
     * @var RandomFactory
     */
    protected $randomFactory;

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
            $collaborators['httpClient'] = new Client();
        }
        $this->setHttpClient($collaborators['httpClient']);

        if (empty($collaborators['randomFactory'])) {
            $collaborators['randomFactory'] = new RandomFactory();
        }
        $this->setRandomFactory($collaborators['randomFactory']);
    }

    public function setHttpClient(ClientInterface $client)
    {
        $this->httpClient = $client;
        return $this;
    }

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Set the instance of the CSPRNG random generator factory to use.
     *
     * @param  RandomFactory $factory
     * @return $this
     */
    public function setRandomFactory(RandomFactory $factory)
    {
        $this->randomFactory = $factory;
        return $this;
    }

    /**
     * Get the instance of the CSPRNG random generatory factory.
     *
     * @return RandomFactory
     */
    public function getRandomFactory()
    {
        return $this->randomFactory;
    }

    // Implementing these interfaces methods should not be required, but not
    // doing so will break HHVM because of https://github.com/facebook/hhvm/issues/5170
    // Once HHVM is working, delete the following abstract methods.
    abstract public function urlAuthorize();
    abstract public function urlAccessToken();
    abstract public function urlUserDetails(AccessToken $token);
    abstract public function userDetails($response, AccessToken $token);
    // End of methods to delete.

    public function getScopes()
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * Get a new random string to use for auth state.
     *
     * @param  integer $length
     * @return string
     */
    protected function getRandomState($length = 32)
    {
        $generator = $this
            ->getRandomFactory()
            ->getMediumStrengthGenerator();

        return $generator->generateString($length);
    }

    public function getAuthorizationUrl(array $options = [])
    {
        if (empty($options['state'])) {
            $options['state'] = $this->getRandomState();
        }

        // Store the state, it may need to be accessed later.
        $this->state = $options['state'];

        $options += [
            // Do not set the default state here! The random generator takes a
            // non-trivial amount of time to run.
            'response_type'   => 'code',
            'approval_prompt' => 'auto',
        ];

        $scopes = is_array($this->scopes) ? implode($this->scopeSeparator, $this->scopes) : $this->scopes;

        $params = [
            'client_id'       => $this->clientId,
            'redirect_uri'    => $this->redirectUri,
            'state'           => $this->state,
            'scope'           => $scopes,
            'response_type'   => $options['response_type'],
            'approval_prompt' => $options['approval_prompt'],
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
            $method = $this->method;

            $response = $client->$method($this->urlAccessToken(), [
                'headers' => $this->getHeaders(),
                'query' => $requestParams,
            ]);

        } catch (RequestException $e) {
            $response = $e->getResponse();
        }

        $response = $this->parseResponse($response);
        $response = $this->prepareAccessTokenResult($response);

        return $grant->handleResponse($response);
    }

    /**
     * Get an authenticated request instance.
     */
    public function getRequest($method, $url, AccessToken $token, $body = null)
    {
        return new Request($method, $url, $this->getHeaders($token), $body);
    }

    public function makeRequest($method, $url, AccessToken $token, $body = null)
    {
        $request = $this->getAuthenticatedRequest($method, $url, $token, $body);
        return $this->getResponse($request);
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
            $response = $this->getHttpClient()->sendRequest($request);
            return $this->parseResponse($response);

        } catch (RequestException $e) {
            return $this->parseResponse($e->getResponse());
        }
    }

    protected function parseJsonResponse($response)
    {
        $result = json_decode($response, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new UnexpectedValueException('Unable to parse client response');
        }

        return $result;
    }

    /**
     * Parse the response, according to the provider response type.
     *
     * @throws UnexpectedValueException
     * @param  string $response
     * @return array
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $result = [];

        $body = $response->getBody();

        switch ($this->responseType) {
            case 'json':
                $result = $this->parseJsonResponse($body);
                break;
            case 'string':
                $result = parse_str($response, $result);
                break;
        }

        $this->checkResponse($result);

        return $result;
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  array $response
     * @return void
     */
    abstract protected function checkResponse(array $response);

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
