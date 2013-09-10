<?php

namespace League\OAuth2\Client\Provider;

class Weibo extends IdentityProvider {

	//scope in weibo require admin approval
	//public $scopes = array('email');
	public $responseType = 'string';

	public function urlAuthorize()
	{
		return 'https://api.weibo.com/oauth2/authorize';
	}
	
	public function urlAccessToken()
	{
		return 'https://api.weibo.com/oauth2/access_token';
	}

	public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)	{
		return 'https://api.weibo.com/2/users/show.json?access_token='.$token->access_token.'&uid='.$token->uid;
	}

	public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
	{
		$user = new User;
		$user->uid= $response->id;
		$user->nickname = $response->screen_name;
		$user->name = isset($response->name) ? $response->name : null;
		$user->location = isset($response->location) ? $response->location : null;
		$user->image = isset($response->profile_image_url) ? $response->profile_image_url : null;
		$user->description = isset($response->description) ? $response->description : null;
		
		$user->urls = array(
			'Weibo' => $response->profile_url,
			'Blog' => $response->url,
		);
		
		return $user;
	}
}