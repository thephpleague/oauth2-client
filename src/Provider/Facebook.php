<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class Facebook extends AbstractProvider
{
    public $scopes = array('offline_access', 'email');
    public $responseType = 'string';

    public function urlAuthorize()
    {
        return 'https://www.facebook.com/dialog/oauth';
    }

    public function urlAccessToken()
    {
        return 'https://graph.facebook.com/oauth/access_token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://graph.facebook.com/me?access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        try {
            $client = $this->getHttpClient();
            $client->setBaseUrl('https://graph.facebook.com/me/picture?type=normal&access_token=' . $token->accessToken);
            $request = $client->get()->send();
            $info = $request->getInfo();
            $imageUrl = $info['url'];
        } catch (\Exception $e) {
            $imageUrl = null;
        }

        $user = new User;

        $username = (isset($response->username)) ? $response->username : null;
        $email = (isset($response->email)) ? $response->email : null;
        $location = (isset($response->hometown->name)) ? $response->hometown->name : null;
        $description = (isset($response->bio)) ? $response->bio : null;
        $imageUrl = ($imageUrl) ?: null;

        $user->exchangeArray(array(
            'uid' => $response->id,
            'nickname' => $username,
            'name' => $response->name,
            'firstName' => $response->first_name,
            'lastName' => $response->last_name,
            'email' => $email,
            'location' => $location,
            'description' => $description,
            'imageUrl' => $imageUrl,
            'urls' => array( 'Facebook' => $response->link ),
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
        return array($response->first_name, $response->last_name);
    }

    public function userSex($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $availableSex = ['male', 'female'];
        return in_array($response->gender, $availableSex) ? $response->gender : null;
    }
}
