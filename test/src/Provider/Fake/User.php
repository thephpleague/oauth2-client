<?php

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Provider\UserInterface;

class User implements UserInterface
{
    /**
     * @var array
     */
    protected $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getUserId()
    {
        return $this->response['id'];
    }

    public function getUserEmail()
    {
        return $this->response['email'];
    }

    public function getUserScreenName()
    {
        return $this->response['name'];
    }
}
