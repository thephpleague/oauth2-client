<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class User implements ResourceOwnerInterface
{
    /**
     * @var array<array-key, mixed>
     */
    protected array $response;

    /**
     * @param array<array-key, mixed> $response
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
        return $this->response['id'];
    }

    public function getUserEmail(): ?string
    {
        return $this->response['email'];
    }

    public function getUserScreenName(): ?string
    {
        return $this->response['name'];
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->response;
    }
}
