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

namespace League\OAuth2\Client\Tool;

/**
 *
 */
trait UrlEncodedRequestTrait
{
    protected function getAccessTokenRequestHeaders(array $params)
    {
        return array_merge(
            parent::getAccessTokenRequestHeaders($params),
            [
              'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        );
    }
}
