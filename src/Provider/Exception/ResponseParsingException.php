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

namespace League\OAuth2\Client\Provider\Exception;

use Exception;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

/**
 * Exception thrown if the parser cannot parse the provider response.
 */
class ResponseParsingException extends UnexpectedValueException
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var string
     */
    protected $responseBody;

    /**
     * @param ResponseInterface $response The response
     * @param string $responseBody The response body
     * @param null $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(
        ResponseInterface $response,
        $responseBody,
        $message = null,
        $code = 0,
        Exception $previous = null
    ) {
        $this->response = $response;
        $this->responseBody = $responseBody;

        if (null === $message) {
            $message = sprintf('Cannot parse response body: "%s"', $responseBody);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the exception's response.
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns the exception's response body.
     *
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }
}
