<?php

namespace League\OAuth2\Client\Tool;

use GuzzleHttp\Psr7\Request;

// For history see https://github.com/guzzle/guzzle/pull/1101
class RequestFactory
{
    /**
     * @param  null|string $method HTTP method for the request.
     * @param  null|string $uri URI for the request.
     * @param  array  $headers Headers for the message.
     * @param  string|resource|StreamInterface $body Message body.
     * @param  string $protocolVersion HTTP protocol version.
     * @return Request
     */
    public function getRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ) {
        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    /**
     * Get a request using a simplified array of options.
     *
     * @param  null|string $method
     * @param  null|string $uri
     * @param  array $options
     * @return Request
     */
    public function getRequestWithOptions($method, $uri, array $options = [])
    {
        $func    = new \ReflectionMethod($this, 'getRequest');
        $params  = $func->getParameters();
        $options = array_replace($options, compact('method', 'uri'));
        $args    = [];

        foreach ($params as $param) {
            $name = $param->getName();
            if (isset($options[$name])) {
                $value = $options[$name];
            } else {
                $value = $param->getDefaultValue();
            }
            $args[$name] = $value;
        }

        return $func->invokeArgs($this, array_values($args));
    }
}
