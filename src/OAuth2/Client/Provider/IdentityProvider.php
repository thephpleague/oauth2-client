<?php

namespace OAuth2\Client\Provider;

use Guzzle\Service\Client as GuzzleClient;
use OAuth2\Client\Token\AccessToken as AccessToken;
use OAuth2\Client\Token\Authorize as AuthorizeToken;
use OAuth2\Client\Exception\IDPException as IDPException;

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

    public function __construct($options = array())
    {
        foreach ($options as $option => $value) {
            if (isset($this->{$option})) {
                $this->{$option} = $value;
            }
        }
    }

    abstract public function urlAuthorize();

    abstract public function urlAccessToken();

    abstract public function urlUserDetails(\OAuth2\Client\Token\AccessToken $token);

    abstract public function userDetails($response, \OAuth2\Client\Token\AccessToken $token);

    public function authorize($options = array())
    {
        $state = md5(uniqid(rand(), true));
        setcookie($this->name.'_authorize_state', $state);

        $params = array(
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
            'scope' => is_array($this->scopes) ? implode($this->scopeSeperator, $this->scopes) : $this->scopes,
            'response_type' => isset($options['response_type']) ? $options['response_type'] : 'code',
            'approval_prompt' => isset($options['approval_prompt']) ? $options['approval_prompt'] : 'force' // - google force-recheck
        );

        header('Location: ' . $this->urlAuthorize().'?'.http_build_query($params));
        exit;
    }

    public function getAccessToken($grant = 'authorization_code', $params = array())
    {
        if (is_string($grant)) {
            $grant = 'OAuth2\\Client\\Grant\\'.ucfirst(str_replace('_', '', $grant));
            if ( ! class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }
            $grant = new $grant;
        } elseif ( ! $grant instanceof Grant\GrantInterface) {
            throw new \InvalidArgumentException($grant.' is not an instance of \OAuth2\Client\Grant\GrantInterface');
        }

        $defaultParams = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => (string)$grant,
        );

        $requestParams = $grant->prepRequestParams($defaultParams, $params);

        try {
            switch ($this->method) {
                case 'get':
                    $client = new GuzzleClient($this->urlAccessToken() . '?' . http_build_query($requestParams));
                    $request = $client->send();
                    $response = $request->getBody();
                    break;
                case 'post':
                    $client = new GuzzleClient($this->urlAccessToken());
                    $request = $client->post(null, null, $requestParams)->send();
                    $response = $request->getBody();
                    break;
            }
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $response = (string)$e->getResponse()->getBody();
        }

        switch ($this->responseType) {
            case 'json':
                $result = json_decode($response, true);
                break;
            case 'string':
                parse_str($response, $result);
                break;
        }

        if (isset($result['error']) && ! empty($result['error'])) {
            throw new IDPException($result);
        }

        return $grant->handleResponse($result);
    }

    public function getUserDetails(AccessToken $token)
    {
        $url = $this->urlUserDetails($token);

        try {

            $client = new GuzzleClient($url);
            $request = $client->get()->send();
            $response = $request->getBody();
            return $this->userDetails(json_decode($response), $token);

        } catch (\Guzzle\Http\Exception\BadResponseException $e) {

            $raw_response = explode("\n", $e->getResponse());
            throw new IDPException(end($raw_response));

        }
    }

}
