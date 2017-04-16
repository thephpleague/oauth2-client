<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Uber extends AbstractProvider
{
    public $scopes = [];
    public $responseType = 'json';
    public $authorizationHeader = 'Bearer';
    public $version = 'v1';

    /**
     * Get the URL that this provider uses to begin authorization.
     *
     * @return string
     */
    public function urlAuthorize()
    {
        return 'https://login.uber.com/oauth/authorize';
    }

    /**
     * Get the URL that this provider users to request an access token.
     *
     * @return string
     */
    public function urlAccessToken()
    {
        return 'https://login.uber.com/oauth/token';
    }

    /**
     * Get the URL that this provider uses to request user details.
     *
     * Since this URL is typically an authorized route, most providers will require you to pass the access_token as
     * a parameter to the request. For example, the google url is:
     *
     * 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token
     *
     * @param AccessToken $token
     * @return string
     */
    public function urlUserDetails(AccessToken $token)
    {
        return 'https://api.uber.com/'.$this->version.'/me';
    }

    /**
     * Given an object response from the server, process the user details into a format expected by the user
     * of the client.
     *
     * @param object $response
     * @param AccessToken $token
     * @return mixed
     */
    public function userDetails($response, AccessToken $token)
    {
        $user = new User();

        $user->exchangeArray([
            'uid' => $response->uuid,
            'name' => $response->first_name . ' ' . $response->last_name,
            'firstname' => $response->first_name,
            'lastname' => $response->last_name,
            'email' => $response->email,
            'imageUrl' => $response->picture,
        ]);

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->uuid;
    }

    public function userEmail($response, AccessToken $token)
    {
        return $response->email;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return null;
    }
}
