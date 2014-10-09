<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\YandexMoneyAccount;
use League\OAuth2\Client\Token\AccessToken;

class YandexMoney extends AbstractProvider {

	public $scopes		 = array('account-info');
	public $responseType = 'json';
	public $uidKey		 = 'account';
	
	public function urlAuthorize()
	{
		return 'https://sp-money.yandex.ru/oauth/authorize';
	}

	public function urlAccessToken()
	{
		return 'https://sp-money.yandex.ru/oauth/token';
	}

	public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
	{
		$this->headers = array(
		'Authorization' => 'Bearer ' . $token->accessToken
		);
		return 'https://money.yandex.ru/api/account-info';
	}

	public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
	{
		$account = new YandexMoneyAccount;
		
		$account->exchangeArray(array(
			'account' => $response->account,
			'balance' => $response->balance,
			'currency' => $response->currency,
			'account_status' => $response->account_status,
			'account_type' => $response->account_type,
			'avatar' => isset($response->avatar) ? $response->avatar : null,
			'balance_details' => isset($response->balance_details) ? $response->balance_details : null,
			'cards_linked' => isset($response->cards_linked) ?  $response->cards_linked: null,
			'services_additional' => isset($response->services_additional) ? $response->services_additional : null,
		));
		return $account;
	}

	public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
	{
		return $response->account;
	}
}
