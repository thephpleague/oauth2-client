<?php

namespace League\OAuth2\Client\Test\Provider;

use Mockery as m;

class EventbriteTest extends ConcreteProviderTest
{
    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Provider\Eventbrite([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->state);
    }

    public function testUrlAccessToken()
    {
        $url = $this->provider->urlAccessToken();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $client = $this->createMockHttpClient();
        $client->shouldReceive('post')->times(1)->andReturn($this->createMockResponse('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}'));

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->accessToken);
        $this->assertLessThanOrEqual(time() + 3600, $token->expires);
        $this->assertGreaterThanOrEqual(time(), $token->expires);
        $this->assertEquals('mock_refresh_token', $token->refreshToken);
        $this->assertEquals('1', $token->uid);
    }

    public function testScopes()
    {
        $this->assertEquals([], $this->provider->getScopes());
    }

    public function testUserData()
    {
        $client = $this->createMockHttpClient();

        $postResponse = $this->createMockResponse('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}');
        $getResponse = $this->createMockResponse('{"user": {"user_id": 12345, "email": "mock_email"}}');

        $client->shouldReceive('post')->times(1)->andReturn($postResponse);
        $client->shouldReceive('get')->times(4)->andReturn($getResponse);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getUserDetails($token);

        $this->assertEquals(12345, $this->provider->getUserUid($token));
        $this->assertEquals('mock_email', $this->provider->getUserEmail($token));
        $this->assertEquals(12345, $this->provider->getUserScreenName($token));
        $this->assertEquals('mock_email', $user->email);
    }
}
