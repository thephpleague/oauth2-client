<?php

namespace League\OAuth2\Client\Grant;

class Password extends AbstractGrant
{
    protected function getName()
    {
        return 'password';
    }

    protected function getRequiredRequestParameters()
    {
        return [
            'username',
            'password',
        ];
    }
}
