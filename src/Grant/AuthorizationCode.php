<?php

namespace League\OAuth2\Client\Grant;

class AuthorizationCode extends AbstractGrant
{
    protected function getName()
    {
        return 'authorization_code';
    }

    protected function getRequiredRequestParameters()
    {
        return [
            'code',
        ];
    }
}
