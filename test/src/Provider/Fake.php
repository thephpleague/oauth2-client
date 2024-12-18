<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

use function assert;
use function is_int;
use function is_string;

class Fake extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private string $accessTokenMethod = 'POST';
    private ?string $pkceMethod = null;
    private ?string $fixedPkceCode = null;

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * @inheritDoc
     */
    public function getBaseAuthorizationUrl()
    {
        return 'http://example.com/oauth/authorize';
    }

    /**
     * @inheritDoc
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'http://example.com/oauth/token';
    }

    /**
     * @inheritDoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'http://example.com/oauth/user';
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultScopes()
    {
        return ['test'];
    }

    public function setAccessTokenMethod(string $method): void
    {
        $this->accessTokenMethod = $method;
    }

    /**
     * @inheritDoc
     */
    public function getAccessTokenMethod()
    {
        return $this->accessTokenMethod;
    }

    public function setPkceMethod(string $method): void
    {
        $this->pkceMethod = $method;
    }

    /**
     * @inheritDoc
     */
    public function getPkceMethod()
    {
        return $this->pkceMethod;
    }

    public function setFixedPkceCode(string $code): string
    {
        return $this->fixedPkceCode = $code;
    }

    /**
     * @inheritDoc
     */
    protected function getRandomPkceCode($length = 64)
    {
        return $this->fixedPkceCode ?: parent::getRandomPkceCode($length);
    }

    /**
     * @param array{id?: mixed, email?: string, name?: string} $response
     *
     * @inheritDoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new Fake\User($response);
    }

    /**
     * @inheritDoc
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            assert(is_string($data['error']));
            assert(is_int($data['code']));

            throw new IdentityProviderException($data['error'], $data['code'], $data);
        }
    }
}
