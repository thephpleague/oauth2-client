<?php

namespace League\OAuth2\Client\Test\Provider;

use Mockery as m;

class FacebookTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \League\OAuth2\Client\Provider\Facebook
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Provider\Facebook([
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
        $graphVersion = \League\OAuth2\Client\Provider\Facebook::DEFAULT_GRAPH_VERSION;

        $this->assertEquals('/'.$graphVersion.'/oauth/access_token', $uri['path']);
    }

    public function testGraphApiVersionCanBeCustomized()
    {
        $graphVersion = 'v13.37';
        $provider = new \League\OAuth2\Client\Provider\Facebook([
            'graphApiVersion' => $graphVersion,
        ]);
        $fooToken = new \League\OAuth2\Client\Token\AccessToken(['access_token' => 'foo_token']);

        $urlAuthorize = $provider->urlAuthorize();
        $urlAccessToken = $provider->urlAccessToken();
        $urlUserDetails = parse_url($provider->urlUserDetails($fooToken), PHP_URL_PATH);

        $this->assertEquals('https://www.facebook.com/'.$graphVersion.'/dialog/oauth', $urlAuthorize);
        $this->assertEquals('https://graph.facebook.com/'.$graphVersion.'/oauth/access_token', $urlAccessToken);
        $this->assertEquals('/'.$graphVersion.'/me', $urlUserDetails);
    }

    public function testGraphApiVersionWillFallbackToDefault()
    {
        $graphVersion = \League\OAuth2\Client\Provider\Facebook::DEFAULT_GRAPH_VERSION;
        $fooToken = new \League\OAuth2\Client\Token\AccessToken(['access_token' => 'foo_token']);

        $urlAuthorize = $this->provider->urlAuthorize();
        $urlAccessToken = $this->provider->urlAccessToken();
        $urlUserDetails = parse_url($this->provider->urlUserDetails($fooToken), PHP_URL_PATH);

        $this->assertEquals('https://www.facebook.com/'.$graphVersion.'/dialog/oauth', $urlAuthorize);
        $this->assertEquals('https://graph.facebook.com/'.$graphVersion.'/oauth/access_token', $urlAccessToken);
        $this->assertEquals('/'.$graphVersion.'/me', $urlUserDetails);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Guzzle\Http\Message\Response');
        $response->shouldReceive('getBody')->times(1)->andReturn('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&uid=1');

        $client = m::mock('Guzzle\Service\Client');
        $client->shouldReceive('setBaseUrl')->times(1);
        $client->shouldReceive('post->send')->times(1)->andReturn($response);
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
        $this->assertEquals(['public_profile', 'email'], $this->provider->getScopes());
    }

    public function testUserData()
    {
        $postResponse = m::mock('Guzzle\Http\Message\Response');
        $postResponse->shouldReceive('getBody')->times(1)->andReturn('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&uid=1');

        $getResponse = m::mock('Guzzle\Http\Message\Response');
        $getResponse->shouldReceive('getBody')->andReturn('{"id": 12345, "name": "mock_name", "username": "mock_username", "first_name": "mock_first_name", "last_name": "mock_last_name", "email": "mock_email", "Location": "mock_home", "bio": "mock_description", "link": "mock_facebook_url"}');
        $getResponse->shouldReceive('getInfo')->andReturn(['url' => 'mock_image_url']);

        $client = m::mock('Guzzle\Service\Client');
        $client->shouldReceive('setBaseUrl')->times(5);
        $client->shouldReceive('post->send')->times(1)->andReturn($postResponse);
        $client->shouldReceive('get->send')->andReturn($getResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getUserDetails($token);

        $this->assertEquals(12345, $this->provider->getUserUid($token));
        $this->assertEquals(['mock_first_name', 'mock_last_name'], $this->provider->getUserScreenName($token));
        $this->assertEquals('mock_email', $this->provider->getUserEmail($token));
        $this->assertEquals('mock_email', $user->email);
    }
}
