<?php

namespace League\OAuth2\Client\Test\Grant;

use Ivory\HttpAdapter\Message\Stream\StringStream;
use Mockery as m;

class PasswordTest extends \PHPUnit_Framework_TestCase
{
    /** @var \League\OAuth2\Client\Provider\AbstractProvider */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Provider\Google(array(
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ));
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Ivory\HttpAdapter\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn(new StringStream('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}'));

        $client = m::mock('Ivory\HttpAdapter\HttpAdapterInterface');
        $client->shouldReceive('post')->times(1)->andReturn($response);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('password', array('username' => 'mock_username', 'password' => 'mock_password'));
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $token);

        $grant = new \League\OAuth2\Client\Grant\Password();
        $this->assertEquals('password', (string) $grant);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testInvalidUsername()
    {
        $this->provider->getAccessToken('password', array('invalid_username' => 'mock_username', 'password' => 'mock_password'));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testInvalidPassword()
    {
        $this->provider->getAccessToken('password', array('username' => 'mock_username', 'invalid_password' => 'mock_password'));
    }
}
