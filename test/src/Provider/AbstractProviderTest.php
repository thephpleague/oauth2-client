<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Test\Provider\Fake as MockProvider;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequestFactory;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use RandomLib\Factory as RandomFactory;
use RandomLib\Generator as RandomGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\ClientInterface;

use Mockery as m;

class AbstractProviderTest extends \PHPUnit_Framework_TestCase
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

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @expectedException League\OAuth2\Client\Grant\Exception\InvalidGrantException
     */
    public function testInvalidGrantString()
    {
        $this->provider->getAccessToken('invalid_grant', ['invalid_parameter' => 'none']);
    }

    /**
     * @expectedException League\OAuth2\Client\Grant\Exception\InvalidGrantException
     */
    public function testInvalidGrantObject()
    {
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

    public function testConstructorSetsGrantFactory()
    {
        $mockAdapter = m::mock(GrantFactory::class);

        $mockProvider = new MockProvider([], ['grantFactory' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getGrantFactory());
    }

    public function testConstructorSetsHttpAdapter()
    {
        $mockAdapter = m::mock(ClientInterface::class);

        $mockProvider = new MockProvider([], ['httpClient' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getHttpClient());
    }

    public function testConstructorSetsRandomFactory()
    {
        $mockAdapter = m::mock('RandomLib\Factory');

        $mockProvider = new MockProvider([], ['randomFactory' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getRandomFactory());
    }

    public function testConstructorSetsRequestFactory()
    {
        $mockAdapter = m::mock(RequestFactory::class);

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
        $provider = new MockProvider([
          'clientId' => 'mock_client_id',
          'clientSecret' => 'mock_secret',
          'redirectUri' => 'none',
        ]);

        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

        $stream = m::mock(StreamInterface::class);
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            json_encode(compact('id', 'name', 'email'))
        );

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);
        $response->shouldReceive('getHeader')->with('content-type')->times(1)->andReturn('application/json');

        $url = $provider->getResourceOwnerDetailsUrl($token);

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->with(
            m::on(function ($request) use ($url) {
                return $request->getMethod() === 'GET'
                    && $request->hasHeader('Authorization')
                    && (string) $request->getUri() === $url;
            })
        )->times(1)->andReturn($response);

        $provider->setHttpClient($client);

        $user = $provider->getResourceOwner($token);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($name, $user->getUserScreenName());
        $this->assertEquals($email, $user->getUserEmail());

        $this->assertArrayHasKey('name', $user->toArray());
        $this->assertArrayHasKey('email', $user->toArray());
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

    public function testRandomGeneratorCreatesRandomState()
    {
        $xstate = str_repeat('x', 32);

        $generator = m::mock(RandomGenerator::class);
        $generator->shouldReceive('generateString')->with(32)->times(1)->andReturn($xstate);

        $factory = m::mock(RandomFactory::class);
        $factory->shouldReceive('getMediumStrengthGenerator')->times(1)->andReturn($generator);

        $provider = new MockProvider([], ['randomFactory' => $factory]);

        $url = $provider->getAuthorizationUrl();

        parse_str(parse_url($url, PHP_URL_QUERY), $qs);

        $this->assertArrayHasKey('state', $qs);
        $this->assertSame($xstate, $qs['state']);

        // Same test, but using the non-mock implementation
        $url = $this->provider->getAuthorizationUrl();

        parse_str(parse_url($url, PHP_URL_QUERY), $qs);

        $this->assertRegExp('/^[a-zA-Z0-9\/+]{32}$/', $qs['state']);
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

        $stream = m::mock(StreamInterface::class);
        $stream->shouldReceive('__toString')->times(1)->andReturn($errorJson);

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);
        $response->shouldReceive('getHeader')->with('content-type')->times(1)->andReturn('application/json');

        $method = $provider->getAccessTokenMethod();
        $url = $provider->getBaseAccessTokenUrl([]);

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->with(
            m::on(function ($request) use ($method, $url) {
                return $request->getMethod() === $method
                    && (string) $request->getUri() === $url;
            })
        )->times(1)->andReturn($response);

        $provider->setHttpClient($client);

        $errorMessage = '';
        $errorCode = 0;

        try {
            $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        } catch (IdentityProviderException $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            $errorBody = $e->getResponseBody();
        }

        $this->assertEquals($error['error'], $errorMessage);
        $this->assertEquals($error['code'], $errorCode);
        $this->assertEquals($error, $errorBody);
    }

    /**
     * @expectedException \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function testClientErrorTriggersProviderException()
    {
        $provider = new MockProvider([
          'clientId' => 'mock_client_id',
          'clientSecret' => 'mock_secret',
          'redirectUri' => 'none',
        ]);

        $stream = m::mock(StreamInterface::class);
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            '{"error":"Foo error","code":1337}'
        );

        $request = m::mock(RequestInterface::class);

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->times(1)->andReturn(400);
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);
        $response->shouldReceive('getHeader')->with('content-type')->andReturn('application/json');

        $exception = new BadResponseException(
            'test exception',
            $request,
            $response
        );

        $method = $provider->getAccessTokenMethod();
        $url    = $provider->getBaseAccessTokenUrl([]);

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->with(
            m::on(function ($request) use ($method, $url) {
                return $request->getMethod() === $method
                    && (string) $request->getUri() === $url;
            })
        )->times(1)->andThrow($exception);

        $provider->setHttpClient($client);
        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testAuthenticatedRequestAndResponse()
    {
        $provider = new MockProvider();

        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

        $request = $provider->getAuthenticatedRequest('get', 'https://api.example.com/v1/test', $token);
        $this->assertInstanceOf(RequestInterface::class, $request);

        // Authorization header should contain the token
        $header = $request->getHeader('Authorization');
        $this->assertContains('Bearer abc', $header);

        $stream = m::mock(StreamInterface::class);
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            '{"example":"response"}'
        );

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);
        $response->shouldReceive('getHeader')->with('content-type')->times(1)->andReturn('application/json');

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->with($request)->andReturn($response);

        $provider->setHttpClient($client);

        // Final result should be a parsed response
        $result = $provider->getResponse($request);
        $this->assertSame(['example' => 'response'], $result);
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

        $grant = m::mock(AbstractGrant::class);
        $grant->shouldAllowMockingProtectedMethods();

        $grant->shouldReceive('getName')
              ->andReturn($grant_name);

        $grant->shouldReceive('prepareRequestParameters')
              ->with(
                  m::type('array'),
                  m::type('array')
              )
              ->andReturn([]);

        $stream = m::mock(StreamInterface::class);
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            json_encode($raw_response)
        );

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);
        $response->shouldReceive('getHeader')->with('content-type')->times(1)->andReturn('application/json');

        $method = $provider->getAccessTokenMethod();
        $url    = $provider->getBaseAccessTokenUrl([]);

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->with(
            m::on(function ($request) use ($method, $url) {
                return $request->getMethod() === $method
                    && (string) $request->getUri() === $url;
            })
        )->times(1)->andReturn($response);

        $provider->setHttpClient($client);

        $token = $provider->getAccessToken($grant, ['code' => 'mock_authorization_code']);

        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertSame($raw_response['resource_owner_id'], $token->getResourceOwnerId());
        $this->assertSame($raw_response['access_token'], $token->getToken());
        $this->assertSame($raw_response['expires'], $token->getExpires());
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
    public function testParseResponse($body, $type, $parsed)
    {
        $method = $this->getMethod(AbstractProvider::class, 'parseResponse');

        $stream = m::mock(StreamInterface::class);
        $stream->shouldReceive('__toString')->times(1)->andReturn($body);

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($stream);
        $response->shouldReceive('getHeader')->with('content-type')->andReturn($type);

        $this->assertEquals($parsed, $method->invoke($this->provider, $response));
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testParseResponseJsonFailure()
    {
        $this->testParseResponse('{a: 1}', 'application/json', null);
    }

    public function getAppendQueryProvider()
    {
        return [
            ['test.com/?a=1', 'test.com/', '?a=1'],
            ['test.com/?a=1', 'test.com/', '&a=1'],
            ['test.com/?a=1', 'test.com/', 'a=1'],
            ['test.com/?a=1', 'test.com/?a=1', '?'],
            ['test.com/?a=1', 'test.com/?a=1', '&'],
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
        return m::mock(AbstractProvider::class);
    }

    public function testDefaultAccessTokenMethod()
    {
        $provider = $this->getAbstractProviderMock();
        $expectedMethod = 'POST';

        $method = $provider->getAccessTokenMethod();

        $this->assertEquals($expectedMethod, $method);
    }

    public function testDefaultPrepareAccessTokenResponse()
    {
        $provider = m::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class);
        $result = ['user_id' => uniqid()];

        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertTrue(isset($newResult['resource_owner_id']));
        $this->assertEquals($result['user_id'], $newResult['resource_owner_id']);
    }

    public function testPrepareAccessTokenResponseWithDotNotation()
    {
        $provider = m::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $result = ['user' => ['id' => uniqid()]];
        $provider->shouldReceive('getAccessTokenResourceOwnerId')->andReturn('user.id');

        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertTrue(isset($newResult['resource_owner_id']));
        $this->assertEquals($result['user']['id'], $newResult['resource_owner_id']);
    }

    public function testPrepareAccessTokenResponseWithInvalidKeyType()
    {
        $provider = m::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $result = ['user_id' => uniqid()];

        $provider->shouldReceive('getAccessTokenResourceOwnerId')->andReturn(new \stdClass);

        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertFalse(isset($newResult['resource_owner_id']));
    }

    public function testPrepareAccessTokenResponseWithInvalidKeyPath()
    {
        $provider = m::mock(Fake\ProviderWithAccessTokenResourceOwnerId::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $result = ['user' => ['id' => uniqid()]];

        $provider->shouldReceive('getAccessTokenResourceOwnerId')->andReturn('user.name');

        $newResult = $provider->prepareAccessTokenResponse($result);

        $this->assertFalse(isset($newResult['resource_owner_id']));
    }

    public function testDefaultAuthorizationHeaders()
    {
        $provider = $this->getAbstractProviderMock();

        $headers = $provider->getAuthorizationHeaders();

        $this->assertEquals([], $headers);
    }
}
