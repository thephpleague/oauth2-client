<?php
/**
 * This file is part of the League\OAuth2\Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2014 Alex Bilbie <hello@alexbilbie.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace League\OAuth2\Client\Token;

use InvalidArgumentException;

/**
 * Represents an OAuth 2.0 access token
 *
 * @link http://tools.ietf.org/html/rfc6749#section-1.4 Section 1.4 of RFC 6749
 * @link http://tools.ietf.org/html/rfc6749#section-5.1 Section 5.1 of RFC 6749
 */
class AccessToken
{
    /**
     * The access token issued by the authorization server
     *
     * @var string
     */
    public $accessToken;

    /**
     * The lifetime in seconds of the access token
     *
     * @var int
     */
    public $expires;

    /**
     * The refresh token, which can be used to obtain new access tokens using
     * the same authorization grant
     *
     * @var string
     */
    public $refreshToken;

    /**
     * Some providers return an optional user id of the user for which the
     * token was granted
     *
     * @var string
     */
    public $uid;

    /**
     * Sets the token, expiry, etc values
     *
     * @param array $options Token parameters returned from the provider
     */
    public function __construct(array $options = null)
    {
        if (! isset($options['access_token'])) {
            throw new \InvalidArgumentException(
                'Required option not passed: access_token'.PHP_EOL
                .print_r($options, true)
            );
        }

        $this->accessToken = $options['access_token'];

        // Some providers (not many) give the uid here, so lets take it
        isset($options['uid']) and $this->uid = $options['uid'];

        // Vkontakte uses user_id instead of uid
        isset($options['user_id']) and $this->uid = $options['user_id'];

        // Mailru uses x_mailru_vid instead of uid
        isset($options['x_mailru_vid']) and $this->uid = $options['x_mailru_vid'];

        //Battle.net uses accountId instead of uid
        isset($options['accountId']) and $this->uid = $options['accountId'];

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

        // Grab a refresh token so we can update access tokens when they expires
        isset($options['refresh_token']) and $this->refreshToken = $options['refresh_token'];
    }

    /**
     * Converts this access token to a string representation
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->accessToken;
    }
}
