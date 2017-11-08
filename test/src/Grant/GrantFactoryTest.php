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
     * @var AbstractGrant
     */
    protected $factory;

    protected function setUp()
    {
        $this->factory = new GrantFactory();
    }

    /**
     * @dataProvider providerGetGrantDefaults
     */
    public function testGetGrantDefaults($name)
    {
        $grant = $this->factory->getGrant($name);
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

    /**
     * @expectedException League\OAuth2\Client\Grant\Exception\InvalidGrantException
     */
    public function testGetInvalidGrantFails()
    {
        $this->factory->getGrant('invalid');
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
        $grant = $this->factory->getGrant('password');

        $this->assertTrue($this->factory->isGrant($grant));
        $this->assertFalse($this->factory->isGrant('stdClass'));
    }

    public function testCheckGrant()
    {
        $grant = $this->factory->getGrant('password');
        $this->assertNull($this->factory->checkGrant($grant));
    }

    /**
     * @expectedException League\OAuth2\Client\Grant\Exception\InvalidGrantException
     */
    public function testCheckGrantInvalidFails()
    {
        $this->factory->checkGrant('stdClass');
    }
}
