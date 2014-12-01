<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Facebook extends AbstractProvider
{
    public $scopes = ['offline_access', 'email', 'read_stream'];
    public $responseType = 'string';

    /**
     * @return string
     */
    public function urlAuthorize()
    {
        return 'https://www.facebook.com/dialog/oauth';
    }

    /**
     * @return string
     */
    public function urlAccessToken()
    {
        return 'https://graph.facebook.com/oauth/access_token';
    }

    /**
     * @param AccessToken $token
     *
     * @return string
     */
    public function urlUserDetails(AccessToken $token)
    {
        return 'https://graph.facebook.com/me?access_token='.$token;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     *
     * @return User
     */
    public function userDetails($response, AccessToken $token)
    {
        $client = $this->getHttpClient();
        $client->setBaseUrl('https://graph.facebook.com/me/picture?type=normal&access_token='.$token->accessToken);
        $request = $client->get()->send();
        $info = $request->getInfo();
        $imageUrl = $info['url'];

        $user = new User();

        $username = (isset($response->username)) ? $response->username : null;
        $email = (isset($response->email)) ? $response->email : null;
        $location = (isset($response->hometown->name)) ? $response->hometown->name : null;
        $description = (isset($response->bio)) ? $response->bio : null;
        $imageUrl = ($imageUrl) ?: null;

        $user->exchangeArray([
            'uid' => $response->id,
            'nickname' => $username,
            'name' => $response->name,
            'firstname' => $response->first_name,
            'lastname' => $response->last_name,
            'email' => $email,
            'location' => $location,
            'description' => $description,
            'imageurl' => $imageUrl,
            'urls' => [ 'Facebook' => $response->link ],
        ]);

        return $user;
    }

    /**
     * @param $response
     * @param AccessToken $token
     *
     * @return string
     */
    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     *
     * @return string|null
     */
    public function userEmail($response, AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     *
     * @return array
     */
    public function userScreenName($response, AccessToken $token)
    {
        return [$response->first_name, $response->last_name];
    }
}
