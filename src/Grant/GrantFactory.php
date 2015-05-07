<?php

namespace League\OAuth2\Client\Grant;

class GrantFactory
{
    /**
     * @var array
     */
    protected $registry = [];

    /**
     * Define a grant class in the registry.
     *
     * @param  string $name
     * @param  string $class
     * @return $this
     */
    public function setGrant($name, $class)
    {
        $this->registry[$name] = $this->checkGrant($class);

        return $this;
    }

    /**
     * Get a grant instance by name.
     *
     * If the grant has not be registered, a default grant will be loaded.
     *
     * @param  string $name
     * @param  string $options
     * @return GrantInterface
     */
    public function getGrant($name, array $options = [])
    {
        if (empty($this->registry[$name])) {
            $this->registry[$name] = $this->getGrantClass($name);
        }

        $class = $this->registry[$name];

        return new $class($options);
    }

    /**
     * Guess a grant class from the name of the grant.
     *
     * @param  string $name
     * @return string
     */
    protected function getGrantClass($name)
    {
        // PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
        $class = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
        $class = 'League\\OAuth2\\Client\\Grant\\' . $class;

        return $this->checkGrant($class);
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
     * @throws InvalidArgumentException
     * @param  mixed $class
     * @return mixed
     */
    public function checkGrant($class)
    {
        if (!$this->isGrant($class)) {
            throw new InvalidGrantException(sprintf(
                'Grant "%s" must implement GrantInterface',
                is_object($class) ? get_class($class) : $class
            ));
        }
        return $class;
    }
}
