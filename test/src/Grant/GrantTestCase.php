<?php

namespace League\OAuth2\Client\Test\Grant;

use Eloquent\Phony\Phpunit\Phony;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Test\Provider\Fake as MockProvider;

abstract class GrantTestCase extends TestCase
{
    /**
     * @var \League\OAuth2\Client\Provider\AbstractProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new MockProvider(array(
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ));
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
        // Mock
        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns(
            '{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}'
        );

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());
        $response->getHeader->with('content-type')->returns('application/json');

        $client = Phony::mock(ClientInterface::class);
        $client->send->returns($response->get());

        // Execute
        $this->provider->setHttpClient($client->get());
        $token = $this->provider->getAccessToken($grant, $params);

        // Verify
        $this->assertInstanceOf(AccessTokenInterface::class, $token);

        Phony::inOrder(
            $client->send->times(1)->calledWith(
                $this->callback(function ($request) {
                    parse_str((string) $request->getBody(), $body);
                    return call_user_func($this->getParamExpectation(), $body);
                })
            ),
            $response->getBody->times(1)->called(),
            $stream->__toString->times(1)->called(),
            $response->getHeader->times(1)->called()
        );
    }
}
