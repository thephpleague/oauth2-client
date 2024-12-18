<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Grant;

use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Grant\Exception\InvalidGrantException;
use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Test\Grant\Fake as MockGrant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GrantFactoryTest extends TestCase
{
    #[DataProvider('providerGetGrantDefaults')]
    public function testGetGrantDefaults(string $name): void
    {
        $factory = new GrantFactory();
        $grant = $factory->getGrant($name);
        $this->assertInstanceOf(AbstractGrant::class, $grant);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function providerGetGrantDefaults(): array
    {
        return [
            'authorization_code' => ['authorization_code'],
            'client_credentials' => ['client_credentials'],
            'password' => ['password'],
            'refresh_token' => ['refresh_token'],
        ];
    }

    public function testGetInvalidGrantFails(): void
    {
        $this->expectException(InvalidGrantException::class);

        $factory = new GrantFactory();
        $factory->getGrant('invalid');
    }

    public function testSetGrantReplaceDefault(): void
    {
        $mock = new MockGrant();

        $factory = new GrantFactory();
        $factory->setGrant('password', $mock);

        $grant = $factory->getGrant('password');

        $this->assertSame($mock, $grant);
    }

    public function testSetGrantCustom(): void
    {
        $mock = new MockGrant();

        $factory = new GrantFactory();
        $factory->setGrant('fake', $mock);

        $grant = $factory->getGrant('fake');

        $this->assertSame($mock, $grant);
    }

    public function testIsGrant(): void
    {
        $factory = new GrantFactory();
        $grant = $factory->getGrant('password');

        $this->assertTrue($factory->isGrant($grant));

        /** @phpstan-ignore method.impossibleType */
        $this->assertFalse($factory->isGrant('stdClass'));
    }

    public function testCheckGrant(): void
    {
        $this->expectNotToPerformAssertions();

        $factory = new GrantFactory();
        $grant = $factory->getGrant('password');
        $factory->checkGrant($grant);
    }

    public function testCheckGrantInvalidFails(): void
    {
        $this->expectException(InvalidGrantException::class);

        $factory = new GrantFactory();

        /** @phpstan-ignore method.impossibleType */
        $factory->checkGrant('stdClass');
    }
}
