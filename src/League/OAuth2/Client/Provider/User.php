<?php

namespace League\OAuth2\Client\Provider;

class User implements \IteratorAggregate {

    public $uid = null;
    public $nickname = null;
    public $name = null;
    public $firstName = null;
    public $lastName = null;
    public $email = null;
    public $location = null;
    public $description = null;
    public $imageUrl = null;
    public $urls = null;

    public function __set($name, $value)
    {
        if (isset($this->{$name})) {
            $this->{$name} = $value;
        }
    }

    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        } else {
            return null;
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this);
    }

}

