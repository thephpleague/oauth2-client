<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;

class Buffer extends IdentityProvider
{
    public $responseType = 'json';

    public function urlAuthorize()
    {
        return 'https://bufferapp.com/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://api.bufferapp.com/1/oauth2/token.json';
    }

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://api.bufferapp.com/1/user.json?access_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User;
        $user->uid = $response->id;
        $user->urls = array(
            'Referral' => $response->referral_link,
        );

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return null;
    }
}
