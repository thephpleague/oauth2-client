<?php

namespace League\OAuth2\Client\Test\Provider\Exception;

use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\ResponseParsingException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseParsingExceptionTest extends TestCase
{
    private $body = 'foo';

    protected function generateResponseParsingException()
    {
        return new ResponseParsingException(new Response('401'), $this->body);
    }

    public function testGetResponse()
    {
        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->generateResponseParsingException()->getResponse()
        );
    }

    public function testGetResponseBody()
    {
        $this->assertSame(
            $this->body,
            $this->generateResponseParsingException()->getResponseBody()
        );
    }

    public function testMissingMessage()
    {
        $this->assertNotEmpty($this->generateResponseParsingException()->getMessage());
    }
}
