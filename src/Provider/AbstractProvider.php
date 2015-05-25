<?php

namespace League\OAuth2\Client\Provider;

use Closure;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequestFactory;
use RandomLib\Factory as RandomFactory;
use UnexpectedValueException;
use InvalidArgumentException;

abstract class AbstractProvider
{
    /**
     * @var string Key used in the access token response to identify the user.
     */
    const ACCESS_TOKEN_UID = null;

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

    abstract public function getBaseAuthorizationUrl();
    abstract public function getBaseAccessTokenUrl();
    abstract public function getUserDetailsUrl(AccessToken $token);

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

    /**
     * Get the string used to separate scopes.
     *
     * @return string
     */
    protected function getScopeSeparator()
    {
        return ',';
    }

    /**
     * Returns authorization parameters based on provided options.
     *
     * @param array $options
     * @return array Authorization parameters
     */
    protected function getAuthorizationParameters(array $options)
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
            $options['scope'] = implode($this->getScopeSeparator(), $options['scope']);
        }

        // Store the state, it may need to be accessed later.
        $this->state = $options['state'];

        return [
            'client_id'       => $this->clientId,
            'redirect_uri'    => $this->redirectUri,
            'state'           => $this->state,
            'scope'           => $options['scope'],
            'response_type'   => $options['response_type'],
            'approval_prompt' => $options['approval_prompt'],
        ];
    }

    /**
     * Builds the authorization URL's query string.
     *
     * @param array $params Query parameters
     * @return string Query string
     */
    protected function getAuthorizationQuery(array $params)
    {
        return http_build_query($params);
    }

    /**
     * Returns the authorization URL for given options.
     *
     * @param array $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = [])
    {
        $base   = $this->getBaseAuthorizationUrl();
        $params = $this->getAuthorizationParameters($options);
        $query  = $this->getAuthorizationQuery($params);

        return $this->appendQuery($base, $query);
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
     * Appends a query string to a URL.
     *
     * @param string $url The URL to append the query to
     * @param string $query The HTTP query string
     *
     * @return string
     */
    protected function appendQuery($url, $query)
    {
        $query = trim($query, '?&');

        if ($query) {
            return $url.'?'.$query;
        }

        return $url;
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

    /**
     * Builds the access token URL's query string.
     *
     * @param array $params Query parameters
     * @return string Query string
     */
    protected function getAccessTokenQuery(array $params)
    {
        return http_build_query($params);
    }

    /**
     * Checks that a provided grant is valid, or attempts to produce one if the
     * provided grant is a string.
     *
     * @param mixed $grant
     * @return AbstractGrant
     */
    protected function verifyGrant($grant)
    {
        if (is_string($grant)) {
            return $this->grantFactory->getGrant($grant);
        }

        $this->grantFactory->checkGrant($grant);
        return $grant;
    }

    /**
     * Returns a prepared request for requesting an access token.
     *
     * @param array $params Query string parameters
     */
    protected function getAccessTokenRequest(array $params)
    {
        $url = $this->getBaseAccessTokenUrl();
        $query = $this->getAccessTokenQuery($params);
        $method = strtoupper($this->getAccessTokenMethod());

        $options = [];

        switch ($method) {
            case 'GET':
                $url = $this->appendQuery($url, $query);
                break;
            case 'POST':
                $options['body'] = $query;
                break;
            default:
                throw new InvalidArgumentException(
                    "Unsupported access token request method: '$method'"
                );
        }

        return $this->getRequest($method, $url, $options);
    }

    /**
     * Requests an access token.
     *
     * @param mixed $grant
     * @param array $options
     */
    public function getAccessToken($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
        ];

        $params   = $grant->prepareRequestParameters($params, $options);
        $request  = $this->getAccessTokenRequest($params);
        $response = $this->getResponse($request);

        $prepared = $this->prepareAccessTokenResponse($response);

        return $grant->createAccessToken($prepared);
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
        $response = $this->sendRequest($request);
        $parsed = $this->parseResponse($response);

        $this->checkResponse($response, $parsed);

        return $parsed;
    }


    /**
     * Parses response content as JSON.
     *
     * @param string $content JSON content from response body.
     * @return array Decoded JSON data
     * @throws UnexpectedValueException if the content could not be parsed.
     */
    protected function parseJson($content)
    {
        $content = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException(sprintf(
                "Failed to parse JSON response: %s",
                json_last_error_msg()
            ));
        }

        return $content;
    }

    /**
     * Returns the content type header of a response.
     *
     * @param ResponseInterface $response
     * @return string Semi-colon separated join of content-type headers.
     */
    protected function getContentType(ResponseInterface $response)
    {
        return join(';', (array) $response->getHeader('content-type'));
    }

    /**
     * Parses the response, according to the response's content-type header.
     *
     * @throws UnexpectedValueException
     * @param  ResponseInterface $response
     * @return array
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $content = (string) $response->getBody();
        $type = $this->getContentType($response);

        if (strpos($type, 'json') !== false) {
            return $this->parseJson($content);
        }

        if (strpos($type, 'urlencoded') !== false) {
            parse_str($content, $parsed);
            return $parsed;
        }

        return $content;
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    abstract protected function checkResponse(ResponseInterface $response, $data);

    /**
     * Prepare the access token response for the grant.
     *
     * Custom mapping of expirations, etc should be done here. Always call the
     * parent method when overloading this method!
     *
     * @param  array $result
     * @return array
     */
    protected function prepareAccessTokenResponse(array $result)
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

    protected function fetchUserDetails(AccessToken $token)
    {
        $url = $this->getUserDetailsUrl($token);

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
