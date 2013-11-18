<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;

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

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
    {
        $response = (array) $response;
        $user = new User;
        $user->uid = $response['id'];
        $user->name = $response['name'];
        $user->firstName = $response['given_name'];
        $user->lastName = $response['family_name'];
        $user->email = $response['email'];
        $user->image = (isset($response['picture'])) ? $response['picture'] : null;
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
        return array($response->given_name, $response->family_name);
    }
}
