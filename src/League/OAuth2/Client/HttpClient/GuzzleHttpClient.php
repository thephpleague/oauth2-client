<?php

namespace League\OAuth2\Client\HttpClient;

use Guzzle\Service\Client as GuzzleClient;

class GuzzleHttpClient implements HttpClientInterface
{

    protected $guzzleClient;

    public function __construct()
    {
        $this->guzzleClient = new GuzzleClient();
    }

    public function get($uri = null, $headers = null, array $options = array())
    {

        $request = $this->guzzleClient->get($uri, $headers, $options);

        // use proxy
        //$this->setProxy($request);

        try {
            $response =  $request->send();
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $response = $e->getResponse();
            $result['body'] = $response->getBody(true);
            $result['error'] = "Bad Response";
            $result['code'] = $response->getStatusCode();

            return $result;
        } catch (\Guzzle\Http\Exception\CurlException $e) {
            $result['message'] = 'Curl Exception';

            return $result;
        }

        $result = $response->getBody(true);

        return $result;
    }

    public function post($uri = null, $headers = null, $postBody = null, array $options = array())
    {
        $request = $this->guzzleClient->post($uri, $headers, $postBody, $options);

        // use proxy
        //$this->setProxy($request);

        try {
            $response = $request->send();
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $response = $e->getResponse();
            $result['body'] = $response->getBody(true);
            $result['error'] = "Bad Response";
            $result['code'] = $response->getStatusCode();

            return $result;
        } catch (\Guzzle\Http\Exception\CurlException $e) {
            $result['message'] = 'Curl Exception';

            return $result;
        }

        $result = $response->getBody(true);

        return $result;
    }

    protected function setProxy($request)
    {
        // use proxy
        $request->getCurlOptions()->set(CURLOPT_HTTPPROXYTUNNEL, true);
        $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);
        $request->getCurlOptions()->set(CURLOPT_PROXY, '127.0.0.1:7005');
        $request->getCurlOptions()->set(CURLOPT_PROXYTYPE, 7);
    }
}
