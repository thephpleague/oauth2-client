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

namespace League\OAuth2\Client\Token;

use InvalidArgumentException;
use RuntimeException;

class AccessToken
{
    /**
     * @var  string
     */
    protected $access_token;

    /**
     * @var  int
     */
    protected $expires;

    /**
     * @var  string
     */
    protected $refresh_token;

    /**
     * @var  string
     */
    protected $uid;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (empty($options['access_token'])) {
            throw new InvalidArgumentException('Required option not passed: "access_token"');
        }

        $this->accessToken = $options['access_token'];

        if (!empty($options['uid'])) {
            $this->uid = $options['uid'];
        }

        if (!empty($options['refresh_token'])) {
            $this->refreshToken = $options['refresh_token'];
        }

        // We need to know when the token expires. Show preference to
        // 'expires_in' since it is defined in RFC6749 Section 5.1.
        // Defer to 'expires' if it is provided instead.
        if (!empty($options['expires_in'])) {
            $this->expires = time() + ((int) $options['expires_in']);
        } elseif (!empty($options['expires'])) {
            // Some providers supply the seconds until expiration rather than
            // the exact timestamp. Take a best guess at which we received.
            $expires = $options['expires'];
            $expiresInFuture = $expires > time();
            $this->expires = $expiresInFuture ? $expires : time() + ((int) $expires);
        }
    }

    /**
     * Get the access token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->accessToken;
    }

    /**
     * Get the refresh token, if defined.
     *
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Get the expiration timestamp, if defined.
     *
     * @return integer|null
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Get the user identifier, if defined.
     *
     * @return string|null
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Checks if the token has expired.
     *
     * @return boolean true if the token has expired, false otherwise.
     * @throws RuntimeException if 'expires' is not set on the token.
     */
    public function hasExpired()
    {
        $expires = $this->getExpires();

        if (empty($expires)) {
            throw new RuntimeException('"expires" is not set on the token');
        }

        return $expires < time();
    }

    /**
     * Returns the token key.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getToken();
    }
}
