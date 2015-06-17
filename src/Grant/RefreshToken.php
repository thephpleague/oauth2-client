<?php

namespace League\OAuth2\Client\Grant;

class RefreshToken extends AbstractGrant
{
    public function __toString()
    {
        return 'refresh_token';
    }

    protected function getRequiredRequestParameters()
    {
        return [
            'refresh_token',
        ];
    }
}
