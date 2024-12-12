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

use function array_key_exists;
use function count;
use function explode;
use function is_array;
use function is_string;
use function strpos;

/**
 * Provides generic array navigation tools.
 */
trait ArrayAccessorTrait
{
    /**
     * Returns a value by key using dot notation.
     *
     * @param array<string, mixed> $data
     *
     * @return mixed
     */
    private function getValueByKey(array $data, mixed $key, mixed $default = null)
    {
        if (!is_string($key) || !isset($key) || !count($data)) {
            return $default;
        }

        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey) {
                if (!is_array($data) || !array_key_exists($innerKey, $data)) {
                    return $default;
                }

                $data = $data[$innerKey];
            }

            return $data;
        }

        return array_key_exists($key, $data) ? $data[$key] : $default;
    }
}
