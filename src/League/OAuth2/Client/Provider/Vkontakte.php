<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;


class Vkontakte extends IdentityProvider {

    const API_VERSION = '5.3';
    
    public $scopes = array( '' );
    public $responseType = 'json';
    public $method = 'get';

    public function urlAuthorize() {
        return 'https://oauth.vk.com/authorize';
    }

    public function urlAccessToken() {
        return 'https://oauth.vk.com/access_token';
    }

    public function urlUserDetails( AccessToken $token ) {
        return 'https://api.vk.com/method/users.get?access_token='.$token
                .'&fields=nickname,screen_name,sex,bdate,city,country,'
                .'photo_200_orig'
                .'&name_case=Nom'
                .'&v='.self::API_VERSION;
    }

    public function userDetails( $response, AccessToken $token ) {
        
        $response = $response->response[0];
        
        // Get city name in English
        $city = is_array( $cityResult = json_decode( file_get_contents(
                'https://api.vk.com/method/database.getCitiesById'
                .'?city_ids='.$response->city
                .'&lang=en&v='.self::API_VERSION ), true ) )
                ? $cityResult['response'][0]['title']
                : null;
        
        // Get country name in English
        $country = is_array( $countryResult = json_decode( file_get_contents(
                'https://api.vk.com/method/database.getCountriesById'
                .'?country_ids='.$response->country
                .'&lang=en&v='.self::API_VERSION ), true ) )
                ? $countryResult['response'][0]['title']
                : null;
        
        $user = new User();
        
        $user->uid          = $response->id;
        $user->nickname     = $response->nickname;
        $user->name         = $response->first_name.' '.$response->last_name;
        $user->firstName    = $response->first_name;
        $user->lastName     = $response->last_name;
        $user->email        = null; // Vkontakte never returns email
        $user->location     = $city;
        $user->description  = null;
        $user->imageUrl     = $response->photo_200_orig;
        $user->urls         = array(
            'profile' => 'https://vk.com/id'.$response->id,
        );
        
        $user->city         = $city;
        $user->country      = $country;
        
        $user->birthday = \DateTime::createFromFormat(
            'j.n.Y',
            $response->bdate
        )->format( 'Y-m-d' );
        
        $user->sex = ( $response->sex == 1 )
                ? 'woman'
                : ( ( $response->sex == 2 )
                    ? 'man'
                    : null );
        
        return $user;
    }

    public function userUid( $response, AccessToken $token ) {
        $response = $response->response[0];
        return $response->id;
    }

    public function userEmail( $response, AccessToken $token ) {
        // Vkontakte never returns email
        return null;
    }

    public function userScreenName( $response, AccessToken $token ) {
        $response = $response->response[0];
        return $response->screen_name;
    }

}
