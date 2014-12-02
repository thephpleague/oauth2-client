<?php

namespace League\OAuth2\Client\Test\Grant;

use Mockery as m;

class ClientCredentialsTest extends \PHPUnit_Framework_TestCase
{
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
        $response = m::mock('Guzzle\Http\Message\Response');
        $response->shouldReceive('getBody')->times(1)->andReturn('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}');

        $client = m::mock('Guzzle\Service\Client');
        $client->shouldReceive('setBaseUrl')->times(1);
        $client->shouldReceive('post->send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('client_credentials');
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $token);

        $grant = new \League\OAuth2\Client\Grant\ClientCredentials();
        $this->assertEquals('client_credentials', (string) $grant);
    }
}
