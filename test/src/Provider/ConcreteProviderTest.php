<?php

namespace League\OAuth2\Client\Test\Provider;

use Ivory\HttpAdapter\Message\Stream\StringStream;
use Mockery as m;

abstract class ConcreteProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \League\OAuth2\Client\Provider\AbstractProvider
     */
    protected $provider;

    protected function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    protected function createMockHttpClient()
    {
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('getConfiguration')->andReturn(new \Ivory\HttpAdapter\Configuration());

        return $client;
    }

    protected function createMockResponse($responseBody)
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(new StringStream($responseBody));

        return $response;
    }
}
