<?php

namespace League\OAuth2\Client\Tool;

use GuzzleHttp\Psr7\Request;

/**
 * Used to produce PSR-7 Request instances.
 *
 * @see https://github.com/guzzle/guzzle/pull/1101
 */
class RequestFactory
{
    const DEFAULT_PROTOCOL_VERSION = '1.1';

    /**
     * Creates a PSR-7 Request instance.
     *
     * @param  null|string $method HTTP method for the request.
     * @param  null|string $uri URI for the request.
     * @param  array $headers Headers for the message.
     * @param  string|resource|StreamInterface $body Message body.
     * @param  string $version HTTP protocol version.
     *
     * @return Request
     */
    public function getRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = self::DEFAULT_PROTOCOL_VERSION
    ) {
        return new Request($method, $uri, $headers, $body, $version);
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
            'version' => self::DEFAULT_PROTOCOL_VERSION,
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
     * @return Request
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
