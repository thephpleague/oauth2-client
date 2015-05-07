<?php

namespace League\OAuth2\Client\Grant;

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
     * @param  GrantInterface $class
     * @return $this
     */
    public function setGrant($name, GrantInterface $grant)
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
     * @return GrantInterface
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
        return is_subclass_of($class, 'League\OAuth2\Client\Grant\GrantInterface');
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
                'Grant "%s" must implement GrantInterface',
                is_object($class) ? get_class($class) : $class
            ));
        }
    }
}
