<?php

namespace OAuth2\Client\Exception;


class Oauth2AuthorizationRequiredException extends \Exception {
    
    private $redirectUrl;
    
    
    public function __construct( $redirectUrl ) {
        parent::__construct( 'New access token is required. Please, redirect.',
                302, null );
        $this->redirectUrl = $redirectUrl;
    }
    
    public function getRedirectUrl() {
        return $this->redirectUrl;
    }
    
}
