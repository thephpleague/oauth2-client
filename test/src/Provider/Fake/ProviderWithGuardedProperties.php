<?php

namespace League\OAuth2\Client\Test\Provider\Fake;

use League\OAuth2\Client\Test\Provider\Fake as MockProvider;

class ProviderWithGuardedProperties extends MockProvider
{
    /**
     * The properties that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['skipMeDuringMassAssignment'];

    /**
     * Throwaway property that shouldn't be mass assigned.
     *
     * @var string
     */
    protected $skipMeDuringMassAssignment = 'foo';

    public function getGuarded()
    {
        return $this->guarded;
    }

    public function getSkipMeDuringMassAssignment()
    {
        return $this->skipMeDuringMassAssignment;
    }
}
