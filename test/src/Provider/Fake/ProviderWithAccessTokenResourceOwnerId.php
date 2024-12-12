<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Test\Provider\Fake as MockProvider;

class ProviderWithAccessTokenResourceOwnerId extends MockProvider
{
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'user_id';
}
