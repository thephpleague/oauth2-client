<?php

namespace League\OAuth2\Client\Grant;

interface GrantInterface {

    public function handleResponse( array $response = array() );
    public function prepRequestParams( array $defaultParams, array $params );
    public function __toString();

}
