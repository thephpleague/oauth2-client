<?php

namespace OAuth2\Client\Provider;

class foursquare extends IdentityProvider
{
    public function urlAuthorize()
    {
        return 'https://foursquare.com/oauth2/authenticate';
    }

    public function urlAccessToken()
    {
        return 'https://foursquare.com/oauth2/access_token';
    }

    public function urlUserDetails(\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.foursquare.com/v2/users/selfoauth_token='.$token;
    }

    public function userDetails($response, \OAuth2\Client\Token\AccessToken $token)
    {
        die(print_r($response));

        /*$user = new User;
        $user->uid = $response->response->user->id;
        $user->name = $response->response->user->name;
        $user->lastName = $response->response->user->last_name;
        $user->email = isset($response->response->user->email) ? $response->response->user->email : null;
        $user->location = isset($response->response->user->hometown->name) ? $response->response->user->hometown->name : null;
        $user->description = isset($response->response->user->bio) ? $response->response->user->bio : null;
        $user->imageUrl = $imageHeaders['Location'];
        $user->urls = array(
            'Facebook' => $response->response->user->link,
        );

        return $user;*/
    }
}