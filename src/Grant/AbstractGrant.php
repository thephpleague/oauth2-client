<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequiredParameterTrait;

abstract class AbstractGrant implements GrantInterface
{
    use RequiredParameterTrait;

    // Implementing these interfaces methods should not be required, but not
    // doing so will break HHVM because of https://github.com/facebook/hhvm/issues/5170
    // Once HHVM is working, delete the following abstract methods.
    abstract public function __toString();
    // End of methods to delete.

    /**
     * Get a list of all required request parameters.
     *
     * @return array
     */
    abstract protected function getRequiredRequestParams();

    public function prepRequestParams(array $defaultParams, array $params)
    {
        $this->checkRequiredParameters(
            $this->getRequiredRequestParams(),
            $params
        );

        return array_merge($defaultParams, $params);
    }

    public function handleResponse(array $response)
    {
        return new AccessToken($response);
    }
}
