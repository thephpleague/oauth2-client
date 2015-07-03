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

use League\OAuth2\Client\Token\AccessToken;

/**
 * Enables `MAC` header authorization for providers.
 *
 * http://tools.ietf.org/html/draft-ietf-oauth-v2-http-mac-05
 */
trait MacAuthorizationTrait
{
    /**
     * Get the id of this token for MAC generation.
     *
     * @param  AccessToken $token
     * @return string
     */
    abstract protected function getTokenId(AccessToken $token);

    /**
     * Get the MAC signature for the current request.
     *
     * @param  string $id
     * @param  integer $ts
     * @param  string $nonce
     * @return string
     */
    abstract protected function getMacSignature($id, $ts, $nonce);

    // AbstractProvider
    abstract protected function getRandomState($length);

    /**
     * Get authorization headers
     *
     * @param  AccessToken $token
     *
     * @return array
     * @codeCoverageIgnore
     *
     * @todo This is currently untested and provided only as an example. If you
     * complete the implementation, please create a pull request for
     * https://github.com/thephpleague/oauth2-client
     */
    protected function getAuthorizationHeaders($token = null)
    {
        $ts    = time();
        $id    = $this->getTokenId($token);
        $nonce = $this->getRandomState(16);
        $mac   = $this->getMacSignature($id, $ts, $nonce);

        $parts = [];
        foreach (compact('id', 'ts', 'nonce', 'mac') as $key => $value) {
            $parts[] = sprintf('%s="%s"', $key, $value);
        }

        return ['Authorization' => 'MAC ' . implode(', ', $parts)];
    }
}
