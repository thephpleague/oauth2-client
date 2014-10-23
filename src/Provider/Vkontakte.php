<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Vkontakte extends AbstractProvider
{
    public $scopes = array('email');
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
        $fields = array(
            'email',
            'nickname',
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

        return "https://api.vk.com/method/users.get?user_id={$token->uid}&fields="
            . implode(",", $fields)."&access_token={$token}";
    }

    public function userDetails($response, AccessToken $token)
    {
        $response = $response->response[0];

        $user = new User;

        $email = isset($token->email) ? $token->email : null;
        $location = (isset($response->country)) ? $response->country : null;
        $description = (isset($response->status)) ? $response->status : null;

        $user->exchangeArray(array(
            'uid' => $response->uid,
            'nickname' => $response->nickname,
            'name' => $response->screen_name,
            'firstName' => $response->first_name,
            'lastName' => $response->last_name,
            'email' => $email,
            'location' => $location,
            'description' => $description,
            'imageUrl' => $response->photo_200_orig,
        ));

        $availableSex = [1 => 'female', 2 => 'male'];
        $user->gender = isset($availableSex[$response->sex]) ? $availableSex[$response->sex] : null;

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

        return empty($token->email) ? null : $token->email;
    }

    public function userScreenName($response, AccessToken $token)
    {
        $response = $response->response[0];

        return array($response->first_name, $response->last_name);
    }
}
