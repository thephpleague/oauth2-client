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

namespace League\OAuth2\Client\Token;

use JsonSerializable;
use RuntimeException;

interface AccessTokenInterface extends JsonSerializable
{
    /**
     * Returns the access token string of this instance.
     */
    public function getToken(): string;

    /**
     * Returns the refresh token, if defined.
     */
    public function getRefreshToken(): ?string;

    /**
     * Returns the expiration timestamp in seconds, if defined.
     */
    public function getExpires(): ?int;

    /**
     * Checks if this token has expired.
     *
     * @return bool true if the token has expired, false otherwise.
     *
     * @throws RuntimeException if 'expires' is not set on the token.
     */
    public function hasExpired(): bool;

    /**
     * Returns additional vendor values stored in the token.
     *
     * @return array<string, mixed>
     */
    public function getValues(): array;

    /**
     * Returns a string representation of the access token
     */
    public function __toString(): string;

    /**
     * Returns an array of parameters to serialize when this is serialized with
     * json_encode().
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array;
}
