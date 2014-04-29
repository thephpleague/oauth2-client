# OAuth 2.0 Client Library

[![Build Status](https://travis-ci.org/thephpleague/oauth2-client.png?branch=master)](https://travis-ci.org/thephpleague/oauth2-client)
[![Total Downloads](https://poser.pugx.org/league/oauth2-client/downloads.png)](https://packagist.org/packages/league/oauth2-client)
[![Latest Stable Version](https://poser.pugx.org/league/oauth2-client/v/stable.png)](https://packagist.org/packages/league/oauth2-client)

This library makes it stupidly simple to integrate your application with OAuth 2.0 identity providers. It has built in support for:

* Eventbrite
* Facebook
* Github
* Google
* Instagram
* LinkedIn
* Microsoft
* Vkontakte

Adding support for other providers is trivial.

The library requires PHP 5.4+ and is PSR-4 compatible.

## Usage

```php
$provider = new League\OAuth2\Client\Provider\<provider name>(array(
    'clientId'  =>  'XXXXXXXX',
    'clientSecret'  =>  'XXXXXXXX',
    'redirectUri'   =>  'http://your-registered-redirect-uri/'
));

if ( ! isset($_GET['code'])) {

    // Optionally, based on your Provider, create a State token
    $options = [];
    if ($provider->supportState) {
        $options['state'] = $application->generateState();
    }

    // If we don't have an authorization code then get one
    $provider->authorize($options);

} else {

    try {

	// Optionally, based on your provider, validate the state.  Validation of the state is
        // not handled by this library
        if ($provider->supportState) {
            if (! $application->validateState($_GET['state'])) {
                throw new \Exception('Unable to validate state');
            }
        }

    	// Try to get an access token (using the authorization code grant)
        $t = $provider->getAccessToken('authorization_code', array('code' => $_GET['code']));

        // NOTE: If you are using Eventbrite you will need to add the grant_type parameter (see below)
        // $t = $provider->getAccessToken('authorization_code', array('code' => $_GET['code'], 'grant_type' => 'authorization_code'));

        try {

        	// We got an access token, let's now get the user's details
            $userDetails = $provider->getUserDetails($t);

            foreach ($userDetails as $attribute => $value) {
                var_dump($attribute, $value) . PHP_EOL . PHP_EOL;
            }

        } catch (Exception $e) {

            // Failed to get user details

        }

    } catch (Exception $e) {

        // Failed to get access token
	// If you have a refesh token you can use it here:
        $grant = new \League\OAuth2\Client\Grant\RefreshToken();
        $t = $provider->getAccessToken($grant, array('refresh_token' => $refreshToken));
    }
}
```

### Built-In Providers

- Eventbrite
- Facebook
- Github
- Google
- Instagram
- LinkedIn
- Microsoft

These are as many OAuth 2 services as we plan to support officially. Maintaining a wide selection of providers
damages our ability to make this package the best it can be, especially as we progress towards v1.0. 

If you would like to support other providers, please make them available as a Composer package, then link to them
below.

### Third-Party Providers

These providers allow integration with other providers not supported by `oauth2-client`. They may require an older version
so please help them out with a pull request if you notice this. 

- _< insert providers here >_

## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE) for more information.


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/thephpleague/oauth2-client/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

