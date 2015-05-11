<?php

namespace League\OAuth2\Client\Tool;

/**
 * Enables `Bearer` header authorization for providers.
 *
 * http://tools.ietf.org/html/rfc6750
 */
trait BearerAuthorizationTrait
{
    protected function getAuthorizationHeaders($token = null)
    {
        return ['Authorization' => 'Bearer ' . $token];
    }
}
