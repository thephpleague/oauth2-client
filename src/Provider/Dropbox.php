<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class Dropbox extends AbstractProvider
{

    public function urlAuthorize()
    {
        return 'https://www.dropbox.com/1/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://api.dropbox.com/1/oauth2/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.dropbox.com/1/account/info?access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;

        $user->uid = $response->uid;
        $user->name = $response->display_name;
        $user->email = $response->email;

        return $user;
    }

    public function getAuthorizationUrl($options = array())
    {
        return parent::getAuthorizationUrl(array_merge([
            'approval_prompt' => []
        ], $options));
    }

}