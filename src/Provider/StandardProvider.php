<?php

namespace League\OAuth2\Client\Provider;

use InvalidArgumentException;

class StandardProvider extends AbstractProvider
{
    /**
     * @var string
     */
    private $urlAuthorize;

    /**
     * @var string
     */
    private $urlAccessToken;

    /**
     * @var string
     */
    private $urlUserDetails;

    /**
     * @var string
     */
    private $accessTokenMethod;

    /**
     * @var string
     */
    private $accessTokenUid;

    /**
     * @var string
     */
    private $scopeSeparator;

    /**
     * @var string
     */
    private $responseError = 'error';

    /**
     * @var string
     */
    private $responseCode;

    /**
     * @var string
     */
    private $responseUid = 'id';

    public function __construct($options = [], array $collaborators = [])
    {
        $this->assertRequiredOptions($options);

        $configured = array_intersect_key(array_flip($this->getConfigurableOptions()), $options);
        foreach ($this->getConfigurableOptions() as $key) {
            $this->$key = $options[$key];
        }

        return parent::__construct($options, $collaborators);
    }

    /**
     * Get all options that can be configured.
     *
     * @return array
     */
    protected function getConfigurableOptions()
    {
        return array_merge($this->getRequiredOptions(), [
            'accessTokenMethod',
            'accessTokenUid',
            'scopeSeparator',
            'responseError',
            'responseCode',
            'responseUid',
        ]);
    }

    /**
     * Get all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions()
    {
        return [
            'urlAuthorize',
            'urlAccessToken',
            'urlUserDetails',
        ];
    }

    /**
     * Verify that all required options have been passed.
     *
     * @param  array $options
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);
        if ($missing) {
            throw new InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }

    public function urlAuthorize()
    {
        return $this->urlAuthorize;
    }

    public function urlAccessToken()
    {
        return $this->urlAuthorize;
    }

    public function urlUserDetails(AccessToken $token)
    {
        return $this->urlUserDetails;
    }

    protected function getAccessTokenMethod()
    {
        return $this->accessTokenMethod ?: parent::getAccessTokenMethod();
    }

    protected function getAccessTokenUid()
    {
        return $this->accessTokenUid ?: parent::getAccessTokenUid();
    }

    protected function getScopeSeparator()
    {
        return $this->scopeSeparator ?: parent::getScopeSeparator();
    }

    protected function checkResponse(array $response)
    {
        if (!empty($response[$this->responseError])) {
            $error = $response[$this->responseError];
            $code  = $this->responseCode ? $response[$this->responseCode] : 0;
            throw new IdentityProviderException($error, $code, $response);
        }
    }

    protected function prepareUserDetails(array $response, AccessToken $token)
    {
        return new StandardUser($response, $this->responseUid);
    }
}
