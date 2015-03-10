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

    public function testGetResponseBody()
    {
        $exception = new IDPException(array('error' => 'message', 'code' => 404));

        $this->assertEquals(
            [
                'error' => 'message',
                'code'  => 404
            ],
            $exception->getResponseBody()
        );
    }

    public function testEmptyMessage()
    {
        $exception = new IDPException(array('error' => 'error_message', 'message' => ''));
        // message should be the error text since message isn't specifically defined
        $this->assertEquals('error_message', $exception->getMessage());
    }

    public function testNonEmptyMessage()
    {
        $exception = new IDPException(array('error' => 'error_message', 'message' => 'message'));
        // message should be the error text since message isn't specifically defined
        $this->assertEquals('message', $exception->getMessage());
    }

    public function testEmptyError()
    {
        $exception = new IDPException(array('error' => '', 'message' => 'message'));
        // message should be the error text since message isn't specifically defined
        $this->assertEquals('message', $exception->getMessage());
    }

    public function testEmptyAndMessage()
    {
        $exception = new IDPException(array('error' => '', 'message' => ''));
        // message should be the error text since message isn't specifically defined
        $this->assertEquals('Unknown Error.', $exception->getMessage());
    }
}
