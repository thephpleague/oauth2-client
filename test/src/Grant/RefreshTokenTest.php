<?php

namespace League\OAuth2\Client\Test\Grant;

use League\OAuth2\Client\Grant\RefreshToken;

class RefreshTokenTest extends GrantTestCase
{
    public function providerGetAccessToken()
    {
        return [
            ['refresh_token', ['refresh_token' => 'mock_refresh_token']],
        ];
    }

    protected function getParamExpectation()
    {
        return function ($body) {
            return !empty($body['grant_type'])
                && $body['grant_type'] === 'refresh_token';
        };
    }

    public function testToString()
    {
        $grant = new RefreshToken();
        $this->assertEquals('refresh_token', (string) $grant);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testInvalidRefreshToken()
    {
        $this->provider->getAccessToken('refresh_token', ['invalid_refresh_token' => 'mock_refresh_token']);
    }
}
