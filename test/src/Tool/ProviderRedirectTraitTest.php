<?php

namespace League\OAuth2\Client\Test\Tool;

use Eloquent\Phony\Phpunit\Phony;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use League\OAuth2\Client\Tool\ProviderRedirectTrait;
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

    /**
     * @expectedException InvalidArgumentException
     **/
    public function testSetRedirectLimitThrowsExceptionWhenNonNumericProvided()
    {
        $redirectLimit = 'florp';
        $this->setRedirectLimit($redirectLimit);
    }

    /**
     * @expectedException InvalidArgumentException
     **/
    public function testSetRedirectLimitThrowsExceptionWhenZeroProvided()
    {
        $redirectLimit = 0;
        $this->setRedirectLimit($redirectLimit);
    }

    /**
     * @expectedException InvalidArgumentException
     **/
    public function testSetRedirectLimitThrowsExceptionWhenNegativeIntegerProvided()
    {
        $redirectLimit = -10;
        $this->setRedirectLimit($redirectLimit);
    }

    public function testClientLimitsRedirectResponse()
    {
        $redirectLimit = rand(3, 5);
        $status = rand(301,399);
        $redirectUrl = uniqid();

        $request = Phony::mock(RequestInterface::class);
        $request->withUri->returns($request);

        $response = Phony::mock(ResponseInterface::class);
        $response->hasHeader->with('Location')->returns(true);
        $response->getHeader->with('Location')->returns([$redirectUrl]);
        $response->getStatusCode->returns($status);

        $client = Phony::mock(ClientInterface::class);
        $client->send->times($redirectLimit)->returns($response->get());

        $this->setHttpClient($client->get())->setRedirectLimit($redirectLimit);
        $finalResponse = $this->getResponse($request->get());

        $this->assertInstanceOf(ResponseInterface::class, $finalResponse);
    }

    public function testClientLimitsRedirectLoopWhenRedirectNotDetected()
    {
        $redirectLimit = rand(3, 5);
        $status = 200;

        $request = Phony::mock(RequestInterface::class);
        $request->withUri->returns($request);

        $response = Phony::mock(ResponseInterface::class);
        $response->hasHeader->with('Location')->returns(true);
        $response->getStatusCode->returns($status);

        $client = Phony::mock(ClientInterface::class);
        $client->send->once()->returns($response->get());

        $this->setHttpClient($client->get())->setRedirectLimit($redirectLimit);
        $finalResponse = $this->getResponse($request->get());

        $this->assertInstanceOf(ResponseInterface::class, $finalResponse);
    }

    public function testClientErrorReturnsResponse()
    {
        $status = rand(400, 500);
        $result = ['foo' => 'bar'];

        $request = Phony::mock(RequestInterface::class);
        $request->withUri->returns($request);

        $response = Phony::mock(ResponseInterface::class);
        $response->getStatusCode->returns($status);

        $exception = new BadResponseException('test exception', $request->get(), $response->get());

        $client = Phony::mock(ClientInterface::class);
        $client->send->throws($exception);

        $this->setHttpClient($client->get());
        $finalResponse = $this->getResponse($request->get());

        $this->assertInstanceOf(ResponseInterface::class, $finalResponse);
    }
}
