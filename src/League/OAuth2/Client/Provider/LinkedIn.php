<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;

class LinkedIn extends IdentityProvider
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

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://api.linkedin.com/v1/people/~:('.implode(",", $this->fields).')?format=json&oauth2_access_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User;

        $user->uid = $response->id;
        $user->name = $response->firstName.' '.$response->lastName;
        $user->firstName = $response->firstName;
        $user->lastName = $response->lastName;
        $user->email = isset($response->emailAddress) ? $response->emailAddress : null;
        $user->location = isset($response->location->name) ? $response->location->name : null;
        $user->description = isset($response->headline) ? $response->headline : null;
        $user->imageUrl = isset($response->pictureUrl) ? $response->pictureUrl : null;
        $user->urls = isset($response->publicProfileUrl) ? $response->publicProfileUrl : null;

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->emailAddress) && $response->emailAddress ? $response->emailAddress : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return array($response->firstName, $response->lastName);
    }
}
