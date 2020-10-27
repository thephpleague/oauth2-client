<?php

namespace League\OAuth2\Client\Test\Grant;

use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Grant\Exception\InvalidGrantException;
use League\OAuth2\Client\Test\Grant\Fake as MockGrant;
use PHPUnit\Framework\TestCase;

class GrantFactoryTest extends TestCase
{
    /**
     * @dataProvider providerGetGrantDefaults
     */
    public function testGetGrantDefaults($name)
    {
        $factory = new GrantFactory();
        $grant = $factory->getGrant($name);
        $this->assertInstanceOf(AbstractGrant::class, $grant);
    }

    public function providerGetGrantDefaults()
    {
        return [
            'authorization_code' => ['authorization_code'],
            'client_credentials' => ['client_credentials'],
            'password'           => ['password'],
            'refresh_token'      => ['refresh_token'],
        ];
    }

    public function testGetInvalidGrantFails()
    {
        $this->expectException(InvalidGrantException::class);

        $factory = new GrantFactory();
        $factory->getGrant('invalid');
    }

    public function testSetGrantReplaceDefault()
    {
        $mock = new MockGrant();

        $factory = new GrantFactory();
        $factory->setGrant('password', $mock);

        $grant = $factory->getGrant('password');

        $this->assertSame($mock, $grant);
    }

    public function testSetGrantCustom()
    {
        $mock = new MockGrant();

        $factory = new GrantFactory();
        $factory->setGrant('fake', $mock);

        $grant = $factory->getGrant('fake');

        $this->assertSame($mock, $grant);
    }

    public function testIsGrant()
    {
        $factory = new GrantFactory();
        $grant = $factory->getGrant('password');

        $this->assertTrue($factory->isGrant($grant));
        $this->assertFalse($factory->isGrant('stdClass'));
    }

    public function testCheckGrant()
    {
        $factory = new GrantFactory();
        $grant = $factory->getGrant('password');
        $this->assertNull($factory->checkGrant($grant));
    }

    public function testCheckGrantInvalidFails()
    {
        $this->expectException(InvalidGrantException::class);

        $factory = new GrantFactory();
        $factory->checkGrant('stdClass');
    }
}
