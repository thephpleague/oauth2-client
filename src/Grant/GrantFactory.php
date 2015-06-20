<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Grant\Exception\InvalidGrantException;

class GrantFactory
{
    /**
     * @var array
     */
    protected $registry = [];

    /**
     * Define a grant singleton in the registry.
     *
     * @param  string $name
     * @param  AbstractGrant $class
     * @return $this
     */
    public function setGrant($name, AbstractGrant $grant)
    {
        $this->registry[$name] = $grant;

        return $this;
    }

    /**
     * Get a grant singleton by name.
     *
     * If the grant has not be registered, a default grant will be loaded.
     *
     * @param  string $name
     * @return AbstractGrant
     */
    public function getGrant($name)
    {
        if (empty($this->registry[$name])) {
            $this->registerDefaultGrant($name);
        }

        return $this->registry[$name];
    }

    /**
     * Register a default grant singleton by name.
     *
     * @param  string $name
     * @return $this
     */
    protected function registerDefaultGrant($name)
    {
        // PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
        $class = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
        $class = 'League\\OAuth2\\Client\\Grant\\' . $class;

        $this->checkGrant($class);

        return $this->setGrant($name, new $class);
    }

    /**
     * Determine if a variable is a valid grant.
     *
     * @param  mixed $class
     * @return boolean
     */
    public function isGrant($class)
    {
        return is_subclass_of($class, AbstractGrant::class);
    }

    /**
     * Check if a variable is a valid grant.
     *
     * @throws InvalidGrantException
     * @param  mixed $class
     * @return void
     */
    public function checkGrant($class)
    {
        if (!$this->isGrant($class)) {
            throw new InvalidGrantException(sprintf(
                'Grant "%s" must extend AbstractGrant',
                is_object($class) ? get_class($class) : $class
            ));
        }
    }
}
