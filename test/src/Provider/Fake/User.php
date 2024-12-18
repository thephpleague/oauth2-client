<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

use function is_string;

class User implements ResourceOwnerInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $response;

    /**
     * @param array<string, mixed> $response
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
        if (isset($this->response['email']) && is_string($this->response['email'])) {
            return $this->response['email'];
        }

        return null;
    }

    public function getUserScreenName(): ?string
    {
        if (isset($this->response['name']) && is_string($this->response['name'])) {
            return $this->response['name'];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
