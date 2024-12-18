<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Grant;

use BadMethodCallException;
use Closure;
use League\OAuth2\Client\Grant\AuthorizationCode;

class AuthorizationCodeTest extends GrantTestCase
{
    /**
     * @inheritDoc
     */
    public static function providerGetAccessToken(): array
    {
        return [
            ['authorization_code', ['code' => 'mock_code']],
        ];
    }

    protected function getParamExpectation(): Closure
    {
        return fn (array $body) => isset($body['grant_type'])
                && $body['grant_type'] === 'authorization_code'
                && isset($body['code']);
    }

    public function testToString(): void
    {
        $grant = new AuthorizationCode();
        $this->assertEquals('authorization_code', (string) $grant);
    }

    public function testInvalidRefreshToken(): void
    {
        $this->expectException(BadMethodCallException::class);

        $this->getMockProvider()->getAccessToken('authorization_code', ['invalid_code' => 'mock_authorization_code']);
    }
}
