<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken as AccessToken;

interface ProviderInterface
{

    /**
     * @return string
     */
    public function urlAuthorize();

    /**
     * @return string
     */
    public function urlAccessToken();

    /**
     * @param AccessToken $token
     *
     * @return mixed
     */
    public function urlUserDetails(AccessToken $token);

    /**
     * @param object $response
     * @param AccessToken $token
     *
     * @return User
     */
    public function userDetails($response, AccessToken $token);

    /**
     * @return array
     */
    public function getScopes();

    /**
     * @param array $scopes
     */
    public function setScopes(array $scopes);

    /**
     * @param array $options
     *
     * @return string
     */
    public function getAuthorizationUrl($options = []);

    /**
     * Redirect to the authorize page of the provider
     *
     * @param array $options
     *
     * @return void
     */
    public function authorize($options = []);

    /**
     * @param string $grant
     * @param array $params
     *
     * @return AccessToken
     * @throws IDPException
     */
    public function getAccessToken($grant = 'authorization_code', $params = []);

    /**
     * @param AccessToken $token
     *
     * @return User
     */
    public function getUserDetails(AccessToken $token);

    /**
     * @param AccessToken $token
     *
     * @return string
     */
    public function getUserUid(AccessToken $token);

    /**
     * @param AccessToken $token
     *
     * @return string|null
     */
    public function getUserEmail(AccessToken $token);

    /**
     * @param AccessToken $token
     *
     * @return array
     */
    public function getUserScreenName(AccessToken $token);
}
