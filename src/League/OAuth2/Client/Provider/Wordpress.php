<?php

namespace League\OAuth2\Client\Provider;

class Wordpress extends IdentityProvider
{
    public $scopes = array('offline_access', 'email', 'read_stream');
    public $responseType = 'string';

    public function urlAuthorize()
    {
        return 'https://public-api.wordpress.com/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://public-api.wordpress.com/oauth2/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://public-api.wordpress.com/rest/v1/me?pretty=1&access_token='.$token;
        // In the header: 'authorization: Bearer YOUR_API_TOKEN'
        // 'Authorization: Bearer ' . $access_key
        // If denied... It will be redirected: ?error=access_denied
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response;
    }
}
