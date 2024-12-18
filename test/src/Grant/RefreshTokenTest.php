<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Grant;

use BadMethodCallException;
use Closure;
use League\OAuth2\Client\Grant\RefreshToken;

class RefreshTokenTest extends GrantTestCase
{
    /**
     * @inheritDoc
     */
    public static function providerGetAccessToken(): array
    {
        return [
            ['refresh_token', ['refresh_token' => 'mock_refresh_token']],
        ];
    }

    protected function getParamExpectation(): Closure
    {
        return fn (array $body) => isset($body['grant_type'])
                && $body['grant_type'] === 'refresh_token';
    }

    public function testToString(): void
    {
        $grant = new RefreshToken();
        $this->assertEquals('refresh_token', (string) $grant);
    }

    public function testInvalidRefreshToken(): void
    {
        $this->expectException(BadMethodCallException::class);

        $this->getMockProvider()->getAccessToken('refresh_token', ['invalid_refresh_token' => 'mock_refresh_token']);
    }
}
