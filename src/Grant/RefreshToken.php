<?php

namespace League\OAuth2\Client\Grant;

class RefreshToken extends AbstractGrant
{
    public function __toString()
    {
        return 'refresh_token';
    }

    protected function getRequiredRequestParams()
    {
        return [
            'refresh_token',
        ];
    }
}
