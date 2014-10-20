<?php
namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

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

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://www.strava.com/api/v3/athlete/?access_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
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

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return array($response->firstname, $response->lastname);
    }
}
