<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\Clock;

/**
 * A clock with a frozen time for testing.
 */
class FrozenClock extends Clock
{

    /**
     * The simulated time.
     *
     * Evaluates to 1st January 2015 @ 12pm.
     *
     * @var int
     */
    const NOW = 1420113600;

    /**
     * @inheritdoc
     */
    public function now()
    {
        return (new \DateTimeImmutable('@' . static::NOW));
    }
}
