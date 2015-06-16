<?php

namespace League\OAuth2\Client\Test\Grant;

use League\OAuth2\Client\Grant\ClientCredentials;

class ClientCredentialsTest extends GrantTestCase
{
    public function providerGetAccessToken()
    {
        return [
            ['client_credentials'],
        ];
    }

    protected function getParamExpectation()
    {
        return function ($body) {
            return !empty($body['grant_type'])
                && $body['grant_type'] === 'client_credentials';
        };
    }

    public function testToString()
    {
        $grant = new ClientCredentials();
        $this->assertEquals('client_credentials', (string) $grant);
    }
}
