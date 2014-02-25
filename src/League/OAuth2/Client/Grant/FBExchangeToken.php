<?php 

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessToken as AccessToken;

class FBExchangeToken implements GrantInterface
{
    public function __toString()
    {
        return 'fb_exchange_token';
    }


    public function prepRequestParams($defaultParams, $params)
    {
        if ( ! isset($params['fb_exchange_token']) || empty($params['fb_exchange_token'])) {
            throw new BadMethodCallException('Missing faceboook exchange token');
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
