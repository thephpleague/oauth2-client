<?php

namespace LeagueTest\OAuth2\Client\Provider;

use \Mockery as m;
use Zend\Uri\UriFactory;

class GithubTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Provider\Github(array(
            'clientId' => 'mock',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ));
    }

    protected function tearDown()
    {
#        m::close();
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
    }

    public function testUrlAccessToken()
    {
        $url = $this->provider->urlAccessToken();
        $uri = parse_url($url);

        $this->assertEquals('/login/oauth/access_token', $uri['path']);
    }

/*
    public function testGetAccessToken()
    {
        $t = $this->provider->getAccessToken('authorization_code', array('code' => 'mock_authorization_code'));
    }
*/
}
