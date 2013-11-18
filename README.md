# OAuth 2.0 Client Library

[![Build Status](https://travis-ci.org/php-loep/oauth2-client.png?branch=master)](https://travis-ci.org/php-loep/oauth2-client)
[![Total Downloads](https://poser.pugx.org/league/oauth2-client/downloads.png)](https://packagist.org/packages/league/oauth2-client)
[![Latest Stable Version](https://poser.pugx.org/league/oauth2-client/v/stable.png)](https://packagist.org/packages/league/oauth2-client)

This library makes it stupidly simple to integrate your application with OAuth 2.0 identity providers. It has built in support for:

* Facebook
* Github
* Google
* Instagram
* LinkedIn
* Microsoft
* Vkontakte

Adding support for other providers is trivial.

The library requires PHP 5.3+ and is PSR-0 compatible.

## Usage

```php
$provider = new League\OAuth2\Client\Provider\<provider name>(array(
    'clientId'  =>  'XXXXXXXX',
    'clientSecret'  =>  'XXXXXXXX',
    'redirectUri'   =>  'http://your-registered-redirect-uri/'
));

if ( ! isset($_GET['code'])) {

	// If we don't have an authorization code then get one
    $provider->authorize();

} else {

    try {

    	// Try to get an access token (using the authorization code grant)
        $t = $provider->getAccessToken('authorization_code', array('code' => $_GET['code']));

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

    }
}
```

## Refresh tokens

When first requesting an access token some providers will supply you with a `refresh_token` that you may use to retrieve a brand new access token when your's expires.


```php
if ($refreshToken = $t->refreshToken)
{
    $_SESSION['refreshToken'] = $refreshToken;
}
```

Later, you can use that very refresh token to request a new access token like so.

```php
$refresh_token = $_SESSION['refreshToken'];
$new_token = $provider->getAccessToken('refresh_token', array('refresh_token' => $token->refresh_token));
```

Be sure not to delete your refresh token as you won't be able to refresh your access token.

### List of built-in identity providers

| Provider | uid    | nickname | name   | first_name | last_name | email  | location | description | imageUrl | urls |
| :------- | :----- | :------- | :----- | :--------- | :-------- | :----- | :------- | :---------- | :------- | :--- |
| **Facebook** | string | string | string | string | string | string | string | string | string   | array (Facebook) |
| **Github**   | string | string | string | null | null | string | null | null | null | array (Github, [personal])|
| **Google** | string | string | string | string | string | string | null | null | string | null |
| **Instagram** | string | string | string | null | null | null | null | string | string | null |
| **LinkedIn** | string | null | string | null | null | string | string | string | string | string |
| **Microsoft** | string | null | string | string | string | string | null | null | string | string |

**Notes**: Providers which return URLs sometimes include additional URLs if the user has provided them. These have been wrapped in []

## License

The MIT License (MIT). Please see [License File](https://github.com/php-loep/:package_name/blob/master/LICENSE) for more information.
