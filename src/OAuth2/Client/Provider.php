<?php

namespace OAuth2\Client;

use InvalidArgumentException;

class Provider
{
    private function __constuct() {}

    public static function factory($name, array $options = array())
    {
        $name = 'OAuth2\\Client\\Provider\\'.ucfirst($name);
        if ( ! class_exists($name)) {
            throw new InvalidArgumentException('There is no identity provider called: '.$name);
        }

        return new $name($options);
    }
}