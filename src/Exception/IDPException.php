<?php

namespace League\OAuth2\Client\Exception;

class IDPException extends \Exception
{
    protected $result;

    public function __construct($result, $message = null, $code = null)
    {
        $this->result = $result;

        if (!$code) {
            $code = isset($result['code']) ? $result['code'] : 0;
        }

        if (!$message) {
            if (isset($result['error']) && $result['error'] !== '') {
                // OAuth 2.0 Draft 10 style
                $message = $result['error'];
            } elseif (isset($result['message']) && $result['message'] !== '') {
                // cURL style
                $message = $result['message'];
            } else {
                $message = 'Unknown Error.';
            }
        }

        parent::__construct($message, $code);
    }

    public function getResponseBody()
    {
        return $this->result;
    }

    public function getType()
    {
        $result = 'Exception';

        if (isset($this->result['error'])) {
            $message = $this->result['error'];

            if (is_string($message)) {
                // OAuth 2.0 Draft 10 style
                $result = $message;
            }
        }

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
