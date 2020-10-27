<?php

namespace League\OAuth2\Client\Test\Grant;

use BadMethodCallException;
use League\OAuth2\Client\Grant\Password;

class PasswordTest extends GrantTestCase
{
    public function providerGetAccessToken()
    {
        return [
            ['password', ['username' => 'mock_username', 'password' => 'mock_password']],
        ];
    }

    protected function getParamExpectation()
    {
        return function ($body) {
            return !empty($body['grant_type'])
                && $body['grant_type'] === 'password'
                && !empty($body['username'])
                && !empty($body['password']);
        };
    }

    public function testToString()
    {
        $grant = new Password();
        $this->assertEquals('password', (string) $grant);
    }

    public function testInvalidUsername()
    {
        $this->expectException(BadMethodCallException::class);

        $this->getMockProvider()->getAccessToken('password', ['invalid_username' => 'mock_username', 'password' => 'mock_password']);
    }

    public function testInvalidPassword()
    {
        $this->expectException(BadMethodCallException::class);

        $this->getMockProvider()->getAccessToken('password', ['username' => 'mock_username', 'invalid_password' => 'mock_password']);
    }
}
