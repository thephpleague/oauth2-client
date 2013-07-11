<?php

namespace League\OAuth2\Client\Provider;

class Wordpress extends IdentityProvider
{
    public $scopes = array('offline_access', 'email', 'read_stream');
    public $responseType = 'string';

    public function urlAuthorize()
    {
        return 'https://public-api.wordpress.com/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://public-api.wordpress.com/oauth2/token';
    }

    // 'Authorization: Bearer ' . $access_key
    // If denied... It will be redirected: ?error=access_denied

    public function getUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        $userDetails = $this->getDataFromURL('https://public-api.wordpress.com/rest/v1/me?pretty=1&access_token='.$token, array('Authorization' => 'Bearer '.$token));

        $displayName = explode(' ', $userDetails->displayname, 2);

        $user = new User;
        $user->uid = $userDetails->ID;
        $user->nickname = $userDetails->username;
        $user->firstName = $displayName[0];
        $user->lastName = $displayName[1];
        $user->email = isset($userDetails->email) ? $userDetails->email : null;
        $user->imageUrl = $userDetails->avatar_URL;

        return $user;
    }
}
