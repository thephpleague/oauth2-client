<?php

namespace League\OAuth2\Client\Grant;

class ClientCredentials extends AbstractGrant
{
    public function __toString()
    {
        return 'client_credentials';
    }

    protected function getRequiredRequestParams()
    {
        return [];
    }
}
