<?php

namespace OAuth2\Client\Provider;

class UniLincoln extends IdentityProvider {

    public $scopes = array('public');

    public function urlAuthorize()
    {
        return 'https://ssotest.online.lincoln.ac.uk/oauth';
    }

    public function urlAccessToken()
    {
        return 'https://ssotest.online.lincoln.ac.uk/access_token';
    }

    public function urlUserDetails(\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://n2.online.lincoln.ac.uk/people/me?access_token='.$token;
    }

    public function userDetails($response, \OAuth2\Client\Token\AccessToken $token)
    {
        die(var_dump($response));
    }

}