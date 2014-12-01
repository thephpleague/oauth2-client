<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Instagram extends AbstractProvider
{
    public $scopes = ['basic'];
    public $responseType = 'json';

    /**
     * @return string
     */
    public function urlAuthorize()
    {
        return 'https://api.instagram.com/oauth/authorize';
    }

    /**
     * @return string
     */
    public function urlAccessToken()
    {
        return 'https://api.instagram.com/oauth/access_token';
    }

    /**
     * @param AccessToken $token
     *
     * @return string
     */
    public function urlUserDetails(AccessToken $token)
    {
        return 'https://api.instagram.com/v1/users/self?access_token='.$token;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     *
     * @return User
     */
    public function userDetails($response, AccessToken $token)
    {
        $user = new User();

        $description = (isset($response->data->bio)) ? $response->data->bio : null;

        $user->exchangeArray([
            'uid' => $response->data->id,
            'nickname' => $response->data->username,
            'name' => $response->data->full_name,
            'description' => $description,
            'imageUrl' => $response->data->profile_picture,
        ]);

        return $user;
    }

    /**
     * @param $response
     * @param AccessToken $token
     *
     * @return string
     */
    public function userUid($response, AccessToken $token)
    {
        return $response->data->id;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     */
    public function userEmail($response, AccessToken $token)
    {
        return;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     *
     * @return mixed
     */
    public function userScreenName($response, AccessToken $token)
    {
        return $response->data->full_name;
    }
}
