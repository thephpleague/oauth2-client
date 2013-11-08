<?php

namespace League\OAuth2\Client\Provider;

use \Guzzle\Service\Client as GuzzleClient;
use \League\OAuth2\Client\Token\AccessToken;
use \League\OAuth2\Client\Exception\IDPException;
use \League\OAuth2\Client\Exception\Oauth2AuthorizationRequiredException;
use \Guzzle\Http\Exception\BadResponseException;
use \League\OAuth2\Client\Grant\GrantInterface;


abstract class IdentityProvider {

    public $clientId = '';

    public $clientSecret = '';

    public $redirectUri = '';

    public $name;

    public $uidKey = 'uid';

    public $scopes = array();

    public $method = 'post';

    public $scopeSeperator = ',';

    public $responseType = 'json';

    protected $cachedUserDetailsResponse;

    private $throwOauth2AuthorizationRequiredException;
    
    
    public function __construct( array $options = array(),
            $throwOauth2AuthorizationRequiredException = false ) {
        
        foreach( $options as $option => $value ) {
            if( isset( $this->{$option} ) ) {
                $this->{$option} = $value;
            }
        }
        
        $this->throwOauth2AuthorizationRequiredException =
                $throwOauth2AuthorizationRequiredException;
    }

    
    abstract public function urlAuthorize();

    abstract public function urlAccessToken();

    abstract public function urlUserDetails( AccessToken $token );

    abstract public function userDetails( $response, AccessToken $token );

    
    public function getScopes() {
        return $this->scopes;
    }

    public function setScopes( array $scopes ) {
        $this->scopes = $scopes;
    }

    public function getAuthorizationUrl( $options = array() ) {
        
        $state = md5( uniqid( mt_rand(), true ) );
        setcookie( $this->name.'_authorize_state', $state );

        $params = array(
            'client_id'         => $this->clientId,
            'redirect_uri'      => $this->redirectUri,
            'state'             => $state,
            'scope'             => is_array( $this->scopes )
                    ? implode( $this->scopeSeperator, $this->scopes )
                    : $this->scopes,
            'response_type'     => isset( $options['response_type'] )
                    ? $options['response_type']
                    : 'code',
            'approval_prompt'   => 'force' // - google force-recheck
        );

        return $this->urlAuthorize().'?'.http_build_query( $params );
    }

    public function authorize( $options = array() ) {
        
        $url = $this->getAuthorizationUrl( $options );
        
        if( $this->throwOauth2AuthorizationRequiredException ) {
            throw new Oauth2AuthorizationRequiredException( $url );
        } else {
            header( 'Location: '.$url );
            exit();
        }
    }

    public function getAccessToken( $grant = 'authorization_code',
            $params = array() ) {
        
        if( is_string( $grant ) ) {
            
            $grant = 'League\\OAuth2\\Client\\Grant\\'
                    .ucfirst( str_replace( '_', '', $grant ) );
            
            if( !class_exists( $grant ) ) {
                throw new \InvalidArgumentException(
                        'Unknown grant "'.$grant.'"' );
            }
            
            $grant = new $grant;
            
        } elseif( !$grant instanceof GrantInterface ) {
            throw new \InvalidArgumentException(
                    $grant.' is not an instance of'
                    .' \League\OAuth2\Client\Grant\GrantInterface' );
        }

        $defaultParams = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => $grant,
        );

        $requestParams = $grant->prepRequestParams( $defaultParams, $params );

        try {
            switch( $this->method ) {
                case 'get':
                    $client = new GuzzleClient(
                            $this->urlAccessToken()
                            .'?'.http_build_query( $requestParams ) );
                    $request = $client->send();
                    $response = $request->getBody();
                    break;
                case 'post':
                    $client = new GuzzleClient( $this->urlAccessToken() );
                    $request = $client->post( null, null, $requestParams )
                            ->send();
                    $response = $request->getBody();
                    break;
            }
        } catch( BadResponseException $ex ) {
            $raw_response = explode( "\n", $ex->getResponse() );
            $response = end( $raw_response );
        }

        switch( $this->responseType ) {
            case 'json':
                $result = json_decode( $response, true );
                break;
            case 'string':
                parse_str( $response, $result );
                break;
        }

        if( !empty( $result['error'] ) ) {
            throw new IDPException( $result );
        }

        return $grant->handleResponse( $result );
    }

    public function getUserDetails( AccessToken $token, $force = false ) {
        $response = $this->fetchUserDetails( $token, $force );
        return $this->userDetails( json_decode( $response ), $token );
    }

    public function getUserUid(AccessToken $token, $force = false ) {
        $response = $this->fetchUserDetails( $token, $force );
        return $this->userUid( json_decode( $response ), $token );
    }

    public function getUserEmail( AccessToken $token, $force = false ) {
        $response = $this->fetchUserDetails( $token, $force );
        return $this->userEmail( json_decode( $response ), $token );
    }

    public function getUserScreenName( AccessToken $token, $force = false ) {
        $response = $this->fetchUserDetails( $token, $force );
        return $this->userScreenName( json_decode( $response ), $token );
    }

    protected function fetchUserDetails( AccessToken $token, $force = false ) {
        
        if( !$this->cachedUserDetailsResponse || $force == true ) {

            $url = $this->urlUserDetails( $token );

            try {

                $client = new GuzzleClient( $url );
                $request = $client->get()->send();
                $response = $request->getBody();
                $this->cachedUserDetailsResponse = $response;

            } catch( BadResponseException $ex ) {
                $raw_response = explode( "\n", $ex->getResponse() );
                throw new IDPException( end( $raw_response ) );
            }
        }

        return $this->cachedUserDetailsResponse;
    }

}
