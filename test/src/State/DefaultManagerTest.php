<?php

namespace LeagueTest\OAuth2\Client\State;

use League\OAuth2\Client\State\DefaultManager;

class DefaultManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testNamespace()
    {
        $manager = new DefaultManager;

        $this->assertEquals('oauth2-client', $manager->getNamespace());
        
        $manager->setNamespace('oauth2');
        $this->assertEquals('oauth2', $manager->getNamespace());
    }
}
