<?php

namespace OAuth2\Client\Provider;

class User implements \IteratorAggregate {

    protected $uid = null;
    protected $nickname = null;
    protected $name = null;
    protected $firstName = null;
    protected $lastName = null;
    protected $email = null;
    protected $location = null;
    protected $description = null;
    protected $imageUrl = null;
    protected $urls = null;

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

}

