<?php

namespace League\OAuth2\Client\Provider;

class Microsoft extends IdentityProvider
{
    public $scopes = array('wl.basic', 'wl.emails');
    public $responseType = 'json';

    public $name = "microsoft";

    public function urlAuthorize()
    {
        return 'https://login.live.com/oauth20_authorize.srf';
    }

    public function urlAccessToken()
    {
        return 'https://login.live.com/oauth20_token.srf';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://apis.live.net/v5.0/me?access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $imageHeaders = get_headers('https://apis.live.net/v5.0/'.$response->id.'/picture', 1);

        $user = new User;

        $user->uid = $response->id;
        $user->name = isset($response->name) && $response->name ? $response->name : null;
        $user->firstName = isset($response->first_name) && $response->first_name
            ? $response->first_name : null;
        $user->lastName = isset($response->last_name) && $response->last_name
            ? $response->last_name : null;
        $user->email = isset($response->emails->preferred) && $response->emails->preferred
            ? $response->emails->preferred : null;
        $user->imageUrl = isset($imageHeaders['Location']) && $imageHeaders['Location']
            ? $imageHeaders['Location'] : null;
        $user->urls = array(
            'profile' => $response->link.'/cid-'.$response->id,
        );

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->emails->preferred) && $response->emails->preferred
            ? $response->emails->preferred : null;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->name) && $response->name ? $response->name : null;
    }
}
