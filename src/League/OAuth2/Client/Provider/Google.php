<?php

namespace League\OAuth2\Client\Provider;

class Google extends IdentityProvider
{
    /**
     * Scopes seporator
     * @var string
     */
    public $scopeSeperator = ' ';

    /**
     * Required Google scopes.
     * @var array
     */
    public $scopes = array(
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email'
    );


    /**
     * Googles authorization endpoint
     * @return String
     */
    public function urlAuthorize()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    /**
     * Googles access token endpoint
     * @return String
     */
    public function urlAccessToken()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    /**
     * Get the authorized url for the Google user data
     * @return String
     */
    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token;
    }

    /**
     * Construct a User object with the response
     * @param  stdClass                               $response Response frm the api server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return League\OAuth2\Client\Provider\User               User Object
     */
    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = (array) $response;
        $user = new User;
        $user->uid = $response['id'];
        $user->name = $response['name'];
        $user->firstName = $response['given_name'];
        $user->lastName = $response['family_name'];
        $user->email = $response['email'];
        $user->imageUrl = (isset($response['picture'])) ? $response['picture'] : null;
        return $user;
    }

    /**
     * Return the User ID from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Facebook User ID
     */
    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    /**
     * Return the Email from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Email address
     */
    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    /**
     * Return the screen name from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return Array                                           Firstname, Lastname
     */
    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return array($response->given_name, $response->family_name);
    }
}
