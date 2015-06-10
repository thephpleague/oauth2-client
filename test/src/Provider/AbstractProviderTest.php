<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Test\Provider\Fake as MockProvider;
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
     * @expectedException League\OAuth2\Client\Grant\InvalidGrantException
     */
    public function testInvalidGrantString()
    {
        $this->provider->getAccessToken('invalid_grant', ['invalid_parameter' => 'none']);
    }

    /**
     * @expectedException League\OAuth2\Client\Grant\InvalidGrantException
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
     * Tests https://github.com/thephpleague/oauth2-client/issues/134
     */
    public function testConstructorSetsProperties()
    {
        $options = [
            'clientId' => '1234',
            'clientSecret' => '4567',
            'redirectUri' => 'http://example.org/redirect',
            'httpBuildEncType' => 4,
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
        $mockAdapter = m::mock('League\OAuth2\Client\Grant\GrantFactory');

        $mockProvider = new MockProvider([], ['grantFactory' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getGrantFactory());
    }

    public function testConstructorSetsHttpAdapter()
    {
        $mockAdapter = m::mock('GuzzleHttp\ClientInterface');

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
        $mockAdapter = m::mock('League\OAuth2\Client\Tool\RequestFactory');

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

        $stream = m::mock('Psr\Http\Message\StreamInterface');
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            json_encode(compact('id', 'name', 'email'))
        );

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);

        $url = $provider->urlUserDetails($token);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->with(
            m::on(function ($request) use ($url) {
                return $request->getMethod() === 'GET'
                    && $request->hasHeader('Authorization')
                    && (string) $request->getUri() === $url;
            })
        )->times(1)->andReturn($response);

        $provider->setHttpClient($client);

        $user = $provider->getUserDetails($token);

        $this->assertEquals($id, $user->getUserId());
        $this->assertEquals($name, $user->getUserScreenName());
        $this->assertEquals($email, $user->getUserEmail());
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

        $generator = m::mock('RandomLib\Generator');
        $generator->shouldReceive('generateString')->with(32)->times(1)->andReturn($xstate);

        $factory = m::mock('RandomLib\Factory');
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

    public function testGetAccessTokenMethods()
    {
        $this->accessTokenTest('GET');
        $this->accessTokenTest('POST');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidAccessTokenMethod()
    {
        $provider = new MockProvider([
          'clientId' => 'mock_client_id',
          'clientSecret' => 'mock_secret',
          'redirectUri' => 'none',
        ]);

        $provider->setAccessTokenMethod('PUT');
        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testErrorResponsesCanBeCustomizedAtTheProvider()
    {
        $provider = new MockProvider([
          'clientId' => 'mock_client_id',
          'clientSecret' => 'mock_secret',
          'redirectUri' => 'none',
        ]);


        $stream = m::mock('Psr\Http\Message\StreamInterface');
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            '{"error":"Foo error","code":1337}'
        );

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);

        $method = $provider->getAccessTokenMethod();
        $url = $provider->urlAccessToken();

        $client = m::mock('GuzzleHttp\ClientInterface');
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
        }

        $this->assertEquals('Foo error', $errorMessage);
        $this->assertEquals(1337, $errorCode);
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

        $stream = m::mock('Psr\Http\Message\StreamInterface');
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            '{"error":"Foo error","code":1337}'
        );

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);

        $exception = m::mock('GuzzleHttp\Exception\BadResponseException');
        $exception->shouldReceive('getResponse')->andReturn($response);

        $method = $provider->getAccessTokenMethod();
        $url    = $provider->urlAccessToken();

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->with(
            m::on(function ($request) use ($method, $url) {
                return $request->getMethod() === $method
                    && (string) $request->getUri() === $url;
            })
        )->times(1)->andThrow($exception);

        $provider->setHttpClient($client);

        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testParseResponseJsonFailure()
    {
        $provider = new MockProvider([
          'clientId' => 'mock_client_id',
          'clientSecret' => 'mock_secret',
          'redirectUri' => 'none',
        ]);

        $stream = m::mock('Psr\Http\Message\StreamInterface');
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            'not json'
        );

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);

        $method = $provider->getAccessTokenMethod();
        $url    = $provider->urlAccessToken();

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->with(
            m::on(function ($request) use ($method, $url) {
                return $request->getMethod() === $method
                    && (string) $request->getUri() === $url;
            })
        )->times(1)->andReturn($response);

        $provider->setHttpClient($client);

        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testAuthenticatedRequestAndResponse()
    {
        $provider = new MockProvider();

        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

        $request = $provider->getAuthenticatedRequest('get', 'https://api.example.com/v1/test', $token);
        $this->assertInstanceOf('Psr\Http\Message\RequestInterface', $request);

        // Authorization header should contain the token
        $header = $request->getHeader('Authorization');
        $this->assertContains('Bearer abc', $header);

        $stream = m::mock('Psr\Http\Message\StreamInterface');
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            '{"example":"response"}'
        );

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->with($request)->andReturn($response);

        $provider->setHttpClient($client);

        // Final result should be a parsed response
        $result = $provider->getResponse($request);
        $this->assertSame(['example' => 'response'], $result);
    }

    private function accessTokenTest($method)
    {
        $provider = new MockProvider([
          'clientId' => 'mock_client_id',
          'clientSecret' => 'mock_secret',
          'redirectUri' => 'none',
        ]);

        $provider->setAccessTokenMethod($method);

        $grant_name = 'mock';
        $raw_response = ['access_token' => 'okay', 'expires' => time() + 3600, 'uid' => 3];
        $token = new AccessToken($raw_response);

        $contains_correct_grant_type = function ($params) use ($grant_name) {
            return is_array($params) && $params['grant_type'] === $grant_name;
        };

        $grant = m::mock('League\OAuth2\Client\Grant\GrantInterface');
        $grant->shouldReceive('__toString')
              ->times(1)
              ->andReturn($grant_name);
        $grant->shouldReceive('prepRequestParams')
              ->with(
                  m::on($contains_correct_grant_type),
                  m::type('array')
              )
              ->andReturn([]);
        $grant->shouldReceive('handleResponse')
              ->with($raw_response)
              ->andReturn($token);

        $stream = m::mock('Psr\Http\Message\StreamInterface');
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            json_encode($raw_response)
        );

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);

        $url = $provider->urlAccessToken();

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->with(
            m::on(function ($request) use ($method, $url) {
                return $request->getMethod() === $method
                    && (string) $request->getUri() === $url;
            })
        )->times(1)->andReturn($response);

        $provider->setHttpClient($client);

        $result = $provider->getAccessToken($grant, ['code' => 'mock_authorization_code']);

        $this->assertSame($result, $token);
        $this->assertSame($raw_response['uid'], $token->getUid());
        $this->assertSame($raw_response['access_token'], $token->getToken());
        $this->assertSame($raw_response['expires'], $token->getExpires());
    }
}
