<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\OptionProvider\PostAuthOptionProvider;
use Mockery;
use ReflectionClass;
use UnexpectedValueException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\ClientInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Test\Provider\Fake as MockProvider;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Grant\Exception\InvalidGrantException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Tool\RequestFactory;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class AbstractProviderTest extends TestCase
{
    protected function getMockProvider()
    {
        return new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function testGetOptionProvider()
    {
        $this->assertInstanceOf(
            PostAuthOptionProvider::class,
            $this->getMockProvider()->getOptionProvider()
        );
    }

    public function testInvalidGrantString()
    {
        $this->expectException(InvalidGrantException::class);
        $this->getMockProvider()->getAccessToken('invalid_grant', ['invalid_parameter' => 'none']);
    }

    public function testInvalidGrantObject()
    {
        $this->expectException(InvalidGrantException::class);
        $grant = new \StdClass();
        $this->getMockProvider()->getAccessToken($grant, ['invalid_parameter' => 'none']);
    }

    public function testAuthorizationUrlStateParam()
    {
        $authUrl = $this->getMockProvider()->getAuthorizationUrl([
            'state' => 'XXX',
        ]);

        $this->assertTrue(strpos($authUrl, 'state=XXX') !== false);
    }

    /**
     * Tests https://github.com/thephpleague/oauth2-client/pull/485
     */
    public function testCustomAuthorizationUrlOptions()
    {
        $url = $this->getMockProvider()->getAuthorizationUrl([
            'foo' => 'BAR'
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
    public function testConstructorSetsProperties()
    {
        $options = [
            'clientId' => '1234',
            'clientSecret' => '4567',
            'redirectUri' => 'http://example.org/redirect'
        ];

        $mockProvider = new MockProvider($options);

        $this->assertSame($options['clientId'], $mockProvider->getClientId());
        $this->assertSame($options['clientSecret'], $mockProvider->getClientSecret());
        $this->assertSame($options['redirectUri'], $mockProvider->getRedirectUri());
    }

    public function testConstructorSetsClientOptions()
    {
        $timeout = rand(100, 900);

        $mockProvider = new MockProvider(compact('timeout'));

        $config = $mockProvider->getHttpClient()->getConfig();

        $this->assertArrayHasKey('timeout', $config);
        $this->assertEquals($timeout, $config['timeout']);
    }

    public function testCanSetAProxy()
    {
        $proxy = '192.168.0.1:8888';

        $mockProvider = new MockProvider(['proxy' => $proxy]);

        $config = $mockProvider->getHttpClient()->getConfig();

        $this->assertArrayHasKey('proxy', $config);
        $this->assertEquals($proxy, $config['proxy']);
    }

    public function testCannotDisableVerifyIfNoProxy()
    {
        $mockProvider = new MockProvider(['verify' => false]);

        $config = $mockProvider->getHttpClient()->getConfig();

        $this->assertArrayHasKey('verify', $config);
        $this->assertTrue($config['verify']);
    }

    public function testCanDisableVerificationIfThereIsAProxy()
    {
        $mockProvider = new MockProvider(['proxy' => '192.168.0.1:8888', 'verify' => false]);

        $config = $mockProvider->getHttpClient()->getConfig();

        $this->assertArrayHasKey('verify', $config);
        $this->assertFalse($config['verify']);
    }

    public function testConstructorSetsGrantFactory()
    {
        $mockAdapter = Mockery::mock(GrantFactory::class);

        $mockProvider = new MockProvider([], ['grantFactory' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getGrantFactory());
    }

    public function testConstructorSetsHttpAdapter()
    {
        $mockAdapter = Mockery::mock(ClientInterface::class);

        $mockProvider = new MockProvider([], ['httpClient' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getHttpClient());
    }

    public function testConstructorSetsRequestFactory()
    {
        $mockAdapter = Mockery::mock(RequestFactory::class);

        $mockProvider = new MockProvider([], ['requestFactory' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getRequestFactory());
    }

    public function testSetRedirectHandler()
    {
        $testFunction = false;
        $state = false;

        $callback = function ($url, $provider) use (&$testFunction, &$state) {
            $testFunction = $url;
            $state = $provider->getState();
        };

        $this->getMockProvider()->authorize([], $callback);

        $this->assertNotFalse($testFunction);
        $this->assertNotFalse($state);
    }

    /**
     * @dataProvider userPropertyProvider
     */
    public function testGetUserProperties($name = null, $email = null, $id = null)
    {
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
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
            ->andReturn('application/json');


        $client = Mockery::spy(ClientInterface::class, [
            'send' => $response,
        ]);

        $provider->setHttpClient($client);
        $user = $provider->getResourceOwner($token);
        $url = $provider->getResourceOwnerDetailsUrl($token);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($name, $user->getUserScreenName());
        $this->assertEquals($email, $user->getUserEmail());

        $this->assertArrayHasKey('name', $user->toArray());
        $this->assertArrayHasKey('email', $user->toArray());

        $client
            ->shouldHaveReceived('send')
            ->once()
            ->withArgs(function ($request) use ($url) {
                return $request->getMethod() === 'GET'
                    && $request->hasHeader('Authorization')
                    && (string) $request->getUri() === $url;
            });
    }

    public function testGetUserPropertiesThrowsExceptionWhenNonJsonResponseIsReceived()
    {
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
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
            ->andReturn('text/html');

        $client = Mockery::mock(ClientInterface::class, [
            'send' => $response,
        ]);

        $provider->setHttpClient($client);

        $this->expectException(UnexpectedValueException::class);

        $user = $provider->getResourceOwner($token);
    }

    public function userPropertyProvider()
    {
        return [
            'full response'  => ['test', 'test@example.com', 1],
            'no response'    => [],
        ];
    }

    public function testGetHeaders()
    {
        $provider = $this->getMockProvider();

        $this->assertEquals([], $provider->getHeaders());
        $this->assertEquals(['Authorization' => 'Bearer mock_token'], $provider->getHeaders('mock_token'));
        $this->assertEquals(['Authorization' => 'Bearer abc'], $provider->getHeaders('abc'));

        $token = new AccessToken(['access_token' => 'xyz', 'expires_in' => 3600]);
        $this->assertEquals(['Authorization' => 'Bearer xyz'], $provider->getHeaders($token));
    }

    public function testScopesOverloadedDuringAuthorize()
    {
        $provider = $this->getMockProvider();

        $url = $provider->getAuthorizationUrl();

        parse_str(parse_url($url, PHP_URL_QUERY), $qs);

        $this->assertArrayHasKey('scope', $qs);
        $this->assertSame('test', $qs['scope']);

        $url = $provider->getAuthorizationUrl(['scope' => ['foo', 'bar']]);

        parse_str(parse_url($url, PHP_URL_QUERY), $qs);

        $this->assertArrayHasKey('scope', $qs);
        $this->assertSame('foo,bar', $qs['scope']);
    }

    public function testAuthorizationStateIsRandom()
    {
        $last = null;
        $provider = $this->getMockProvider();

        for ($i = 0; $i < 100; $i++) {
            // Repeat the test multiple times to verify state changes
            $url = $provider->getAuthorizationUrl();

            parse_str(parse_url($url, PHP_URL_QUERY), $qs);

            $this->assertTrue(1 === preg_match('/^[a-zA-Z0-9\/+]{32}$/', $qs['state']));
            $this->assertNotSame($qs['state'], $last);

            $last = $qs['state'];
        }
    }

    public function testErrorResponsesCanBeCustomizedAtTheProvider()
    {
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);

        $error = ["error" => "Foo error", "code" => 1337];
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
            ->andReturn('application/json');

        $client = Mockery::spy(ClientInterface::class, [
            'send' => $response,
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
            ->shouldHaveReceived('send')
            ->once()
            ->withArgs(function ($request) use ($method, $url) {
                return $request->getMethod() === $method
                    && (string) $request->getUri() === $url;
            });
    }

    public function testClientErrorTriggersProviderException()
    {
        $this->expectException(IdentityProviderException::class);
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
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
            ->andReturn('application/json');

        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('send')
            ->andThrow(new BadResponseException('test exception', $request, $response));

        $provider->setHttpClient($client);
        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testGetResponse()
    {
        $provider = new MockProvider();

        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('send')
            ->with($request)
            ->andReturn($response);

        $provider->setHttpClient($client);
        $output = $provider->getResponse($request);

        $this->assertSame($output, $response);
    }

    public function testAuthenticatedRequestAndResponse()
    {
        $provider = new MockProvider();

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
            ->andReturn('application/json');

        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('send')
            ->with($request)
            ->andReturn($response);

        $provider->setHttpClient($client);
        $result = $provider->getParsedResponse($request);

        $this->assertSame(['example' => 'response'], $result);

        $this->assertInstanceOf(RequestInterface::class, $request);

        // Authorization header should contain the token
        $header = $request->getHeader('Authorization');
        $this->assertContains('Bearer abc', $header);
    }

    public function getAccessTokenMethodProvider()
    {
        return [
            ['GET'],
            ['POST'],
        ];
    }

    /**
     * @dataProvider getAccessTokenMethodProvider
     */
    public function testGetAccessToken($method)
    {
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);

        $provider->setAccessTokenMethod($method);

        $raw_response = ['access_token' => 'okay', 'expires' => time() + 3600, 'resource_owner_id' => 3];

        $grant = Mockery::mock(AbstractGrant::class);
        $grant
            ->shouldReceive('prepareRequestParameters')
            ->once()
            ->with(
                ['client_id' => 'mock_client_id', 'client_secret' => 'mock_secret', 'redirect_uri' => 'none'],
                ['code' => 'mock_authorization_code']
            )
            ->andReturn([]);

        $stream = Mockery::mock(StreamInterface::class);
        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn(json_encode($raw_response));

        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);
        $response
            ->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn('application/json');

        $client = Mockery::spy(ClientInterface::class, [
            'send' => $response,
        ]);

        $provider->setHttpClient($client);
        $token = $provider->getAccessToken($grant, ['code' => 'mock_authorization_code']);

        $this->assertInstanceOf(AccessTokenInterface::class, $token);

        $this->assertSame($raw_response['resource_owner_id'], $token->getResourceOwnerId());
        $this->assertSame($raw_response['access_token'], $token->getToken());
        $this->assertSame($raw_response['expires'], $token->getExpires());

        $client
            ->shouldHaveReceived('send')
            ->once()
            ->withArgs(function ($request) use ($provider) {
                return $request->getMethod() === $provider->getAccessTokenMethod()
                    && (string) $request->getUri() === $provider->getBaseAccessTokenUrl([]);
            });
    }

    public function testGetAccessTokenWithNonJsonResponse()
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
            ->andReturn('text/plain');

        $client = Mockery::mock(ClientInterface::class, [
            'send' => $response,
        ]);

        $provider->setHttpClient($client);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid response received from Authorization Server. Expected JSON.');
        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    private function getMethod($class, $name)
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);

        $method->setAccessible(true);
        return $method;
    }

    public function parseResponseProvider()
    {
        return [
            [
                'body'    => '{"a": 1}',
                'type'    => 'application/json',
                'parsed'  => ['a' => 1]
            ],
            [
                'body'    => 'string',
                'type'    => 'unknown',
                'parsed'  => 'string'
            ],
            [
                'body'    => 'a=1&b=2',
                'type'    => 'application/x-www-form-urlencoded',
                'parsed'  => ['a' => 1, 'b' => 2]
            ],
        ];
    }

    /**
     * @dataProvider parseResponseProvider
     */
    public function testParseResponse($body, $type, $parsed, $statusCode = 200)
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
            ->andReturn($type);

        $method = $this->getMethod(AbstractProvider::class, 'parseResponse');
        $result = $method->invoke($this->getMockProvider(), $response);

        $this->assertEquals($parsed, $result);
    }

    public function testParseResponseJsonFailure()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->testParseResponse('{a: 1}', 'application/json', null);
    }

    public function testParseResponseNonJsonFailure()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->testParseResponse('<xml></xml>', 'application/xml', null, 500);
    }

    public function getAppendQueryProvider()
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

    /**
     * @dataProvider getAppendQueryProvider
     */
    public function testAppendQuery($expected, $url, $query)
    {
        $method = $this->getMethod(AbstractProvider::class, 'appendQuery');
        $this->assertEquals($expected, $method->invoke($this->getMockProvider(), $url, $query));
    }

    protected function getAbstractProviderMock()
    {
        return Mockery::mock(AbstractProvider::class)->makePartial();
    }

    public function testDefaultAccessTokenMethod()
    {
        $provider = $this->getAbstractProviderMock();

        $method = $provider->getAccessTokenMethod();

        $expectedMethod = 'POST';
        $this->assertEquals($expectedMethod, $method);
    }

    public function testDefaultPrepareAccessTokenResponse()
    {
        $provider = Mockery::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class)->makePartial();

        $result = ['user_id' => uniqid()];
        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertArrayHasKey('resource_owner_id', $newResult);
        $this->assertEquals($result['user_id'], $newResult['resource_owner_id']);
    }

    public function testGuardedProperties()
    {
        $options = [
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'skipMeDuringMassAssignment' => 'bar',
            'guarded' => 'foo'
        ];

        $provider = new Fake\ProviderWithGuardedProperties($options);

        $this->assertNotEquals(
            $options['skipMeDuringMassAssignment'],
            $provider->getSkipMeDuringMassAssignment()
        );

        $this->assertNotEquals(
            $options['guarded'],
            $provider->getGuarded()
        );
    }

    public function testPrepareAccessTokenResponseWithDotNotation()
    {
        $provider = Mockery::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class)->makePartial();
        $provider->shouldAllowMockingProtectedMethods();
        $provider
            ->shouldReceive('getAccessTokenResourceOwnerId')
            ->andReturn('user.id');

        $result = ['user' => ['id' => uniqid()]];
        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertArrayHasKey('resource_owner_id', $newResult);
        $this->assertEquals($result['user']['id'], $newResult['resource_owner_id']);
    }

    public function testPrepareAccessTokenResponseWithInvalidKeyType()
    {
        $provider = Mockery::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class)->makePartial();
        $provider->shouldAllowMockingProtectedMethods();
        $provider
            ->shouldReceive('getAccessTokenResourceOwnerId')
            ->andReturn(new \stdClass());

        $result = ['user_id' => uniqid()];
        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertFalse(isset($newResult['resource_owner_id']));
    }

    public function testPrepareAccessTokenResponseWithInvalidKeyPath()
    {
        $provider = Mockery::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class)->makePartial();
        $provider->shouldAllowMockingProtectedMethods();
        $provider
            ->shouldReceive('getAccessTokenResourceOwnerId')
            ->andReturn('user.name');

        $result = ['user' => ['id' => uniqid()]];
        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertFalse(isset($newResult['resource_owner_id']));
    }

    public function testDefaultAuthorizationHeaders()
    {
        $provider = $this->getAbstractProviderMock();

        $headers = $provider->getAuthorizationHeaders();

        $this->assertEquals([], $headers);
    }

    /**
     * This test helps show the fatal errors occurring as a result of incompatible
     * method signatures after the 2.4.0 release.
     *
     * @link https://github.com/thephpleague/oauth2-client/issues/752
     */
    public function testExtendedProviderDoesNotErrorWhenUsingAccessTokenAsTheTypeHint()
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
        ]);

        $reflectedProvider = new \ReflectionObject($provider);
        $getTokenId = $reflectedProvider->getMethod('getTokenId');
        $getTokenId->setAccessible(true);

        $url = $provider->getResourceOwnerDetailsUrl($token);
        $tokenId = $getTokenId->invoke($provider, $token);

        $this->assertSame('https://api.example.com/owner/mock_resource_owner_id', $url);
        $this->assertSame('fake_token_id', $tokenId);
    }
}
