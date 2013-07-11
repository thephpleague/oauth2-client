<?php

namespace League\OAuth2\Client\Provider;

class Twitter extends IdentityProvider
{
    public $responseType = 'string';

    public function urlAuthorize()
    {
        return 'https://api.twitter.com/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    public function userDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        $this->getDataFromURL('https://api.twitter.com/1.1/users/show.json?screen_name=&');

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
