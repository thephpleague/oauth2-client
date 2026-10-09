<?php

namespace League\OAuth2\Client\Test\Provider;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Test\Provider\Fake as MockProvider;
use League\OAuth2\Client\Tool\ProviderRedirectTrait;
use League\OAuth2\Client\Tool\RequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

class PsrHttpClientTest extends TestCase
{
    private function getPsrClient(array $responses)
    {
        return new class($responses) implements ClientInterface {
            public $requests = [];
            private $responses;

            public function __construct(array $responses)
            {
                $this->responses = $responses;
            }

            public function sendRequest(RequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                $this->requests[] = $request;
                $next = array_shift($this->responses);

                if ($next instanceof \Exception) {
                    throw $next;
                }

                return $next;
            }
        };
    }

    private function getProvider(array $collaborators = [])
    {
        return new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ], $collaborators);
    }

    public function testPsrClientDefaultsToNull()
    {
        $this->assertNull($this->getProvider()->getPsrHttpClient());
    }

    public function testPsrClientCanBeSetViaSetterAndCollaborator()
    {
        $client = $this->getPsrClient([]);

        $provider = $this->getProvider();
        $this->assertSame($provider, $provider->setPsrHttpClient($client));
        $this->assertSame($client, $provider->getPsrHttpClient());

        $provider = $this->getProvider(['psrHttpClient' => $client]);
        $this->assertSame($client, $provider->getPsrHttpClient());
    }

    public function testGetResponseUsesPsrClient()
    {
        $response = new Response(200);
        $client = $this->getPsrClient([$response]);
        $request = new Request('GET', 'http://example.com');

        $this->assertSame($response, $this->getProvider(['psrHttpClient' => $client])->getResponse($request));
        $this->assertSame([$request], $client->requests);
    }

    public function testErrorStatusIsParsedWithoutException()
    {
        $client = $this->getPsrClient([
            new Response(400, ['Content-Type' => 'application/json'], '{"error":"invalid_request","code":400}'),
        ]);
        $provider = $this->getProvider(['psrHttpClient' => $client]);

        $this->expectException(IdentityProviderException::class);

        $provider->getParsedResponse(new Request('GET', 'http://example.com'));
    }

    public function testClientExceptionIsPropagated()
    {
        $exception = new class('network down') extends \RuntimeException implements ClientExceptionInterface {
        };
        $provider = $this->getProvider(['psrHttpClient' => $this->getPsrClient([$exception])]);

        $this->expectException(ClientExceptionInterface::class);

        $provider->getResponse(new Request('GET', 'http://example.com'));
    }

    public function testRedirectsAreFollowedWithPsrClient()
    {
        $client = $this->getPsrClient([
            new Response(302, ['Location' => 'http://example.com/next']),
            new Response(200),
        ]);
        $provider = new class([], ['psrHttpClient' => $client]) extends MockProvider {
            use ProviderRedirectTrait;
        };

        $response = $provider->getResponse(new Request('GET', 'http://example.com/start'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(2, $client->requests);
        $this->assertSame('http://example.com/next', (string) $client->requests[1]->getUri());
    }

    public function testRequestFactoryWithPsr17Factories()
    {
        if (!class_exists(HttpFactory::class)) {
            $this->markTestSkipped('guzzlehttp/psr7 >= 2 is required for its PSR-17 factory.');
        }

        $psr17 = new HttpFactory();
        $factory = new RequestFactory($psr17, $psr17, $psr17);

        $request = $factory->getRequest('POST', 'http://example.com/token', ['X-Foo' => 'bar'], 'a=b', '1.0');

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('http://example.com/token', (string) $request->getUri());
        $this->assertSame('bar', $request->getHeaderLine('X-Foo'));
        $this->assertSame('a=b', (string) $request->getBody());
        $this->assertSame('1.0', $request->getProtocolVersion());
        $this->assertSame('http://example.com/x', (string) $factory->getUri('http://example.com/x'));
    }

    public function testRequestFactoryRequiresStreamFactoryForBody()
    {
        if (!class_exists(HttpFactory::class)) {
            $this->markTestSkipped('guzzlehttp/psr7 >= 2 is required for its PSR-17 factory.');
        }

        $factory = new RequestFactory(new HttpFactory());

        $this->expectException(\LogicException::class);

        $factory->getRequest('POST', 'http://example.com', [], 'a=b');
    }

    public function testRequestFactoryDefaultsToGuzzle()
    {
        $factory = new RequestFactory();

        $this->assertInstanceOf(Request::class, $factory->getRequest('GET', 'http://example.com'));
    }
}
