<?php

namespace League\OAuth2\Client\Entity;

class YandexMoneyAccount
{
    protected $uid;
    protected $account;
    protected $balance;
    protected $currency;
    protected $account_status;
    protected $account_type;
    protected $avatar;
    protected $balance_details;
    protected $cards_linked;
    protected $services_additional;

    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }

        return $this->{$name};
    }

    public function __set($property, $value)
    {
        if (!property_exists($this, $property)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $property
            ));
        }

        $this->$property = $value;

        return $this;
    }

    public function __isset($name)
    {
        return (property_exists($this, $name));
    }

    public function getArrayCopy()
    {
        return array(
            'uid'					 => $this->uid,
            'account'				 => $this->account,
            'balance'				 => $this->balance,
            'currency'				 => $this->currency,
            'account_status'		 => $this->account_status,
            'account_type'			 => $this->account_type,
            'avatar'				 => $this->avatar,
            'balance_details'		 => $this->balance_details,
            'cards_linked'			 => $this->cards_linked,
            'services_additional'	 => $this->services_additional,
        );
    }

    public function exchangeArray(array $data)
    {
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            switch ($key) {
                case 'account':
                    $this->uid = $value;
                    $this->account = $value;
                    break;
                case 'balance':
                    $this->balance = $value;
                    break;
                case 'currency':
                    $this->currency = $value;
                    break;
                case 'account_status':
                    $this->account_status = $value;
                    break;
                case 'account_type':
                    $this->account_type = $value;
                    break;
                case 'avatar':
                    $this->avatar = $value;
                    break;
                case 'balance_details':
                    $this->balance_details = $value;
                    break;
                case 'cards_linked':
                    $this->cards_linked = $value;
                    break;
                case 'services_additional':
                    $this->services_additional = $value;
                    break;
            }
        }

        return $this;
    }
}
