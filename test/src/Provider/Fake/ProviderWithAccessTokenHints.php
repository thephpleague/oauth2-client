<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
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
        return new GenericResourceOwner($response, $token->getResourceOwnerId());
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

    /**
     * @inheritDoc
     */
    protected function getTokenId(AccessToken $token)
    {
        return 'fake_token_id';
    }

    /**
     * @inheritDoc
     */
    protected function getMacSignature($id, $ts, $nonce)
    {
        return 'fake_mac_signature';
    }
}
