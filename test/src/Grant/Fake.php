<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Grant;

use League\OAuth2\Client\Grant\AbstractGrant;

class Fake extends AbstractGrant
{
    protected function getName(): string
    {
        return 'fake';
    }

    /**
     * @return list<string>
     */
    protected function getRequiredRequestParameters(): array
    {
        return [];
    }
}
