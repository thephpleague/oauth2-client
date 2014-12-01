<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Microsoft extends AbstractProvider
{
    public $scopes = ['wl.basic', 'wl.emails'];
    public $responseType = 'json';

    /**
     * @return string
     */
    public function urlAuthorize()
    {
        return 'https://oauth.live.com/authorize';
    }

    /**
     * @return string
     */
    public function urlAccessToken()
    {
        return 'https://oauth.live.com/token';
    }

    /**
     * @param AccessToken $token
     *
     * @return string
     */
    public function urlUserDetails(AccessToken $token)
    {
        return 'https://apis.live.net/v5.0/me?access_token='.$token;
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
        $client->setBaseUrl('https://apis.live.net/v5.0/'.$response->id.'/picture');
        $request = $client->get()->send();
        $info = $request->getInfo();
        $imageUrl = $info['url'];

        $user = new User();

        $email = (isset($response->emails->preferred)) ? $response->emails->preferred : null;

        $user->exchangeArray([
            'uid' => $response->id,
            'name' => $response->name,
            'firstname' => $response->first_name,
            'lastname' => $response->last_name,
            'email' => $email,
            'imageurl' => $imageUrl,
            'urls' => $response->link.'/cid-'.$response->id,
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
     * @param $response
     * @param AccessToken $token
     *
     * @return string|null
     */
    public function userEmail($response, AccessToken $token)
    {
        return isset($response->emails->preferred) && $response->emails->preferred
            ? $response->emails->preferred
            : null;
    }

    /**
     * @param $response
     * @param AccessToken $token
     *
     * @return array
     */
    public function userScreenName($response, AccessToken $token)
    {
        return [$response->first_name, $response->last_name];
    }
}
