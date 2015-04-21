<?php

namespace League\OAuth2\Client\Entity;

use ArrayAccess;
use OutOfRangeException;

class User implements ArrayAccess
{
    protected $details = [];

    public function __get($name)
    {
        if (!isset($this->details[$name])) {
            throw new OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }

        return $this->details[$name];
    }

    public function __set($name, $value)
    {
        $this->details[$name] = $value;

        return $this;
    }

    public function __isset($name)
    {
        return (isset($this->details[$name]));
    }

    public function __unset($name)
    {
        unset($this->details[$name]);
    }

    public function getArrayCopy()
    {
        return $this->details;
    }

    public function exchangeArray(array $data)
    {
        $this->details = $data;

        return $this;
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}
