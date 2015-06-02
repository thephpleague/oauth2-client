<?php

namespace League\OAuth2\Client\Test\Grant;

use Mockery as m;

class ClientCredentialsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \League\OAuth2\Client\Provider\AbstractProvider */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Test\Provider\Fake(array(
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
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}');

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('post')->times(1)->andReturn($response);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('client_credentials');
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $token);

        $grant = new \League\OAuth2\Client\Grant\ClientCredentials();
        $this->assertEquals('client_credentials', (string) $grant);
    }
}
