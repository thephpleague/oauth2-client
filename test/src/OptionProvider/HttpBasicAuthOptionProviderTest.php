<?php

namespace League\OAuth2\Client\Test\OptionProvider;

use InvalidArgumentException;
use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\AbstractProvider;

#[CoversClass(HttpBasicAuthOptionProvider::class)]
#[CoversMethod(HttpBasicAuthOptionProvider::class, 'getAccessTokenOptions')]
class HttpBasicAuthOptionProviderTest extends TestCase
{
    /**
     * data provider for testGetAccessTokenOptionsException
     * @return array
     */
    public static function providerTestGetAccessTokenOptionsException()
    {
        return [
            [['client_id' => 'test']],
            [['client_secret' => 'test']],
        ];
    }

    /**
     * @param array $params
     */
    #[DataProvider('providerTestGetAccessTokenOptionsException')]
    public function testGetAccessTokenOptionsException($params)
    {
        $this->expectException(InvalidArgumentException::class);

        $provider = new HttpBasicAuthOptionProvider();
        $provider->getAccessTokenOptions(AbstractProvider::METHOD_POST, $params);
    }

    public function testGetAccessTokenOptions()
    {
        $provider = new HttpBasicAuthOptionProvider();
        $options = $provider->getAccessTokenOptions(AbstractProvider::METHOD_POST, [
            'client_id' => 'test',
            'client_secret' => 'test',
            'redirect_uri' => 'http://localhost'
        ]);

        $this->assertEquals('Basic ' . base64_encode('test:test'), $options['headers']['Authorization']);
        $this->assertArrayNotHasKey('client_id', $options);
        $this->assertArrayNotHasKey('client_secret', $options);
    }
}
