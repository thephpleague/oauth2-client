<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Test\Provider\Fake as MockProvider;

class ProviderWithGuardedProperties extends MockProvider
{
    /**
     * The properties that aren't mass assignable.
     *
     * @var list<string>
     */
    protected array $guarded = ['skipMeDuringMassAssignment'];

    /**
     * Throwaway property that shouldn't be mass assigned.
     */
    protected string $skipMeDuringMassAssignment = 'foo';

    /**
     * @inheritDoc
     */
    public function getGuarded(): array
    {
        return $this->guarded;
    }

    public function getSkipMeDuringMassAssignment(): string
    {
        return $this->skipMeDuringMassAssignment;
    }
}
