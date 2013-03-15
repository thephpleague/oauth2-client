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
        $imageHeaders = get_headers('https://graph.facebook.com/me/picture?type=normal&access_token='.$token->accessToken, 1);

        $user = new User;
        $user->uid = $response->id;
        $user->nickname = $response->username;
        $user->name = $response->name;
        $user->firstName = $response->first_name;
        $user->lastName = $response->last_name;
        $user->email = isset($response->email) ? $response->email : null;
        $user->location = isset($response->hometown->name) ? $response->hometown->name : null;
        $user->description = isset($response->bio) ? $response->bio : null;
        $user->imageUrl = $imageHeaders['Location'];
        $user->urls = array(
            'Facebook' => $response->link,
        );

        return $user;
    }
}
