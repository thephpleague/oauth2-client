<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class Strava extends AbstractProvider
{
    public $responseType = 'json';

    public function urlAuthorize()
    {
        return 'https://www.strava.com/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://www.strava.com/oauth/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://www.strava.com/api/v3/athlete/?access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;

        $user->exchangeArray(array(
            'uid' => $response->id,
            'name' => implode(" ", array($response->firstname, $response->lastname)),
            'firstName' => $response->firstname,
            'lastName' => $response->lastname,
            'email' => $response->email,
            'location' => $response->country,
            'imageUrl' => $response->profile,
            'gender' => $response->sex,
        ));

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
        return implode(" ", array($response->firstname, $response->lastname));
    }
}
