<?php

namespace League\OAuth2\Client\Test\Grant;

use BadMethodCallException;
use League\OAuth2\Client\Grant\AuthorizationCode;

class AuthorizationCodeTest extends GrantTestCase
{
    public function providerGetAccessToken()
    {
        return [
            ['authorization_code', ['code' => 'mock_code']],
        ];
    }

    protected function getParamExpectation()
    {
        return function ($body) {
            return !empty($body['grant_type'])
                && $body['grant_type'] === 'authorization_code'
                && !empty($body['code']);
        };
    }

    public function testToString()
    {
        $grant = new AuthorizationCode();
        $this->assertEquals('authorization_code', (string) $grant);
    }

    public function testInvalidRefreshToken()
    {
        $this->expectException(BadMethodCallException::class);

        $this->getMockProvider()->getAccessToken('authorization_code', ['invalid_code' => 'mock_authorization_code']);
    }
}
