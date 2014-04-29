<?php

namespace LeagueTest\OAuth2\Client\State;

use League\OAuth2\Client\State\SymfonyManager;
use Symfony\Component\HttpFoundation\Session\Session;

class SymfonyManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetterAndGetter()
    {
        $session = new Session();
        $manager = new SymfonyManager($session);

        $manager->state = 'mock_state';;
        $this->assertEquals('mock_state', $manager->state);
    }
}

