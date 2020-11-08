<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\Clock;

/**
 * A clock which must be initialised, and may be changed at any time.
 */
class ProgrammableClock extends Clock
{

    /**
     * @var \DateTimeImmutable|null
     */
    protected $time = null;

    /**
     * @inheritdoc
     */
    public function now()
    {
        if (!isset($this->time)) {
            throw new \LogicException('Time must be set explicitly');
        }
        return $this->time;
    }

    /**
     * Sets the current time.
     *
     * @param \DateTimeImmutable|null the current time.
     * @return self
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }
}
