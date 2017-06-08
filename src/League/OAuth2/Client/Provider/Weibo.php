<?php

namespace League\OAuth2\Client\Provider;

class Weibo extends IdentityProvider
{
    public $scope = array('email');

    public function urlAuthorize()
    {
        return 'https://api.weibo.com/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://api.weibo.com/oauth2/access_token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.weibo.com/2/users/show.json?access_token='.$token.'&uid='.$token->uid;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = (array) $response;
        $user = new User;
        $user->uid = $response['id'];
        $user->name = $response['name'];
        $user->description = $response['description'];
        $user->location = $response['location'];
        $user->imageUrl = $response['profile_image_url'];
        $user->urls = array(
            'Weibo' => 'http://weibo.com/' . $response['profile_url']
        );

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return null;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->screen_name;
    }
}
