<?php

namespace League\OAuth2\Client\Token;

use ReturnTypeWillChange;

interface ArrayableAccessTokenInterface
{
    /**
     * Returns an array of parameters provided to the access token
     *
     * @return array
     */
    #[ReturnTypeWillChange]
    public function toArray();
}
