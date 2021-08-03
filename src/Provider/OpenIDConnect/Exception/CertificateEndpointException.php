<?php
namespace League\OAuth2\Client\Provider\OpenIDConnect\Exception;

class CertificateEndpointException extends \Exception
{
    public function __construct($message = null, $code = null, $previous = null)
    {
        $this->message = "Certificate endpoint error. [$message]";
        $this->code = $code;
    }
}
