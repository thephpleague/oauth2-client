<?php

namespace League\OAuth2\Client\Test\Grant;

use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Token\AccessToken;

class Fake extends AbstractGrant
{
    protected function getName()
    {
        return 'fake';
    }

    protected function getRequiredRequestParameters()
    {
        return [];
    }
}
