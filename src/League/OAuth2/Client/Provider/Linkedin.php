<?php

namespace League\OAuth2\Client\Provider;

class Linkedin extends IdentityProvider
{
    public $scopes = array('r_basicprofile r_emailaddress');
    public $responseType = 'json';
    public $fields = array('id', 'email-address', 'first-name', 'last-name', 'headline', 'industry', 'picture-url', 'public-profile-url');

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
        return 'https://api.linkedin.com/v1/people/~:('.implode(",", $this->fields).')?format=json&oauth2_access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;

        $user->uid = $response->id;
        $user->name = isset($response->firstName) && isset($response->lastName) && $response->firstName && $response->lastName ? $response->firstName.' '.$response->lastName : null;
        $user->firstName = isset($response->firstName) && $response->firstName ? $response->firstName : null;
        $user->lastName = isset($response->lastName) && $response->lastName ? $response->lastName : null;
        $user->email = isset($response->emailAddress) && $response->emailAddress ? $response->emailAddress : null;
        $user->description = isset($response->headline) && $response->headline ? $response->headline : null;
        $user->imageUrl = isset($response->pictureUrl) && $response->pictureUrl ? $response->pictureUrl : null;
        $user->urls = isset($response->publicProfileUrl) && $response->publicProfileUrl ? $response->publicProfileUrl : null;

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
        return isset($response->firstName) && isset($response->lastName) && $response->firstName && $response->lastName ? $response->firstName.' '.$response->lastName : null;
    }
}