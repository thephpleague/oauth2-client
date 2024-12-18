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

use BadMethodCallException;

use function sprintf;

/**
 * Provides functionality to check for required parameters.
 */
trait RequiredParameterTrait
{
    /**
     * Checks for a required parameter in a hash.
     *
     * @param array<string, mixed> $params
     *
     * @return void
     *
     * @throws BadMethodCallException
     */
    private function checkRequiredParameter(string $name, array $params)
    {
        if (!isset($params[$name])) {
            throw new BadMethodCallException(sprintf(
                'Required parameter not passed: "%s"',
                $name,
            ));
        }
    }

    /**
     * Checks for multiple required parameters in a hash.
     *
     * @param list<string> $names
     * @param array<string, mixed> $params
     *
     * @return void
     *
     * @throws BadMethodCallException
     */
    private function checkRequiredParameters(array $names, array $params)
    {
        foreach ($names as $name) {
            $this->checkRequiredParameter($name, $params);
        }
    }
}
