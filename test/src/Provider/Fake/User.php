<?php

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class User implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
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

    public function toArray()
    {
        return $this->response;
    }
}
