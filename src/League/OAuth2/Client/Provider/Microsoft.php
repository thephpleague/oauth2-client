<?php

namespace League\OAuth2\Client\Provider;

class Microsoft extends IdentityProvider
{
    /**
     * Required Microsofts scopes.
     * @var array
     */
    public $scopes = array('wl.basic', 'wl.emails');

    /**
     * Response Type
     * @var string
     */
    public $responseType = 'json';

    /**
     * Microsofts authorization endpoint
     * @return String
     */
    public function urlAuthorize()
    {
        return 'https://oauth.live.com/authorize';
    }

    /**
     * Microsofts access token endpoint
     * @return String
     */
    public function urlAccessToken()
    {
        return 'https://oauth.live.com/token';
    }

    /**
     * Get the authorized url for the microsofts user data
     * @return String
     */
    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://apis.live.net/v5.0/me?access_token='.$token;
    }

    /**
     * Construct a User object with the response
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return League\OAuth2\Client\Provider\User               User Object
     */
    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $imageHeaders = get_headers('https://apis.live.net/v5.0/'.$response->id.'/picture', 1);

        $user = new User;

        $user->uid = $response->id;
        $user->name = $response->name;
        $user->firstName = $response->first_name;
        $user->lastName = $response->last_name;
        $user->email = isset($response->emails->preferred) ? $response->emails->preferred : null;
        $user->imageUrl = $imageHeaders['Location'];
        $user->urls = $response->link.'/cid-'.$response->id;

        return $user;
    }

    /**
     * Return the User ID from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Microsofts User ID
     */
    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    /**
     * Return the Email from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Email address
     */
    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->emails->preferred) && $response->emails->preferred ? $response->emails->preferred : null;
    }

    /**
     * Return the screen name from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return Array                                            Firstname, Lastname
     */
    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return array($response->first_name, $response->last_name);
    }
}
