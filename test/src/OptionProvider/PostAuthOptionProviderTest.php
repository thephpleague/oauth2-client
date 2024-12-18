<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\OptionProvider;

use League\OAuth2\Client\OptionProvider\PostAuthOptionProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(PostAuthOptionProvider::class)]
#[CoversMethod(PostAuthOptionProvider::class, 'getAccessTokenOptions')]
class PostAuthOptionProviderTest extends TestCase
{
    public function testGetAccessTokenOptions(): void
    {
        $provider = new PostAuthOptionProvider();

        $options = $provider->getAccessTokenOptions(AbstractProvider::METHOD_POST, [
            'client_id' => 'test',
            'client_secret' => 'test',
        ]);

        $this->assertArrayHasKey('headers', $options);
        $this->assertEquals('client_id=test&client_secret=test', $options['body'] ?? null);
    }
}
