<?php

namespace League\OAuth2\Client\Test\Exception;

use League\OAuth2\Client\Exception\IDPException;

class IDPExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testGetTypeErrorMessage()
    {
        $exception = new IDPException(array('error' => 'message'));

        $this->assertEquals('message', $exception->getType());
    }

    public function testGetTypeMessage()
    {
        $exception = new IDPException(array('message' => 'message'));

        $this->assertEquals('Exception', $exception->getType());
    }

    public function testGetTypeEmpty()
    {
        $exception = new IDPException([]);

        $this->assertEquals('Exception', $exception->getType());
    }

    public function testAsString()
    {
        $exception = new IDPException(array('error' => 'message'));

        $this->assertEquals('message: message', (string)$exception);
    }

    public function testAsStringWithCode()
    {
        $exception = new IDPException(array('error' => 'message', 'code' => 404));

        $this->assertEquals('message: 404: message', (string)$exception);
    }
}
