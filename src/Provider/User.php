<?php

namespace League\OAuth2\Client\Provider;

class User implements \IteratorAggregate
{
    public $uid;
    public $nickname;
    public $name;
    public $firstName;
    public $lastName;
    public $email;
    public $location;
    public $description;
    public $imageUrl;
    public $urls;

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
