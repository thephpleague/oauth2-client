<?php

namespace League\OAuth2\Client\Test\Grant;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use League\OAuth2\Client\Test\Provider\Fake as MockProvider;
use Mockery as m;

abstract class GrantTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var \League\OAuth2\Client\Provider\AbstractProvider */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new MockProvider(array(
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ));
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
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
        $stream = m::mock(StreamInterface::class);
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            '{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}'
        );

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);
        $response->shouldReceive('getHeader')->with('content-type')->times(1)->andReturn('application/json');

        $paramCheck = $this->getParamExpectation();

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->with(
            $request = m::on(function ($request) use ($paramCheck) {
                parse_str((string) $request->getBody(), $body);
                return $paramCheck($body);
            })
        )->times(1)->andReturn($response);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken($grant, $params);
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $token);
    }
}
