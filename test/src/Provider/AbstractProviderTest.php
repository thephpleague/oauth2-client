<?php

namespace League\OAuth2\Client\Test\Provider;

use UnexpectedValueException;
use Eloquent\Liberator\Liberator;
use Eloquent\Phony\Phpunit\Phony;
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
    /**
     * @var AbstractProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function testInvalidGrantString()
    {
        $this->expectException(InvalidGrantException::class);
        $this->provider->getAccessToken('invalid_grant', ['invalid_parameter' => 'none']);
    }

    public function testInvalidGrantObject()
    {
        $this->expectException(InvalidGrantException::class);
        $grant = new \StdClass();
        $this->provider->getAccessToken($grant, ['invalid_parameter' => 'none']);
    }

    public function testAuthorizationUrlStateParam()
    {
        $this->assertContains('state=XXX', $this->provider->getAuthorizationUrl([
            'state' => 'XXX'
        ]));
    }

    /**
     * Tests https://github.com/thephpleague/oauth2-client/pull/485
     */
    public function testCustomAuthorizationUrlOptions()
    {
        $url = $this->provider->getAuthorizationUrl([
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

        foreach ($options as $key => $value) {
            $this->assertAttributeEquals($value, $key, $mockProvider);
        }
    }

    public function testConstructorSetsClientOptions()
    {
        $timeout = rand(100, 900);

        $mockProvider = new MockProvider(compact('timeout'));

        $config = $mockProvider->getHttpClient()->getConfig();

        $this->assertContains('timeout', $config);
        $this->assertEquals($timeout, $config['timeout']);
    }

    public function testCanSetAProxy()
    {
        $proxy = '192.168.0.1:8888';

        $mockProvider = new MockProvider(['proxy' => $proxy]);

        $config = $mockProvider->getHttpClient()->getConfig();

        $this->assertContains('proxy', $config);
        $this->assertEquals($proxy, $config['proxy']);
    }

    public function testCannotDisableVerifyIfNoProxy()
    {
        $mockProvider = new MockProvider(['verify' => false]);

        $config = $mockProvider->getHttpClient()->getConfig();

        $this->assertContains('verify', $config);
        $this->assertTrue($config['verify']);
    }

    public function testCanDisableVerificationIfThereIsAProxy()
    {
        $mockProvider = new MockProvider(['proxy' => '192.168.0.1:8888', 'verify' => false]);

        $config = $mockProvider->getHttpClient()->getConfig();

        $this->assertContains('verify', $config);
        $this->assertFalse($config['verify']);
    }

    public function testConstructorSetsGrantFactory()
    {
        $mockAdapter = Phony::mock(GrantFactory::class)->get();

        $mockProvider = new MockProvider([], ['grantFactory' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getGrantFactory());
    }

    public function testConstructorSetsHttpAdapter()
    {
        $mockAdapter = Phony::mock(ClientInterface::class)->get();

        $mockProvider = new MockProvider([], ['httpClient' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getHttpClient());
    }

    public function testConstructorSetsRequestFactory()
    {
        $mockAdapter = Phony::mock(RequestFactory::class)->get();

        $mockProvider = new MockProvider([], ['requestFactory' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getRequestFactory());
    }

    public function testSetRedirectHandler()
    {
        $this->testFunction = false;
        $this->state = false;

        $callback = function ($url, $provider) {
            $this->testFunction = $url;
            $this->state = $provider->getState();
        };

        $this->provider->authorize([], $callback);

        $this->assertNotFalse($this->testFunction);
        $this->assertAttributeEquals($this->state, 'state', $this->provider);
    }

    /**
     * @dataProvider userPropertyProvider
     */
    public function testGetUserProperties($response, $name = null, $email = null, $id = null)
    {
        // Mock
        $provider = new MockProvider([
          'clientId' => 'mock_client_id',
          'clientSecret' => 'mock_secret',
          'redirectUri' => 'none',
        ]);

        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns(json_encode(compact('id', 'name', 'email')));

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());
        $response->getHeader->with('content-type')->returns('application/json');

        $client = Phony::mock(ClientInterface::class);
        $client->send->returns($response->get());

        // Run
        $provider->setHttpClient($client->get());
        $user = $provider->getResourceOwner($token);
        $url = $provider->getResourceOwnerDetailsUrl($token);

        // Verify
        $this->assertEquals($id, $user->getId());
        $this->assertEquals($name, $user->getUserScreenName());
        $this->assertEquals($email, $user->getUserEmail());

        $this->assertArrayHasKey('name', $user->toArray());
        $this->assertArrayHasKey('email', $user->toArray());

        Phony::inOrder(
            $client->send->calledWith(
                $this->callback(function ($request) use ($url) {
                    return $request->getMethod() === 'GET'
                        && $request->hasHeader('Authorization')
                        && (string) $request->getUri() === $url;
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called(),
            $response->getHeader->called()
        );
    }

    /**
     * @dataProvider userPropertyProvider
     */
    public function testGetUserPropertiesThrowsExceptionWhenNonJsonResponseIsReceived()
    {
        $this->expectException(\UnexpectedValueException::class);
// Mock
        $provider = new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);

        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns("<html><body>some unexpected response.</body></html>");

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());
        $response->getHeader->with('content-type')->returns('text/html');

        $client = Phony::mock(ClientInterface::class);
        $client->send->returns($response->get());

        // Run
        $provider->setHttpClient($client->get());

        $user = $provider->getResourceOwner($token);
    }

    public function userPropertyProvider()
    {
        $response = [
            'id'    => 1,
            'email' => 'test@example.com',
            'name'  => 'test',
        ];

        $response2 = [
            'id'    => null,
            'email' => null,
            'name'  => null,
        ];

        $response3 = [];

        return [
            'full response'  => [$response, 'test', 'test@example.com', 1],
            'empty response' => [$response2],
            'no response'    => [$response3],
        ];
    }

    public function getHeadersTest()
    {
        $provider = $this->getMockForAbstractClass(
            '\League\OAuth2\Client\Provider\AbstractProvider',
            [
              [
                  'clientId'     => 'mock_client_id',
                  'clientSecret' => 'mock_secret',
                  'redirectUri'  => 'none',
              ]
            ]
        );

        /**
         * @var $provider AbstractProvider
         */
        $this->assertEquals([], $provider->getHeaders());
        $this->assertEquals([], $provider->getHeaders('mock_token'));

        $provider->authorizationHeader = 'Bearer';
        $this->assertEquals(['Authorization' => 'Bearer abc'], $provider->getHeaders('abc'));

        $token = new AccessToken(['access_token' => 'xyz', 'expires_in' => 3600]);
        $this->assertEquals(['Authorization' => 'Bearer xyz'], $provider->getHeaders($token));
    }

    public function testScopesOverloadedDuringAuthorize()
    {
        $url = $this->provider->getAuthorizationUrl();

        parse_str(parse_url($url, PHP_URL_QUERY), $qs);

        $this->assertArrayHasKey('scope', $qs);
        $this->assertSame('test', $qs['scope']);

        $url = $this->provider->getAuthorizationUrl(['scope' => ['foo', 'bar']]);

        parse_str(parse_url($url, PHP_URL_QUERY), $qs);

        $this->assertArrayHasKey('scope', $qs);
        $this->assertSame('foo,bar', $qs['scope']);
    }

    public function testAuthorizationStateIsRandom()
    {
        $last = null;

        for ($i = 0; $i < 100; $i++) {
            // Repeat the test multiple times to verify state changes
            $url = $this->provider->getAuthorizationUrl();

            parse_str(parse_url($url, PHP_URL_QUERY), $qs);

            $this->assertRegExp('/^[a-zA-Z0-9\/+]{32}$/', $qs['state']);
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

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($errorJson);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());
        $response->getHeader->with('content-type')->returns('application/json');

        $client = Phony::mock(ClientInterface::class);
        $client->send->returns($response->get());

        // Run
        $provider->setHttpClient($client->get());

        $errorMessage = '';
        $errorCode = 0;

        try {
            $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        } catch (IdentityProviderException $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            $errorBody = $e->getResponseBody();
        }

        $method = $provider->getAccessTokenMethod();
        $url = $provider->getBaseAccessTokenUrl([]);

        // Verify
        $this->assertEquals($error['error'], $errorMessage);
        $this->assertEquals($error['code'], $errorCode);
        $this->assertEquals($error, $errorBody);

        Phony::inOrder(
            $client->send->calledWith(
                $this->callback(function ($request) use ($method, $url) {
                    return $request->getMethod() === $method
                        && (string) $request->getUri() === $url;
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called(),
            $response->getHeader->called()
        );
    }

    public function testClientErrorTriggersProviderException()
    {
        $this->expectException(IdentityProviderException::class);
        $provider = new MockProvider([
          'clientId' => 'mock_client_id',
          'clientSecret' => 'mock_secret',
          'redirectUri' => 'none',
        ]);

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns('{"error":"Foo error","code":1337}');

        $request = Phony::mock(RequestInterface::class);

        $response = Phony::mock(ResponseInterface::class);
        $response->getStatusCode->returns(400);
        $response->getBody->returns($stream->get());
        $response->getHeader->with('content-type')->returns('application/json');

        $client = Phony::mock(ClientInterface::class);
        $client->send->throws(new BadResponseException(
            'test exception',
            $request->get(),
            $response->get()
        ));

        // Run
        $provider->setHttpClient($client->get());
        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testGetResponse()
    {
        $provider = new MockProvider();

        $request = Phony::mock(RequestInterface::class)->get();
        $response = Phony::mock(ResponseInterface::class)->get();

        $client = Phony::mock(ClientInterface::class);
        $client->send->with($request)->returns($response);

        // Run
        $provider->setHttpClient($client->get());
        $output = $provider->getResponse($request);

        // Verify
        $this->assertSame($output, $response);
    }

    public function testAuthenticatedRequestAndResponse()
    {
        $provider = new MockProvider();

        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);
        $request = $provider->getAuthenticatedRequest('get', 'https://api.example.com/v1/test', $token);

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns('{"example":"response"}');

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());
        $response->getHeader->with('content-type')->returns('application/json');

        $client = Phony::mock(ClientInterface::class);
        $client->send->with($request)->returns($response->get());

        // Run
        $provider->setHttpClient($client->get());
        $result = $provider->getParsedResponse($request);

        // Verify
        $this->assertSame(['example' => 'response'], $result);

        $this->assertInstanceOf(RequestInterface::class, $request);

        // Authorization header should contain the token
        $header = $request->getHeader('Authorization');
        $this->assertContains('Bearer abc', $header);

        Phony::inOrder(
            $client->send->called(),
            $response->getBody->called(),
            $stream->__toString->called(),
            $response->getHeader->called()
        );
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

        $grant_name = 'mock';
        $raw_response = ['access_token' => 'okay', 'expires' => time() + 3600, 'resource_owner_id' => 3];

        $grant = Phony::mock(AbstractGrant::class);
        $grant->prepareRequestParameters->returns([]);

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns(json_encode($raw_response));

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());
        $response->getHeader->with('content-type')->returns('application/json');

        $client = Phony::mock(ClientInterface::class);
        $client->send->returns($response->get());

        // Run
        $provider->setHttpClient($client->get());
        $token = $provider->getAccessToken($grant->get(), ['code' => 'mock_authorization_code']);

        // Verify
        $this->assertInstanceOf(AccessTokenInterface::class, $token);

        $this->assertSame($raw_response['resource_owner_id'], $token->getResourceOwnerId());
        $this->assertSame($raw_response['access_token'], $token->getToken());
        $this->assertSame($raw_response['expires'], $token->getExpires());

        Phony::inOrder(
            $grant->prepareRequestParameters->calledWith('~', '~'),
            $client->send->calledWith(
                $this->callback(function ($request) use ($provider) {
                    return $request->getMethod() === $provider->getAccessTokenMethod()
                        && (string) $request->getUri() === $provider->getBaseAccessTokenUrl([]);
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called(),
            $response->getHeader->called()
        );
    }

    public function testGetAccessTokenWithNonJsonResponse()
    {
        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns('');

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());
        $response->getHeader->with('content-type')->returns('text/plain');

        $client = Phony::mock(ClientInterface::class);
        $client->send->returns($response->get());
        $this->provider->setHttpClient($client->get());

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid response received from Authorization Server. Expected JSON.');
        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    private function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
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
        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($body);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());
        $response->getHeader->with('content-type')->returns($type);
        $response->getStatusCode->returns($statusCode);

        $method = $this->getMethod(AbstractProvider::class, 'parseResponse');
        $result = $method->invoke($this->provider, $response->get());

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
        $this->assertEquals($expected, $method->invoke($this->provider, $url, $query));
    }

    protected function getAbstractProviderMock()
    {
        $mock = Phony::partialMock(AbstractProvider::class);
        return Liberator::liberate($mock->get());
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
        $provider = Phony::partialMock(Fake\ProviderWithAccessTokenResourceOwnerId::class);
        $provider = Liberator::liberate($provider->get());

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

        $this->assertAttributeNotEquals(
            $options['skipMeDuringMassAssignment'],
            'skipMeDuringMassAssignment',
            $provider
        );

        $this->assertAttributeNotEquals(
            $options['guarded'],
            'guarded',
            $provider
        );
    }

    public function testPrepareAccessTokenResponseWithDotNotation()
    {
        $provider = Phony::partialMock(Fake\ProviderWithAccessTokenResourceOwnerId::class);
        $provider->getAccessTokenResourceOwnerId->returns('user.id');
        $provider = Liberator::liberate($provider->get());

        $result = ['user' => ['id' => uniqid()]];
        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertArrayHasKey('resource_owner_id', $newResult);
        $this->assertEquals($result['user']['id'], $newResult['resource_owner_id']);
    }

    public function testPrepareAccessTokenResponseWithInvalidKeyType()
    {
        $provider = Phony::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class);
        $provider->getAccessTokenResourceOwnerId->returns(new \stdClass);
        $provider = Liberator::liberate($provider->get());

        $result = ['user_id' => uniqid()];
        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertFalse(isset($newResult['resource_owner_id']));
    }

    public function testPrepareAccessTokenResponseWithInvalidKeyPath()
    {
        $provider = Phony::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class);
        $provider->getAccessTokenResourceOwnerId->returns('user.name');
        $provider = Liberator::liberate($provider->get());

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
