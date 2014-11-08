<?php

namespace League\OAuth2\Client\Test\Provider;


class AbstractProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Provider\Google([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidGrantString()
    {
        $this->provider->getAccessToken('invalid_grant', ['invalid_parameter' => 'none']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidGrantObject()
    {
        $grant = new \StdClass();
        $this->provider->getAccessToken($grant, ['invalid_parameter' => 'none']);
    }
}
