<?php

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Provider\UserInterface;

class User implements UserInterface
{
    public function getUserId()
    {
        return 123;
    }
}
