<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;

class Vkontakte extends IdentityProvider
{
    public $scopes = array();
    public $responseType = 'json';

    public function urlAuthorize()
    {
        return 'https://oauth.vk.com/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://oauth.vk.com/access_token';
    }

    public function urlUserDetails(AccessToken $token)
    {
        $fields = array('nickname',
            'screen_name',
            'sex',
            'bdate',
            'city',
            'country',
            'timezone',
            'photo_50',
            'photo_100',
            'photo_200_orig',
            'has_mobile',
            'contacts',
            'education',
            'online',
            'counters',
            'relation',
            'last_seen',
            'status',
            'can_write_private_message',
            'can_see_all_posts',
            'can_see_audio',
            'can_post',
            'universities',
            'schools',
            'verified');

        return "https://api.vk.com/method/users.get?user_id={$token->uid}&fields=".implode(",", $fields)."&access_token=".$token;
    }

    public function userDetails($response, AccessToken $token)
    {
        $response = $response->response[0];

        $user = new User;
        $user->uid = $response->uid;
        $user->nickname = $response->nickname;
        $user->name = $response->screen_name;
        $user->firstName = $response->first_name;
        $user->lastName = $response->last_name;
        $user->email = isset($response->email) ? $response->email : null;
        $user->location = isset($response->country) ? $response->country : null;
        $user->description = isset($response->status) ? $response->status : null;
        $user->imageUrl = $response->photo_200_orig;

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        $response = $response->response[0];

        return $response->uid;
    }

    public function userEmail($response, AccessToken $token)
    {
        $response = $response->response[0];

        return isset($response->email) && $response->email ? $response->email : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        $response = $response->response[0];

        return array($response->first_name, $response->last_name);
    }
}
