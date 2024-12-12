<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Grant;

use BadMethodCallException;
use Closure;
use League\OAuth2\Client\Grant\Password;

class PasswordTest extends GrantTestCase
{
    /**
     * @inheritDoc
     */
    public static function providerGetAccessToken(): array
    {
        return [
            ['password', ['username' => 'mock_username', 'password' => 'mock_password']],
        ];
    }

    protected function getParamExpectation(): Closure
    {
        return fn ($body) => isset($body['grant_type'])
                && $body['grant_type'] === 'password'
                && isset($body['username'])
                && isset($body['password'])
                && isset($body['scope']);
    }

    public function testToString(): void
    {
        $grant = new Password();
        $this->assertEquals('password', (string) $grant);
    }

    public function testInvalidUsername(): void
    {
        $this->expectException(BadMethodCallException::class);

        $this->getMockProvider()->getAccessToken(
            'password',
            ['invalid_username' => 'mock_username', 'password' => 'mock_password'],
        );
    }

    public function testInvalidPassword(): void
    {
        $this->expectException(BadMethodCallException::class);

        $this->getMockProvider()->getAccessToken(
            'password',
            ['username' => 'mock_username', 'invalid_password' => 'mock_password'],
        );
    }
}
