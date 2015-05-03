<?php

namespace League\OAuth2\Client\Test\Grant;

use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Grant\InvalidGrantException;
use League\OAuth2\Client\Test\Grant\Fake as MockGrant;
use Mockery as m;

class GrantFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractGrant
     */
    protected $factory;

    protected function setUp()
    {
        $this->factory = new GrantFactory();
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @dataProvider providerGetGrantDefaults
     */
    public function testGetGrantDefaults($name)
    {
        $grant = $this->factory->getGrant($name);
        $this->assertInstanceOf('League\OAuth2\Client\Grant\GrantInterface', $grant);
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
     * @expectedException League\OAuth2\Client\Grant\InvalidGrantException
     */
    public function testGetInvalidGrantFails()
    {
        $this->factory->getGrant('invalid');
    }

    public function testSetGrantReplaceDefault()
    {
        $class = 'League\OAuth2\Client\Test\Grant\Fake';

        $factory = new GrantFactory();
        $factory->setGrant('password', $class);

        $grant = $factory->getGrant('password');

        $this->assertInstanceOf($class, $grant);
    }

    public function testSetGrantCustom()
    {
        $class = 'League\OAuth2\Client\Test\Grant\Fake';

        $factory = new GrantFactory();
        $factory->setGrant('fake', $class);

        $grant = $factory->getGrant('fake');

        $this->assertInstanceOf($class, $grant);

    }

    /**
     * @expectedException League\OAuth2\Client\Grant\InvalidGrantException
     */
    public function testSetGrantInvalidFails()
    {
        $this->factory->setGrant('fail', 'stdClass');
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
        $this->assertSame($grant, $this->factory->checkGrant($grant));
    }

    /**
     * @expectedException League\OAuth2\Client\Grant\InvalidGrantException
     */
    public function testCheckGrantInvalidFails()
    {
        $this->factory->checkGrant('stdClass');
    }
}
