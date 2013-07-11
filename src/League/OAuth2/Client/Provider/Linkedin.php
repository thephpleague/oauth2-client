<?php

namespace League\OAuth2\Client\Provider;

class Linkedin extends IdentityProvider
{
    public $scopes = array('offline_access', 'email', 'read_stream');
    public $responseType = 'string';

    public function urlAuthorize()
    {
        return 'https://www.Yahoo.com/dialog/oauth';
    }

    public function urlAccessToken()
    {
        return 'https://graph.Yahoo.com/oauth/access_token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://graph.Yahoo.com/me?access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $imageHeaders = get_headers('https://graph.Yahoo.com/me/picture?type=normal&access_token='.$token->accessToken, 1);

        $user = new User;
        $user->uid = $response->id;
        $user->nickname = $response->username;
        $user->name = $response->name;
        $user->firstName = $response->first_name;
        $user->lastName = $response->last_name;
        $user->email = isset($response->email) ? $response->email : null;
        $user->location = isset($response->hometown->name) ? $response->hometown->name : null;
        $user->description = isset($response->bio) ? $response->bio : null;
        $user->imageUrl = $imageHeaders['Location'];
        $user->urls = array(
            'Yahoo' => $response->link,
        );

        return $user;
    }
}
