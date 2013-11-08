<?php

namespace League\OAuth2\Client\Token;


class AccessToken {
    
    /**
     * @var string accessToken
     */
    public $accessToken;

    /**
     * @var int expires
     */
    public $expires;

    /**
     * @var string refreshToken
     */
    public $refreshToken;

    /**
     * @var string uid
     */
    public $uid;

    /**
     * Sets the token, expiry, etc values.
     *
     * @param array token options
     */
    public function __construct( array $options = null ) {
        
        if( !isset( $options['access_token'] ) ) {
            throw new \InvalidArgumentException(
                    'Required option not passed: access_token'
                    .PHP_EOL.print_r( $options, true ) );
        }

        $this->accessToken = $options['access_token'];

        // Some providers (not many) give the uid here, so lets take it
        if( isset( $options['uid'] ) ) {
            $this->uid = $options['uid'];
        }

        // VKontakte uses user_id instead of uid
        if( isset( $options['user_id'] ) ) {
            $this->uid = $options['user_id'];
        }

        // Mail.ru uses x_mailru_vid instead of uid
        if( isset( $options['x_mailru_vid'] ) ) {
            $this->uid = $options['x_mailru_vid'];
        }

        // We need to know when the token expires, add num. seconds
        // to current time
        if( isset( $options['expires_in'] ) ) {
            $this->expires = time() + intval( $options['expires_in'] );
        }

        // Facebook is just being a spec ignoring jerk
        if( isset( $options['expires'] ) ) {
            $this->expires = time() + intval( $options['expires'] );
        }

        // Grab a refresh token so we can update access tokens when they expires
        if( isset( $options['refresh_token'] ) ) {
            $this->refreshToken = $options['refresh_token'];
        }
    }

    /**
     * Returns the token key.
     *
     * @return string
     */
    public function __toString() {
        return (string)$this->accessToken;
    }

    /**
     * Return a boolean if the property is set
     *
     * @param string variable name
     * @return bool true if the property is set, false otherwise
     */
    public function __isset( $key ) {
        return isset( $this->$key );
    }

}
