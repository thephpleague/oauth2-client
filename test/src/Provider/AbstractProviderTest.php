<?php

namespace League\OAuth2\Client\Test\Provider;

use Ivory\HttpAdapter\HttpAdapterException;
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
            'state' => 'foo',
            'name' => 'bar',
            'uidKey' => 'mynewuid',
            'method' => 'get',
            'responseType' => 'csv',
        ];

        $mockProvider = new MockProvider($options);

        foreach ($options as $key => $value) {
            $this->assertAttributeEquals($value, $key, $mockProvider);
        }
    }

    public function testConstructorSetsGrantFactory()
    {
        $mockAdapter = m::mock('League\OAuth2\Client\Grant\GrantFactory');

        $mockProvider = new MockProvider([], ['grantFactory' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getGrantFactory());
    }

    public function testConstructorSetsHttpAdapter()
    {
        $mockAdapter = m::mock('Ivory\HttpAdapter\HttpAdapterInterface');

        $mockProvider = new MockProvider([], ['httpClient' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getHttpClient());
    }

    public function testConstructorSetsRandomFactory()
    {
        $mockAdapter = m::mock('RandomLib\Factory');

        $mockProvider = new MockProvider([], ['randomFactory' => $mockAdapter]);
        $this->assertSame($mockAdapter, $mockProvider->getRandomFactory());
    }

    public function testSetRedirectHandler()
    {
        $this->testFunction = false;
        $this->state = false;

        $callback = function ($url, $provider) {
            $this->testFunction = $url;
            $this->state = $provider->getState();
        };

        $this->provider->setRedirectHandler($callback);

        $this->provider->authorize();

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

        $request = m::mock('Ivory\HttpAdapter\Message\RequestInterface');
        $request->shouldReceive('addHeaders')->times(1);

        $response = m::mock('Ivory\HttpAdapter\Message\ResponseInterface');
        $response->shouldReceive('getBody')
                 ->times(1)
                 ->andReturn(json_encode(compact('id', 'name', 'email')));

        $factory = m::mock('Ivory\HttpAdapter\Message\MessageFactoryInterface');
        $factory->shouldReceive('createRequest')->times(1)->andReturn($request);

        $config = m::mock('Ivory\HttpAdapter\ConfigurationInterface');
        $config->shouldReceive('getMessageFactory')->times(1)->andReturn($factory);

        $client = m::mock('Ivory\HttpAdapter\HttpAdapterInterface');
        $client->shouldReceive('getConfiguration')->times(1)->andReturn($config);
        $client->shouldReceive('sendRequest')->with($request)->times(1)->andReturn($response);

        $provider->setHttpClient($client);

        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

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

    public function testGetAccessToken()
    {
        $provider = new MockProvider([
          'clientId' => 'mock_client_id',
          'clientSecret' => 'mock_secret',
          'redirectUri' => 'none',
        ]);

        $grant_name = 'mock';
        $raw_response = ['access_token' => 'okay', 'expires_in' => 3600];
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

        $response = m::mock('Ivory\HttpAdapter\Message\ResponseInterface');
        $response->shouldReceive('getBody')
                 ->times(1)
                 ->andReturn(json_encode($raw_response));

        $client = m::mock('Ivory\HttpAdapter\HttpAdapterInterface');
        $client->shouldReceive('post')
            ->with(
                $provider->urlAccessToken(),
                $headers = m::type('array'),
                $params = m::type('array')
            )
            ->times(1)->andReturn($response);

        $provider->setHttpClient($client);

        $result = $provider->getAccessToken($grant, ['code' => 'mock_authorization_code']);

        $this->assertSame($result, $token);
    }

    public function testErrorResponsesCanBeCustomizedAtTheProvider()
    {
        $provider = new MockProvider([
          'clientId' => 'mock_client_id',
          'clientSecret' => 'mock_secret',
          'redirectUri' => 'none',
        ]);

        $response = m::mock('Ivory\HttpAdapter\Message\ResponseInterface');
        $response->shouldReceive('getBody')
                 ->times(1)
                 ->andReturn('{"error":"Foo error","code":1337}');

        $client = m::mock('Ivory\HttpAdapter\HttpAdapterInterface');
        $client->shouldReceive('post')
            ->with(
                $provider->urlAccessToken(),
                $headers = m::type('array'),
                $params = m::type('array')
            )
            ->times(1)->andReturn($response);
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

        $response = m::mock('Ivory\HttpAdapter\Message\ResponseInterface');
        $response->shouldReceive('getBody')
                 ->times(1)
                 ->andReturn('{"error":"BadResponse","code":500}');

        $exception = new HttpAdapterException();
        $exception->setResponse($response);

        $client = m::mock('Ivory\HttpAdapter\HttpAdapterInterface');
        $client->shouldReceive('post')
            ->with(
                $provider->urlAccessToken(),
                $headers = m::type('array'),
                $params = m::type('array')
            )
            ->times(1)->andThrow($exception);
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

        $response = m::mock('Ivory\HttpAdapter\Message\ResponseInterface');
        $response->shouldReceive('getBody')
                 ->times(1)
                 ->andReturn('not json');

        $client = m::mock('Ivory\HttpAdapter\HttpAdapterInterface');
        $client->shouldReceive('post')
            ->with(
                $provider->urlAccessToken(),
                $headers = m::type('array'),
                $params = m::type('array')
            )
            ->times(1)->andReturn($response);
        $provider->setHttpClient($client);

        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testAuthenticatedRequestAndResponse()
    {
        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

        $provider = new MockProvider(['authorizationHeader' => 'Bearer']);

        $request = $provider->getAuthenticatedRequest('get', 'https://api.example.com/v1/test', $token);
        $this->assertInstanceOf('Ivory\HttpAdapter\Message\RequestInterface', $request);

        // Authorization header should contain the token
        $header = $request->getHeader('Authorization');
        $this->assertEquals('Bearer abc', $header);

        $response = m::mock('Ivory\HttpAdapter\Message\ResponseInterface');
        $response->shouldReceive('getBody')
                 ->times(1)
                 ->andReturn('{"example":"response"}');

        $client = m::mock('Ivory\HttpAdapter\HttpAdapterInterface');
        $client->shouldReceive('sendRequest')
               ->times(1)
               ->with($request)
               ->andReturn($response);

        $provider->setHttpClient($client);

        // Final result should be a parsed response
        $result = $provider->getResponse($request);
        $this->assertSame(['example' => 'response'], $result);
    }
}
