<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class Fake extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function urlAuthorize()
    {
        return 'http://example.com/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return 'http://example.com/oauth/token';
    }

    public function urlUserDetails(AccessToken $token)
    {
        return 'http://example.com/oauth/user';
    }

    protected function getDefaultScopes()
    {
        return ['test'];
    }

    protected function prepareUserDetails(array $response, AccessToken $token)
    {
        return new Fake\User($response);
    }

    protected function checkResponse(array $response)
    {
        if (!empty($response['error'])) {
            throw new IdentityProviderException($response['error'], $response['code'], $response);
        }
    }
}
