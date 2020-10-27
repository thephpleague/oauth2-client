<?php

namespace League\OAuth2\Client\Test\Tool;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use InvalidArgumentException;
use League\OAuth2\Client\Tool\ProviderRedirectTrait;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class ProviderRedirectTraitTest extends TestCase
{
    use ProviderRedirectTrait;

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function testRedirectLimitDefault()
    {
        $this->assertEquals(2, $this->getRedirectLimit());
    }

    public function testSetRedirectLimit()
    {
        $redirectLimit = rand(3, 5);
        $this->setRedirectLimit($redirectLimit);
        $this->assertEquals($redirectLimit, $this->getRedirectLimit());
    }

    public function testSetRedirectLimitThrowsExceptionWhenNonNumericProvided()
    {
        $redirectLimit = 'florp';

        $this->expectException(InvalidArgumentException::class);

        $this->setRedirectLimit($redirectLimit);
    }

    public function testSetRedirectLimitThrowsExceptionWhenZeroProvided()
    {
        $redirectLimit = 0;

        $this->expectException(InvalidArgumentException::class);

        $this->setRedirectLimit($redirectLimit);
    }

    public function testSetRedirectLimitThrowsExceptionWhenNegativeIntegerProvided()
    {
        $redirectLimit = -10;

        $this->expectException(InvalidArgumentException::class);

        $this->setRedirectLimit($redirectLimit);
    }

    public function testClientLimitsRedirectResponse()
    {
        $redirectLimit = rand(3, 5);
        $status = rand(301,399);
        $redirectUrl = uniqid();

        $request = Mockery::mock(RequestInterface::class);
        $request
            ->shouldReceive('withUri')
            ->andReturnSelf();

        $response = Mockery::mock(ResponseInterface::class, [
            'getStatusCode' => $status,
        ]);
        $response
            ->shouldReceive('hasHeader')
            ->with('Location')
            ->andReturnTrue();
        $response
            ->shouldReceive('getHeader')
            ->with('Location')
            ->andReturn([$redirectUrl]);

        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('send')
            ->times($redirectLimit)
            ->andReturn($response);

        $this->setHttpClient($client)->setRedirectLimit($redirectLimit);
        $finalResponse = $this->getResponse($request);

        $this->assertInstanceOf(ResponseInterface::class, $finalResponse);
    }

    public function testClientLimitsRedirectLoopWhenRedirectNotDetected()
    {
        $redirectLimit = rand(3, 5);
        $status = 200;

        $request = Mockery::mock(RequestInterface::class);
        $request
            ->shouldReceive('withUri')
            ->andReturnSelf();

        $response = Mockery::mock(ResponseInterface::class, [
            'getStatusCode' => $status,
        ]);
        $response
            ->shouldReceive('hasHeader')
            ->with('Location')
            ->andReturnTrue();

        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('send')
            ->once()
            ->andReturn($response);

        $this->setHttpClient($client)->setRedirectLimit($redirectLimit);
        $finalResponse = $this->getResponse($request);

        $this->assertInstanceOf(ResponseInterface::class, $finalResponse);
    }

    public function testClientErrorReturnsResponse()
    {
        $status = rand(400, 500);
        $result = ['foo' => 'bar'];

        $request = Mockery::mock(RequestInterface::class);
        $request
            ->shouldReceive('withUri')
            ->andReturnSelf();

        $response = Mockery::mock(ResponseInterface::class, [
            'getStatusCode' => $status,
        ]);

        $exception = new BadResponseException('test exception', $request, $response);

        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('send')
            ->andThrow($exception);

        $this->setHttpClient($client);
        $finalResponse = $this->getResponse($request);

        $this->assertInstanceOf(ResponseInterface::class, $finalResponse);
    }
}
