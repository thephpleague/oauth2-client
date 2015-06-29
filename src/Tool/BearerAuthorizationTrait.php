<?php

namespace League\OAuth2\Client\Tool;

use League\OAuth2\Client\Token\AccessToken;

/**
 * Enables `Bearer` header authorization for providers.
 *
 * http://tools.ietf.org/html/rfc6750
 */
trait BearerAuthorizationTrait
{
    protected function getAuthorizationHeaders(AccessToken $token = null)
    {
        return ['Authorization' => 'Bearer ' . $token];
    }
}
