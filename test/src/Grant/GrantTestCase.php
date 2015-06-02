<?php

namespace League\OAuth2\Client\Test\Grant;

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
     * Callback to test access token request params.
     *
     * @return Closure
     */
    abstract protected function getParamsExpectation();

    /**
     * @dataProvider providerGetAccessToken
     */
    public function testGetAccessToken($grant, array $params = [])
    {
        $stream = m::mock('Psr\Http\Message\StreamableInterface');
        $stream->shouldReceive('__toString')->times(1)->andReturn(
            '{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1}'
        );

        $response = m::mock('Ivory\HttpAdapter\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn($stream);

        $client = m::mock('Ivory\HttpAdapter\HttpAdapterInterface');
        $client->shouldReceive('post')->with(
            $this->provider->urlAccessToken(),
            $this->provider->getHeaders(),
            m::on($this->getParamsExpectation())
        )->times(1)->andReturn($response);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken($grant, $params);
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $token);
    }
}
