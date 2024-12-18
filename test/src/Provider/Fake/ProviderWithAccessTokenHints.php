<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Tool\MacAuthorizationTrait;

class ProviderWithAccessTokenHints extends GenericProvider
{
    use MacAuthorizationTrait;

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.example.com/owner/' . $token->getResourceOwnerId();
    }

    /**
     * @inheritDoc
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new GenericResourceOwner($response, (string) $token->getResourceOwnerId());
    }

    public function getResourceOwner(AccessToken $token): ResourceOwnerInterface
    {
        return $this->createResourceOwner([], $token);
    }

    /**
     * @inheritDoc
     */
    protected function fetchResourceOwnerDetails(AccessToken $token): array
    {
        return [];
    }

    protected function getTokenId(AccessTokenInterface | string | null $token): string
    {
        return 'fake_token_id';
    }

    protected function getMacSignature(string $id, int $ts, string $nonce): string
    {
        return 'fake_mac_signature';
    }
}
