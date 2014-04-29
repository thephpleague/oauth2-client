<?php

namespace League\OAuth2\Client\State;

class DefaultManager extends \ArrayObject
{
    protected $namespace = 'oauth2-client';

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($value)
    {
        $this->namespace = $value;
    }

    public function __get($name)
    {
        return (isset($_SESSION[$this->getNamespace()][$name])) ? $_SESSION[$this->namespace][$name]: null ;
    }

    public function __set($property, $value)
    {
        $_SESSION[$this->getNamespace()][$property] = $value;

        return $this;
    }
}
