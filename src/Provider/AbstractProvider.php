<?php
/**
 * This file is part of the League\OAuth2\Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2014 Alex Bilbie <hello@alexbilbie.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace League\OAuth2\Client\Provider;

use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Service\Client as GuzzleClient;
use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Exception\IDPException;
use League\OAuth2\Client\Grant\GrantInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Base class representing OAuth 2.0 providers
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * The client identifier issued to the client by the provider
     *
     * @var string
     */
    public $clientId = '';

    /**
     * The client secret used to authenticate the client with the provider
     *
     * @var string
     */
    public $clientSecret = '';

    /**
     * URL to which the authorization server redirects the user-agent after
     * completing the authorization request
     *
     * @var string
     */
    public $redirectUri = '';

    /**
     * An opaque value used by the client to maintain state between the
     * request and callback
     *
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $name;

    /**
     * The name used by the provider for their user identifier
     *
     * This is usually "uid," but it may be known by other names, so we provide
     * the ability to set it on a per provider basis.
     *
     * @var string
     */
    public $uidKey = 'uid';

    /**
     * List of permission scopes we are requesting from the provider
     *
     * @var array
     */
    public $scopes = [];

    /**
     * HTTP request method to use when communicating with the provider
     *
     * @var string
     */
    public $method = 'post';

    /**
     * Separator string to use between scopes, when sending them to the provider
     *
     * @var string
     */
    public $scopeSeparator = ',';

    /**
     * The expected response type from the provider
     *
     * @var string
     */
    public $responseType = 'json';

    /**
     * Array of additional headers to add to the HTTP request
     *
     * This array must be in key/value format, where key is the header name
     * and value is the header value, for example:
     *
     * ```php
     * $provider->headers = [
     *     'X-Foo' => 'Bar',
     * ];
     * ```
     *
     * @var array|null
     */
    public $headers = null;

    /**
     * The HTTP client to use for requests to the provider
     *
     * @var GuzzleClient
     */
    protected $httpClient;

    /**
     * This represents: PHP_QUERY_RFC1738, which is the default value for php 5.4
     * and the default encryption type for the http_build_query setup
     *
     * @link http://php.net/http-build-query
     * @var int
     */
    protected $httpBuildEncType = 1;

    /**
     * Constructs a provider
     *
     * @param array $options Options for initializing this provider
     */
    public function __construct($options = [])
    {
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->{$option} = $value;
            }
        }

        $this->setHttpClient(new GuzzleClient());
    }

    /**
     * Sets the HTTP client to use for requests with this provider
     *
     * @param GuzzleClient $client
     * @return self
     */
    public function setHttpClient(GuzzleClient $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * Returns a copy of the HTTP client used by this provider
     *
     * @return GuzzleClient
     */
    public function getHttpClient()
    {
        $client = clone $this->httpClient;

        return $client;
    }

    /**
     * Get the URL that this provider uses to begin authorization.
     *
     * @return string
     */
    abstract public function urlAuthorize();

    /**
     * Get the URL that this provider users to request an access token.
     *
     * @return string
     */
    abstract public function urlAccessToken();

    /**
     * Get the URL that this provider uses to request user details.
     *
     * Since this URL is typically an authorized route, most providers will require you to pass the access_token as
     * a parameter to the request. For example, the google url is:
     *
     * 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token
     *
     * @param AccessToken $token
     * @return string
     */
    abstract public function urlUserDetails(AccessToken $token);

    /**
     * Given an object response from the server, process the user details into a format expected by the user
     * of the client.
     *
     * @param object $response
     * @param AccessToken $token
     * @return User
     */
    abstract public function userDetails($response, AccessToken $token);

    /**
     * Returns the array of permission scopes to be requested from this provider
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Sets the permission scopes to be requested from this provider
     *
     * @param array $scopes
     */
    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * Returns the fully constructed URL used to authorize a user with this provider
     *
     * @param array $options
     *
     * @return string
     */
    public function getAuthorizationUrl($options = [])
    {
        $this->state = isset($options['state']) ? $options['state'] : md5(uniqid(rand(), true));

        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => $this->state,
            'scope' => is_array($this->scopes) ? implode($this->scopeSeparator, $this->scopes) : $this->scopes,
            'response_type' => isset($options['response_type']) ? $options['response_type'] : 'code',
            'approval_prompt' => 'auto',
        ];

        return $this->urlAuthorize().'?'.$this->httpBuildQuery($params, '', '&');
    }

    /**
     * Sets a Location header and immediately exits to redirect the user-agent to the authorization URL
     *
     * @param array $options
     * @codeCoverageIgnore
     */
    public function authorize($options = [])
    {
        header('Location: '.$this->getAuthorizationUrl($options));
        exit;
    }

    /**
     * Retrieves an access token from the provider
     *
     * @param string $grant the grant type
     * @param array $params
     * @return AccessToken
     * @throws InvalidArgumentException for an unknown grant type
     * @throws IDPException if the provider returns an error
     * @link http://tools.ietf.org/html/rfc6749#section-4.1.3
     */
    public function getAccessToken($grant = 'authorization_code', $params = [])
    {
        if (is_string($grant)) {
            // PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
            $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $grant)));
            $grant = 'League\\OAuth2\\Client\\Grant\\'.$className;
            if (! class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }
            $grant = new $grant();
        } elseif (! $grant instanceof GrantInterface) {
            $message = get_class($grant).' is not an instance of League\OAuth2\Client\Grant\GrantInterface';
            throw new \InvalidArgumentException($message);
        }

        $defaultParams = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => $grant,
        ];

        $requestParams = $grant->prepRequestParams($defaultParams, $params);

        try {
            switch (strtoupper($this->method)) {
                case 'GET':
                    // @codeCoverageIgnoreStart
                    // No providers included with this library use get but 3rd parties may
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->urlAccessToken().'?'.$this->httpBuildQuery($requestParams, '', '&'));
                    $request = $client->send();
                    $response = $request->getBody();
                    break;
                    // @codeCoverageIgnoreEnd
                case 'POST':
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->urlAccessToken());
                    $request = $client->post(null, null, $requestParams)->send();
                    $response = $request->getBody();
                    break;
                // @codeCoverageIgnoreStart
                default:
                    throw new \InvalidArgumentException('Neither GET nor POST is specified for request');
                // @codeCoverageIgnoreEnd
            }
        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $raw_response = explode("\n", $e->getResponse());
            $response = end($raw_response);
            // @codeCoverageIgnoreEnd
        }

        switch ($this->responseType) {
            case 'json':
                $result = json_decode($response, true);
                break;
            case 'string':
                parse_str($response, $result);
                break;
        }

        if (isset($result['error']) && ! empty($result['error'])) {
            // @codeCoverageIgnoreStart
            throw new IDPException($result);
            // @codeCoverageIgnoreEnd
        }

        $this->setResultUid($result);

        return $grant->handleResponse($result);
    }

    /**
     * Sets any result keys we've received matching our provider-defined uidKey to the key "uid".
     *
     * @param array $result
     */
    protected function setResultUid(array &$result)
    {
        // If we're operating with the default uidKey there's nothing to do.
        if ($this->uidKey === "uid") {
            return;
        }

        if (isset($result[$this->uidKey])) {
            // The AccessToken expects a "uid" to have the key "uid".
            $result['uid'] = $result[$this->uidKey];
        }
    }

    /**
     * Returns a representation of the authorized user profile for this provider
     *
     * @param AccessToken $token
     * @return User
     * @throws IDPException
     */
    public function getUserDetails(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token);

        return $this->userDetails(json_decode($response), $token);
    }

    /**
     * Returns the user identifier for the authorized user profile for this provider
     *
     * @param AccessToken $token
     * @return string
     * @throws IDPException
     */
    public function getUserUid(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userUid(json_decode($response), $token);
    }

    /**
     * Returns the email address for the authorized user profile for this provider
     *
     * @param AccessToken $token
     * @return string
     * @throws IDPException
     */
    public function getUserEmail(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userEmail(json_decode($response), $token);
    }

    /**
     * Returns the screen namem for the authorized user profile for this provider
     *
     * @param AccessToken $token
     * @return array
     * @throws IDPException
     */
    public function getUserScreenName(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userScreenName(json_decode($response), $token);
    }

    /**
     * Build URL query string, handling PHP version control options
     *
     * @param array $params
     * @param integer $numeric_prefix
     * @param string $arg_separator
     * @param null|integer $enc_type
     * @return string
     * @codeCoverageIgnore
     */
    protected function httpBuildQuery($params, $numeric_prefix = 0, $arg_separator = '&', $enc_type = null)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !defined('HHVM_VERSION')) {
            if ($enc_type === null) {
                $enc_type = $this->httpBuildEncType;
            }
            $url = http_build_query($params, $numeric_prefix, $arg_separator, $enc_type);
        } else {
            $url = http_build_query($params, $numeric_prefix, $arg_separator);
        }

        return $url;
    }

    /**
     * Makes an HTTP request to get information about the authorized user profile for this provider
     *
     * @param AccessToken $token
     * @return string
     * @throws IDPException if the provider returns a bad response
     */
    protected function fetchUserDetails(AccessToken $token)
    {
        $url = $this->urlUserDetails($token);

        try {
            $client = $this->getHttpClient();
            $client->setBaseUrl($url);

            if ($this->headers) {
                $client->setDefaultOption('headers', $this->headers);
            }

            $request = $client->get()->send();
            $response = $request->getBody();
        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $raw_response = explode("\n", $e->getResponse());
            throw new IDPException(end($raw_response));
            // @codeCoverageIgnoreEnd
        }

        return $response;
    }
}
