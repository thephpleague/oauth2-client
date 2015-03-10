<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Mockery as m;

class AbstractProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Provider\Google([
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
     * @expectedException InvalidArgumentException
     */
    public function testInvalidGrantString()
    {
        $this->provider->getAccessToken('invalid_grant', ['invalid_parameter' => 'none']);
    }

    /**
     * @expectedException InvalidArgumentException
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

    public function testSetRedirectHandler()
    {
        $this->testFunction = false;

        $callback = function ($url) {
            $this->testFunction = $url;
        };

        $this->provider->setRedirectHandler($callback);

        $this->provider->authorize('http://test.url/');

        $this->assertNotFalse($this->testFunction);
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
        $response = new \stdClass();
        $response->id = 1;
        $response->email = 'test@example.com';
        $response->name = 'test';

        $response2 = new \stdClass();
        $response2->id = null;
        $response2->email = null;
        $response2->name = null;

        $response3 = new \stdClass();

        return [
            [$response, 'test', 'test@example.com', 1],
            [$response2],
            [$response3],
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
}
