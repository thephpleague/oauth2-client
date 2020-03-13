<?php

namespace League\OAuth2\Client\Test\Provider\Exception;

use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\ResponseParsingException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseParsingExceptionTest extends TestCase
{
    protected $result;

    /**
     * @var ResponseParsingException
     */
    protected $exception;

    protected function setUp()
    {
        $this->result = [
            'response' => new Response('401'),
            'body' => ''
        ];
        $this->exception = new ResponseParsingException($this->result['response'], $this->result['body']);
    }

    public function testGetResponse()
    {
        $this->assertInstanceOf(ResponseInterface::class, $this->exception->getResponse());
    }

    public function testGetResponseBody()
    {
        $this->assertSame($this->result['body'], $this->exception->getResponseBody());
    }
}
