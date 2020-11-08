<?php

namespace League\OAuth2\Client\Provider;

/**
 * Represents an implementation of a Clock.
 */
class Clock
{

  /**
   * Get the current time.
   *
   * @return \DateTimeImmutable
   */
    public function now()
    {
        return new \DateTimeImmutable();
    }
}
