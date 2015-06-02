<?php

namespace League\OAuth2\Client\Test\Grant;

use League\OAuth2\Client\Grant\AuthorizationCode;

class AuthorizationCodeTest extends GrantTestCase
{
    public function providerGetAccessToken()
    {
        return [
            ['authorization_code', ['code' => 'mock_code']],
        ];
    }

    protected function getParamsExpectation()
    {
        return function ($params) {
            return !empty($params['grant_type'])
                && $params['grant_type'] === 'authorization_code'
                && !empty($params['code']);
        };
    }

    public function testToString()
    {
        $grant = new AuthorizationCode();
        $this->assertEquals('authorization_code', (string) $grant);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testInvalidRefreshToken()
    {
        $this->provider->getAccessToken('authorization_code', ['invalid_code' => 'mock_authorization_code']);
    }
}
