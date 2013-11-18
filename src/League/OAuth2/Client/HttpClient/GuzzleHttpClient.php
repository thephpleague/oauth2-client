<?php

namespace League\OAuth2\Client\HttpClient;

use Guzzle\Service\Client as GuzzleClient;

class GuzzleHttpClient implements HttpClientInterface {


    private $guzzleClient;

    public function __construct() {
       $this->guzzleClient = new GuzzleClient();
    }
    
    public function get($uri = null, array $headers = null, array $options = array()) {
        
        $request = $this->guzzleClient->get($uri, $headers, $options);
        try {
            $response =  $request->send();
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $response = $e->getResponse();
            $result['body'] = $response->getBody(true);
            $result['error'] = "Bad Response";
            $result['code'] = $response->getStatusCode();

            return $result;
        }

        $result = $response->getBody(true);

        return $result;
    }

    public function post($uri = null, array $headers = null, array $postBody = null, array $options = array()) {
    
        $request = $this->guzzleClient->post($uri, $headers, $postBody, $options);
        try {
            $response = $request->send();
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $response = $e->getResponse();
            $result['body'] = $response->getBody(true);
            $result['error'] = "Bad Response";
            $result['code'] = $response->getStatusCode();

            return $result; 
        }

        $result = $response->getBody(true);
        
        return $result;
    }
}
