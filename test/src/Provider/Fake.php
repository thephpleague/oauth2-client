<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\UserInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\AbstractProvider;

class Fake extends AbstractProvider
{
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

    public function userDetails($response, AccessToken $token)
    {
        return new User;
    }
}

class User implements UserInterface
{
    public function getUserId()
    {
        return 123;
    }
}
