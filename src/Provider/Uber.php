<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Uber extends AbstractProvider
{
    public $scopes = [];
    public $responseType = 'json';
    public $authorizationHeader = 'Bearer';
    public $version = 'v1';

    public function urlAuthorize()
    {
        return 'https://login.uber.com/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://login.uber.com/oauth/token';
    }

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://api.uber.com/'.$this->version.'/me';
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User();

        $user->exchangeArray([
            'uid' => $response->uuid,
            'name' => $response->first_name . ' ' . $response->last_name,
            'firstname' => $response->first_name,
            'lastname' => $response->last_name,
            'email' => $response->email,
            'imageUrl' => $response->picture,
        ]);

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->uuid;
    }

    public function userEmail($response, AccessToken $token)
    {
        return $response->email;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return [$response->first_name, $response->last_name];
    }
}
