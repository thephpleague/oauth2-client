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

    public function testAsStringCustom()
    {
        $exception = new IDPException(array('custom_error' => 'message'), 'message');

        $this->assertEquals('Exception: message', (string)$exception);
    }

    public function testAsStringWithCode()
    {
        $exception = new IDPException(array('error' => 'message', 'code' => 404));

        $this->assertEquals('message: 404: message', (string)$exception);
    }

    public function testAsStringWithCodeCustom()
    {
        $exception = new IDPException(array('custom_error' => 'message', 'custom_code' => 404), 'message', 404);

        $this->assertEquals('Exception: 404: message', (string)$exception);
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

    public function testGetResponseBodyCustom()
    {
        $exception = new IDPException(array('custom_error' => 'message', 'custom_code' => 404));

        $this->assertEquals(
            [
                'custom_error' => 'message',
                'custom_code'  => 404
            ],
            $exception->getResponseBody()
        );
    }

    public function testEmptyMessage()
    {
        $exception = new IDPException(array('error' => 'error_message', 'message' => ''));
        $this->assertEquals('error_message', $exception->getMessage());
    }

    public function testNonEmptyErrorAndMessage()
    {
        $exception = new IDPException(array('error' => 'error_message', 'message' => 'message'));
        $this->assertEquals('error_message', $exception->getMessage());
    }

    public function testEmptyError()
    {
        $exception = new IDPException(array('error' => '', 'message' => 'message'));
        $this->assertEquals('message', $exception->getMessage());
    }

    public function testEmptyErrorAndMessage()
    {
        $exception = new IDPException(array('error' => '', 'message' => ''));
        $this->assertEquals('Unknown Error.', $exception->getMessage());
    }

    public function testGetMessageCustom()
    {
        $exception = new IDPException(array('custom_error' => 'error_message', 'custom_code' => 404), 'error_message', 404);
        $this->assertEquals('error_message', $exception->getMessage());
    }

    public function testGetCodeCustom()
    {
        $exception = new IDPException(array('custom_error' => 'error_message', 'custom_code' => 404), 'error_message', 404);
        $this->assertEquals(404, $exception->getCode());
    }
}
