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
}
