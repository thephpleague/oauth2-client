<?php

namespace League\OAuth2\Client\Provider;

class Weibo extends IdentityProvider {

	//scope in weibo require manual approval
	//public $scopes = array('email');

	public $responseType = 'json';

	public function urlAuthorize()
	{
		return 'https://api.weibo.com/oauth2/authorize';
	}
	
	public function urlAccessToken()
	{
		return 'https://api.weibo.com/oauth2/access_token';
	}

	public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)	{
		//either uid or screen_name is required (even when access token is present)
		return 'https://api.weibo.com/2/users/show.json?access_token='.$token->accessToken.'&uid='.$token->uid;
	}

	public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
	{
		$user = new User;
		$user->uid = $response->id;
		//username can be: 1) domain (= profile_url), 2) idstr (= id, but return as string)
		$user->nickname = isset($response->domain) ? $response->domain : $response->idstr;
		//screen_name is display name (= name, there are no username/nickname field)
		$user->name = isset($response->screen_name) ? $response->screen_name : null; 
		$user->location = isset($response->location) ? $response->location : null;
		//profile_image_url is 50x50, avatar_large is 180x180 (unit:px)
		$user->imageUrl = isset($response->avatar_large) ? $response->avatar_large : $response->profile_image_url;
		$user->description = isset($response->description) ? $response->description : null;
		
		$user->urls = array(
			//weibo url defaults to user id, but redirect to custom uri when set
			'Weibo' => isset($response->profile_url) ? 'http://weibo.com/'.$response->profile_url : 'http://weibo.com/'.$response->idstr,
			//custom input, labelled as blog address
			'Blog' => isset($response->url) ? $response->url : null,
		);
		
		return $user;
	}
}