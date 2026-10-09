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

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Used to produce PSR-7 Request instances.
 *
 * When PSR-17 factories are provided they are used to build messages;
 * otherwise this falls back to the guzzlehttp/psr7 implementation.
 *
 * @link https://github.com/guzzle/guzzle/pull/1101
 */
class RequestFactory
{
    /**
     * @var RequestFactoryInterface|null
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface|null
     */
    private $streamFactory;

    /**
     * @var UriFactoryInterface|null
     */
    private $uriFactory;

    /**
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory
     * @param StreamFactoryInterface|null  $streamFactory  PSR-17 stream factory, required to send a body
     * @param UriFactoryInterface|null     $uriFactory     PSR-17 URI factory
     */
    public function __construct(
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UriFactoryInterface $uriFactory = null
    ) {
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->uriFactory = $uriFactory;
    }

    /**
     * Creates a PSR-7 Uri instance.
     *
     * @param  string $uri
     *
     * @return UriInterface
     */
    public function getUri($uri)
    {
        if ($this->uriFactory) {
            return $this->uriFactory->createUri($uri);
        }

        return new Uri($uri);
    }

    /**
     * Creates a PSR-7 Request instance.
     *
     * @param  null|string $method HTTP method for the request.
     * @param  null|string $uri URI for the request.
     * @param  array $headers Headers for the message.
     * @param  string|resource|StreamInterface $body Message body.
     * @param  string $version HTTP protocol version.
     *
     * @return RequestInterface
     */
    public function getRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1'
    ) {
        if ($this->requestFactory) {
            return $this->getPsrRequest($method, $uri, $headers, $body, $version);
        }

        return new Request($method, $uri, $headers, $body, $version);
    }

    /**
     * Creates a request using the PSR-17 factories.
     *
     * @return RequestInterface
     */
    private function getPsrRequest($method, $uri, array $headers, $body, $version)
    {
        $request = $this->requestFactory->createRequest($method, $uri)
            ->withProtocolVersion($version);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null && $body !== '') {
            if (!$this->streamFactory) {
                throw new \LogicException(
                    'A PSR-17 StreamFactoryInterface is required to send a request body.'
                );
            }

            $request = $request->withBody(
                is_resource($body)
                    ? $this->streamFactory->createStreamFromResource($body)
                    : (is_string($body) ? $this->streamFactory->createStream($body) : $body)
            );
        }

        return $request;
    }

    /**
     * Parses simplified options.
     *
     * @param array $options Simplified options.
     *
     * @return array Extended options for use with getRequest.
     */
    protected function parseOptions(array $options)
    {
        // Should match default values for getRequest
        $defaults = [
            'headers' => [],
            'body'    => null,
            'version' => '1.1',
        ];

        return array_merge($defaults, $options);
    }

    /**
     * Creates a request using a simplified array of options.
     *
     * @param  null|string $method
     * @param  null|string $uri
     * @param  array $options
     *
     * @return RequestInterface
     */
    public function getRequestWithOptions($method, $uri, array $options = [])
    {
        $options = $this->parseOptions($options);

        return $this->getRequest(
            $method,
            $uri,
            $options['headers'],
            $options['body'],
            $options['version']
        );
    }
}
