<?php

namespace League\OAuth2\Client\Test\Grant;

use Mockery as m;

class AuthorizationCodeTest extends \PHPUnit_Framework_TestCase
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
        $grant = new \League\OAuth2\Client\Grant\AuthorizationCode();
        $this->assertEquals('authorization_code', (string) $grant);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testInvalidRefreshToken()
    {
        $this->provider->getAccessToken('authorization_code', ['invalid_code' => 'mock_authorization_code']);
    }
}
