<?php
namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class Concrete5 extends AbstractProvider
{

    public $headers = array('Authorization' => 'Bearer');

    public function urlBase()
    {
        return 'https://www.concrete5.org';
    }

    public function urlAPI()
    {
        return $this->urlBase() . '/api/v1';
    }

    public function urlAuthorize()
    {
        return $this->urlAPI() . '/-/authorize';
    }

    public function urlAccessToken()
    {
        return $this->urlAPI() . '/-/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        $this->headers = array(
            'Authentication' => 'Bearer ' . $token
        );
        return $this->urlAPI() . '/-/user/?access_token=' . $token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;

        $user->exchangeArray(
            array(
                'uid'       => $response->id,
                'email'     => $response->email,
                'firstname' => $response->first_name,
                'lastname'  => $response->last_name,
                'nickname'  => $response->username
            )
        );

        return $user;
    }
}
