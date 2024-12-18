<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Token;

use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessToken;
use Mockery;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function json_decode;
use function json_encode;
use function strtotime;
use function time;
use function uniqid;

class AccessTokenTest extends TestCase
{
    /**
     * BC teardown.
     *
     * This is for backwards compatibility of older PHP versions. Ideally we would just implement a tearDown() here but
     * older PHP versions this library supports don't have return typehint support, so this is the workaround.
     */
    private static function tearDownForBackwardsCompatibility(): void
    {
        /* reset the test double time if it was set */
        AccessToken::resetTimeNow();
    }

    public function testInvalidRefreshToken(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getAccessToken(['invalid_access_token' => 'none']);

        self::tearDownForBackwardsCompatibility();
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function getAccessToken(array $options = []): AccessToken
    {
        return new AccessToken($options);
    }

    public function testExpiresInCorrection(): void
    {
        $options = ['access_token' => 'access_token', 'expires_in' => 100];
        $token = $this->getAccessToken($options);

        $expires = $token->getExpires();

        $this->assertNotNull($expires);
        $this->assertGreaterThan(time(), $expires);
        $this->assertLessThan(time() + 200, $expires);

        self::tearDownForBackwardsCompatibility();
    }

    public function testExpiresInCorrectionUsingSetTimeNow(): void
    {
        /* set fake time at 2020-01-01 00:00:00 */
        AccessToken::setTimeNow(1577836800);
        $options = ['access_token' => 'access_token', 'expires_in' => 100];
        $token = $this->getAccessToken($options);

        $expires = $token->getExpires();

        $this->assertNotNull($expires);
        $this->assertEquals(1577836900, $expires);

        self::tearDownForBackwardsCompatibility();
    }

    public function testSetTimeNow(): void
    {
        AccessToken::setTimeNow(1577836800);
        $timeNow = $this->getAccessToken(['access_token' => 'asdf'])->getTimeNow();

        $this->assertEquals(1577836800, $timeNow);

        self::tearDownForBackwardsCompatibility();
    }

    public function testResetTimeNow(): void
    {
        AccessToken::setTimeNow(1577836800);
        $token = $this->getAccessToken(['access_token' => 'asdf']);

        $this->assertEquals(1577836800, $token->getTimeNow());
        AccessToken::resetTimeNow();

        $this->assertNotEquals(1577836800, $token->getTimeNow());

        $timeBeforeAssertion = time();
        $this->assertGreaterThanOrEqual($timeBeforeAssertion, $token->getTimeNow());

        self::tearDownForBackwardsCompatibility();
    }

    public function testExpiresPastTimestamp(): void
    {
        $options = ['access_token' => 'access_token', 'expires' => strtotime('5 days ago')];
        $token = $this->getAccessToken($options);

        $this->assertTrue($token->hasExpired());

        $options = ['access_token' => 'access_token', 'expires' => 3600];
        $token = $this->getAccessToken($options);

        $this->assertFalse($token->hasExpired());

        self::tearDownForBackwardsCompatibility();
    }

    public function testGetRefreshToken(): void
    {
        $options = [
            'access_token' => 'access_token',
            'refresh_token' => uniqid(),
        ];
        $token = $this->getAccessToken($options);

        $refreshToken = $token->getRefreshToken();

        $this->assertEquals($options['refresh_token'], $refreshToken);

        self::tearDownForBackwardsCompatibility();
    }

    public function testSetRefreshToken(): void
    {
        $refreshToken = 'refresh_token';

        $options = [
            'access_token' => 'access_token',
        ];

        $token = $this->getAccessToken($options);

        $token->setRefreshToken($refreshToken);

        $this->assertEquals($refreshToken, $token->getRefreshToken());

        self::tearDownForBackwardsCompatibility();
    }

    public function testHasNotExpiredWhenPropertySetInFuture(): void
    {
        $options = [
            'access_token' => 'access_token',
        ];

        $expectedExpires = strtotime('+1 day');

        $token = Mockery::mock(AccessToken::class, [$options])->makePartial();
        $token
            ->shouldReceive('getExpires')
            ->once()
            ->andReturn($expectedExpires);

        $this->assertFalse($token->hasExpired());

        self::tearDownForBackwardsCompatibility();
    }

    public function testHasExpiredWhenPropertySetInPast(): void
    {
        $options = [
            'access_token' => 'access_token',
        ];

        $expectedExpires = strtotime('-1 day');

        $token = Mockery::mock(AccessToken::class, [$options])->makePartial();
        $token
            ->shouldReceive('getExpires')
            ->once()
            ->andReturn($expectedExpires);

        $this->assertTrue($token->hasExpired());

        self::tearDownForBackwardsCompatibility();
    }

    public function testHasExpiredWhenTimeNowIsInFuture(): void
    {
        $options = [
            'access_token' => 'mock_access_token',
            'expires' => time(),
        ];

        $token = $this->getAccessToken($options);

        $token->setTimeNow(time() + 60);

        $this->assertTrue($token->hasExpired());

        self::tearDownForBackwardsCompatibility();
    }

    public function testCannotReportExpiredWhenNoExpirationSet(): void
    {
        $options = [
            'access_token' => 'access_token',
        ];
        $token = $this->getAccessToken($options);

        $this->expectException(RuntimeException::class);

        $token->hasExpired();

        self::tearDownForBackwardsCompatibility();
    }

    public function testInvalidExpiresIn(): void
    {
         $options = [
             'access_token' => 'access_token',
             'expires_in' => 'TEXT',
         ];

         $this->expectException(InvalidArgumentException::class);

         $this->getAccessToken($options);

         self::tearDownForBackwardsCompatibility();
    }

    public function testInvalidExpiresWhenExpiresDoesNotCastToInteger(): void
    {
        $options = [
            'access_token' => 'access_token',
            'expires' => 'TEXT',
        ];

        $token = $this->getAccessToken($options);

        $this->assertSame($token->getTimeNow(), $token->getExpires());
    }

    public function testInvalidExpiresWhenExpiresCastsToInteger(): void
    {
        $options = [
            'access_token' => 'access_token',
            'expires' => '3TEXT',
        ];

        $token = $this->getAccessToken($options);

        $this->assertSame($token->getTimeNow() + 3, $token->getExpires());
        $this->assertFalse($token->hasExpired());

        self::tearDownForBackwardsCompatibility();
    }

    public function testJsonSerializable(): void
    {
        $options = [
            'access_token' => 'mock_access_token',
            'refresh_token' => 'mock_refresh_token',
            'expires' => time(),
            'resource_owner_id' => 'mock_resource_owner_id',
        ];

        $token = $this->getAccessToken($options);
        $jsonToken = json_encode($token);

        $this->assertEquals($options, json_decode((string) $jsonToken, true));

        self::tearDownForBackwardsCompatibility();
    }

    public function testValues(): void
    {
        $options = [
            'access_token' => 'mock_access_token',
            'refresh_token' => 'mock_refresh_token',
            'expires' => time(),
            'resource_owner_id' => 'mock_resource_owner_id',
            'custom_thing' => 'i am a test!',
        ];

        $token = $this->getAccessToken($options);

        $values = $token->getValues();

        $this->assertArrayHasKey('custom_thing', $values);
        $this->assertSame($options['custom_thing'], $values['custom_thing']);

        self::tearDownForBackwardsCompatibility();
    }
}
