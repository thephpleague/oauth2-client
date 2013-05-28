<?php

namespace League\OAuth2\Client\Provider;

class Google extends IdentityProvider
{
    public $scopeSeperator = ' ';

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

    public function urlUserDetails(\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token;
    }

    public function userDetails($response, \OAuth2\Client\Token\AccessToken $token)
    {
        $response = (array) $response;
        $user = new User;
        $user->uid = $response['id'];
        $user->name = $response['name'];
        $user->first_name = $response['given_name'];
        $user->last_name = $response['family_name'];
        $user->email = $response['email'];
        $user->image = (isset($response['picture'])) ? $response['picture'] : null;
        return $user;
    }
}
