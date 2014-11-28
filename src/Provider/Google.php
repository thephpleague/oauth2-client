<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class Google extends AbstractProvider
{
    public $scopeSeparator = ' ';

    public $scopes = [
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email',
    ];

   /**
    * @var string If set, this will be sent to google as the "hd" parameter.
    * @link https://developers.google.com/accounts/docs/OAuth2Login#hd-param
    */
    public $hostedDomain = '';

    public function setHostedDomain($hd)
    {
        $this->hostedDomain = $hd;
    }

    public function getHostedDomain()
    {
        return $this->hostedDomain;
    }

    public function urlAuthorize()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    public function urlAccessToken()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = (array) $response;

        $user = new User();

        $imageUrl = (isset($response['picture'])) ? $response['picture'] : null;

        $user->exchangeArray([
            'uid' => $response['id'],
            'name' => $response['name'],
            'firstname' => $response['given_name'],
            'lastName' => $response['family_name'],
            'email' => $response['email'],
            'imageUrl' => $imageUrl,
        ]);

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return [$response->given_name, $response->family_name];
    }

    public function getAuthorizationUrl($options = array())
    {
        $url = parent::getAuthorizationUrl($options);

        if (!empty($this->hostedDomain)) {
            $url .= '&' . $this->httpBuildQuery(['hd' => $this->hostedDomain]);
        }

        return $url;
    }
}
