<?php

namespace League\OAuth2\Client\Test\Provider;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Test\Provider\Generic as MockProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionProperty;

class GenericProviderTest extends TestCase
{
    public function testRequiredOptions()
    {
        // Additionally, these options are required by the GenericProvider
        $required = [
            'urlAuthorize'   => 'http://example.com/authorize',
            'urlAccessToken' => 'http://example.com/token',
            'urlResourceOwnerDetails' => 'http://example.com/user',
        ];

        foreach ($required as $key => $value) {
            // Test each of the required options by removing a single value
            // and attempting to create a new provider.
            $options = $required;
            unset($options[$key]);

            try {
                $provider = new GenericProvider($options);
            } catch (\Exception $e) {
                $this->assertInstanceOf(InvalidArgumentException::class, $e);
            }
        }

        $provider = new GenericProvider($required + [
        ]);
    }

    public function testConfigurableOptions()
    {
        $options = [
            'urlAuthorize'      => 'http://example.com/authorize',
            'urlAccessToken'    => 'http://example.com/token',
            'urlResourceOwnerDetails' => 'http://example.com/user',
            'accessTokenMethod' => 'mock_method',
            'accessTokenResourceOwnerId' => 'mock_token_uid',
            'scopeSeparator'    => 'mock_separator',
            'responseError'     => 'mock_error',
            'responseCode'      => 'mock_code',
            'responseResourceOwnerId' => 'mock_response_uid',
            'scopes'            => ['mock', 'scopes'],
        ];

        $provider = new GenericProvider($options + [
            'clientId'       => 'mock_client_id',
            'clientSecret'   => 'mock_secret',
            'redirectUri'    => 'none',
        ]);

        foreach ($options as $key => $expected) {
            $property = new ReflectionProperty(GenericProvider::class, $key);
            $property->setAccessible(true);

            $this->assertEquals($expected, $property->getValue($provider));
        }

        $this->assertEquals($options['urlAuthorize'], $provider->getBaseAuthorizationUrl());
        $this->assertEquals($options['urlAccessToken'], $provider->getBaseAccessTokenUrl([]));
        $this->assertEquals($options['urlResourceOwnerDetails'], $provider->getResourceOwnerDetailsUrl(new AccessToken(['access_token' => '1234'])));
        $this->assertEquals($options['scopes'], $provider->getDefaultScopes());

        $reflection = new ReflectionClass(get_class($provider));

        $getAccessTokenMethod = $reflection->getMethod('getAccessTokenMethod');
        $getAccessTokenMethod->setAccessible(true);
        $this->assertEquals($options['accessTokenMethod'], $getAccessTokenMethod->invoke($provider));

        $getAccessTokenResourceOwnerId = $reflection->getMethod('getAccessTokenResourceOwnerId');
        $getAccessTokenResourceOwnerId->setAccessible(true);
        $this->assertEquals($options['accessTokenResourceOwnerId'], $getAccessTokenResourceOwnerId->invoke($provider));

        $getScopeSeparator = $reflection->getMethod('getScopeSeparator');
        $getScopeSeparator->setAccessible(true);
        $this->assertEquals($options['scopeSeparator'], $getScopeSeparator->invoke($provider));
    }

    public function testResourceOwnerDetails()
    {
        $token = new AccessToken(['access_token' => 'mock_token']);

        $provider = new MockProvider([
            'urlAuthorize'   => 'http://example.com/authorize',
            'urlAccessToken' => 'http://example.com/token',
            'urlResourceOwnerDetails' => 'http://example.com/user',
            'responseResourceOwnerId' => 'mock_response_uid',
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

    public function testCheckResponse()
    {
        $response = Mockery::mock(ResponseInterface::class);

        $options = [
            'urlAuthorize'      => 'http://example.com/authorize',
            'urlAccessToken'    => 'http://example.com/token',
            'urlResourceOwnerDetails' => 'http://example.com/user',
        ];

        $provider = new GenericProvider($options);

        $reflection = new ReflectionClass(get_class($provider));

        $checkResponse = $reflection->getMethod('checkResponse');
        $checkResponse->setAccessible(true);

        $this->assertNull($checkResponse->invokeArgs($provider, [$response, []]));
    }

    /**
     * @param array $error The error response to parse
     * @param array $extraOptions Any extra options to configure the generic provider with.
     * @dataProvider checkResponseThrowsExceptionProvider
     */
    public function testCheckResponseThrowsException(array $error, array $extraOptions = [])
    {
        $response = Mockery::mock(ResponseInterface::class);

        $options = [
            'urlAuthorize'      => 'http://example.com/authorize',
            'urlAccessToken'    => 'http://example.com/token',
            'urlResourceOwnerDetails' => 'http://example.com/user',
        ];

        $provider = new GenericProvider($options + $extraOptions);

        $reflection = new ReflectionClass(get_class($provider));

        $checkResponse = $reflection->getMethod('checkResponse');
        $checkResponse->setAccessible(true);

        $this->expectException(IdentityProviderException::class);

        $checkResponse->invokeArgs($provider, [$response, $error]);
    }

    public function checkResponseThrowsExceptionProvider() {
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
