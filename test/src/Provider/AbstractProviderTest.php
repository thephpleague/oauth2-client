<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Grant\Exception\InvalidGrantException;
use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\OptionProvider\PostAuthOptionProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Test\Provider\Fake as MockProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\ResourceOwnerAccessTokenInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use RuntimeException;
use UnexpectedValueException;
use stdClass;

use function json_encode;
use function parse_str;
use function parse_url;
use function preg_match;
use function str_contains;
use function time;
use function uniqid;

use const PHP_URL_QUERY;

class AbstractProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function getMockProvider(): MockProvider
    {
        return new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);
    }

    public function testGetOptionProvider(): void
    {
        $this->assertInstanceOf(
            PostAuthOptionProvider::class,
            $this->getMockProvider()->getOptionProvider(),
        );
    }

    public function testInvalidGrantString(): void
    {
        $this->expectException(InvalidGrantException::class);
        $this->getMockProvider()->getAccessToken('invalid_grant', ['invalid_parameter' => 'none']);
    }

    public function testInvalidGrantObject(): void
    {
        $this->expectException(InvalidGrantException::class);
        $grant = new stdClass();
        $this->getMockProvider()->getAccessToken($grant, ['invalid_parameter' => 'none']);
    }

    public function testMissingRequestFactory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No request factory set');

        new Fake();
    }

    public function testMissingStreamFactory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No stream factory set');

        new Fake(
            [],
            [
                'requestFactory' => new HttpFactory(),
            ],
        );
    }

    public function testMissingHttpClient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No http client set');

        new Fake(
            [],
            [
                'requestFactory' => new HttpFactory(),
                'streamFactory' => new HttpFactory(),
            ],
        );
    }

    public function testAuthorizationUrlStateParam(): void
    {
        $authUrl = $this->getMockProvider()->getAuthorizationUrl([
            'state' => 'XXX',
        ]);

        $this->assertTrue(str_contains($authUrl, 'state=XXX'));
    }

    /**
     * Tests https://github.com/thephpleague/oauth2-client/pull/485
     */
    public function testCustomAuthorizationUrlOptions(): void
    {
        $url = $this->getMockProvider()->getAuthorizationUrl([
            'foo' => 'BAR',
        ]);
        $query = parse_url($url, PHP_URL_QUERY);
        $this->assertNotEmpty($query);

        parse_str($query, $params);
        $this->assertArrayHasKey('foo', $params);
        $this->assertSame('BAR', $params['foo']);
    }

    /**
     * Tests https://github.com/thephpleague/oauth2-client/issues/134
     */
    public function testConstructorSetsProperties(): void
    {
        $options = [
            'clientId' => '1234',
            'clientSecret' => '4567',
            'redirectUri' => 'http://example.org/redirect',
        ];

        $mockProvider = new MockProvider($options, [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $this->assertSame($options['clientId'], $mockProvider->getClientId());
        $this->assertSame($options['clientSecret'], $mockProvider->getClientSecret());
        $this->assertSame($options['redirectUri'], $mockProvider->getRedirectUri());
    }

    public function testConstructorSetsGrantFactory(): void
    {
        $mockAdapter = Mockery::mock(GrantFactory::class);

        $mockProvider = new MockProvider([], [
            'grantFactory' => $mockAdapter,
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);
        $this->assertSame($mockAdapter, $mockProvider->getGrantFactory());
    }

    public function testConstructorSetsHttpAdapter(): void
    {
        $mockAdapter = Mockery::mock(ClientInterface::class);

        $mockProvider = new MockProvider([], [
            'httpClient' => $mockAdapter,
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);
        $this->assertSame($mockAdapter, $mockProvider->getHttpClient());
    }

    public function testConstructorSetsRequestFactory(): void
    {
        $mockAdapter = Mockery::mock(RequestFactoryInterface::class);

        $mockProvider = new MockProvider([], [
            'httpClient' => new Client(),
            'requestFactory' => $mockAdapter,
            'streamFactory' => new HttpFactory(),
        ]);
        $this->assertSame($mockAdapter, $mockProvider->getRequestFactory());
    }

    public function testConstructorSetsStreamFactory(): void
    {
        $mockAdapter = Mockery::mock(StreamFactoryInterface::class);

        $mockProvider = new MockProvider([], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => $mockAdapter,
        ]);
        $this->assertSame($mockAdapter, $mockProvider->getStreamFactory());
    }

    public function testSetRedirectHandler(): void
    {
        $testFunction = false;
        $state = false;

        $callback = function (string $url, AbstractProvider $provider) use (&$testFunction, &$state) {
            $testFunction = $url;
            $state = $provider->getState();

            throw new RuntimeException('Prevent test from exiting');
        };

        try {
            $this->getMockProvider()->authorize([], $callback);
        } catch (RuntimeException) {
            // We throw the exception from the callback to prevent the script from exiting.
        }

        $this->assertNotFalse($testFunction);
        $this->assertNotFalse($state);
    }

    #[DataProvider('userPropertyProvider')]
    public function testGetUserProperties(?string $name = null, ?string $email = null, ?int $id = null): void
    {
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

        $stream = Mockery::mock(StreamInterface::class);
        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn(json_encode([
                'id' => $id,
                'name' => $name,
                'email' => $email,
            ]));

        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);
        $response
            ->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn(['application/json']);

        $client = Mockery::spy(ClientInterface::class, [
            'sendRequest' => $response,
        ]);

        $provider->setHttpClient($client);

        /** @var MockProvider\User $user */
        $user = $provider->getResourceOwner($token);
        $url = $provider->getResourceOwnerDetailsUrl($token);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($name, $user->getUserScreenName());
        $this->assertEquals($email, $user->getUserEmail());

        $this->assertArrayHasKey('name', $user->toArray());
        $this->assertArrayHasKey('email', $user->toArray());

        $client
            ->shouldHaveReceived('sendRequest')
            ->once()
            ->withArgs(fn (RequestInterface $request) => $request->getMethod() === 'GET'
                    && $request->hasHeader('Authorization')
                    && (string) $request->getUri() === $url);
    }

    public function testGetUserPropertiesThrowsExceptionWhenNonJsonResponseIsReceived(): void
    {
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

        $stream = Mockery::mock(StreamInterface::class, [
            '__toString' => '<html><body>some unexpected response.</body></html>',
        ]);

        $response = Mockery::mock(ResponseInterface::class, [
            'getStatusCode' => 200,
            'getBody' => $stream,
        ]);
        $response
            ->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn(['text/html']);

        $client = Mockery::mock(ClientInterface::class, [
            'sendRequest' => $response,
        ]);

        $provider->setHttpClient($client);

        $this->expectException(UnexpectedValueException::class);

        $provider->getResourceOwner($token);
    }

    /**
     * @return array<string, array{0?: string, 1?: string, 2?: int}>
     */
    public static function userPropertyProvider(): array
    {
        return [
            'full response' => ['test', 'test@example.com', 1],
            'no response' => [],
        ];
    }

    public function testGetHeaders(): void
    {
        $provider = $this->getMockProvider();

        $this->assertEquals([], $provider->getHeaders());
        $this->assertEquals(['Authorization' => 'Bearer mock_token'], $provider->getHeaders('mock_token'));
        $this->assertEquals(['Authorization' => 'Bearer abc'], $provider->getHeaders('abc'));

        $token = new AccessToken(['access_token' => 'xyz', 'expires_in' => 3600]);
        $this->assertEquals(['Authorization' => 'Bearer xyz'], $provider->getHeaders($token));
    }

    public function testScopesOverloadedDuringAuthorize(): void
    {
        $provider = $this->getMockProvider();

        $url = $provider->getAuthorizationUrl();

        parse_str((string) parse_url($url, PHP_URL_QUERY), $qs);

        $this->assertArrayHasKey('scope', $qs);
        $this->assertSame('test', $qs['scope']);

        $url = $provider->getAuthorizationUrl(['scope' => ['foo', 'bar']]);

        parse_str((string) parse_url($url, PHP_URL_QUERY), $qs);

        $this->assertArrayHasKey('scope', $qs);
        $this->assertSame('foo,bar', $qs['scope']);
    }

    public function testAuthorizationStateIsRandom(): void
    {
        $last = null;
        $provider = $this->getMockProvider();

        for ($i = 0; $i < 100; $i++) {
            // Repeat the test multiple times to verify state changes
            $url = $provider->getAuthorizationUrl();

            parse_str((string) parse_url($url, PHP_URL_QUERY), $qs);
            $this->assertIsString($qs['state'] ?? null);
            $this->assertTrue(preg_match('/^[a-zA-Z0-9\/+]{32}$/', $qs['state']) === 1);
            $this->assertNotSame($qs['state'], $last);

            $last = $qs['state'];
        }
    }

    public function testSetGetPkceCode(): void
    {
        $pkceCode = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';

        $provider = $this->getMockProvider();
        $this->assertEquals($provider, $provider->setPkceCode($pkceCode));
        $this->assertEquals($pkceCode, $provider->getPkceCode());
    }

    #[DataProvider('pkceMethodProvider')]
    public function testPkceMethod(string $pkceMethod, string $pkceCode, string $expectedChallenge): void
    {
        $provider = $this->getMockProvider();
        $provider->setPkceMethod($pkceMethod);
        $provider->setFixedPkceCode($pkceCode);

        $url = $provider->getAuthorizationUrl();
        $this->assertSame($pkceCode, $provider->getPkceCode());

        parse_str((string) parse_url($url, PHP_URL_QUERY), $qs);
        $this->assertArrayHasKey('code_challenge', $qs);
        $this->assertArrayHasKey('code_challenge_method', $qs);
        $this->assertSame($pkceMethod, $qs['code_challenge_method']);
        $this->assertSame($expectedChallenge, $qs['code_challenge']);

        // Simulate re-initialization of provider after authorization request
        $provider = $this->getMockProvider();

        $rawResponse = ['access_token' => 'okay', 'expires' => time() + 3600, 'resource_owner_id' => 3];
        $stream = Mockery::mock(StreamInterface::class);
        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn(json_encode($rawResponse));

        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);
        $response
            ->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn(['application/json']);

        $client = Mockery::spy(ClientInterface::class, [
            'sendRequest' => $response,
        ]);
        $provider->setHttpClient($client);

        // restore $pkceCode (normally done by client from session)
        $provider->setPkceCode($pkceCode);

        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $client
            ->shouldHaveReceived('sendRequest')
            ->once()
            ->withArgs(function (RequestInterface $request) use ($pkceCode) {
                parse_str((string) $request->getBody(), $body);

                return ($body['code_verifier'] ?? null) === $pkceCode;
            });
    }

    /**
     * @return array<list<string>>
     */
    public static function pkceMethodProvider(): array
    {
        return [
            [
                AbstractProvider::PKCE_METHOD_S256,
                '1234567890123456789012345678901234567890',
                'pOvdVBRUuEzGcMnx9VCLr2f_0_5ZuIMmeAh4H5kqCx0',
            ],
            [
                AbstractProvider::PKCE_METHOD_PLAIN,
                '1234567890123456789012345678901234567890',
                '1234567890123456789012345678901234567890',
            ],
        ];
    }

    public function testInvalidPkceMethod(): void
    {
        $provider = $this->getMockProvider();
        $provider->setPkceMethod('non-existing');

        $this->expectExceptionMessage('Unknown PKCE method "non-existing".');
        $provider->getAuthorizationUrl();
    }

    public function testPkceCodeIsRandom(): void
    {
        $last = null;
        $provider = $this->getMockProvider();
        $provider->setPkceMethod('S256');

        for ($i = 0; $i < 100; $i++) {
            // Repeat the test multiple times to verify code_challenge changes
            $url = $provider->getAuthorizationUrl();

            parse_str((string) parse_url($url, PHP_URL_QUERY), $qs);
            $this->assertIsString($qs['code_challenge'] ?? null);
            $this->assertTrue(preg_match('/^[a-zA-Z0-9-_]{43}$/', $qs['code_challenge']) === 1);
            $this->assertNotSame($qs['code_challenge'], $last);
            $last = $qs['code_challenge'];
        }
    }

    public function testPkceMethodIsDisabledByDefault(): void
    {
        $provider = $this->getAbstractProviderMock();
        $provider->shouldAllowMockingProtectedMethods();
        /** @phpstan-ignore method.protected */
        $pkceMethod = $provider->getPkceMethod();
        $this->assertNull($pkceMethod);
    }

    public function testErrorResponsesCanBeCustomizedAtTheProvider(): void
    {
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $error = ['error' => 'Foo error', 'code' => 1337];
        $errorJson = json_encode($error);

        $stream = Mockery::mock(StreamInterface::class);
        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn($errorJson);

        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);
        $response
            ->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn(['application/json']);

        $client = Mockery::spy(ClientInterface::class, [
            'sendRequest' => $response,
        ]);

        $provider->setHttpClient($client);

        $errorMessage = '';
        $errorCode = 0;
        $errorBody = false;

        try {
            $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        } catch (IdentityProviderException $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            $errorBody = $e->getResponseBody();
        }

        $method = $provider->getAccessTokenMethod();
        $url = $provider->getBaseAccessTokenUrl([]);

        $this->assertEquals($error['error'], $errorMessage);
        $this->assertEquals($error['code'], $errorCode);
        $this->assertEquals($error, $errorBody);

        $client
            ->shouldHaveReceived('sendRequest')
            ->once()
            ->withArgs(fn (RequestInterface $request) => $request->getMethod() === $method
                    && (string) $request->getUri() === $url);
    }

    public function testClientErrorTriggersProviderException(): void
    {
        $this->expectException(IdentityProviderException::class);
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $stream = Mockery::mock(StreamInterface::class, [
            '__toString' => '{"error":"Foo error","code":1337}',
        ]);

        $request = Mockery::mock(RequestInterface::class);

        $response = Mockery::mock(ResponseInterface::class, [
            'getStatusCode' => 400,
            'getBody' => $stream,
        ]);
        $response
            ->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn(['application/json']);

        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('sendRequest')
            ->andThrow(new BadResponseException('test exception', $request, $response));

        $provider->setHttpClient($client);
        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testGetResponse(): void
    {
        $provider = new MockProvider([], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('sendRequest')
            ->with($request)
            ->andReturn($response);

        $provider->setHttpClient($client);
        $output = $provider->getResponse($request);

        $this->assertSame($output, $response);
    }

    public function testAuthenticatedRequestAndResponse(): void
    {
        $provider = new MockProvider([], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);
        $request = $provider->getAuthenticatedRequest('get', 'https://api.example.com/v1/test', $token);

        $stream = Mockery::mock(StreamInterface::class, [
            '__toString' => '{"example":"response"}',
        ]);

        $response = Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]);
        $response
            ->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn(['application/json']);

        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('sendRequest')
            ->with($request)
            ->andReturn($response);

        $provider->setHttpClient($client);
        $result = $provider->getParsedResponse($request);

        $this->assertSame(['example' => 'response'], $result);
        $this->assertSame('GET', $request->getMethod());

        // Authorization header should contain the token
        $header = $request->getHeader('Authorization');
        $this->assertContains('Bearer abc', $header);
    }

    /**
     * @return array<list<string>>
     */
    public static function getAccessTokenMethodProvider(): array
    {
        return [
            ['GET'],
            ['POST'],
        ];
    }

    #[DataProvider('getAccessTokenMethodProvider')]
    public function testGetAccessToken(string $method): void
    {
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $provider->setAccessTokenMethod($method);

        $rawResponse = ['access_token' => 'okay', 'expires' => time() + 3600, 'resource_owner_id' => 3];

        $grant = Mockery::mock(AbstractGrant::class);
        $grant
            ->shouldReceive('prepareRequestParameters')
            ->once()
            ->with(
                ['client_id' => 'mock_client_id', 'client_secret' => 'mock_secret', 'redirect_uri' => 'none'],
                ['code' => 'mock_authorization_code'],
            )
            ->andReturn([]);

        $stream = Mockery::mock(StreamInterface::class);
        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn(json_encode($rawResponse));

        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);
        $response
            ->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn(['application/json']);

        $client = Mockery::spy(ClientInterface::class, [
            'sendRequest' => $response,
        ]);

        $provider->setHttpClient($client);
        $token = $provider->getAccessToken($grant, ['code' => 'mock_authorization_code']);

        $this->assertInstanceOf(ResourceOwnerAccessTokenInterface::class, $token);

        $this->assertSame($rawResponse['resource_owner_id'], $token->getResourceOwnerId());
        $this->assertSame($rawResponse['access_token'], $token->getToken());
        $this->assertSame($rawResponse['expires'], $token->getExpires());

        $client
            ->shouldHaveReceived('sendRequest')
            ->once()
            ->withArgs(fn (RequestInterface $request) => $request->getMethod() === $provider->getAccessTokenMethod()
                    && (string) $request->getUri() === $provider->getBaseAccessTokenUrl([]));
    }

    #[DataProvider('getAccessTokenMethodProvider')]
    public function testGetAccessTokenWithScope(string $method): void
    {
        $provider = $this->getMockProvider();
        $provider->setAccessTokenMethod($method);

        $rawResponse = ['access_token' => 'okay', 'expires' => time() + 3600, 'resource_owner_id' => 3];

        $grant = Mockery::mock(AbstractGrant::class);
        $grant
            ->shouldReceive('prepareRequestParameters')
            ->once()
            ->with(
                ['client_id' => 'mock_client_id', 'client_secret' => 'mock_secret', 'redirect_uri' => 'none'],
                ['code' => 'mock_authorization_code', 'scope' => 'foo,bar'],
            )
            ->andReturn([]);

        $stream = Mockery::mock(StreamInterface::class);
        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn(json_encode($rawResponse));

        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);
        $response
            ->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn(['application/json']);

        $client = Mockery::spy(ClientInterface::class, [
            'sendRequest' => $response,
        ]);

        $provider->setHttpClient($client);
        $token = $provider->getAccessToken($grant, ['code' => 'mock_authorization_code', 'scope' => ['foo', 'bar']]);

        $this->assertInstanceOf(ResourceOwnerAccessTokenInterface::class, $token);

        $this->assertSame($rawResponse['resource_owner_id'], $token->getResourceOwnerId());
        $this->assertSame($rawResponse['access_token'], $token->getToken());
        $this->assertSame($rawResponse['expires'], $token->getExpires());

        $client
            ->shouldHaveReceived('sendRequest')
            ->once()
            ->withArgs(fn (RequestInterface $request) => $request->getMethod() === $provider->getAccessTokenMethod()
                    && (string) $request->getUri() === $provider->getBaseAccessTokenUrl([]));
    }

    public function testGetAccessTokenWithNonJsonResponse(): void
    {
        $provider = $this->getMockProvider();

        $stream = Mockery::mock(StreamInterface::class, [
            '__toString' => '',
        ]);

        $response = Mockery::mock(ResponseInterface::class, [
            'getStatusCode' => 200,
            'getBody' => $stream,
        ]);
        $response
            ->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn(['text/plain']);

        $client = Mockery::mock(ClientInterface::class, [
            'sendRequest' => $response,
        ]);

        $provider->setHttpClient($client);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid response received from Authorization Server. Expected JSON.');
        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    private function getMethod(string $name): ReflectionMethod
    {
        $class = new ReflectionClass(AbstractProvider::class);

        return $class->getMethod($name);
    }

    /**
     * @return array<array{body: string, type: string, parsed: mixed, statusCode?: int}>
     */
    public static function parseResponseProvider(): array
    {
        return [
            [
                'body' => '{"a": 1}',
                'type' => 'application/json',
                'parsed' => ['a' => 1],
            ],
            [
                'body' => 'string',
                'type' => 'unknown',
                'parsed' => 'string',
            ],
            [
                'body' => 'a=1&b=2',
                'type' => 'application/x-www-form-urlencoded',
                'parsed' => ['a' => 1, 'b' => 2],
            ],
        ];
    }

    #[DataProvider('parseResponseProvider')]
    public function testParseResponse(string $body, string $type, mixed $parsed, int $statusCode = 200): void
    {
        $stream = Mockery::mock(StreamInterface::class, [
            '__toString' => $body,
        ]);

        $response = Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
            'getStatusCode' => $statusCode,
        ]);
        $response
            ->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn([$type]);

        $method = $this->getMethod('parseResponse');
        $result = $method->invoke($this->getMockProvider(), $response);

        $this->assertEquals($parsed, $result);
    }

    public function testParseResponseJsonFailure(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->testParseResponse('{a: 1}', 'application/json', null);
    }

    public function testParseResponseNonJsonFailure(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->testParseResponse('<xml></xml>', 'application/xml', null, 500);
    }

    /**
     * @return array<list<string>>
     */
    public static function getAppendQueryProvider(): array
    {
        return [
            ['test.com/?a=1', 'test.com/', '?a=1'],
            ['test.com/?a=1', 'test.com/', '&a=1'],
            ['test.com/?a=1', 'test.com/', 'a=1'],
            ['test.com/?a=1', 'test.com/?a=1', '?'],
            ['test.com/?a=1', 'test.com/?a=1', '&'],
            ['test.com/?a=1&b=2', 'test.com/?a=1', '&b=2'],
            ['test.com/?a=1&b=2', 'test.com/?a=1', 'b=2'],
            ['test.com/?a=1&b=2', 'test.com/?a=1', '?b=2'],
            ['test.com/?a=1&b=1&b=2', 'test.com/?a=1&b=1', 'b=2'],
            ['test.com/?a=1&b=2&b=2', 'test.com/?a=1&b=2', 'b=2'],
        ];
    }

    #[DataProvider('getAppendQueryProvider')]
    public function testAppendQuery(string $expected, string $url, string $query): void
    {
        $method = $this->getMethod('appendQuery');
        $this->assertEquals($expected, $method->invoke($this->getMockProvider(), $url, $query));
    }

    protected function getAbstractProviderMock(): AbstractProvider & MockInterface
    {
        return Mockery::mock(AbstractProvider::class)->makePartial();
    }

    public function testDefaultAccessTokenMethod(): void
    {
        $provider = $this->getAbstractProviderMock();
        $provider->shouldAllowMockingProtectedMethods();

        /** @phpstan-ignore method.protected */
        $method = $provider->getAccessTokenMethod();

        $expectedMethod = 'POST';
        $this->assertEquals($expectedMethod, $method);
    }

    public function testDefaultPrepareAccessTokenResponse(): void
    {
        $provider = Mockery::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class)->makePartial();
        $provider->shouldAllowMockingProtectedMethods();

        $result = ['user_id' => uniqid()];

        /** @phpstan-ignore method.protected */
        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertArrayHasKey('resource_owner_id', $newResult);
        $this->assertEquals($result['user_id'], $newResult['resource_owner_id']);
    }

    public function testGuardedProperties(): void
    {
        $options = [
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'skipMeDuringMassAssignment' => 'bar',
            'guarded' => 'foo',
        ];

        $provider = new Fake\ProviderWithGuardedProperties($options, [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $this->assertNotEquals(
            $options['skipMeDuringMassAssignment'],
            $provider->getSkipMeDuringMassAssignment(),
        );

        $this->assertNotEquals(
            $options['guarded'],
            $provider->getGuarded(),
        );
    }

    public function testPrepareAccessTokenResponseWithDotNotation(): void
    {
        $provider = Mockery::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class)->makePartial();
        $provider->shouldAllowMockingProtectedMethods();
        $provider
            ->shouldReceive('getAccessTokenResourceOwnerId')
            ->andReturn('user.id');

        $result = ['user' => ['id' => uniqid()]];

        /** @phpstan-ignore method.protected */
        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertArrayHasKey('resource_owner_id', $newResult);
        $this->assertEquals($result['user']['id'], $newResult['resource_owner_id']);
    }

    public function testPrepareAccessTokenResponseWithInvalidKeyPath(): void
    {
        $provider = Mockery::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class)->makePartial();
        $provider->shouldAllowMockingProtectedMethods();
        $provider
            ->shouldReceive('getAccessTokenResourceOwnerId')
            ->andReturn('user.name');

        $result = ['user' => ['id' => uniqid()]];

        /** @phpstan-ignore method.protected */
        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertFalse(isset($newResult['resource_owner_id']));
    }

    public function testDefaultAuthorizationHeaders(): void
    {
        $provider = $this->getAbstractProviderMock();
        $provider->shouldAllowMockingProtectedMethods();

        /** @phpstan-ignore method.protected */
        $headers = $provider->getAuthorizationHeaders();

        $this->assertEquals([], $headers);
    }

    /**
     * This test helps show the fatal errors occurring as a result of incompatible
     * method signatures after the 2.4.0 release.
     *
     * @link https://github.com/thephpleague/oauth2-client/issues/752
     */
    public function testExtendedProviderDoesNotErrorWhenUsingAccessTokenAsTheTypeHint(): void
    {
        $token = new AccessToken([
            'access_token' => 'mock_access_token',
            'refresh_token' => 'mock_refresh_token',
            'expires' => time(),
            'resource_owner_id' => 'mock_resource_owner_id',
        ]);

        $provider = new Fake\ProviderWithAccessTokenHints([
            'urlAuthorize' => 'https://example.com/authorize',
            'urlAccessToken' => 'https://example.com/accessToken',
            'urlResourceOwnerDetails' => 'https://api.example.com/owner',
        ], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $reflectedProvider = new ReflectionObject($provider);
        $getTokenId = $reflectedProvider->getMethod('getTokenId');

        $url = $provider->getResourceOwnerDetailsUrl($token);
        $tokenId = $getTokenId->invoke($provider, $token);

        $this->assertSame('https://api.example.com/owner/mock_resource_owner_id', $url);
        $this->assertSame('fake_token_id', $tokenId);
    }
}
