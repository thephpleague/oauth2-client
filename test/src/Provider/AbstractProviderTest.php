<?php

namespace League\OAuth2\Client\Test\Provider;

use Ivory\HttpAdapter\HttpAdapterException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Mockery as m;

class AbstractProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Test\Provider\Fake([
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
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidGrantString()
    {
        $this->provider->getAccessToken('invalid_grant', ['invalid_parameter' => 'none']);
    }

    /**
     * @expectedException \InvalidArgumentException
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
            'scopes' => ['a', 'b', 'c'],
            'method' => 'get',
            'scopeSeparator' => ';',
            'responseType' => 'csv',
            'headers' => ['Foo' => 'Bar'],
            'authorizationHeader' => 'Bearer',
        ];

        $mockProvider = new MockProvider($options);

        foreach ($options as $key => $value) {
            $this->assertEquals($value, $mockProvider->{$key});
        }
    }

    public function testConstructorSetsHttpAdapter()
    {
        $mockAdapter = m::mock('Ivory\HttpAdapter\HttpAdapterInterface');

        $mockProvider = new MockProvider([], $mockAdapter);
        $this->assertSame($mockAdapter, $mockProvider->getHttpClient());
    }

    public function testSetRedirectHandler()
    {
        $this->testFunction = false;
        $this->state = false;

        $callback = function ($url, $provider) {
            $this->testFunction = $url;
            $this->state = $provider->state;
        };

        $this->provider->setRedirectHandler($callback);

        $this->provider->authorize();

        $this->assertNotFalse($this->testFunction);
        $this->assertEquals($this->provider->state, $this->state);
    }

    /**
     * @param $response
     *
     * @dataProvider userPropertyProvider
     */
    public function testGetUserProperties($response, $name = null, $email = null, $id = null)
    {
        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

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

        $this->assertEquals($name, $provider->userScreenName($response, $token));
        $this->assertEquals($email, $provider->userEmail($response, $token));
        $this->assertEquals($id, $provider->userUid($response, $token));
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
        $client->shouldReceive('post')->times(1)->andReturn($response);
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

    public function testClientErrorTriggersHttpException()
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
        $client->shouldReceive('post')->times(1)->andThrow($exception);
        $provider->setHttpClient($client);

        try {
            $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        } catch (IdentityProviderException $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
        }

        $this->assertEquals('BadResponse', $errorMessage);
        $this->assertEquals(500, $errorCode);
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
        $client->shouldReceive('post')->times(1)->andReturn($response);
        $provider->setHttpClient($client);

        $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testAuthenticatedRequestAndResponse()
    {
        $token = new AccessToken(['access_token' => 'abc', 'expires_in' => 3600]);

        $provider = clone $this->provider;
        $provider->authorizationHeader = 'Bearer';

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

class MockProvider extends \League\OAuth2\Client\Provider\AbstractProvider
{
    public function urlAuthorize()
    {
        return '';
    }

    public function urlAccessToken()
    {
        return '';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return '';
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return '';
    }

    public function errorCheck(array $result)
    {
        if (isset($result['error']) && !empty($result['error'])) {
            throw new IdentityProviderException($result['error'], $result['code'], $result);
        }
    }
}
