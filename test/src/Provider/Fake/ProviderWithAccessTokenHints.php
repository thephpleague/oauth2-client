<?php

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\MacAuthorizationTrait;

class ProviderWithAccessTokenHints extends GenericProvider
{
    use MacAuthorizationTrait;

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.example.com/owner/' . $token->getResourceOwnerId();
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $token->getResourceOwnerId());
    }

    public function getResourceOwner(AccessToken $token)
    {
        return $this->createResourceOwner([], $token);
    }

    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return [];
    }

    protected function getTokenId(AccessToken $token)
    {
        return 'fake_token_id';
    }

    protected function getMacSignature($id, $ts, $nonce)
    {
        return 'fake_mac_signature';
    }
}
