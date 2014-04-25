<?php

namespace LeagueTest\OAuth2\Client\Token;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidRefreshToken()
    {
        $test = new \League\OAuth2\Client\Token\AccessToken(array('invalid_access_token' => 'none'));
    }
}
