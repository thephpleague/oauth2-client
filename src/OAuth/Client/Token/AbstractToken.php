<?php

namespace OAuth2\Client;

use InvalidArgumentException;

abstract class AbstractToken
{
    /**
     * Create a new token object.
     *
     * @param   string  token type
     * @param   array   token options
     * @return  Token
     */
    public static function factory($name = 'access', array $options = null)
    {
        $class = 'OAuth2\\Client\\Token\\'.ucfirst($name);
        if ( ! class_exists($name)) {
            throw new InvalidArgumentException('Invalide token type: '.$name);
        }

        return new $class($options);
    }

    /**
     * Return a boolean if the property is set
     *
     * @param   string  variable name
     * @return  bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }

    /**
     * Return the token string.
     *
     * @return string
     */
    public function __toString();
}
