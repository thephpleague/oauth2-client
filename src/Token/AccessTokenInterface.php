<?php

namespace League\OAuth2\Client\Token;

interface AccessTokenInterface
{
    /**
     * Sets the token, expiry, etc values.
     *
     * @param  array $options token options
     * @return void
     */
    public function __construct(array $options = null);
    
    /**
     * Returns the token key.
     *
     * @return string
     */
    public function __toString();
            
}
