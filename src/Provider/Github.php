<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Class Github
 * @package League\OAuth2\Client\Provider
 */
class Github extends AbstractProvider
{
    /**
     * @var string
     */
    public $responseType = 'string';

    /**
     * @return string
     */
    public function urlAuthorize()
    {
        return 'https://github.com/login/oauth/authorize';
    }

    /**
     * @return string
     */
    public function urlAccessToken()
    {
        return 'https://github.com/login/oauth/access_token';
    }

    /**
     * @param AccessToken $token
     *
     * @return string
     */
    public function urlUserDetails(AccessToken $token)
    {
        return 'https://api.github.com/user?access_token='.$token;
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

        $name = (isset($response->name)) ? $response->name : null;
        $email = (isset($response->email)) ? $response->email : null;

        $user->exchangeArray([
            'uid' => $response->id,
            'nickname' => $response->login,
            'name' => $name,
            'email' => $email,
            'urls'  => [
                'GitHub' => 'http://github.com/'.$response->login,
            ],
        ]);

        return $user;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     *
     * @return mixed
     */
    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     *
     * @return string|null
     */
    public function userEmail($response, AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     *
     * @return string
     */
    public function userScreenName($response, AccessToken $token)
    {
        return $response->name;
    }
}
