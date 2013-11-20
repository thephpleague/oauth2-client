<?php

namespace League\OAuth2\Client\Grant;

interface GrantInterface
{
    public function __toString();

    public function handleResponse($response = array());

    public function prepRequestParams($defaultParams, $params);

}
