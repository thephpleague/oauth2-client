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

namespace League\OAuth2\Client\Exception;

/**
 * Thrown when an exception occurs with an identity provider
 */
class IDPException extends \Exception
{
    protected $result;

    /**
     * Constructs an identity provider exception
     *
     * @param array $result The parsed response from an identity provider.
     */
    public function __construct($result)
    {
        $this->result = $result;

        $code = isset($result['code']) ? $result['code'] : 0;

        if (isset($result['error'])) {
            // OAuth 2.0 Draft 10 style
            $message = $result['error'];
        } elseif (isset($result['message'])) {
            // cURL style
            $message = $result['message'];
        } else {
            $message = 'Unknown Error.';
        }

        parent::__construct($message, $code);
    }

    /**
     * Returns the error response type
     *
     * See section 5.2 of RFC 6749 and the OAuth extensions error registry for
     * possible types and their meanings.
     *
     * @link http://tools.ietf.org/html/rfc6749#section-5.2 RFC 6749, Section 5.2
     * @link http://www.iana.org/assignments/oauth-parameters/oauth-parameters.xhtml#extensions-error OAuth Extensions Error Registry
     * @return string
     */
    public function getType()
    {
        if (isset($this->result['error'])) {
            $message = $this->result['error'];

            if (is_string($message)) {
                // OAuth 2.0 Draft 10 style
                return $message;
            }
        }

        return 'Exception';
    }

    /**
     * Converts this exception to a string representation
     *
     * @return string The string representation of the error.
     */
    public function __toString()
    {
        $str = $this->getType().': ';

        if ($this->code != 0) {
            $str .= $this->code.': ';
        }

        return $str.$this->message;
    }
}
