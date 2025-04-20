<?php
/**
 * Implements "device_code" for league/oauth2-client library
 *
 * @copyright Copyright (c) Sebastian Lemke
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace League\OAuth2\Client\Grant;

/**
 * Represents a device authorization grant.
 *
 * @link https://tools.ietf.org/html/rfc8628
 */
class DeviceCode extends AbstractGrant
{
    /**
     * @inheritdoc
     */
    protected function getName()
    {
        return 'urn:ietf:params:oauth:grant-type:device_code';
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredRequestParameters()
    {
        return [
            'device_code',
        ];
    }
}
