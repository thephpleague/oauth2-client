<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessTokenInterface;

class ClientCredentials implements GrantInterface
{
    public function __toString()
    {
        return 'client_credentials';
    }

    public function prepRequestParams($defaultParams, $params)
    {
        $params['grant_type'] = 'client_credentials';

        return array_merge($defaultParams, $params);
    }

    public function handleResponse(AccessTokenInterface $token, array $response = null)
    {
        return $token;
    }
}
