<?php

namespace League\OAuth2\Client\Test\Provider;

use \Mockery as m;

class AbstractProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Provider\Google(array(
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ));
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidGrantString()
    {
        $this->provider->getAccessToken('invalid_grant', array('invalid_parameter' => 'none'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidGrantObject()
    {
        $grant = new \StdClass;
        $this->provider->getAccessToken($grant, array('invalid_parameter' => 'none'));
    }
}
