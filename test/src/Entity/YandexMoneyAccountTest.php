<?php

namespace League\OAuth2\Client\Test\Token;

use League\OAuth2\Client\Entity\YandexMoneyAccount;

class YandexMoneyAccountTest extends \PHPUnit_Framework_TestCase
{
    private $account;

    private $accountArray;

    public function setUp()
    {
        $this->account = new YandexMoneyAccount;

        $this->accountArray = array(
            'uid' => 'mock_account',
            'account' => 'mock_account',
            'balance' => 'mock_balance',
            'currency' => 'mock_currency',
            'account_status' => 'mock_account_status',
            'account_type' => 'mock_account_type',
            'avatar' => 'mock_avatar',
            'balance_details' => 'mock_balance_details',
            'cards_linked' => 'mock_cards_linked',
            'services_additional' => 'mock_services_additional',
        );
    }

    public function testExchangeArrayGetArrayCopy()
    {
        $this->account->exchangeArray($this->accountArray);
        $this->assertEquals($this->accountArray, $this->account->getArrayCopy());
    }

    public function testMagicMethods()
    {
        $this->account->exchangeArray($this->accountArray);

        $this->account->account_status = 'mock_change_account_status';

        $this->assertTrue(isset($this->account->account_status));
        $this->assertEquals('mock_change_account_status', $this->account->account_status);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testInvalidMagicSet()
    {
        $this->account->invalidProp = 'mock';
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testInvalidMagicGet()
    {
        $this->account->invalidProp;
    }
}
