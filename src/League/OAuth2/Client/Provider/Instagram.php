<?php

namespace League\OAuth2\Client\Provider;

class Instagram extends IdentityProvider
{
    /**
     * Required Instagram scopes.
     * @var array
     */
    public $scopes = array('basic');

    /**
     * Response type
     * @var string
     */
    public $responseType = 'json';

    /**
     * Instagrams authorization endpoint
     * @return String
     */
    public function urlAuthorize()
    {
        return 'https://api.instagram.com/oauth/authorize';
    }

    /**
     * Instagrams access token endpoint
     * @return String
     */
    public function urlAccessToken()
    {
        return 'https://api.instagram.com/oauth/access_token';
    }

    /**
     * Get the authorized url for the instagram user data
     * @return String
     */
    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.instagram.com/v1/users/self?access_token='.$token;
    }

    /**
     * Construct a User object with the response
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return League\OAuth2\Client\Provider\User               User Object
     */
    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {

        $user = new User;

        $user->uid = $response->data->id;
        $user->nickname = $response->data->username;
        $user->name = $response->data->full_name;
        $user->description = isset($response->data->bio) ? $response->data->bio : null;
        $user->imageUrl = $response->data->profile_picture;

        return $user;
    }
}
