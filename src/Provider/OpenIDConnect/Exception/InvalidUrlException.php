<?php
namespace League\OAuth2\Client\Provider\OpenIDConnect\Exception;

class InvalidUrlException extends \Exception
{
    public function __construct ($message = null, $code = null, $previous = null) {
        $this->message = "Invalid URL [$message]";
    }
}

