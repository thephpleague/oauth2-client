<?php

namespace League\OAuth2\Client\Provider;

interface UserInterface
{
    /**
     * Get the identifier of the authorized user.
     *
     * @return mixed
     */
    public function getUserId();
}
