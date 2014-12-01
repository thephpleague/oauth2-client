<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Eventbrite extends AbstractProvider
{

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->headers = [
            'Authorization' => 'Bearer',
        ];
    }

    /**
     * @return string
     */
    public function urlAuthorize()
    {
        return 'https://www.eventbrite.com/oauth/authorize';
    }

    /**
     * @return string
     */
    public function urlAccessToken()
    {
        return 'https://www.eventbrite.com/oauth/token';
    }

    /**
     * @param AccessToken $token
     *
     * @return string
     */
    public function urlUserDetails(AccessToken $token)
    {
        return 'https://www.eventbrite.com/json/user_get?access_token='.$token;
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
        $user->exchangeArray([
            'uid' => $response->user->user_id,
            'email' => $response->user->email,
        ]);

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->user->user_id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->user->email) && $response->user->email ? $response->user->email : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return $response->user->user_id;
    }
}
