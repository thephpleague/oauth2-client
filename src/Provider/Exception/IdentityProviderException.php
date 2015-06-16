<?php

namespace League\OAuth2\Client\Provider\Exception;

class IdentityProviderException extends \Exception
{
    /**
     * @var mixed
     */
    protected $response;

    public function __construct($message, $code, $response)
    {
        $this->response = $response;

        parent::__construct($message, $code);
    }

    public function getResponseBody()
    {
        return $this->response;
    }
}
