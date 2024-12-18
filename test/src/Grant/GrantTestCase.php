<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Grant;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use League\OAuth2\Client\Test\Provider\Fake as MockProvider;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

use function call_user_func;
use function parse_str;

abstract class GrantTestCase extends TestCase
{
    protected function getMockProvider(): MockProvider
    {
        return new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);
    }

    /**
     * Test that the grant's __toString method.
     */
    abstract public function testToString(): void;

    /**
     * Data provider for access token tests.
     *
     * @return array<array-key, mixed>
     */
    abstract public static function providerGetAccessToken(): array;

    /**
     * Callback to test access token request parameters.
     *
     * @return Closure(array<string, mixed>): bool
     */
    abstract protected function getParamExpectation(): Closure;

    /**
     * @param array<string, mixed> $params
     */
    #[DataProvider('providerGetAccessToken')]
    public function testGetAccessToken(string $grant, array $params = []): void
    {
        $provider = $this->getMockProvider();

        /** @var StreamInterface & MockInterface $stream */
        $stream = Mockery::spy(StreamInterface::class)->makePartial();
        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn(
                '{"access_token": "mock_access_token", "expires": 3600, '
                . '"refresh_token": "mock_refresh_token", "uid": 1}',
            );

        /** @var ResponseInterface & MockInterface $response */
        $response = Mockery::spy(ResponseInterface::class)->makePartial();
        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);
        $response
            ->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn(['application/json']);

        /** @var ClientInterface & MockInterface $client */
        $client = Mockery::spy(ClientInterface::class)->makePartial();
        $client
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (RequestInterface $request) {
                parse_str((string) $request->getBody(), $body);

                /** @var array<string, string> $bodyTyped */
                $bodyTyped = $body;

                return call_user_func($this->getParamExpectation(), $bodyTyped);
            })
            ->andReturn($response);

        $provider->setHttpClient($client);
        $token = $provider->getAccessToken($grant, $params);

        $this->assertSame('mock_access_token', $token->getToken());
    }
}
