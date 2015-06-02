<?php

namespace League\OAuth2\Client\Grant;

class AuthorizationCode extends AbstractGrant
{
    public function __toString()
    {
        return 'authorization_code';
    }

    protected function getRequiredRequestParams()
    {
        return [
            'code',
        ];
    }
}
