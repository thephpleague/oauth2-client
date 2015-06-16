<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequiredParameterTrait;

abstract class AbstractGrant
{
    use RequiredParameterTrait;

    /**
     * Returns the name of this grant, eg. 'grant_name', which is used as the
     * grant type when encoding URL query parameters.
     *
     * @return string
     */
    abstract protected function getName();

    /**
     * Get a list of all required request parameters.
     *
     * @return array
     */
    abstract protected function getRequiredRequestParameters();

    /**
     * Returns this grant's name as its string representation. This allows for
     * string interpolation when building URL query parameters.
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Converts the result from a response into an `AccessToken`.
     *
     * @param array $response
     *
     * @return AccessToken
     */
    public function createAccessToken(array $response)
    {
        return new AccessToken($response);
    }

    /**
     * Prepares an access token request's parameters by checking that all
     * required parameters are set, then merging with any given defaults.
     *
     * @param array $defaults
     * @param array $options
     *
     * @return array
     */
    public function prepareRequestParameters(array $defaults, array $options)
    {
        $defaults['grant_type'] = $this->getName();

        $required = $this->getRequiredRequestParameters();
        $provided = array_merge($defaults, $options);

        $this->checkRequiredParameters($required, $provided);

        return $provided;
    }
}
