<?php

namespace LeagueTest\OAuth2\Client\Provider;

use \Mockery as m;

class IdentityProviderTest extends \PHPUnit_Framework_TestCase
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

    protected function tearDown()
    {
#        m::close();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidGrantString()
    {
        $test = $this->provider->getAccessToken('invalid_grant', array('invalid_parameter' => 'none'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidGrantObject()
    {
        $grant = new \StdClass;
        $test = $this->provider->getAccessToken($grant, array('invalid_parameter' => 'none'));
    }
}
