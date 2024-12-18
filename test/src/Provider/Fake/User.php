<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class User implements ResourceOwnerInterface
{
    /**
     * @var array{id?: mixed, email?: string, name?: string}
     */
    protected array $response;

    /**
     * @param array{id?: mixed, email?: string, name?: string} $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->response['id'] ?? null;
    }

    public function getUserEmail(): ?string
    {
        return $this->response['email'] ?? null;
    }

    public function getUserScreenName(): ?string
    {
        return $this->response['name'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
