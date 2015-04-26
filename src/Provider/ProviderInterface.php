<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;

interface ProviderInterface
{
    /**
     * Get the URL that this provider uses to begin authorization.
     *
     * @return string
     */
    public function urlAuthorize();

    /**
     * Get the URL that this provider users to request an access token.
     *
     * @return string
     */
    public function urlAccessToken();

    /**
     * Get the URL that this provider uses to request user details.
     *
     * Since this URL is typically an authorized route, most providers will require
     * you to pass the access_token as a header or parameter with the request.
     * If the URL contains the access token as a parameter, it will be added here.
     *
     * @param AccessToken $token
     * @return string
     */
    public function urlUserDetails(AccessToken $token);

    /**
     * Given an object response from the server, process the user details into a
     * format expected by the user of the client.
     *
     * @param object $response
     * @param AccessToken $token
     * @return mixed
     */
    public function userDetails($response, AccessToken $token);

    /**
     * Get the configured scopes for this provider.
     *
     * @return array
     */
    public function getScopes();

    /**
     * Configure the scopes that will be requested by this provider.
     *
     * @param array $scopes
     * @return void
     */
    public function setScopes(array $scopes);

    /**
     * Get the URL that this provider uses to request authorization.
     *
     * Additional options such as the OAuth state and response type can be set here.
     *
     * @param array $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = []);

    /**
     * Redirect to the authorization URL, using the configured redirect handler if
     * it is available.
     *
     * @param array $options
     * @return void
     */
    public function authorize(array $options = []);

    /**
     * Get the access token using the specified grant type.
     *
     * Either the grant name or a grant instance can be used. Additional grant
     * parameters, such as the authorization code, will be added to the request.
     *
     * @param mixed $grant
     * @param array $params
     * @return AccessToken
     */
    public function getAccessToken($grant = 'authorization_code', array $params = []);

    /**
     * Get any configured request or provider headers.
     *
     * Typically this is used to set the `Authorization` header and any additional
     * headers, such as `Content-Type`, that are required by the provider.
     *
     * @param AccessToken $token
     * @return array
     */
    public function getHeaders($token = null);

    /**
     * Throws an IdentityProviderException when an error response is received
     *
     * @throws IdentityProviderException
     * @param array $result
     * @return void
     */
    public function errorCheck(array $result);

    /**
     * Get the authorized user details from the provider.
     *
     * Details are specific to the individual provider and may not be consistent!
     *
     * @param AccessToken $token
     * @return League\OAuth2\Client\Provider\UserInterface
     */
    public function getUserDetails(AccessToken $token);

    /**
     * Get the authorized user id from the provider.
     *
     * @param AccessToken $token
     * @return mixed
     */
    public function getUserUid(AccessToken $token);

    /**
     * Get the authorized user email from the provider.
     *
     * @param AccessToken $token
     * @return mixed
     */
    public function getUserEmail(AccessToken $token);

    /**
     * Get the authorized user screen name from the provider.
     *
     * @param AccessToken $token
     * @return mixed
     */
    public function getUserScreenName(AccessToken $token);
}
