<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessToken;

class AuthorizationCode implements GrantInterface
{
    /**
     * @return string
     */
    public function __toString()
    {
        return 'authorization_code';
    }

    /**
     * @param array $defaultParams
     * @param array $params
     *
     * @return array
     */
    public function prepRequestParams($defaultParams, $params)
    {
        if (! isset($params['code']) || empty($params['code'])) {
            throw new \BadMethodCallException('Missing authorization code');
        }

        return array_merge($defaultParams, $params);
    }

    /**
     * @param array $response
     *
     * @return AccessToken
     */
    public function handleResponse($response = [])
    {
        return new AccessToken($response);
    }
}
