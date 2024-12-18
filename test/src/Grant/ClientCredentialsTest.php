<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Grant;

use Closure;
use League\OAuth2\Client\Grant\ClientCredentials;

class ClientCredentialsTest extends GrantTestCase
{
    /**
     * @inheritDoc
     */
    public static function providerGetAccessToken(): array
    {
        return [
            ['client_credentials'],
        ];
    }

    protected function getParamExpectation(): Closure
    {
        return fn (array $body) => isset($body['grant_type'])
                && $body['grant_type'] === 'client_credentials';
    }

    public function testToString(): void
    {
        $grant = new ClientCredentials();
        $this->assertEquals('client_credentials', (string) $grant);
    }
}
