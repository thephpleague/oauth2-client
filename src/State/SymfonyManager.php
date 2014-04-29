<?php

namespace League\OAuth2\Client\State;

use Symfony\Component\HttpFoundation\Session\Session;

class SymfonyManager extends \ArrayObject
{
    protected $session;

    public function __construct(Session $session) 
    {
        $this->session = $session;
    }

    public function __get($name)
    {
        return $this->session->get($name);
    }

    public function __set($property, $value)
    {
        $this->session->set($property, $value);

        return $this;
    }
}
