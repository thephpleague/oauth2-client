<?php
namespace OAuth2\Client\Provider;

class Blooie extends IdentityProvider
{
    public $scope = array('user.profile', 'user.picture');

    public function urlAuthorize()
    {
        return 'https://bloo.ie/oauth';
    }

    public function urlAccessToken()
    {
        return 'https://bloo.ie/oauth/access_token';
    }

    public function urlUserDetails(\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://graph.facebook.com/me?access_token='.$token;
    }

    public function userDetails($response, \OAuth2\Client\Token\AccessToken $token)
    {
        return array(
            'uid' => $response->id,
            'nickname' => $response->username,
            'name' => $response->name,
            'first_name' => $response->first_name,
            'last_name' => $response->last_name,
            'email' => isset($response->email) ? $response->email : null,
            'location' => isset($response->hometown->name) ? $response->hometown->name : null,
            'description' => isset($response->bio) ? $response->bio : null,
            'image' => 'https://graph.facebook.com/me/picture?type=normal&access_token='.$token->access_token,
            'urls' => array(
              'Facebook' => $response->link,
            ),
        );
    }
}
