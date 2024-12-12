<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

class Generic extends GenericProvider
{
    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        // Add the required defaults for AbstractProvider
        $options += [
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ];

        parent::__construct($options, $collaborators);
    }

    /**
     * @inheritDoc
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return [
            'mock_response_uid' => 1,
            'username' => 'testmock',
            'email' => 'mock@example.com',
        ];
    }
}
