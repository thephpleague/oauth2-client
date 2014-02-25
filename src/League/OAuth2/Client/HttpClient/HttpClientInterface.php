<?php

namespace League\OAuth2\Client\HttpClient;

/**
 * HttpClientInterface
 *
 *  Oauth-client requires A PHP HTTP client, adding a httpclient interface
 *  only for dependency injection and testcases
 *
 */

interface HttpClientInterface
{
    /**
     * HTTP Method GET
     *
     * @param  string  $uri      Uri to send HTTP request to
     * @param  array   $headers  Array of Headers|null
     * @param  array   $options  Vendor specific options to activate specific features
     * @return  mixed
     */
    public function get($uri = null, $headers = null, array $options = array());


    /**
     * HTTP Method POST
     *
     * @param  string  $uri       Uri to send HTTP request to
     * @param  array   $headers   Array of Headers|null
     * @param  array   $postBody  Array of POST body|null
     * @param  array   $options   Vendor specific options to activate specific features
     * @return  mixed
     */
    public function post($uri = null, $headers = null, $postBody = null, array $options = array());
}
