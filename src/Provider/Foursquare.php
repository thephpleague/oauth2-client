<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class Foursquare extends AbstractProvider
{
    public $responseType = 'json';
    public $apiVersion = 20140712;
    public $photoSize = '64x64';

    public function urlAuthorize()
    {
        return 'https://foursquare.com/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://foursquare.com/oauth2/access_token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.foursquare.com/v2/users/self?oauth_token='.$token.'&v='.$this->apiVersion;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;

        $user_response = $response->response->user;

        $photo = $user_response->photo->prefix . $this->photoSize . $user_response->photo->suffix;

        // Some fields are always returned, others not always e.g. Venue accounts have no
        // lastname. See: https://developer.foursquare.com/docs/users/users
        $user->exchangeArray(array(
            'uid' => $user_response->id,
            'name' => $this->userScreenName($response, $token),
            'firstName' => (isset($user_response->firstName)) ? $user_response->firstName : null,
            'lastName' => (isset($user_response->lastName)) ? $user_response->lastName : null,
            'email' => (isset($user_response->contact->email)) ? $user_response->contact->email : null,
            'location' => $user_response->homeCity,
            'description' => (isset($user_response->bio)) ? $user_response->bio : null,
            'imageUrl' => $photo,
            'gender' => $user_response->gender
        ));

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->response->user->id;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $contact = $response->response->user->contact;

        return isset($contact->email) && $contact->email ? $contact->email : null;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        // First and last name are not always both present e.g. Venue accounts
        // and there is no access to the username / displayname per-say
        // without hacking it out of a users todo list, which I don't imagine is
        // guaranteed to work, and probably won't work for venue accounts either
        $name_parts = array();
        $checks = array('firstName', 'lastName');

        foreach ($checks as $check) {
            if (isset($response->response->user->$check)) {
                $name_parts[] = $response->response->user->$check;
            }
        }

        return implode(' ', $name_parts);
    }
}
