<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Tool;

use GuzzleHttp\Exception\BadResponseException;
use InvalidArgumentException;
use League\OAuth2\Client\Tool\ProviderRedirectTrait;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function rand;
use function uniqid;

class ProviderRedirectTraitTest extends TestCase
{
    use ProviderRedirectTrait;

    private ClientInterface $httpClient;

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    public function setHttpClient(ClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function testRedirectLimitDefault(): void
    {
        $this->assertEquals(2, $this->getRedirectLimit());
    }

    public function testSetRedirectLimit(): void
    {
        $redirectLimit = rand(3, 5);
        $this->setRedirectLimit($redirectLimit);
        $this->assertEquals($redirectLimit, $this->getRedirectLimit());
    }

    public function testSetRedirectLimitThrowsExceptionWhenZeroProvided(): void
    {
        $redirectLimit = 0;

        $this->expectException(InvalidArgumentException::class);

        $this->setRedirectLimit($redirectLimit);
    }

    public function testSetRedirectLimitThrowsExceptionWhenNegativeIntegerProvided(): void
    {
        $redirectLimit = -10;

        $this->expectException(InvalidArgumentException::class);

        $this->setRedirectLimit($redirectLimit);
    }

    public function testClientLimitsRedirectResponse(): void
    {
        $redirectLimit = rand(3, 5);
        $status = rand(301, 399);
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

    public function testClientLimitsRedirectLoopWhenRedirectNotDetected(): void
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

    public function testClientErrorReturnsResponse(): void
    {
        $status = rand(400, 500);

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
