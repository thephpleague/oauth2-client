<?php

namespace League\OAuth2\Client\Provider;

use Closure;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\RequestInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequestFactory;
use RandomLib\Factory as RandomFactory;
use UnexpectedValueException;
use InvalidArgumentException;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var string JSON response type.
     */
    const RESPONSE_TYPE_JSON = 'json';

    /**
     * @var string Parameter string response type.
     */
    const RESPONSE_TYPE_STRING = 'string';

    /**
     * @var string Key used in the access token response to identify the user.
     */
    const ACCESS_TOKEN_UID = null;

    /**
     * @var string Separator used for authorization scopes.
     */
    const SCOPE_SEPARATOR = ',';

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var GrantFactory
     */
    protected $grantFactory;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var HttpAdapterInterface
     */
    protected $httpClient;

    /**
     * @var RandomFactory
     */
    protected $randomFactory;

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

        if (empty($collaborators['grantFactory'])) {
            $collaborators['grantFactory'] = new GrantFactory();
        }
        $this->setGrantFactory($collaborators['grantFactory']);

        if (empty($collaborators['requestFactory'])) {
            $collaborators['requestFactory'] = new requestFactory();
        }
        $this->setRequestFactory($collaborators['requestFactory']);

        if (empty($collaborators['httpClient'])) {
            $client_options = ['timeout'];
            $collaborators['httpClient'] = new HttpClient(
                array_intersect_key($options, array_flip($client_options))
            );
        }
        $this->setHttpClient($collaborators['httpClient']);

        if (empty($collaborators['randomFactory'])) {
            $collaborators['randomFactory'] = new RandomFactory();
        }
        $this->setRandomFactory($collaborators['randomFactory']);
    }

    /**
     * Set the grant factory instance.
     *
     * @param  GrantFactory $factory
     * @return $this
     */
    public function setGrantFactory(GrantFactory $factory)
    {
        $this->grantFactory = $factory;

        return $this;
    }

    /**
     * Get the grant factory instance.
     *
     * @return GrantFactory
     */
    public function getGrantFactory()
    {
        return $this->grantFactory;
    }

    /**
     * Set the request factory instance.
     *
     * @param  RequestFactory $factory
     * @return $this
     */
    public function setRequestFactory(RequestFactory $factory)
    {
        $this->requestFactory = $factory;

        return $this;
    }

    /**
     * Get the request factory instance.
     *
     * @return RequestFactory
     */
    public function getRequestFactory()
    {
        return $this->requestFactory;
    }

    /**
     * Set the HTTP adapter instance.
     *
     * @param  HttpClientInterface $client
     * @return $this
     */
    public function setHttpClient(HttpClientInterface $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * Get the HTTP adapter instance.
     *
     * @return HttpAdapterInterface
     */
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

    /**
     * Get the current state of the OAuth flow.
     *
     * This can be accessed by the redirect handler during authorization.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    // Implementing these interfaces methods should not be required, but not
    // doing so will break HHVM because of https://github.com/facebook/hhvm/issues/5170
    // Once HHVM is working, delete the following abstract methods.
    abstract public function urlAuthorize();
    abstract public function urlAccessToken();
    abstract public function urlUserDetails(AccessToken $token);
    // End of methods to delete.

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

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    abstract protected function getDefaultScopes();

    public function getAuthorizationUrl(array $options = [])
    {
        if (empty($options['state'])) {
            $options['state'] = $this->getRandomState();
        }
        if (empty($options['scope'])) {
            $options['scope'] = $this->getDefaultScopes();
        }

        $options += [
            'response_type'   => 'code',
            'approval_prompt' => 'auto'
        ];

        if (is_array($options['scope'])) {
            $options['scope'] = implode(static::SCOPE_SEPARATOR, $options['scope']);
        }

        // Store the state, it may need to be accessed later.
        $this->state = $options['state'];

        $params = [
            'client_id'       => $this->clientId,
            'redirect_uri'    => $this->redirectUri,
            'state'           => $this->state,
            'scope'           => $options['scope'],
            'response_type'   => $options['response_type'],
            'approval_prompt' => $options['approval_prompt'],
        ];

        return $this->urlAuthorize().'?'.$this->httpBuildQuery($params, '', '&');
    }

    public function authorize(array $options = [], $redirectHandler = null)
    {
        $url = $this->getAuthorizationUrl($options);
        if ($redirectHandler) {
            return $redirectHandler($url, $this);
        }

        // @codeCoverageIgnoreStart
        header('Location: ' . $url);
        exit;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the method to use when requesting an access token.
     *
     * @return string HTTP method
     */
    protected function getAccessTokenMethod()
    {
        return 'POST';
    }

    public function getAccessToken($grant = 'authorization_code', array $params = [])
    {
        if (is_string($grant)) {
            $grant = $this->grantFactory->getGrant($grant);
        } else {
            $this->grantFactory->checkGrant($grant);
        }

        $defaultParams = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => (string) $grant,
        ];

        $requestParams = $grant->prepRequestParams($defaultParams, $params);
        $requestParams = $this->httpBuildQuery($requestParams);

        $url = $this->urlAccessToken();
        $method = strtoupper($this->getAccessTokenMethod());
        $options = [];

        switch ($method) {
            case 'GET':
                // No providers included with this library use get but 3rd parties may
                $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . $requestParams;
                break;
            case 'POST':
                $options['body'] = $requestParams;
                break;
            default:
                throw new InvalidArgumentException(
                    "Unsupported access token request method: '$method'"
                );
        }

        $request  = $this->getRequest($method, $url, $options);
        $response = $this->getResponse($request);
        $response = $this->prepareAccessTokenResult($response);

        return $grant->handleResponse($response);
    }

    /**
     * Get a request instance.
     *
     * Creates a PSR-7 compatible request instance that can be modified.
     * The request is not automatically authenticated.
     *
     * @param  string $method
     * @param  string $url
     * @param  array  $options Any of "headers", "body", and "protocolVersion".
     * @return RequestInterface
     */
    public function getRequest($method, $url, array $options = [])
    {
        return $this->getRequestFactory()->getRequestWithOptions($method, $url, $options);
    }

    /**
     * Get an authenticated request instance.
     *
     * Creates a PSR-7 compatible request instance that can be modified.
     *
     * @param  string $method
     * @param  string $url
     * @param  AccessToken $token
     * @param  array  $options Any of "headers", "body", and "protocolVersion".
     * @return RequestInterface
     */
    public function getAuthenticatedRequest($method, $url, AccessToken $token, array $options = [])
    {
        $options['headers'] = $this->getHeaders($token);
        return $this->getRequest($method, $url, $options);
    }

    /**
     * Sends a request instance and returns a response instance.
     *
     * @param  RequestInterface $request
     * @return ResponseInterface
     */
    protected function sendRequest(RequestInterface $request)
    {
        try {
            $response = $this->getHttpClient()->send($request);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }
        return $response;
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
        $response = (string) $this->sendRequest($request)->getBody();
        return $this->parseResponse($response);
    }

    /**
     * Get the expected type of response for this provider.
     *
     * @return string
     */
    protected function getResponseType()
    {
        return static::RESPONSE_TYPE_JSON;
    }

    /**
     * Parse the response, according to the provider response type.
     *
     * @throws UnexpectedValueException
     * @param  string $response
     * @return array
     */
    protected function parseResponse($response)
    {
        $result = [];

        switch ($this->getResponseType()) {
            case static::RESPONSE_TYPE_JSON:
                $result = json_decode($response, true);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new UnexpectedValueException('Unable to parse client response');
                }
                break;
            case static::RESPONSE_TYPE_STRING:
                parse_str($response, $result);
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
     * Prepare the access token response for the grant.
     *
     * Custom mapping of expirations, etc should be done here. Always call the
     * parent method when overloading this method!
     *
     * @param  array $result
     * @return array
     */
    protected function prepareAccessTokenResult(array $result)
    {
        if (static::ACCESS_TOKEN_UID) {
            $result['uid'] = $result[static::ACCESS_TOKEN_UID];
        }
        return $result;
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param object $response
     * @param AccessToken $token
     * @return League\OAuth2\Client\Provider\UserInterface
     */
    abstract protected function prepareUserDetails(array $response, AccessToken $token);

    public function getUserDetails(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token);

        return $this->prepareUserDetails($response, $token);
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

        $request = $this->getAuthenticatedRequest('GET', $url, $token);

        return $this->getResponse($request);
    }

    /**
     * Get additional headers used by this provider.
     *
     * Typically this is used to set Accept or Content-Type headers.
     *
     * @param  AccessToken $token
     * @return array
     */
    protected function getDefaultHeaders($token = null)
    {
        return [];
    }

    /**
     * Get authorization headers used by this provider.
     *
     * Typically this is "Bearer" or "MAC". For more information see:
     * http://tools.ietf.org/html/rfc6749#section-7.1
     *
     * No default is provided, providers must overload this method to activate
     * authorization headers.
     *
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        return [];
    }

    /**
     * Get the headers used by this provider for a request.
     *
     * If a token is passed, the request may be authenticated through headers.
     *
     * @param  mixed $token  object or string
     * @return array
     */
    public function getHeaders($token = null)
    {
        $headers = $this->getDefaultHeaders();
        if ($token) {
            $headers = array_merge($headers, $this->getAuthorizationHeaders($token));
        }
        return $headers;
    }
}
