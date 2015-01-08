<?php

namespace League\OAuth2\Client\Test\Entity;

use League\OAuth2\Client\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    private $user;

    private $userArray;

    public function setUp()
    {
        $this->user = new User();

        $this->userArray = [
            'uid' => 'mock_uid',
            'nickname' => 'mock_nickname',
            'name' => 'mock_name',
            'firstName' => 'mock_firstName',
            'lastName' => 'mock_lastName',
            'email' => 'mock_email',
            'location' => 'mock_location',
            'description' => 'mock_description',
            'imageUrl' => 'mock_imageUrl',
            'urls' => 'mock_urls',
            'gender' => 'mock_gender',
            'locale' => 'mock_locale',
        ];
    }

    public function testExchangeArrayGetArrayCopy()
    {
        $this->user->exchangeArray($this->userArray);
        $this->assertEquals($this->userArray, $this->user->getArrayCopy());
    }

    public function testMagicMethos()
    {
        $this->user->exchangeArray($this->userArray);

        $this->user->name = 'mock_change_test';

        $this->assertTrue(isset($this->user->name));
        $this->assertEquals('mock_change_test', $this->user->name);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testInvalidMagicSet()
    {
        $this->user->invalidProp = 'mock';
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testInvalidMagicGet()
    {
        $this->user->invalidProp;
    }
}
