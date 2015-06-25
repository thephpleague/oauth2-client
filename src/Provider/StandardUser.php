<?php

namespace League\OAuth2\Client\Provider;

class StandardUser implements UserInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @var string
     */
    protected $uid;

    public function __construct(array $response, $uid)
    {
        $this->response = $response;
        $this->uid = $uid;
    }

    public function getUserId()
    {
        return $this->response[$this->uid];
    }

    /**
     * Get the raw user response.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
