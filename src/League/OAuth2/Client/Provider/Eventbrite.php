<?php

namespace League\OAuth2\Client\Provider;

class Eventbrite extends IdentityProvider
{

    public function __construct()
    {
        $this->headers = array(
            'Authorization' => 'Bearer'
        );
    }

    public function urlAuthorize()
    {
        return 'https://www.eventbrite.com/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://www.eventbrite.com/oauth/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://www.eventbrite.com/json/user_get?access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;
        $user->uid = $response->user->user_id;
        $user->email = $response->user->email;
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
        return array($response->first_name, $response->last_name);
    }
}
