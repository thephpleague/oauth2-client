<?php

namespace League\OAuth2\Client\Test\Token;

use League\OAuth2\Client\Token\AccessToken;
use Mockery as m;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidRefreshToken()
    {
        $token = $this->getAccessToken(['invalid_access_token' => 'none']);
    }

    protected function getAccessToken($options = [])
    {
        return new AccessToken($options);
    }

    public function testExpiresInCorrection()
    {
        $options = ['access_token' => 'access_token', 'expires_in' => 100];
        $token = $this->getAccessToken($options);

        $expires = $token->getExpires();

        $this->assertNotNull($expires);
        $this->assertGreaterThan(time(), $expires);
        $this->assertLessThan(time() + 200, $expires);
    }

    public function testExpiresPastTimestamp()
    {
        $options = ['access_token' => 'access_token', 'expires' => strtotime('5 days ago')];
        $token = $this->getAccessToken($options);

        $this->assertTrue($token->hasExpired());

        $options = ['access_token' => 'access_token', 'expires' => 3600];
        $token = $this->getAccessToken($options);

        $this->assertFalse($token->hasExpired());
    }

    public function testGetRefreshToken()
    {
        $options = [
            'access_token' => 'access_token',
            'refresh_token' => uniqid()
        ];
        $token = $this->getAccessToken($options);

        $refreshToken = $token->getRefreshToken();

        $this->assertEquals($options['refresh_token'], $refreshToken);
    }

    public function testHasNotExpiredWhenPropertySetInFuture()
    {
        $options = [
            'access_token' => 'access_token'
        ];

        $expectedExpires = strtotime('+1 day');
        $token = m::mock(AccessToken::class, [$options])->makePartial();
        $token->shouldReceive('getExpires')->once()->andReturn($expectedExpires);

        $hasExpired = $token->hasExpired();

        $this->assertFalse($hasExpired);
    }

    public function testHasExpiredWhenPropertySetInPast()
    {
        $options = [
            'access_token' => 'access_token'
        ];

        $expectedExpires = strtotime('-1 day');
        $token = m::mock(AccessToken::class, [$options])->makePartial();
        $token->shouldReceive('getExpires')->once()->andReturn($expectedExpires);

        $hasExpired = $token->hasExpired();

        $this->assertTrue($hasExpired);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCannotReportExpiredWhenNoExpirationSet()
    {
        $options = [
            'access_token' => 'access_token',
        ];
        $token = $this->getAccessToken($options);

        $hasExpired = $token->hasExpired();
    }

    public function testJsonSerialzable()
    {
        $options = [
            'access_token' => 'mock_access_token',
            'refresh_token' => 'mock_refresh_token',
            'expires' => time(),
        ];

        $token = $this->getAccessToken($options);
        $jsonToken = json_encode($token);

        $this->assertEquals($options, json_decode($jsonToken, true));
    }
}
