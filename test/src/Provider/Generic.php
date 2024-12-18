<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\OptionProvider\OptionProviderInterface;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Generic extends GenericProvider
{
    /**
     * @param array<string, mixed> $options
     * @param array{
     *     grantFactory?: GrantFactory,
     *     requestFactory?: RequestFactoryInterface,
     *     streamFactory?: StreamFactoryInterface,
     *     httpClient?: ClientInterface,
     *     optionProvider?: OptionProviderInterface,
     * } $collaborators
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
    protected function fetchResourceOwnerDetails(AccessToken $token): array
    {
        return [
            'mock_response_uid' => 1,
            'username' => 'testmock',
            'email' => 'mock@example.com',
        ];
    }
}
