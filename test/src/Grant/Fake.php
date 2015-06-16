<?php

namespace League\OAuth2\Client\Test\Grant;

use League\OAuth2\Client\Grant\GrantInterface;
use League\OAuth2\Client\Token\AccessToken;

class Fake implements GrantInterface
{
    public function __toString()
    {
        return 'fake';
    }

    public function prepareRequestParameters(array $defaultParams, array $params)
    {
        return array_merge($defaultParams, $params);
    }

    public function createAccessToken(array $response = [])
    {
        return new AccessToken($response);
    }
}
