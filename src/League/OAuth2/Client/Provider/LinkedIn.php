<?php

namespace League\OAuth2\Client\Provider;

class LinkedIn extends IdentityProvider
{
    /**
     * Required Linkedin's scopes.
     * @var array
     */
    public $scopes = array('r_basicprofile r_emailaddress r_contactinfo');

    /**
     * Response Type
     * @var string
     */
    public $responseType = 'json';

    /**
     * Request fields (properties that we want from linkedin's API)
     * @var array
     */
    public $fields = array(
        'id',
        'email-address',
        'first-name',
        'last-name',
        'headline',
        'location',
        'industry',
        'picture-url',
        'public-profile-url'
    );

    /**
     * Linkedin's authorization endpoint
     * @return String
     */
    public function urlAuthorize()
    {
        return 'https://www.linkedin.com/uas/oauth2/authorization';
    }

    /**
     * Linkedin's access token endpoint
     * @return String
     */
    public function urlAccessToken()
    {
        return 'https://www.linkedin.com/uas/oauth2/accessToken';
    }

    /**
     * Get the authorized url for the linkedin user data
     * @return String
     */
    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.linkedin.com/v1/people/~:('.implode(",", $this->fields).')?format=json&oauth2_access_token='.$token;
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

        $user->uid = $response->id;
        $user->name = $response->firstName.' '.$response->lastName;
        $user->firstName = $response->firstName;
        $user->lastName = $response->lastName;
        $user->email = isset($response->emailAddress) ? $response->emailAddress : null;
        $user->location = isset($response->location->name) ? $response->location->name : null;
        $user->description = isset($response->headline) ? $response->headline : null;
        $user->imageUrl = $response->pictureUrl;
        $user->urls = $response->publicProfileUrl;

        return $user;
    }

    /**
     * Return the User ID from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return String                                           Linkedin's User ID
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
        return isset($response->emailAddress) && $response->emailAddress ? $response->emailAddress : null;
    }

    /**
     * Return the screen name from the response object
     * @param  stdClass                               $response Response from the API Server
     * @param  League\OAuth2\Client\Token\AccessToken $token    Token Object
     * @return Array                                            Firstname, Lastname
     */
    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return array($response->firstName, $response->lastName);
    }
}
