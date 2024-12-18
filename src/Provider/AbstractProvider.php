<?php

/**
 * This file is part of the league/oauth2-client library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Alex Bilbie <hello@alexbilbie.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link http://thephpleague.com/oauth2-client/ Documentation
 * @link https://packagist.org/packages/league/oauth2-client Packagist
 * @link https://github.com/thephpleague/oauth2-client GitHub
 */

declare(strict_types=1);

namespace League\OAuth2\Client\Provider;

use GuzzleHttp\Exception\BadResponseException;
use InvalidArgumentException;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\OptionProvider\OptionProviderInterface;
use League\OAuth2\Client\OptionProvider\PostAuthOptionProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Tool\GuardedPropertyTrait;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use UnexpectedValueException;

use function array_merge;
use function array_merge_recursive;
use function assert;
use function base64_encode;
use function bin2hex;
use function hash;
use function header;
use function implode;
use function intdiv;
use function is_array;
use function is_string;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function parse_str;
use function random_bytes;
use function sprintf;
use function strpos;
use function strstr;
use function strtr;
use function substr;
use function trim;

use const JSON_ERROR_NONE;

/**
 * Represents a service provider (authorization server).
 *
 * @link http://tools.ietf.org/html/rfc6749#section-1.1 Roles (RFC 6749, §1.1)
 */
abstract class AbstractProvider
{
    use ArrayAccessorTrait;
    use GuardedPropertyTrait;
    use QueryBuilderTrait;

    /**
     * Key used in a token response to identify the resource owner.
     */
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;

    /**
     * HTTP method used to fetch access tokens.
     */
    public const METHOD_GET = 'GET';

    /**
     * HTTP method used to fetch access tokens.
     */
    public const METHOD_POST = 'POST';

    /**
     * PKCE method used to fetch authorization token.
     *
     * The PKCE code challenge will be hashed with sha256 (recommended).
     */
    public const PKCE_METHOD_S256 = 'S256';

    /**
     * PKCE method used to fetch authorization token.
     *
     * The PKCE code challenge will be sent as plain text, this is NOT recommended.
     * Only use `plain` if no other option is possible.
     */
    public const PKCE_METHOD_PLAIN = 'plain';

    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected string $state;
    protected ?string $pkceCode = null;
    protected GrantFactory $grantFactory;
    protected RequestFactoryInterface $requestFactory;
    protected StreamFactoryInterface $streamFactory;
    protected ClientInterface $httpClient;
    protected OptionProviderInterface $optionProvider;

    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * @param array<string, mixed> $options An array of options to set on this
     *     provider. Options include `clientId`, `clientSecret`, `redirectUri`,
     *     and `state`. Individual providers may introduce more options, as needed.
     * @param array{
     *     grantFactory?: GrantFactory,
     *     requestFactory?: RequestFactoryInterface,
     *     streamFactory?: StreamFactoryInterface,
     *     httpClient?: ClientInterface,
     *     optionProvider?: OptionProviderInterface,
     * } $collaborators An array of collaborators that may be used to override
     *     this provider's default behavior. Individual providers may introduce
     *     more collaborators, as needed.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        // We'll let the GuardedPropertyTrait handle mass assignment of incoming
        // options, skipping any blacklisted properties defined in the provider
        $this->fillProperties($options);

        if (!isset($collaborators['grantFactory'])) {
            $collaborators['grantFactory'] = new GrantFactory();
        }
        $this->setGrantFactory($collaborators['grantFactory']);

        if (!isset($collaborators['requestFactory'])) {
            throw new InvalidArgumentException('No request factory set');
        }
        $this->setRequestFactory($collaborators['requestFactory']);

        if (!isset($collaborators['streamFactory'])) {
            throw new InvalidArgumentException('No stream factory set');
        }
        $this->setStreamFactory($collaborators['streamFactory']);

        if (!isset($collaborators['httpClient'])) {
            throw new InvalidArgumentException('No http client set');
        }
        $this->setHttpClient($collaborators['httpClient']);

