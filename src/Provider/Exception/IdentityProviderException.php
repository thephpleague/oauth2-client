<?php

namespace League\OAuth2\Client\Provider\Exception;

class IdentityProviderException extends \Exception
{
    protected $result;

    public function __construct($message, $code, $result)
    {
        $this->result = $result;

        parent::__construct($message, $code);
    }

    public function getResponseBody()
    {
        return $this->result;
    }

    public function getType()
    {
        $result = 'Exception';

        return $result;
    }

    /**
     * To make debugging easier.
     *
     * @return string The string representation of the error.
     */
    public function __toString()
    {
        $str = $this->getType().': ';

        if ($this->code != 0) {
            $str .= $this->code.': ';
        }

        return $str.$this->message;
    }
}
