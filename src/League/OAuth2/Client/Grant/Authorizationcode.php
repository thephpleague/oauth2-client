<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessToken;


class Authorizationcode implements GrantInterface {

    public function prepRequestParams( array $defaultParams, array $params ) {
        
        if( empty( $params['code'] ) ) {
            throw new \BadMethodCallException( 'Missing authorization code' );
        }

        return array_merge( $defaultParams, $params );
    }

    public function handleResponse( array $response = array() ) {
        return new AccessToken( $response );
    }
    
    public function __toString() {
        return 'authorization_code';
    }
    
}
