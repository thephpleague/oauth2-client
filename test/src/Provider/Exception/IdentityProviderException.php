<?php

namespace League\OAuth2\Client\Test\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class IdentityProviderExceptionTest extends \PHPUnit\Framework\TestCase
{
    protected $result;

    /**
     * @var IdentityProviderException
     */
    protected $exception;

    protected function setUp()
    {
        $this->result = [
            'error' => 'message',
            'code' => 404
        ];
        $this->exception = new IdentityProviderException($this->result['error'], $this->result['code'], $this->result);
    }

    public function testGetType()
    {
        $this->assertEquals('Exception', $this->exception->getType());
    }

    public function testAsString()
    {
        $this->assertEquals('Exception: 404: message', (string)$this->exception);
    }

    public function testGetResponseBody()
    {

        $this->assertEquals(
            $this->result,
            $this->exception->getResponseBody()
        );
    }

    public function testGetMessage()
    {
        $this->assertEquals(
            $this->result['error'],
            $this->exception->getMessage()
        );
    }

    public function testGetCode()
    {
        $this->assertEquals(
            $this->result['code'],
            $this->exception->getCode()
        );
    }
}
