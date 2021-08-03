<?php
namespace League\OAuth2\Client\Provider\OpenIDConnect\Exception;

class TokenIntrospectionException extends \Exception
{
    public function __construct ($message = null, $code = null, $previous = null) {
        $this->message = "Token Introspection Exception [$message]";
        parent::__construct($message, $code, $previous);
    }
}

