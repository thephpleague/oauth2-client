<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessToken;

interface GrantInterface
{
    public function __toString();

    /**
     * @param array $response
     *
     * @return AccessToken
     */
    public function handleResponse($response = []);

    /**
     * @param array $defaultParams
     * @param array $params
     *
     * @return array
     */
    public function prepRequestParams($defaultParams, $params);
}
