<?php

namespace League\OAuth2\Client\Provider;

class User implements \IteratorAggregate
{
    /**
     * Users unique identifer (UID)
     * @var String
     */
    public $uid = null;

    /**
     * Users nickname
     * @var String
     */
    public $nickname = null;

    /**
     * Users name
     * @var String
     */
    public $name = null;

    /**
     * Users firstname
     * @var String
     */
    public $firstName = null;

    /**
     * Users lastmane
     * @var String
     */
    public $lastName = null;

    /**
     * Users email
     * @var String
     */
    public $email = null;

    /**
     * Users location
     * @var String
     */
    public $location = null;

    /**
     * Users description
     * @var String
     */
    public $description = null;

    /**
     * Users image url.
     * @var String
     */
    public $imageUrl = null;

    /**
     * Users URLs
     * @var String|Array
     */
    public $urls = null;

    /**
     * Setter helper
     * @param String $name  property to be set
     * @param String $value value to set on that property.
     */
    public function __set($name, $value)
    {
        if (isset($this->{$name})) {
            $this->{$name} = $value;
        }
    }

    /**
     * Getter helper
     * @param  String $name property to get
     * @return String       Property value, returns null if property does not exist.
     */
    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        } else {
            return null;
        }
    }

    /**
     * Return the ArrayIterator for this class, allows the use of fopr and foreach loops.
     * @return \ArrayIterator   Iterator.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }

}
