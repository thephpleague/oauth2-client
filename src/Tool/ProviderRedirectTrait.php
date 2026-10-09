<?php

namespace League\OAuth2\Client\Tool;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

trait ProviderRedirectTrait
{
    /**
     * Maximum number of times to follow provider initiated redirects
     *
     * @var integer
     */
    protected $redirectLimit = 2;

    /**
     * Retrieves a response for a given request and retrieves subsequent
     * responses, with authorization headers, if a redirect is detected.
     *
     * @param  RequestInterface $request
     * @return ResponseInterface
     * @throws BadResponseException
     */
    protected function followRequestRedirects(RequestInterface $request)
    {
        $response = null;
        $attempts = 0;

        while ($attempts < $this->redirectLimit) {
            $attempts++;
            $response = $this->sendWithoutFollowingRedirects($request);

            if ($this->isRedirect($response)) {
                $redirectUrl = $this->createRedirectUri($response->getHeader('Location')[0]);
                $request = $request->withUri($redirectUrl);
            } else {
                break;
            }
        }

        return $response;
    }

    /**
     * Sends a request without letting the HTTP client follow redirects.
     *
     * A PSR-18 client (see `setPsrHttpClient()`) must be configured not to
     * follow redirects itself, as PSR-18 has no per-request option for it.
     *
     * @param  RequestInterface $request
     * @return ResponseInterface
     */
    protected function sendWithoutFollowingRedirects(RequestInterface $request)
    {
        $psrClient = method_exists($this, 'getPsrHttpClient') ? $this->getPsrHttpClient() : null;

        if ($psrClient instanceof PsrClientInterface) {
            return $psrClient->sendRequest($request);
        }

        return $this->getHttpClient()->send($request, [
            'allow_redirects' => false
        ]);
    }

    /**
     * Builds the URI of a redirect target.
     *
     * @param  string $location
     * @return UriInterface
     */
    protected function createRedirectUri($location)
    {
        if (method_exists($this, 'getRequestFactory')) {
            return $this->getRequestFactory()->getUri($location);
        }

        return new Uri($location);
    }

    /**
     * Returns the HTTP client instance.
     *
     * @deprecated Will return a PSR-18 client in 3.0, see the migration guide.
     *
     * @return GuzzleHttp\ClientInterface
     */
    abstract public function getHttpClient();

    /**
     * Retrieves current redirect limit.
     *
     * @return integer
     */
    public function getRedirectLimit()
    {
        return $this->redirectLimit;
    }

    /**
     * Determines if a given response is a redirect.
     *
     * @param  ResponseInterface  $response
     *
     * @return boolean
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
     * @param  RequestInterface $request
     * @return ResponseInterface
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
     * @param integer $limit
     * @return League\OAuth2\Client\Provider\AbstractProvider
     * @throws InvalidArgumentException
     */
    public function setRedirectLimit($limit)
    {
        if (!is_int($limit)) {
            throw new InvalidArgumentException('redirectLimit must be an integer.');
        }

        if ($limit < 1) {
            throw new InvalidArgumentException('redirectLimit must be greater than or equal to one.');
        }

        $this->redirectLimit = $limit;

        return $this;
    }
}
