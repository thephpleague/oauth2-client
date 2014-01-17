<?php

namespace League\OAuth2\Client\Provider;

class Github extends IdentityProvider
{
    /**
     * Response Type
     * @var string
     */
    public $responseType = 'string';

    /**
     * Githubs Authorization url
     * @return String
     */
    public function urlAuthorize()
    {
        return 'https://github.com/login/oauth/authorize';
    }

    /**
     * Githubs Access Token url
     * @return String
     */
    public function urlAccessToken()
    {
        return 'https://github.com/login/oauth/access_token';
    }

    /**
     * Get the authorized url for the githubs user data
     * @param  League\OAuth2\Client\Token\AccessToken $token Token Object
     * @return String                                        Authorized url
     */
    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.github.com/user?access_token='.$token;
    }

    /**
     * Construct a User object with the response
     * @param  stdClass                               $response Response from the api server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return League\OAuth2\Client\Provider\User               User Object
     */
    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;
        $user->uid = $response->id;
        $user->nickname = $response->login;
        $user->name = isset($response->name) ? $response->name : null;
        $user->email = isset($response->email) ? $response->email : null;
        $user->urls = array(
            'GitHub' => 'http://github.com/'.$user->login,
            'Blog' => $user->blog,
        );

        return $user;
    }

    /**
     * Return the User ID from the response object
     * @param  stdClass                               $response Response from the API
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Github User ID
     */
    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    /**
     * Return the Email from the response object
     * @param  stdClass                               $response Response from the API
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Email address
     */
    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    /**
     * Return the screen name from the response object
     * @param  stdClass                               $response Response from the API
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Screename
     */
    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->name;
    }
}
