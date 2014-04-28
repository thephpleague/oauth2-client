<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class LinkedIn extends AbstractProvider
{
    public $scopes = array('r_basicprofile r_emailaddress r_contactinfo');
    public $responseType = 'json';
    public $fields = array('id', 'email-address', 'first-name', 'last-name', 'headline', 'location', 'industry', 'picture-url', 'public-profile-url');

    public function urlAuthorize()
    {
        return 'https://www.linkedin.com/uas/oauth2/authorization';
    }

    public function urlAccessToken()
    {
        return 'https://www.linkedin.com/uas/oauth2/accessToken';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.linkedin.com/v1/people/~:(' . implode(",", $this->fields) . ')?format=json&oauth2_access_token=' . $token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;

        $email = (isset($response->emailAddress)) ? $response->emailAddress : null;
        $location = (isset($response->location->name)) ? $response->location->name : null;
        $description = (isset($response->headline)) ? $response->headline : null;

        $user->exchangeArray(array(
            'uid' => $response->id,
            'name' => $response->firstName . ' ' . $response->lastName,
            'firstname' => $response->firstName,
            'lastname' => $response->lastName,
            'email' => $email,
            'location' => $location,
            'description' => $description,
            'imageurl' => $response->pictureUrl,
            'urls' => $response->publicProfileUrl,
        ));

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->emailAddress) && $response->emailAddress ? $response->emailAddress : null;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return array($response->firstName, $response->lastName);
    }
}
