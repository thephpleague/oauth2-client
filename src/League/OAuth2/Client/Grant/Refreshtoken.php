<?php 

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessToken as AcessToken;

class Refreshtoken implements GrantInterface
{

    public function __toString()
    {
        return 'refresh_token';
    }


    public function prepRequestParams($defaultParams, $params)
    {
        if (!isset($parmas['refresh_token']) || empty($params['refresh_token'])) {
            throw new BadMethodCallException('Missing refresh token');
        }

        // redirect uri will throw an unexpected paramater exception if included
        unset($defaultParams['redirect_uri']);

        return array_merge($defaultParams, $params);
    }


    public function handleResponse($response = array())
    {
        return new AccessToken($response);
    }
}
