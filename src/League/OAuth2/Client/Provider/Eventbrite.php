<?php

namespace League\OAuth2\Client\Provider;

class Eventbrite extends IdentityProvider
{

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

        echo '<pre>';
        print_r($response);

        $user = new User;
        $user->uid = $response->user->user_id;
        //$user->nickname = $response->username;
        //$user->name = $response->name;
        //$user->firstName = $response->first_name;
        //$user->lastName = $response->last_name;
        $user->email = $response->user->email;
        //$user->location = isset($response->hometown->name) ? $response->hometown->name : null;
        //$user->description = isset($response->bio) ? $response->bio : null;
//        $user->imageUrl = $imageHeaders['Location'];
//        $user->urls = array(
//            'Facebook' => $response->link,
//        );

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
