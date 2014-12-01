<?php

namespace League\OAuth2\Client\Test\Token;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidRefreshToken()
    {
        new \League\OAuth2\Client\Token\AccessToken(['invalid_access_token' => 'none']);
    }

    public function testExpiresInCorrection()
    {
        $options = array('access_token' => 'access_token', 'expires_in' => 100);
        $token = new \League\OAuth2\Client\Token\AccessToken($options);
        $this->assertNotNull($token->expires);
    }
}
