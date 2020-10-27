<?php

namespace League\OAuth2\Client\Test\Grant;

use GuzzleHttp\ClientInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Test\Provider\Fake as MockProvider;

abstract class GrantTestCase extends TestCase
{
    protected function getMockProvider()
    {
        return new MockProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    /**
     * Test that the grant's __toString method.
     */
    abstract public function testToString();

    /**
     * Data provider for access token tests.
     *
     * @return array
     */
    abstract public function providerGetAccessToken();

    /**
     * Callback to test access token request parameters.
     *
     * @return Closure
     */
    abstract protected function getParamExpectation();

    /**
     * @dataProvider providerGetAccessToken
     */
    public function testGetAccessToken($grant, array $params = [])
    {
        $provider = $this->getMockProvider();

        /** @var StreamInterface & MockInterface $stream */
        $stream = Mockery::spy(StreamInterface::class)->makePartial();
        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}');

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
            ->andReturn('application/json');

        /** @var ClientInterface & MockInterface $client */
        $client = Mockery::spy(ClientInterface::class)->makePartial();
        $client
            ->shouldReceive('send')
            ->once()
            ->withArgs(function ($request) {
                parse_str((string) $request->getBody(), $body);
                return call_user_func($this->getParamExpectation(), $body);
            })
            ->andReturn($response);

        $provider->setHttpClient($client);
        $token = $provider->getAccessToken($grant, $params);

        $this->assertInstanceOf(AccessTokenInterface::class, $token);
    }
}
