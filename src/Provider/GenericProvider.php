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

namespace League\OAuth2\Client\Provider;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

use function array_diff_key;
use function array_flip;
use function array_intersect_key;
use function array_keys;
use function array_merge;
use function implode;
use function intval;
use function is_int;
use function is_string;
use function var_export;

/**
 * Represents a generic service provider that may be used to interact with any
 * OAuth 2.0 service provider, using Bearer token authentication.
 */
class GenericProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private string $urlAuthorize;
    private string $urlAccessToken;
    private string $urlResourceOwnerDetails;
    private string $accessTokenMethod;
    private string $accessTokenResourceOwnerId;

    /**
     * @var list<string> | null
     */
    private ?array $scopes = null;

    private string $scopeSeparator;
    private string $responseError = 'error';
    private string $responseCode;
    private string $responseResourceOwnerId = 'id';
    private ?string $pkceMethod = null;

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $this->assertRequiredOptions($options);

        $possible = $this->getConfigurableOptions();
        $configured = array_intersect_key($options, array_flip($possible));

        foreach ($configured as $key => $value) {
            $this->$key = $value;
        }

        // Remove all options that are only used locally
        $options = array_diff_key($options, $configured);

        parent::__construct($options, $collaborators);
    }

    /**
     * Returns all options that can be configured.
     *
     * @return list<string>
     */
    protected function getConfigurableOptions()
    {
        return array_merge($this->getRequiredOptions(), [
            'accessTokenMethod',
            'accessTokenResourceOwnerId',
            'scopeSeparator',
            'responseError',
            'responseCode',
            'responseResourceOwnerId',
            'scopes',
            'pkceMethod',
        ]);
    }

    /**
     * Returns all options that are required.
     *
     * @return list<string>
     */
    protected function getRequiredOptions()
    {
        return [
            'urlAuthorize',
            'urlAccessToken',
            'urlResourceOwnerDetails',
        ];
    }

    /**
     * Verifies that all required options have been passed.
     *
     * @param array<string, mixed> $options
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);

        if ($missing !== []) {
            throw new InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing)),
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->urlAuthorize;
    }

    /**
     * @inheritdoc
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->urlAccessToken;
    }

    /**
     * @inheritdoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->urlResourceOwnerDetails;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScopes()
    {
        return $this->scopes;
    }

    /**
     * @inheritdoc
     */
    protected function getAccessTokenMethod()
    {
        return $this->accessTokenMethod ?: parent::getAccessTokenMethod();
    }

    /**
     * @inheritdoc
     */
    protected function getAccessTokenResourceOwnerId()
    {
        return $this->accessTokenResourceOwnerId ?: parent::getAccessTokenResourceOwnerId();
    }

    /**
     * @inheritdoc
     */
    protected function getScopeSeparator()
    {
        return $this->scopeSeparator ?: parent::getScopeSeparator();
    }

    /**
     * @inheritdoc
     */
    protected function getPkceMethod()
    {
        return $this->pkceMethod ?: parent::getPkceMethod();
    }

    /**
     * @inheritdoc
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data[$this->responseError])) {
            $error = $data[$this->responseError];
            if (!is_string($error)) {
                $error = var_export($error, true);
            }
            $code = isset($this->responseCode) && isset($data[$this->responseCode]) ? $data[$this->responseCode] : 0;
            if (!is_int($code)) {
                $code = intval($code);
            }

            throw new IdentityProviderException($error, $code, $data);
        }
    }

    /**
     * @inheritdoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $this->responseResourceOwnerId);
    }
}
