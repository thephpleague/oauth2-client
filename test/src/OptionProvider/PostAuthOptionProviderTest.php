<?php

namespace League\OAuth2\Client\Test\OptionProvider;

use League\OAuth2\Client\OptionProvider\PostAuthOptionProvider;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\AbstractProvider;

/**
 * @coversDefaultClass \League\OAuth2\Client\OptionProvider\PostAuthOptionProvider
 */
class PostAuthOptionProviderTest extends TestCase
{
    /**
     * @covers ::getAccessTokenOptions
     */
    public function testGetAccessTokenOptions()
    {
        $provider = new PostAuthOptionProvider();

        $options = $provider->getAccessTokenOptions(AbstractProvider::METHOD_POST, [
            'client_id' => 'test',
            'client_secret' => 'test'
        ]);

        $this->assertArrayHasKey('headers', $options);
        $this->assertEquals('client_id=test&client_secret=test', $options['body']);
    }
}