        if (!isset($collaborators['optionProvider'])) {
            $collaborators['optionProvider'] = new PostAuthOptionProvider();
        }
        $this->setOptionProvider($collaborators['optionProvider']);
    }

    /**
     * Returns the list of options that can be passed to the HttpClient
     *
     * @param array<string, mixed> $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *     Individual providers may introduce more options, as needed.
     *
     * @return list<string> The options to pass to the HttpClient constructor
     */
    protected function getAllowedClientOptions(array $options): array
    {
        $clientOptions = ['timeout', 'proxy'];

        // Only allow turning off ssl verification if it's for a proxy
        if (isset($options['proxy'])) {
            $clientOptions[] = 'verify';
        }

        return $clientOptions;
    }

    /**
     * Sets the grant factory instance.
     */
    public function setGrantFactory(GrantFactory $factory): static
    {
        $this->grantFactory = $factory;

        return $this;
    }

    /**
     * Returns the current grant factory instance.
     */
    public function getGrantFactory(): GrantFactory
    {
        return $this->grantFactory;
    }

    /**
     * Sets the request factory instance.
     */
    public function setRequestFactory(RequestFactoryInterface $factory): static
    {
        $this->requestFactory = $factory;

        return $this;
    }

    /**
     * Returns the request factory instance.
     */
    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    /**
     * Sets the stream factory instance.
     */
    public function setStreamFactory(StreamFactoryInterface $factory): static
    {
        $this->streamFactory = $factory;

        return $this;
    }

    /**
     * Returns the stream factory instance.
     */
    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    /**
     * Sets the HTTP client instance.
     */
    public function setHttpClient(ClientInterface $client): static
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * Returns the HTTP client instance.
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Sets the option provider instance.
     */
    public function setOptionProvider(OptionProviderInterface $provider): static
    {
        $this->optionProvider = $provider;

        return $this;
    }

    /**
     * Returns the option provider instance.
     */
    public function getOptionProvider(): OptionProviderInterface
    {
        return $this->optionProvider;
    }

    /**
     * Returns the current value of the state parameter.
     *
     * This can be accessed by the redirect handler during authorization.
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Set the value of the pkceCode parameter.
     *
     * When using PKCE this should be set before requesting an access token.
     */
    public function setPkceCode(string $pkceCode): static
    {
        $this->pkceCode = $pkceCode;

        return $this;
    }

    /**
     * Returns the current value of the pkceCode parameter.
     *
     * This can be accessed by the redirect handler during authorization.
     */
    public function getPkceCode(): ?string
    {
        return $this->pkceCode;
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     */
    abstract public function getBaseAuthorizationUrl(): string;

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array<string, mixed> $params
     */
    abstract public function getBaseAccessTokenUrl(array $params): string;

    /**
     * Returns the URL for requesting the resource owner's details.
     */
    abstract public function getResourceOwnerDetailsUrl(AccessToken $token): string;

    /**
     * Returns a new random string to use as the state parameter in an
     * authorization flow.
     *
     * @param int<1, max> $length Length of the random string to be generated.
     */
    protected function getRandomState(int $length = 32): string
    {
        // Converting bytes to hex will always double length. Hence, we can reduce
        // the amount of bytes by half to produce the correct length.
        $length = intdiv($length, 2);
        assert($length >= 1);

        return bin2hex(random_bytes($length));
    }

    /**
     * Returns a new random string to use as PKCE code_verifier and
     * hashed as code_challenge parameters in an authorization flow.
     * Must be between 43 and 128 characters long.
     *
     * @param int<1, max> $length Length of the random string to be generated.
     */
    protected function getRandomPkceCode(int $length = 64): string
    {
        assert($length >= 1);

        return substr(strtr(base64_encode(random_bytes($length)), '+/', '-_'), 0, $length);
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return list<string>
     */
    abstract protected function getDefaultScopes(): array;

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ','
     */
    protected function getScopeSeparator(): string
    {
        return ',';
    }

    protected function getPkceMethod(): ?string
    {
        return null;
    }

    /**
     * Returns authorization parameters based on provided options.
     *
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed> Authorization parameters
     *
     * @throws InvalidArgumentException
     */
    protected function getAuthorizationParameters(array $options): array
    {
        $options += [
            'response_type' => 'code',
            'approval_prompt' => 'auto',
        ];

        if (!isset($options['state'])) {
            $options['state'] = $this->getRandomState();
        }

        if (!isset($options['scope'])) {
            $options['scope'] = $this->getDefaultScopes();
        }

        if (is_array($options['scope'])) {
            $separator = $this->getScopeSeparator();
            $options['scope'] = implode($separator, $options['scope']);
        }

        // Store the state as it may need to be accessed later on.
        assert(is_string($options['state']));
        $this->state = $options['state'];

        $pkceMethod = $this->getPkceMethod();
        if ($pkceMethod !== null) {
            $this->pkceCode = $this->getRandomPkceCode();
            if ($pkceMethod === static::PKCE_METHOD_S256) {
                $options['code_challenge'] = trim(
                    strtr(
                        base64_encode(hash('sha256', $this->pkceCode, true)),
                        '+/',
                        '-_',
                    ),
                    '=',
                );
            } elseif ($pkceMethod === static::PKCE_METHOD_PLAIN) {
                $options['code_challenge'] = $this->pkceCode;
            } else {
                throw new InvalidArgumentException('Unknown PKCE method "' . $pkceMethod . '".');
            }
            $options['code_challenge_method'] = $pkceMethod;
        }

        // Business code layer might set a different redirect_uri parameter
        // depending on the context, leave it as-is
        if (!isset($options['redirect_uri'])) {
            $options['redirect_uri'] = $this->redirectUri;
        }

        $options['client_id'] = $this->clientId;

        return $options;
    }

    /**
     * Builds the authorization URL's query string.
     *
     * @param array<string, mixed> $params Query parameters
     *
     * @return string Query string
     */
    protected function getAuthorizationQuery(array $params): string
    {
        return $this->buildQueryString($params);
    }

    /**
     * Builds the authorization URL.
     *
     * @param array<string, mixed> $options
     *
     * @return string Authorization URL
     *
     * @throws InvalidArgumentException
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        $base = $this->getBaseAuthorizationUrl();
        $params = $this->getAuthorizationParameters($options);
        $query = $this->getAuthorizationQuery($params);

        return $this->appendQuery($base, $query);
    }

    /**
     * Redirects the client for authorization.
     *
     * @param array<string, mixed> $options
     * @param callable(string, static): never | null $redirectHandler The callable should set up redirection, according
     *     to the application's needs and then exit, flushing the headers, etc. to the client. The expectation is that
     *     it _never_ returns.
     *
     * @throws InvalidArgumentException
     */
    public function authorize(
        array $options = [],
        ?callable $redirectHandler = null,
    ): never {
        $url = $this->getAuthorizationUrl($options);
        if ($redirectHandler) {
            $redirectHandler($url, $this);
        // @codeCoverageIgnoreStart
        } else {
            header('Location: ' . $url);
        }
        exit;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Appends a query string to a URL.
     *
     * @param  string $url The URL to append the query to
     * @param  string $query The HTTP query string
     *
     * @return string The resulting URL
     */
    protected function appendQuery(string $url, string $query): string
    {
        $query = trim($query, '?&');

        if ($query) {
            $glue = strstr($url, '?') === false ? '?' : '&';

            return $url . $glue . $query;
        }

        return $url;
    }

    /**
     * Returns the method to use when requesting an access token.
     *
     * @return string HTTP method
     */
    protected function getAccessTokenMethod(): string
    {
        return self::METHOD_POST;
    }

    /**
     * Returns the key used in the access token response to identify the resource owner.
     *
     * @return string | null Resource owner identifier key
     */
    protected function getAccessTokenResourceOwnerId(): ?string
    {
        /** @var string | null */
        return static::ACCESS_TOKEN_RESOURCE_OWNER_ID;
    }

    /**
     * Builds the access token URL's query string.
     *
     * @param array<string, mixed> $params Query parameters
     *
     * @return string Query string
     */
    protected function getAccessTokenQuery(array $params): string
    {
        return $this->buildQueryString($params);
    }

    /**
     * Checks that a provided grant is valid, or attempts to produce one if the
     * provided grant is a string.
     */
    protected function verifyGrant(mixed $grant): AbstractGrant
    {
        if (is_string($grant)) {
            return $this->grantFactory->getGrant($grant);
        }

        $this->grantFactory->checkGrant($grant);

        return $grant;
    }

    /**
     * Returns the full URL to use when requesting an access token.
     *
     * @param array<string, mixed> $params Query parameters
     */
    protected function getAccessTokenUrl(array $params): string
    {
        $url = $this->getBaseAccessTokenUrl($params);

        if ($this->getAccessTokenMethod() === self::METHOD_GET) {
            $query = $this->getAccessTokenQuery($params);

            return $this->appendQuery($url, $query);
        }

        return $url;
    }

    /**
     * Returns a prepared request for requesting an access token.
     *
     * @param array{
     *     headers?: array<string, string>,
     *     version?: string,
     *     body?: string,
     * } $params Any of "headers", "body", and "version".
     */
    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $method = $this->getAccessTokenMethod();
        $url = $this->getAccessTokenUrl($params);

        /**
         * @var array{
         *     headers?: array<string, string>,
         *     version?: string,
         *     body?: string,
         * } $options
         */
        $options = $this->optionProvider->getAccessTokenOptions($this->getAccessTokenMethod(), $params);

        return $this->getRequest($method, $url, $options);
    }

    /**
     * Requests an access token using a specified grant and option set.
     *
     * @param array<string, mixed> $options
     *
     * @throws ClientExceptionInterface
     * @throws IdentityProviderException
     * @throws UnexpectedValueException
     */
    public function getAccessToken(mixed $grant, array $options = []): AccessTokenInterface
    {
        $grant = $this->verifyGrant($grant);

        if (!isset($options['scope'])) {
            $options['scope'] = $this->getDefaultScopes();
        }

        if (is_array($options['scope'])) {
            $separator = $this->getScopeSeparator();
            $options['scope'] = implode($separator, $options['scope']);
        }

        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
        ];

        if ($this->pkceCode !== null) {
            $params['code_verifier'] = $this->pkceCode;
        }

        /**
         * @var array{
         *     headers?: array<string, string>,
         *     version?: string,
         *     body?: string,
         * } $params
         */
        $params = $grant->prepareRequestParameters($params, $options);

        $request = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);
        $prepared = $this->prepareAccessTokenResponse($response);

        return $this->createAccessToken($prepared, $grant);
    }

    /**
     * Returns a PSR-7 request instance that is not authenticated.
     *
     * @param array{
     *     headers?: array<string, string>,
     *     version?: string,
     *     body?: string,
     * } $options Any of "headers", "body", and "version".
     */
    public function getRequest(string $method, string $url, array $options = []): RequestInterface
    {
        return $this->createRequest($method, $url, null, $options);
    }

    /**
     * Returns an authenticated PSR-7 request instance.
     *
     * @param array{
     *     headers?: array<string, string>,
     *     version?: string,
     *     body?: string,
     * } $options Any of "headers", "body", and "version".
     */
    public function getAuthenticatedRequest(
        string $method,
        string $url,
        AccessTokenInterface | string | null $token,
        array $options = [],
    ): RequestInterface {
        return $this->createRequest($method, $url, $token, $options);
    }

    /**
     * Creates a PSR-7 request instance.
     *
     * @param array{
     *     headers?: array<string, string>,
     *     version?: string,
     *     body?: string,
     * } $options
     */
    protected function createRequest(
        string $method,
        string $url,
        AccessTokenInterface | string | null $token,
        array $options,
    ): RequestInterface {
        $defaults = [
            'headers' => $this->getHeaders($token),
        ];

        /**
         * @var array{
         *     headers: array<string, string>,
         *     version?: string,
         *     body?: string,
         * } $options
         */
        $options = array_merge_recursive($defaults, $options);
        $requestFactory = $this->getRequestFactory();
        $streamFactory = $this->getStreamFactory();

        $request = $requestFactory->createRequest($method, $url);
        foreach ($options['headers'] as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }

        $request = $request->withProtocolVersion($options['version'] ?? '1.1');

        if (isset($options['body'])) {
            $request = $request->withBody($streamFactory->createStream($options['body']));
        }

        return $request;
    }

    /**
     * Sends a request instance and returns a response instance.
     *
     * WARNING: This method does not attempt to catch exceptions caused by HTTP
     * errors! It is recommended to wrap this method in a try/catch block.
     *
     * @throws ClientExceptionInterface
     */
    public function getResponse(RequestInterface $request): ResponseInterface
    {
        return $this->getHttpClient()->sendRequest($request);
    }

    /**
     * Sends a request and returns the parsed response.
     *
     * @return array<string, mixed>
     *
     * @throws ClientExceptionInterface
     * @throws IdentityProviderException
     * @throws UnexpectedValueException
     */
    public function getParsedResponse(RequestInterface $request): array
    {
        try {
            $response = $this->getResponse($request);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }

        /** @var array<string, mixed> $parsed */
        $parsed = $this->parseResponse($response);

        if (is_array($parsed) === false) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.',
            );
        }

        $this->checkResponse($response, $parsed);

        return $parsed;
    }

    /**
     * Attempts to parse a JSON response.
     *
     * @param string $content JSON content from response body
     *
     * @return array<string, mixed> Parsed JSON data
     *
     * @throws UnexpectedValueException if the content could not be parsed
     */
    protected function parseJson(string $content): array
    {
        $content = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException(sprintf(
                'Failed to parse JSON response: %s',
                json_last_error_msg(),
            ));
        }

        /** @var array<string, mixed> */
        return $content;
    }

    /**
     * Returns the content type header of a response.
     *
     * @return string Semi-colon separated join of content-type headers.
     */
    protected function getContentType(ResponseInterface $response): string
    {
        return implode(';', $response->getHeader('content-type'));
    }

    /**
     * Parses the response according to its content-type header.
     *
     * @return array<string, mixed> | string
     *
     * @throws UnexpectedValueException
     */
    protected function parseResponse(ResponseInterface $response): array | string
    {
        $content = (string) $response->getBody();
        $type = $this->getContentType($response);

        if (strpos($type, 'urlencoded') !== false) {
            parse_str($content, $parsed);

            /** @var array<string, string> */
            return $parsed;
        }

        // Attempt to parse the string as JSON regardless of content type,
        // since some providers use non-standard content types. Only throw an
        // exception if the JSON could not be parsed when it was expected to.
        try {
            return $this->parseJson($content);
        } catch (UnexpectedValueException $e) {
            if (strpos($type, 'json') !== false) {
                throw $e;
            }

            if ($response->getStatusCode() === 500) {
                throw new UnexpectedValueException(
                    'An OAuth server error was encountered that did not contain a JSON body',
                    0,
                    $e,
                );
            }

            return $content;
        }
    }

    /**
     * Checks a provider response for errors.
     *
     * @param mixed[] | string $data Parsed response data
     *
     * @throws IdentityProviderException
     */
    abstract protected function checkResponse(ResponseInterface $response, array | string $data): void;

    /**
     * Prepares an parsed access token response for a grant.
     *
     * Custom mapping of expiration, etc should be done here. Always call the
     * parent method when overloading this method.
     *
     * @param array<string, mixed> $result
     *
     * @return array<string, mixed>
     */
    protected function prepareAccessTokenResponse(array $result): array
    {
        if ($this->getAccessTokenResourceOwnerId() !== null) {
            $result['resource_owner_id'] = $this->getValueByKey(
                $result,
                $this->getAccessTokenResourceOwnerId(),
            );
        }

        return $result;
    }

    /**
     * Creates an access token from a response.
     *
     * The grant that was used to fetch the response can be used to provide
     * additional context.
     *
     * @param array<string, mixed> $response
     */
    protected function createAccessToken(array $response, AbstractGrant $grant): AccessTokenInterface
    {
        return new AccessToken($response);
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array<string, mixed> $response
     */
    abstract protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface;

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @throws ClientExceptionInterface
     * @throws IdentityProviderException
     * @throws UnexpectedValueException
     */
    public function getResourceOwner(AccessToken $token): ResourceOwnerInterface
    {
        $response = $this->fetchResourceOwnerDetails($token);

        return $this->createResourceOwner($response, $token);
    }

    /**
     * Requests resource owner details.
     *
     * @return array<string, mixed>
     *
     * @throws ClientExceptionInterface
     * @throws IdentityProviderException
     * @throws UnexpectedValueException
     */
    protected function fetchResourceOwnerDetails(AccessToken $token): array
    {
        $url = $this->getResourceOwnerDetailsUrl($token);
        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);

        return $this->getParsedResponse($request);
    }

    /**
     * Returns the default headers used by this provider.
     *
     * Typically this is used to set 'Accept' or 'Content-Type' headers.
     *
     * @return array<string, string | string[]>
     */
    protected function getDefaultHeaders(): array
    {
        return [];
    }

    /**
     * Returns the authorization headers used by this provider.
     *
     * Typically this is "Bearer" or "MAC". For more information see:
     * http://tools.ietf.org/html/rfc6749#section-7.1
     *
     * No default is provided, providers must overload this method to activate
     * authorization headers.
     *
     * @param AccessTokenInterface | string | null $token Either a string or an access token instance
     *
     * @return array<string, string | string[]>
     */
    protected function getAuthorizationHeaders(AccessTokenInterface | string | null $token = null): array
    {
        return [];
    }

    /**
     * Returns all headers used by this provider for a request.
     *
     * The request will be authenticated if an access token is provided.
     *
     * @param AccessTokenInterface | string | null $token object or string
     *
     * @return array<string, string | string[]>
     */
    public function getHeaders(AccessTokenInterface | string | null $token = null): array
    {
        if ($token) {
            return array_merge(
                $this->getDefaultHeaders(),
                $this->getAuthorizationHeaders($token),
            );
        }

        return $this->getDefaultHeaders();
    }
}
