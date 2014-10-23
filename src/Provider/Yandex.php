<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Yandex extends AbstractProvider {

	public $scopes = array();
	public $responseType = 'json';

	public function urlAuthorize()
	{
		return 'https://oauth.yandex.ru/authorize';
	}

	public function urlAccessToken()
	{
		return 'https://oauth.yandex.ru/token';
	}

	public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
	{
		$this->headers = array(
			'Authorization' => 'Bearer ' . $token->accessToken
		);
		return 'https://login.yandex.ru/info';
	}

	public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
	{
		$user = new User;
		
		$user->exchangeArray(array(
			'uid' => $response->id,
			'name' => $response->real_name,
			'firstName' => $response->first_name,
			'lastName' => $response->last_name,
			'email' => $response->default_email,
			'imageUrl' => null,
			'gender' => $response->sex
		));
		return $user;
	}

	public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
	{
		return $response->id;
	}

	public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
	{
		return isset($response->default_email) && $response->default_email ? $response->default_email : null;
	}

	public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
	{
		return array($response->first_name, $response->last_name);
	}
}
