<?php

namespace League\OAuth2\Client\Provider;

class Github extends IdentityProvider
{
    public $responseType = 'string';

    public function urlAuthorize()
    {
        return 'https://github.com/login/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://github.com/login/oauth/access_token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.github.com/user?access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;
        $user->uid = $response->id;
        $user->nickname = $response->login;
        $user->name = $response->name;
        $user->email = isset($response->email) ? $response->email : null;
        $user->urls = array(
            'GitHub' => 'http://github.com/'.$user->login,
            'Blog' => $user->blog,
        );

        return $user;
    }
}