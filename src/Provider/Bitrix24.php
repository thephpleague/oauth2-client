<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Bitrix24 extends AbstractProvider
{
    protected $domain;

    public $responseType = 'json';
    public $method = 'get';

    public function urlAuthorize()
    {
        return sprintf('https://%s/oauth/authorize/', $this->domain);
    }

    public function urlAccessToken()
    {
        return sprintf('https://%s/oauth/token/', $this->domain);
    }

    public function urlUserDetails(AccessToken $token)
    {
        return sprintf('https://%s/oauth/rest/user.current.json?token=%s', $this->domain, $token->accessToken);
    }

    /**
     * @param array $domain DOMAIN.bitrix24.com or DOMAIN.bitrix24.ru
     * @param array $options
     */
    public function __construct($domain, $options = array())
    {
        $this->domain = $domain;

        parent::__construct($options);
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User;

        $name = (isset($response->NAME)) ? $response->NAME : null;
        $lastname = (isset($response->LAST_NAME)) ? $response->LAST_NAME : null;
        $email = (isset($response->EMAIL)) ? $response->EMAIL : null;
        $imageUrl = (isset($response->PERSONAL_PHOTO)) ? $response->PERSONAL_PHOTO : null;

        $user->exchangeArray(array(
            'uid'       => $response->ID,
            'nickname'  => $name,
            'name'      => $name,
            'lastname'  => $lastname,
            'email'     => $email,
            'imageUrl'  => $imageUrl
        ));

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->ID;
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->EMAIL) && $response->EMAIL ? $response->EMAIL : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return $response->NAME;
    }
}
