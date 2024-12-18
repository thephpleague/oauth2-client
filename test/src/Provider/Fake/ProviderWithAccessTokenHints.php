<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Tool\MacAuthorizationTrait;

class ProviderWithAccessTokenHints extends GenericProvider
{
    use MacAuthorizationTrait;

    /**
     * @inheritDoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.example.com/owner/' . $token->getResourceOwnerId();
    }

    /**
     * @inheritDoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, (string) $token->getResourceOwnerId());
    }

    /**
     * @inheritDoc
     */
    public function getResourceOwner(AccessToken $token)
    {
        return $this->createResourceOwner([], $token);
    }

    /**
     * @inheritDoc
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
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
