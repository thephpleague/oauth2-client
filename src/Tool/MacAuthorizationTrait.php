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

use League\OAuth2\Client\Token\AccessTokenInterface;

use function compact;
use function implode;
use function sprintf;
use function time;

/**
 * Enables `MAC` header authorization for providers.
 *
 * @link http://tools.ietf.org/html/draft-ietf-oauth-v2-http-mac-05 Message Authentication Code (MAC) Tokens
 */
trait MacAuthorizationTrait
{
    /**
     * Returns the id of this token for MAC generation.
     */
    abstract protected function getTokenId(AccessTokenInterface | string | null $token): string;

    /**
     * Returns the MAC signature for the current request.
     */
    abstract protected function getMacSignature(string $id, int $ts, string $nonce): string;

    /**
     * Returns a new random string to use as the state parameter in an
     * authorization flow.
     *
     * @param int<1, max> $length Length of the random string to be generated.
     */
    abstract protected function getRandomState(int $length = 32): string;

    /**
     * Returns the authorization headers for the 'mac' grant.
     *
     * @param AccessTokenInterface | string | null $token Either a string or an access token instance
     *
     * @return array<string, string>
     *
     * @codeCoverageIgnore
     *
     * phpcs:ignore Generic.Commenting.Todo.CommentFound
     * @todo This is currently untested and provided only as an example. If you
     *       complete the implementation, please create a pull request for
     *       https://github.com/thephpleague/oauth2-client
     */
    protected function getAuthorizationHeaders(AccessTokenInterface | string | null $token = null): array
    {
        if ($token === null) {
            return [];
        }

        $ts = time();
        $id = $this->getTokenId($token);
        $nonce = $this->getRandomState(16);
        $mac = $this->getMacSignature($id, $ts, $nonce);

        $parts = [];
        foreach (compact('id', 'ts', 'nonce', 'mac') as $key => $value) {
            $parts[] = sprintf('%s="%s"', $key, $value);
        }

        return ['Authorization' => 'MAC ' . implode(', ', $parts)];
    }
}
