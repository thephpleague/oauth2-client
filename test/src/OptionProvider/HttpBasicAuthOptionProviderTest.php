<?php

namespace League\OAuth2\Client\Test\OptionProvider;

use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\AbstractProvider;

/**
 * @coversDefaultClass \League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider
 */
class HttpBasicAuthOptionProviderTest extends TestCase
{
    /**
     * @var HttpBasicAuthOptionProvider
     */
    protected $provider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->provider = new HttpBasicAuthOptionProvider();
    }

    /**
     * data provider for testGetAccessTokenOptionsException
     * @return array
     */
    public function providerTestGetAccessTokenOptionsException()
    {
        return [
            [['client_id' => 'test']],
            [['client_secret' => 'test']],
        ];
    }

    /**
     * @covers ::getAccessTokenOptions
     * @dataProvider providerTestGetAccessTokenOptionsException
     * @expectedException \InvalidArgumentException
     * @param array $params
     */
    public function testGetAccessTokenOptionsException($params)
    {
        $this->provider->getAccessTokenOptions(AbstractProvider::METHOD_POST, $params);
    }

    /**
     * @covers ::getAccessTokenOptions
     */
    public function testGetAccessTokenOptions()
    {
        $options = $this->provider->getAccessTokenOptions(AbstractProvider::METHOD_POST, [
            'client_id' => 'test',
            'client_secret' => 'test',
            'redirect_uri' => 'http://localhost'
        ]);
        $this->assertEquals('Basic ' . base64_encode('test:test'), $options['headers']['Authorization']);
        $this->assertNotContains('client_id', $options['body']);
        $this->assertNotContains('client_secret', $options['body']);
    }
}
