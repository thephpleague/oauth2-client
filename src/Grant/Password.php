<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessTokenInterface as AccessToken;

class Password implements GrantInterface
{
    public function __toString()
    {
        return 'password';
    }

    public function prepRequestParams($defaultParams, $params)
    {
        if (! isset($params['username']) || empty($params['username'])) {
            throw new \BadMethodCallException('Missing username');
        }

        if (! isset($params['password']) || empty($params['password'])) {
            throw new \BadMethodCallException('Missing password');
        }

        $params['grant_type'] = 'password';

        return array_merge($defaultParams, $params);
    }

    public function handleResponse(AccessToken $token, array $response = null)
    {
        return $token;
    }
}
