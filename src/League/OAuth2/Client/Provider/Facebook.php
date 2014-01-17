<?php

namespace League\OAuth2\Client\Provider;

class Facebook extends IdentityProvider
{
    /**
     * Required Facebook scopes.
     * @var array
     */
    public $scopes = array('offline_access', 'email', 'read_stream');

    /**
     * Response type
     * @var string
     */
    public $responseType = 'string';

    /**
     * Facebooks authorization endpoint
     * @return String
     */
    public function urlAuthorize()
    {
        return 'https://www.facebook.com/dialog/oauth';
    }

    /**
     * Facebooks access token endpoint
     * @return String
     */
    public function urlAccessToken()
    {
        return 'https://graph.facebook.com/oauth/access_token';
    }

    /**
     * Get the authorized url for the facebook user data
     * @return String
     */
    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://graph.facebook.com/me?access_token='.$token;
    }

    /**
     * Construct a User object with the response
     * @param  stdClass                               $response Response frm the graph server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return League\OAuth2\Client\Provider\User               User Object
     */
    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $imageHeaders = get_headers('https://graph.facebook.com/me/picture?type=normal&access_token='.$token->accessToken, 1);

        $user = new User;
        $user->uid = $response->id;
        $user->nickname = $response->username;
        $user->name = $response->name;
        $user->firstName = $response->first_name;
        $user->lastName = $response->last_name;
        $user->email = isset($response->email) ? $response->email : null;
        $user->location = isset($response->hometown->name) ? $response->hometown->name : null;
        $user->description = isset($response->bio) ? $response->bio : null;
        $user->imageUrl = $imageHeaders['Location'];
        $user->urls = array(
            'Facebook' => $response->link,
        );

        return $user;
    }

    /**
     * Return the User ID from the response object
     * @param  stdClass                               $response Response from the Facebook graph
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Facebook User ID
     */
    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    /**
     * Return the Email from the response object
     * @param  stdClass                               $response Response from the Facebook graph
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Email address
     */
    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    /**
     * Return the screen name from the response object
     * @param  stdClass                               $response Response from the Facebook graph
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return Array                                           Firstname, Lastname
     */
    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return array($response->first_name, $response->last_name);
    }
}
