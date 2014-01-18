<?php

namespace League\OAuth2\Client\Provider;

class Vkontakte extends IdentityProvider
{
    /**
     * Required Vkontakte scopes
     * @var array
     */
    public $scopes = array();

    /**
     * Response Type
     * @var string
     */
    public $responseType = 'json';

    /**
     * Request fields (properties that we want from Vkontakte's API)
     * @var array
     */
    public $fields = array(
        'nickname',
        'screen_name',
        'sex',
        'bdate',
        'city',
        'country',
        'timezone',
        'photo_50',
        'photo_100',
        'photo_200_orig',
        'has_mobile',
        'contacts',
        'education',
        'online',
        'counters',
        'relation',
        'last_seen',
        'status',
        'can_write_private_message',
        'can_see_all_posts',
        'can_see_audio',
        'can_post',
        'universities',
        'schools',
        'verified'
    );

    /**
     * Vkontakte's Auhtorization endpoint
     * @return String
     */
    public function urlAuthorize()
    {
        return 'https://oauth.vk.com/authorize';
    }

    /**
     * Vkontakte's Access Token endpoint
     * @return String
     */
    public function urlAccessToken()
    {
        return 'https://oauth.vk.com/access_token';
    }

    /**
     * Authorized endpoint for user data
     * @param  League\OAuth2\Clien\tToken\AccessToken $token Access Token
     * @return String
     */
    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return "https://api.vk.com/method/users.get?user_id={$token->uid}&fields=".implode(",", $this->fields)."&access_token=".$token;
    }


    /**
     * Construct a User object with the response
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return League\OAuth2\Client\Provider\User               User Object
     */
    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = $response->response[0];

        $user = new User;
        $user->uid = $response->uid;
        $user->nickname = $response->nickname;
        $user->name = $response->screen_name;
        $user->firstName = $response->first_name;
        $user->lastName = $response->last_name;
        $user->email = isset($response->email) ? $response->email : null;
        $user->location = isset($response->country) ? $response->country : null;
        $user->description = isset($response->status) ? $response->status : null;
        $user->imageUrl = $response->photo_200_orig;

        return $user;
    }

    /**
     * Return the User ID from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Vkontakte's User ID
     */
    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = $response->response[0];

        return $response->uid;
    }

    /**
     * Return the Email from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Email address
     */
    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = $response->response[0];

        return isset($response->email) && $response->email ? $response->email : null;
    }

    /**
     * Return the screen name from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return Array                                            Firstname, Lastname
     */
    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = $response->response[0];

        return array($response->first_name, $response->last_name);
    }
}
