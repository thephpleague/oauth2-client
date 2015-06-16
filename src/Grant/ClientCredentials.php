<?php

namespace League\OAuth2\Client\Grant;

class ClientCredentials extends AbstractGrant
{
    protected function getName()
    {
        return 'client_credentials';
    }

    protected function getRequiredRequestParameters()
    {
        return [];
    }
}
