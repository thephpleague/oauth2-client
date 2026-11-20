<?php

namespace League\OAuth2\Client\Test\Tool;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use League\OAuth2\Client\Tool\ProviderRedirectTrait;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class ProviderRedirectTraitTest extends TestCase
{
    use ProviderRedirectTrait;

    /**
     * @var ClientInterface
     */
    private $httpClient;

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
            ->shouldReceive('getUri')
            ->andReturn(new Uri('https://example.com/token'));
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

    /**
     * Returns a client mock that answers with the given responses in order
     * and records every request it is asked to send.
     *
     * @param  ResponseInterface[] $responses
     * @param  RequestInterface[]  $sent
     * @return ClientInterface
     */
    private function getRecordingClient(array $responses, &$sent)
    {
        $sent = [];

        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('send')
            ->andReturnUsing(function (RequestInterface $request) use (&$sent, &$responses) {
                $sent[] = $request;
                return array_shift($responses);
            });

        return $client;
    }

    private function getTokenRequest($uri)
    {
        return new Request(
            'POST',
            $uri,
            ['Authorization' => 'Basic Y2xpZW50OnNlY3JldA=='],
            'grant_type=authorization_code&code=abc&client_secret=secret'
        );
    }

    public function testClientFollowsSameOriginRedirect()
    {
        $client = $this->getRecordingClient([
            new Response(307, ['Location' => 'https://example.com/v2/token']),
            new Response(200, [], '{"access_token":"mock"}'),
        ], $sent);

        $this->setHttpClient($client);
        $finalResponse = $this->getResponse($this->getTokenRequest('https://example.com/token'));

        $this->assertSame(200, $finalResponse->getStatusCode());
        $this->assertCount(2, $sent);
        $this->assertSame('https://example.com/v2/token', (string) $sent[1]->getUri());
    }

    public function testClientResolvesRelativeRedirect()
    {
        $client = $this->getRecordingClient([
            new Response(307, ['Location' => '/v2/token']),
            new Response(200, [], '{"access_token":"mock"}'),
        ], $sent);

        $this->setHttpClient($client);
        $this->getResponse($this->getTokenRequest('https://example.com/token'));

        $this->assertCount(2, $sent);
        $this->assertSame('https://example.com/v2/token', (string) $sent[1]->getUri());
    }

    /**
     * @dataProvider crossOriginRedirectProvider
     */
    #[DataProvider('crossOriginRedirectProvider')]
    public function testClientDoesNotFollowCrossOriginRedirect($location)
    {
        $redirect = new Response(307, ['Location' => $location]);
        $client = $this->getRecordingClient([
            $redirect,
            new Response(200, [], '{"access_token":"mock"}'),
        ], $sent);

        $this->setHttpClient($client);
        $finalResponse = $this->getResponse($this->getTokenRequest('https://example.com/token'));

        $this->assertSame($redirect, $finalResponse);
        $this->assertCount(1, $sent);
    }

    public static function crossOriginRedirectProvider()
    {
        return [
            'different host' => ['https://attacker.example/token'],
            'protocol-relative different host' => ['//attacker.example/token'],
            'scheme downgrade' => ['http://example.com/token'],
            'different port' => ['https://example.com:8443/token'],
        ];
    }
}
