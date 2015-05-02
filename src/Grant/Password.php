<?php

namespace League\OAuth2\Client\Grant;

class Password extends AbstractGrant
{
    public function __toString()
    {
        return 'password';
    }

    protected function getRequiredRequestParams()
    {
        return [
            'username',
            'password',
        ];
    }
}
