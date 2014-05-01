<?php

namespace League\OAuth2\Client\Token;

use InvalidArgumentException;

class AccessToken
{
    /**
     * @var  string  accessToken
     */
    public $accessToken;

    /**
     * @var  int  expires
     */
    public $expires_in;

    /**
     * @var  string  refreshToken
     */
    public $refreshToken;

    /**
     * @var  string  uid
     */
    public $uid;

    /**
     * Sets the token, expiry, etc values.
     *
     * @param  array $options token options
     * @return void
     */
    public function __construct(array $options = null)
    {
        if (! isset($options['access_token'])) {
            throw new \InvalidArgumentException('Required option not passed: access_token'
                                                 . PHP_EOL.print_r($options, true));
        }

        $this->accessToken = $options['access_token'];

        // Find uid
        if (isset($options['uid'])) {
            $this->uid = $options['uid'];
        } elseif (isset($options['user_id'])) {
            // Vkontakte uses user_id instead of uid
            $this->uid = $options['user_id'];
        } elseif (isset($options['x_mailru_vid'])) {
            // Mailru uses x_mailru_vid instead of uid
            $this->uid = $options['x_mailru_vid'];
        }

        // The OAuth2 spec works in expires_in values and
        // not expiratory dates (as previously coded)
        if (isset($options['expires_in'])) {
            $this->expires_in = $options['expires_in'];
        } elseif (isset($options['expires'])) {
            // Facebook uses expires instead of expires_in
            $this->expires_in = $options['expires'];
        }

        // Grab a refresh token so we can update access tokens when they expires
        if (isset($options['refresh_token'])) {
            $this->refreshToken = $options['refresh_token'];
        }
    }

    /**
     * Returns the token key.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->accessToken;
    }
}
