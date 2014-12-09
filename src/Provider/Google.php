<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class Google extends AbstractProvider
{
    public $scopeSeparator = ' ';

    public $scopes = array(
        'https://www.googleapis.com/auth/profile',
        'https://www.googleapis.com/auth/email'
    );

    public function urlAuthorize()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    public function urlAccessToken()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://www.googleapis.com/plus/v1/people/me?alt=json&access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = (array) $response;

        $user = new User;

        $imageUrl = (isset($response['image']) && $response['image'] != null) ? $response['image']->url : null;

        $user->exchangeArray(array(
            'uid' => $response['id'],
            'name' => $response['displayName'],
            'firstname' => $response['name']->givenName,
            'lastName' => $response['name']->familyName,
            'email' => $response['emails'][0]->value,
            'imageUrl' => $imageUrl,
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
        return array($response->given_name, $response->family_name);
    }
}
