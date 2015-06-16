<?php

namespace League\OAuth2\Client\Grant;

interface GrantInterface
{
    /**
     * Get the simple name of the grant type.
     *
     * @return string
     */
    public function __toString();

    /**
     * Prepare the request parameters for an authorization request.
     *
     * This should set the grant type and verify that required parameters are set.
     * If required parameters are not defined, an exception should be thrown.
     *
     * @throws BadMethodCallException
     * @param  array $defaults
     * @param  array $params
     * @return array
     */
    public function prepareRequestParameters(array $defaults, array $params);

    /**
     * Generate an access token from a successful authorization request.
     *
     * @param  array $response
     * @return League\OAuth2\Client\Token\AccessToken
     */
    public function createAccessToken(array $response);
}
