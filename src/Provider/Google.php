<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Google extends AbstractProvider
{
    public $scopeSeparator = ' ';

    public $scopes = [
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email',
    ];

   /**
    * @var string If set, this will be sent to google as the "hd" parameter.
    * @link https://developers.google.com/accounts/docs/OAuth2Login#hd-param
    */
    public $hostedDomain = '';

    /**
     * @param string $hd
     */
    public function setHostedDomain($hd)
    {
        $this->hostedDomain = $hd;
    }

    /**
     * @return string
     */
    public function getHostedDomain()
    {
        return $this->hostedDomain;
    }

    /**
     * @return string
     */
    public function urlAuthorize()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    /**
     * @return string
     */
    public function urlAccessToken()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    /**
     * @param AccessToken $token
     *
     * @return string
     */
    public function urlUserDetails(AccessToken $token)
    {
        return 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     *
     * @return User
     */
    public function userDetails($response, AccessToken $token)
    {
        $response = (array) $response;

        $user = new User();

        $imageUrl = (isset($response['picture'])) ? $response['picture'] : null;

        $user->exchangeArray([
            'uid' => $response['id'],
            'name' => $response['name'],
            'firstname' => $response['given_name'],
            'lastName' => $response['family_name'],
            'email' => $response['email'],
            'imageUrl' => $imageUrl,
        ]);

        return $user;
    }

    /**
     * @param object $response
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
     * @return string | null
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
        return [$response->given_name, $response->family_name];
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function getAuthorizationUrl($options = array())
    {
        $url = parent::getAuthorizationUrl($options);

        if (!empty($this->hostedDomain)) {
            $url .= '&' . $this->httpBuildQuery(['hd' => $this->hostedDomain]);
        }

        return $url;
    }
}
