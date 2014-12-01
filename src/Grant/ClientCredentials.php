<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessToken;

class ClientCredentials implements GrantInterface
{
    /**
     * @return string
     */
    public function __toString()
    {
        return 'client_credentials';
    }

    /**
     * @param array $defaultParams
     * @param array $params
     *
     * @return array
     */
    public function prepRequestParams($defaultParams, $params)
    {
        $params['grant_type'] = 'client_credentials';

        return array_merge($defaultParams, $params);
    }

    /**
     * @param array $response
     *
     * @return AccessToken
     */
    public function handleResponse($response = array())
    {
        return new AccessToken($response);
    }
}
