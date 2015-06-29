<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Test\Provider\Standard as MockProvider;
use League\OAuth2\Client\Provider\StandardProvider;
use League\OAuth2\Client\Provider\StandardUser;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

use Mockery as m;

class StandardProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRequiredOptions()
    {
        // Additionally, these options are required by the StandardProvider
        $required = [
            'urlAuthorize'   => 'http://example.com/authorize',
            'urlAccessToken' => 'http://example.com/token',
            'urlUserDetails' => 'http://example.com/user',
        ];

        foreach ($required as $key => $value) {
            // Test each of the required options by removing a single value
            // and attempting to create a new provider.
            $options = $required;
            unset($options[$key]);

            try {
                $provider = new StandardProvider($options);
            } catch (\Exception $e) {
                $this->assertInstanceOf('\InvalidArgumentException', $e);
            }
        }

        $provider = new StandardProvider($required + [
        ]);
    }

    public function testConfigurableOptions()
    {
        $options = [
            'urlAuthorize'      => 'http://example.com/authorize',
            'urlAccessToken'    => 'http://example.com/token',
            'urlUserDetails'    => 'http://example.com/user',
            'accessTokenMethod' => 'mock_method',
            'accessTokenUid'    => 'mock_token_uid',
            'scopeSeparator'    => 'mock_separator',
            'responseError'     => 'mock_error',
            'responseCode'      => 'mock_code',
            'responseUid'       => 'mock_response_uid',
            'scopes'            => ['mock', 'scopes'],
        ];

        $provider = new StandardProvider($options + [
            'clientId'       => 'mock_client_id',
            'clientSecret'   => 'mock_secret',
            'redirectUri'    => 'none',
        ]);

        foreach ($options as $key => $expected) {
            $this->assertAttributeEquals($expected, $key, $provider);
        }

        $this->assertEquals($options['urlAuthorize'], $provider->getBaseAuthorizationUrl());
        $this->assertEquals($options['urlAccessToken'], $provider->getBaseAccessTokenUrl());
        $this->assertEquals($options['urlUserDetails'], $provider->getUserDetailsUrl(new AccessToken(['access_token' => '1234'])));
        $this->assertEquals($options['scopes'], $provider->getDefaultScopes());

        $reflection = new \ReflectionClass(get_class($provider));

        $getAccessTokenMethod = $reflection->getMethod('getAccessTokenMethod');
        $getAccessTokenMethod->setAccessible(true);
        $this->assertEquals($options['accessTokenMethod'], $getAccessTokenMethod->invoke($provider));

        $getAccessTokenUid = $reflection->getMethod('getAccessTokenUid');
        $getAccessTokenUid->setAccessible(true);
        $this->assertEquals($options['accessTokenUid'], $getAccessTokenUid->invoke($provider));

        $getScopeSeparator = $reflection->getMethod('getScopeSeparator');
        $getScopeSeparator->setAccessible(true);
        $this->assertEquals($options['scopeSeparator'], $getScopeSeparator->invoke($provider));
    }

    public function testUserDetails()
    {
        $token = new AccessToken(['access_token' => 'mock_token']);

        $provider = new MockProvider([
            'urlAuthorize'   => 'http://example.com/authorize',
            'urlAccessToken' => 'http://example.com/token',
            'urlUserDetails' => 'http://example.com/user',
            'responseUid'    => 'mock_response_uid',
        ]);

        $user = $provider->getUser($token);

        $this->assertInstanceOf(StandardUser::class, $user);
        $this->assertSame(1, $user->getUserId());

        $data = $user->toArray();

        $this->assertArrayHasKey('username', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertSame('testmock', $data['username']);
        $this->assertSame('mock@example.com', $data['email']);
    }

    public function testCheckResponse()
    {
        $response = m::mock(ResponseInterface::class);

        $options = [
            'urlAuthorize'      => 'http://example.com/authorize',
            'urlAccessToken'    => 'http://example.com/token',
            'urlUserDetails'    => 'http://example.com/user',
        ];

        $provider = new StandardProvider($options);

        $reflection = new \ReflectionClass(get_class($provider));

        $checkResponse = $reflection->getMethod('checkResponse');
        $checkResponse->setAccessible(true);

        $this->assertNull($checkResponse->invokeArgs($provider, [$response, []]));
    }

    /**
     * @expectedException League\Oauth2\Client\Provider\Exception\IdentityProviderException
     */
    public function testCheckResponseThrowsException()
    {
        $response = m::mock(ResponseInterface::class);

        $options = [
            'urlAuthorize'      => 'http://example.com/authorize',
            'urlAccessToken'    => 'http://example.com/token',
            'urlUserDetails'    => 'http://example.com/user',
        ];

        $provider = new StandardProvider($options);

        $reflection = new \ReflectionClass(get_class($provider));

        $checkResponse = $reflection->getMethod('checkResponse');
        $checkResponse->setAccessible(true);

        $checkResponse->invokeArgs($provider, [$response, [
            'error' => 'foobar',
        ]]);
    }
}
