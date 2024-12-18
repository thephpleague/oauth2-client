<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
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

    public function getBaseAuthorizationUrl(): string
    {
        return 'http://example.com/oauth/authorize';
    }

    /**
     * @inheritDoc
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'http://example.com/oauth/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'http://example.com/oauth/user';
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultScopes(): array
    {
        return ['test'];
    }

    public function setAccessTokenMethod(string $method): void
    {
        $this->accessTokenMethod = $method;
    }

    public function getAccessTokenMethod(): string
    {
        return $this->accessTokenMethod;
    }

    public function setPkceMethod(string $method): void
    {
        $this->pkceMethod = $method;
    }

    public function getPkceMethod(): ?string
    {
        return $this->pkceMethod;
    }

    public function setFixedPkceCode(string $code): string
    {
        return $this->fixedPkceCode = $code;
    }

    protected function getRandomPkceCode(int $length = 64): string
    {
        return $this->fixedPkceCode ?: parent::getRandomPkceCode($length);
    }

    /**
     * @inheritDoc
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new Fake\User($response);
    }

    protected function checkResponse(ResponseInterface $response, array | string $data): void
    {
        if (isset($data['error'])) {
            assert(is_string($data['error']));
            assert(isset($data['code']) && is_int($data['code']));

            throw new IdentityProviderException($data['error'], $data['code'], $data);
        }
    }
}
