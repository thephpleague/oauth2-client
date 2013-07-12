<?php

namespace League\OAuth2\Client\Provider;

class Wordpress extends IdentityProvider
{

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
        $displayName = explode(' ', $userDetails->displayname, 1);

        $user = new User;
        $user->uid = $userDetails->ID;
        $user->nickname = $userDetails->username;
        $user->name = $userDetails->displayname;
        $user->firstName = $displayName[0];
        $user->lastName = $displayName[1];
        $user->email = isset($userDetails->email) ? $userDetails->email : null;
        $user->imageUrl = $userDetails->avatar_URL;
        $user->urls = array(
                "Profile" => $userDetails->profile_URL
            );

        return $user;
    }
}
