<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Test\Provider\Generic as MockProvider;
use League\OAuth2\Client\Token\AccessToken;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

use function array_keys;

class GenericProviderTest extends TestCase
{
    public function testRequiredOptions(): void
    {
        // Additionally, these options are required by the GenericProvider
        $required = [
            'urlAuthorize' => 'http://example.com/authorize',
            'urlAccessToken' => 'http://example.com/token',
            'urlResourceOwnerDetails' => 'http://example.com/user',
        ];

        foreach (array_keys($required) as $key) {
            // Test each of the required options by removing a single value
            // and attempting to create a new provider.
            $options = $required;
            unset($options[$key]);

            try {
                new GenericProvider($options);
            } catch (Throwable $e) {
                $this->assertInstanceOf(InvalidArgumentException::class, $e);
            }
        }

        new GenericProvider($required, [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);
    }

    public function testConfigurableOptions(): void
    {
        $options = [
            'urlAuthorize' => 'http://example.com/authorize',
            'urlAccessToken' => 'http://example.com/token',
            'urlResourceOwnerDetails' => 'http://example.com/user',
            'accessTokenMethod' => 'mock_method',
            'accessTokenResourceOwnerId' => 'mock_token_uid',
            'scopeSeparator' => 'mock_separator',
            'responseError' => 'mock_error',
            'responseCode' => 'mock_code',
            'responseResourceOwnerId' => 'mock_response_uid',
            'scopes' => ['mock', 'scopes'],
            'pkceMethod' => 'S256',
        ];

        $provider = new GenericProvider($options + [
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        foreach ($options as $key => $expected) {
            $property = new ReflectionProperty(GenericProvider::class, $key);
            $this->assertEquals($expected, $property->getValue($provider));
        }

        $this->assertEquals($options['urlAuthorize'], $provider->getBaseAuthorizationUrl());
        $this->assertEquals($options['urlAccessToken'], $provider->getBaseAccessTokenUrl([]));
        $this->assertEquals(
            $options['urlResourceOwnerDetails'],
            $provider->getResourceOwnerDetailsUrl(new AccessToken(['access_token' => '1234'])),
        );
        $this->assertEquals($options['scopes'], $provider->getDefaultScopes());

        $reflection = new ReflectionClass($provider::class);

        $getAccessTokenMethod = $reflection->getMethod('getAccessTokenMethod');
        $this->assertEquals($options['accessTokenMethod'], $getAccessTokenMethod->invoke($provider));

        $getAccessTokenResourceOwnerId = $reflection->getMethod('getAccessTokenResourceOwnerId');
        $this->assertEquals($options['accessTokenResourceOwnerId'], $getAccessTokenResourceOwnerId->invoke($provider));

        $getScopeSeparator = $reflection->getMethod('getScopeSeparator');
        $this->assertEquals($options['scopeSeparator'], $getScopeSeparator->invoke($provider));

        $getPkceMethod = $reflection->getMethod('getPkceMethod');
        $this->assertEquals($options['pkceMethod'], $getPkceMethod->invoke($provider));
    }

    public function testResourceOwnerDetails(): void
    {
        $token = new AccessToken(['access_token' => 'mock_token']);

        $provider = new MockProvider([
            'urlAuthorize' => 'http://example.com/authorize',
            'urlAccessToken' => 'http://example.com/token',
            'urlResourceOwnerDetails' => 'http://example.com/user',
            'responseResourceOwnerId' => 'mock_response_uid',
        ], [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $user = $provider->getResourceOwner($token);

        $this->assertInstanceOf(GenericResourceOwner::class, $user);
        $this->assertSame(1, $user->getId());

        $data = $user->toArray();

        $this->assertArrayHasKey('username', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertSame('testmock', $data['username']);
        $this->assertSame('mock@example.com', $data['email']);
    }

    public function testCheckResponse(): void
    {
        $response = Mockery::mock(ResponseInterface::class);

        $options = [
            'urlAuthorize' => 'http://example.com/authorize',
            'urlAccessToken' => 'http://example.com/token',
            'urlResourceOwnerDetails' => 'http://example.com/user',
        ];

        $provider = new GenericProvider($options, [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $reflection = new ReflectionClass($provider::class);

        $checkResponse = $reflection->getMethod('checkResponse');

        $this->assertNull($checkResponse->invokeArgs($provider, [$response, []]));
    }

    /**
     * @param array<string, mixed> $error The error response to parse
     * @param array<string, mixed> $extraOptions Any extra options to configure the generic provider with.
     */
    #[DataProvider('checkResponseThrowsExceptionProvider')]
    public function testCheckResponseThrowsException(array $error, array $extraOptions = [])
    {
        $response = Mockery::mock(ResponseInterface::class);

        $options = [
            'urlAuthorize' => 'http://example.com/authorize',
            'urlAccessToken' => 'http://example.com/token',
            'urlResourceOwnerDetails' => 'http://example.com/user',
        ];

        $provider = new GenericProvider($options + $extraOptions, [
            'httpClient' => new Client(),
            'requestFactory' => new HttpFactory(),
            'streamFactory' => new HttpFactory(),
        ]);

        $reflection = new ReflectionClass($provider::class);

        $checkResponse = $reflection->getMethod('checkResponse');

        $this->expectException(IdentityProviderException::class);

        $checkResponse->invokeArgs($provider, [$response, $error]);
    }

    /**
     * @return array<array{0: array<string, mixed>, 1?: array<string, mixed>}>
     */
    public static function checkResponseThrowsExceptionProvider(): array
    {
        return [
            [['error' => 'foobar',]],
            [['error' => 'foobar',] , ['responseCode' => 'code']],
            // Some servers return non-compliant responses. Provider shouldn't 'Fatal error: Wrong parameters'
            [['error' => 'foobar', 'code' => 'abc55'], ['responseCode' => 'code']],
            [['error' => 'foobar', 'code' => ['badformat']], ['responseCode' => 'code']],
            [['error' => ['message' => 'msg', 'code' => 56]]],
            [['error' => ['errors' => ['code' => 67, 'message' => 'msg']]]],
        ];
    }
}
