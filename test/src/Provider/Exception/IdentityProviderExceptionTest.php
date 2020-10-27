<?php

namespace League\OAuth2\Client\Test\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class IdentityProviderExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testIdentityProviderException()
    {
        $result = [
            'error' => 'message',
            'code' => 404
        ];
        $exception = new IdentityProviderException($result['error'], $result['code'], $result);

        $this->assertEquals($result, $exception->getResponseBody());
        $this->assertEquals($result['error'], $exception->getMessage());
        $this->assertEquals($result['code'], $exception->getCode());
    }
}
