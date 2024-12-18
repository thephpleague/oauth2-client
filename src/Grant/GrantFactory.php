<?php

/**
 * This file is part of the league/oauth2-client library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Alex Bilbie <hello@alexbilbie.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link http://thephpleague.com/oauth2-client/ Documentation
 * @link https://packagist.org/packages/league/oauth2-client Packagist
 * @link https://github.com/thephpleague/oauth2-client GitHub
 */

declare(strict_types=1);

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Grant\Exception\InvalidGrantException;

use function assert;
use function gettype;
use function is_object;
use function is_scalar;
use function is_string;
use function is_subclass_of;
use function sprintf;
use function str_replace;
use function ucwords;

/**
 * Represents a factory used when retrieving an authorization grant type.
 */
class GrantFactory
{
    /**
     * @var array<string, AbstractGrant>
     */
    protected array $registry = [];

    /**
     * Defines a grant singleton in the registry.
     */
    public function setGrant(string $name, AbstractGrant $grant): static
    {
        $this->registry[$name] = $grant;

        return $this;
    }

    /**
     * Returns a grant singleton by name.
     *
     * If the grant has not be registered, a default grant will be loaded.
     */
    public function getGrant(string $name): AbstractGrant
    {
        if (!isset($this->registry[$name])) {
            $this->registerDefaultGrant($name);
            assert(isset($this->registry[$name]));
        }

        return $this->registry[$name];
    }

    /**
     * Registers a default grant singleton by name.
     */
    protected function registerDefaultGrant(string $name): static
    {
        // PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
        $class = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
        $class = 'League\\OAuth2\\Client\\Grant\\' . $class;

        $this->checkGrant($class);

        return $this->setGrant($name, new $class());
    }

    /**
     * Determines if a variable is a valid grant.
     *
     * @phpstan-assert-if-true class-string<AbstractGrant> | AbstractGrant $class
     */
    public function isGrant(mixed $class): bool
    {
        if (!is_string($class) && !is_object($class)) {
            return false;
        }

        return is_subclass_of($class, AbstractGrant::class);
    }

    /**
     * Checks if a variable is a valid grant.
     *
     * @throws InvalidGrantException
     *
     * @phpstan-assert class-string<AbstractGrant> | AbstractGrant $class
     */
    public function checkGrant(mixed $class): void
    {
        if (!$this->isGrant($class)) {
            $type = match (true) {
                is_object($class) => $class::class,
                is_scalar($class) => $class,
                default => gettype($class),
            };

            throw new InvalidGrantException(sprintf('Grant "%s" must extend AbstractGrant', $type));
        }
    }
}
