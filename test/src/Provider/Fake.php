<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class Fake extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private $accessTokenMethod = 'POST';

    public function getClientId()
    {
        return $this->clientId;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function getBaseAuthorizationUrl()
    {
        return 'http://example.com/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'http://example.com/oauth/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'http://example.com/oauth/user';
    }

    protected function getDefaultScopes()
    {
        return ['test'];
    }

    public function setAccessTokenMethod($method)
    {
        $this->accessTokenMethod = $method;
    }

    public function getAccessTokenMethod()
    {
        return $this->accessTokenMethod;
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new Fake\User($response);
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            throw new IdentityProviderException($data['error'], $data['code'], $data);
        }
    }
}
