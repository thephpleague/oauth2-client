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

namespace League\OAuth2\Client\Tool;

use function in_array;
use function property_exists;

/**
 * Provides support for blacklisting explicit properties from the
 * mass assignment behavior.
 */
trait GuardedPropertyTrait
{
    /**
     * The properties that aren't mass assignable.
     *
     * @var list<string>
     */
    protected array $guarded = [];

    /**
     * Attempts to mass assign the given options to explicitly defined properties,
     * skipping over any properties that are defined in the guarded array.
     *
     * @param array<string, mixed> $options
     *
     * @return mixed
     */
    protected function fillProperties(array $options = [])
    {
        if (isset($options['guarded'])) {
            unset($options['guarded']);
        }

        foreach ($options as $option => $value) {
            if (property_exists($this, $option) && !$this->isGuarded($option)) {
                $this->{$option} = $value;
            }
        }
    }

    /**
     * Returns current guarded properties.
     *
     * @return list<string>
     */
    public function getGuarded()
    {
        return $this->guarded;
    }

    /**
     * Determines if the given property is guarded.
     *
     * @return bool
     */
    public function isGuarded(string $property)
    {
        return in_array($property, $this->getGuarded());
    }
}
