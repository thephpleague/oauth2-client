<?php

namespace League\OAuth2\Client\Provider;

class Google extends IdentityProvider
{
    public $scopeSeperator = ' ';

    public $access_type = 'online';

    public $approval_prompt = 'force';

    public $login_hint;

    public $name = "google";

    public $scopes = array(
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email'
    );

    public function urlAuthorize()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    public function urlAccessToken()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;
        $user->uid = $response->id;
        $user->name = isset($response->name) && $response->name ? $response->name : null;
        $user->first_name = isset($response->given_name) && $response->given_name ? $response->given_name : null;
        $user->last_name = isset($response->family_name) && $response->family_name ? $response->family_name : null;
        $user->email = isset($response->email) && $response->email ? $response->email : null;
        $user->imageUrl = isset($response->picture) && $response->picture ? $response->picture : null;
        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->name) && $response->name ? $response->name : null;
    }
}
