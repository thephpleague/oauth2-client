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

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

use function assert;

trait ProviderRedirectTrait
{
    /**
     * Maximum number of times to follow provider initiated redirects
     */
    protected int $redirectLimit = 2;

    /**
     * Retrieves a response for a given request and retrieves subsequent
     * responses, with authorization headers, if a redirect is detected.
     *
     * @return ResponseInterface | null
     *
     * @throws ClientExceptionInterface
     * @throws RuntimeException
     */
    protected function followRequestRedirects(RequestInterface $request)
    {
        $response = null;
        $attempts = 0;

        while ($attempts < $this->redirectLimit) {
            $attempts++;
            $response = $this->getHttpClient()->sendRequest($request);

            if ($this->isRedirect($response)) {
                assert(isset($response->getHeader('Location')[0]));
                $redirectUrl = new Uri($response->getHeader('Location')[0]);
                $request = $request->withUri($redirectUrl);
            } else {
                break;
            }
        }

        return $response;
    }

    /**
     * Returns the HTTP client instance.
     *
     * @return ClientInterface
     */
    abstract public function getHttpClient();

    /**
     * Retrieves current redirect limit.
     *
     * @return int
     */
    public function getRedirectLimit()
    {
        return $this->redirectLimit;
    }

    /**
     * Determines if a given response is a redirect.
     *
     * @return bool
     */
    protected function isRedirect(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();

        return $statusCode > 300 && $statusCode < 400 && $response->hasHeader('Location');
    }

    /**
     * Sends a request instance and returns a response instance.
     *
     * WARNING: This method does not attempt to catch exceptions caused by HTTP
     * errors! It is recommended to wrap this method in a try/catch block.
     *
     * @return ResponseInterface | null
     *
     * @throws ClientExceptionInterface
     */
    public function getResponse(RequestInterface $request)
    {
        try {
            $response = $this->followRequestRedirects($request);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }

        return $response;
    }

    /**
     * Updates the redirect limit.
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public function setRedirectLimit(int $limit)
    {
        if ($limit < 1) {
            throw new InvalidArgumentException('redirectLimit must be greater than or equal to one.');
        }

        $this->redirectLimit = $limit;

        return $this;
    }
}
