<?php

namespace League\OAuth2\Client\Provider;

class Google extends IdentityProvider
{
    public $scopeSeperator = ' ';

    public $scopes = array(
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email'
    );

    public $authorizeParams = array('access_type' => 'offline');

    public function urlAuthorize()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    public function urlAccessToken()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    public function getUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = $this->getDataFromURL('https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token);

        $response = (array) $response;
        $user = new User;
        $user->uid = $response['id'];
        $user->name = $response['name'];
        $user->firstName = $response['given_name'];
        $user->lastName = $response['family_name'];
        $user->email = $response['email'];
        $user->imageUrl = (isset($response['picture'])) ? $response['picture'] : null;
        $user->gender = $response['gender'];
        $user->urls = array(
            'GooglePlus' => $response['link']
        );

        return $user;
    }
}
