<?php

namespace League\OAuth2\Client\HttpClient;

use Guzzle\Service\Client as GuzzleClient;

class GuzzleHttpClient implements HttpClientInterface {


    private $guzzleClient;

    public function __construct() {
       $guzzleClient = new GuzzleClient();
    }
    
    public function get($uri = null, array $headers = null, array $options = array()) {
        
        $request = $this->guzzleClient->get($uri, $headers, $options);
        try {
            $response =  $request->send();
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            //get reponse with header
            $result = end(explode("\n", $e->getResponse()));

            return $result;
        }

        $result = $response->getBody();

        return $result;
    }

    public function post($uri = null, array $headers = null, array $postBody = null, array $options = array()) {
    
        $request = $this->guzzleClient->post($uri, $headers, $postBody, $options);
        try {
            $response = $request->send();
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            //get reponse with header
            $result = end(explode("\n", $e->getResponse()));

            return $result;
        }

        $result = $response->getBody();
        
        return $result;
    }
}
