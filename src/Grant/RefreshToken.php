<?php

namespace League\OAuth2\Client\Grant;

class RefreshToken extends AbstractGrant
{
    protected function getName()
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
