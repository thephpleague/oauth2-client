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

    public function prepRequestParams($defaultParams, $params)
    {
        return array_merge($defaultParams, $params);
    }

    public function handleResponse($response = array())
    {
        return new AccessToken($response);
    }
}
