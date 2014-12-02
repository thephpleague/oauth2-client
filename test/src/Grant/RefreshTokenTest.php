<?php

namespace League\OAuth2\Client\Test\Grant;

use Mockery as m;

class RefreshTokenTest extends \PHPUnit_Framework_TestCase
{
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

    public function testGetAccessToken()
    {
        $response = m::mock('Guzzle\Http\Message\Response');
        $response->shouldReceive('getBody')->times(2)->andReturn('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}');

        $client = m::mock('Guzzle\Service\Client');
        $client->shouldReceive('setBaseUrl')->times(2);
        $client->shouldReceive('post->send')->times(2)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $token);

        $grant = new \League\OAuth2\Client\Grant\RefreshToken();
        $this->assertEquals('refresh_token', (string) $grant);

        $newToken = $this->provider->getAccessToken($grant, ['refresh_token' => $token->refreshToken]);
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $newToken);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testInvalidRefreshToken()
    {
        $response = m::mock('Guzzle\Http\Message\Response');
        $response->shouldReceive('getBody')->times(1)->andReturn('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}');

        $client = m::mock('Guzzle\Service\Client');
        $client->shouldReceive('setBaseUrl')->times(1);
        $client->shouldReceive('post->send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $grant = new \League\OAuth2\Client\Grant\RefreshToken();
        $refreshToken = $this->provider->getAccessToken($grant, ['invalid_refresh_token' => $token->refreshToken]);
    }
}
