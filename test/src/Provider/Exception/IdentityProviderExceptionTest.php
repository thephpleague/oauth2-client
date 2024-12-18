<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use PHPUnit\Framework\TestCase;

class IdentityProviderExceptionTest extends TestCase
{
    public function testIdentityProviderException(): void
    {
        $result = [
            'error' => 'message',
            'code' => 404,
        ];
        $exception = new IdentityProviderException($result['error'], $result['code'], $result);

        $this->assertEquals($result, $exception->getResponseBody());
        $this->assertEquals($result['error'], $exception->getMessage());
        $this->assertEquals($result['code'], $exception->getCode());
    }
}
