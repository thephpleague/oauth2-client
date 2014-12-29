<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class Github extends AbstractProvider
{
    public $responseType = 'string';

    public $domain = 'https://github.com';

    public function urlAuthorize()
    {
        return $this->domain.'/login/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return $this->domain.'/login/oauth/access_token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        if ($this->domain === 'https://github.com') {
            return $this->domain.'/user?access_token='.$token;
        }
        return $this->domain.'/api/v3/user?access_token='.$token;
    }

    public function urlUserEmails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        if ($this->domain === 'https://github.com') {
            return $this->domain.'/user/emails?access_token='.$token;
        }
        return $this->domain.'/api/v3/user/emails?access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User();

        $name = (isset($response->name)) ? $response->name : null;
        $email = (isset($response->email)) ? $response->email : null;

        $user->exchangeArray([
            'uid' => $response->id,
            'nickname' => $response->login,
            'name' => $name,
            'email' => $email,
            'urls'  => [
                'GitHub' => $this->domain.'/'.$response->login,
            ],
        ]);

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    public function getUserEmails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = $this->fetchUserEmails($token);

        return $this->userEmails(json_decode($response), $token);
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    public function userEmails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->name;
    }

    protected function fetchUserEmails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        $url = $this->urlUserEmails($token);

        return $this->fetchProviderData($url);
    }
}
