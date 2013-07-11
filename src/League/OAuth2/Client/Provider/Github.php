<?php

namespace League\OAuth2\Client\Provider;

class Github extends IdentityProvider
{
    public $scopes = array('user:email');
    public $responseType = 'string';

    public function urlAuthorize()
    {
        return 'https://github.com/login/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://github.com/login/oauth/access_token';
    }

    public function getUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        $userDetails = $this->getDataFromURL('https://api.github.com/user?access_token='.$token);
        $userEmails = $this->getDataFromURL('https://api.github.com/user/emails?access_token='.$token);
        $userFirstEmail = $userEmails[0];
        
        var_dump($userEmails);
        echo $userFirstEmail;

        $user = new User;
        $user->uid = $userDetails->id;
        $user->nickname = $userDetails->login;
        $user->name = $userDetails->name;
        $user->email = isset($userFirstEmail) ? $userFirstEmail : null;
        $user->urls = array(
            'GitHub' => 'http://github.com/'.$userDetails->login,
            'Blog' => $userDetails->blog,
        );

        return $user;
    }
}