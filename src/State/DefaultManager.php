<?php

namespace League\OAuth2\Client\State;

class Default extends \ArrayObject
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
        return $_SESSION[$this->namespace][$name];
    }

    public function __set($property, $value)
    {
        $_SESSION[$this->namespace][$property] = $value;

        return $this;
    }
}
