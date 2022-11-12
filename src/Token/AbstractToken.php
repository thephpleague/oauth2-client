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

abstract class AbstractToken {

  /**
   * @var int
   */
  protected $expires;

  /**
   * @var array
   */
  protected $values = [];

  /**
   * @var int
   */
  private static $timeNow;

  /**
   * Constructs an access token.
   *
   * @param array $options An array of options returned by the service provider
   *     in the access token request. The `access_token` option is required.
   * @throws InvalidArgumentException if `access_token` is not provided in `$options`.
   */
  public function __construct(array $options = []) {
    // We need to know when the token expires. Show preference to
    // 'expires_in' since it is defined in RFC6749 Section 5.1.
    // Defer to 'expires' if it is provided instead.
    if (isset($options['expires_in'])) {
      if (!is_numeric($options['expires_in'])) {
        throw new \InvalidArgumentException('expires_in value must be an integer');
      }

      $this->expires = $options['expires_in'] != 0 ? $this->getTimeNow() + $options['expires_in'] : 0;
    } elseif (!empty($options['expires'])) {
      // Some providers supply the seconds until expiration rather than
      // the exact timestamp. Take a best guess at which we received.
      $expires = $options['expires'];

      if (!$this->isExpirationTimestamp($expires)) {
        $expires += $this->getTimeNow();
      }

      $this->expires = $expires;
    }
  }

  /**
   * Set the time now. This should only be used for testing purposes.
   *
   * @param int $timeNow the time in seconds since epoch
   * @return void
   */
  public static function setTimeNow($timeNow)
  {
    self::$timeNow = $timeNow;
  }

  /**
   * Reset the time now if it was set for test purposes.
   *
   * @return void
   */
  public static function resetTimeNow()
  {
    self::$timeNow = null;
  }

  /**
   * @return int
   */
  public function getTimeNow()
  {
    return self::$timeNow ? self::$timeNow : time();
  }

}
