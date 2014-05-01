<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class Vkontakte extends AbstractProvider
{
    public $scopes = array();
    public $responseType = 'json';
    protected $requireState = true;

    public function urlAuthorize()
    {
        return 'https://oauth.vk.com/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://oauth.vk.com/access_token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
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

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = $response->response[0];

        $user = new User;

        $email = (isset($response->email)) ? $response->email : null;
        $location = (isset($response->country)) ? $response->country : null;
        $description = (isset($response->status)) ? $response->status : null;

        $user->exchangeArray(array(
            'uid' => $response->user_id,
            'nickname' => $response->nickname,
            'name' => $response->screen_name,
            'firstname' => $response->first_name,
            'lastname' => $response->last_name,
            'email' => $email,
            'location' => $location,
            'description' => $description,
            'imageUrl' => $response->photo_200_orig,
        ));

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $this->userDetails($response, $token)->uid;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $this->userDetails($response, $token)->email;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = $this->userDetails($response, $token);

        return array($user->firstName, $user->lastName);
    }
}
