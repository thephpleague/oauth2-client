<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;

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

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://api.github.com/user?access_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User;
        $user->uid = $response->id;
        $user->nickname = $response->login;
        $user->name = isset($response->name) ? $response->name : null;
        $user->email = isset($response->email) ? $response->email : null;
        $user->urls = array(
            'GitHub' => 'http://github.com/'.$user->login,
            'Blog' => $user->blog,
        );

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return $response->name;
    }
}
