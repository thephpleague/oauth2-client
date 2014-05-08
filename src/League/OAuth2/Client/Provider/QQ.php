<?php

namespace League\OAuth2\Client\Provider;

class QQ extends IdentityProvider
{
	public $scope = array('get_user_info');
	public $responseType = 'json';

	public $name = "qq";

	public function urlAuthorize()
	{
		return 'https://graph.qq.com/oauth2.0/authorize';
	}

	public function urlAccessToken()
	{
		return 'https://graph.qq.com/oauth2.0/token';
	}

	public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
	{
		return 'https://graph.qq.com/oauth2.0/me?access_token=' . $token;
	}

	public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
	{
		$user = new User;

		$user->uid = $response->openid;

		return $user;
	}

	public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
	{
		return $response->openid;
	}
}
