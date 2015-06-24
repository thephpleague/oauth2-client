<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessTokenInterface;

interface GrantInterface
{
    public function __toString();

    public function handleResponse(AccessTokenInterface $token, array $response = null);

    public function prepRequestParams($defaultParams, $params);
}
