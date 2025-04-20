<?php

namespace League\OAuth2\Client\Test\Grant;

use BadMethodCallException;
use League\OAuth2\Client\Grant\DeviceCode;

class DeviceCodeTest extends GrantTestCase
{
    public static function providerGetAccessToken()
    {
        return [
            ['device_code', ['device_code' => 'mock_device_code']],
        ];
    }

    protected function getParamExpectation()
    {
        return function ($body) {
            return !empty($body['grant_type'])
                && $body['grant_type'] === 'urn:ietf:params:oauth:grant-type:device_code'
                && !empty($body['device_code']);
        };
    }

    public function testToString()
    {
        $grant = new DeviceCode();
        $this->assertEquals('urn:ietf:params:oauth:grant-type:device_code', (string) $grant);
    }

    public function testInvalidDeviceCode()
    {
        $this->expectException(BadMethodCallException::class);

        $this->getMockProvider()->getAccessToken('device_code', ['invalid_device_code' => 'mock_device_code']);
    }

}
