# OAuth 2.0 Client

[![Build Status](https://travis-ci.org/thephpleague/oauth2-client.png?branch=master)](https://travis-ci.org/thephpleague/oauth2-client)
[![Coverage Status](https://coveralls.io/repos/thephpleague/oauth2-client/badge.png)](https://coveralls.io/r/thephpleague/oauth2-client)
[![Total Downloads](https://poser.pugx.org/league/oauth2-client/downloads.png)](https://packagist.org/packages/league/oauth2-client)
[![Latest Stable Version](https://poser.pugx.org/league/oauth2-client/v/stable.png)](https://packagist.org/packages/league/oauth2-client)

This package makes it stupidly simple to integrate your application with OAuth 2.0 identity providers. 

Everyone is used to seeing those "Connect with Facebook/Google/etc" buttons around the Internet and social network
integration is an important feature of most web-apps these days. Many of these sites use an Authentication and Authorization standard called OAuth 2.0.

It will work with any OAuth 2.0 provider (be it an OAuth 2.0 Server for your own API or Facebook) and provides support
for popular systems out of the box. This package abstracts out some of the subtle but important differences between various providers, handles access tokens and refresh tokens, and allows you easy access to profile information on these other sites.

This package is compliant with [PSR-1][], [PSR-2][] and [PSR-4][]. If you notice compliance oversights, please send 
a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md


### Built-In Providers

This package currently has built-in support for:

- Eventbrite
- Facebook
- Github
- Google
- Instagram
- LinkedIn
- Microsoft

These are as many OAuth 2 services as we plan to support officially. Maintaining a wide selection of providers
damages our ability to make this package the best it can be, especially as we progress towards v1.0. 

### Third-Party Providers

If you would like to support other providers, please make them available as a Composer package, then link to them
below.

These providers allow integration with other providers not included with `oauth2-client`. They may require an older version
so please help them out with a pull request if you notice this. 

- [box.com](https://github.com/StukiOrg/BoxOAuth2Client-PHP)
- add more providers

##Installation with Composer

```sh
$ php composer.phar require league/oauth2-client:~0.3
```
For composer documentation, please refer to [getcomposer.org](http://getcomposer.org/).

## Requirements

The following versions of PHP are supported. 

* PHP 5.4
* PHP 5.5
* PHP 5.6
* HHVM

## Usage

### Authorization Code Flow

```php
$provider = new League\OAuth2\Client\Provider\<ProviderName>(array(
    'clientId'  =>  'XXXXXXXX',
    'clientSecret'  =>  'XXXXXXXX',
    'redirectUri'   =>  'https://your-registered-redirect-uri/'
));

if ( ! isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    header('Location: '.$provider->getAuthorizationUrl());
    exit;

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // If you are using Eventbrite you will need to add the grant_type parameter (see below)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
        'grant_type' => 'authorization_code'
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $userDetails = $provider->getUserDetails($token);

        // Use these details to create a new profile
        printf('Hello %s!', $userDetails->firstName);

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->access_token;
    
    // Use this to get a new access token if the old one expires
    echo $token->refresh_token;

    // Number of seconds until the access token will expire, and need refreshing
    echo $token->expires_in;
}
```

### Refreshing a Token

```php
$provider = new League\OAuth2\Client\Provider\<ProviderName>(array(
    'clientId'  =>  'XXXXXXXX',
    'clientSecret'  =>  'XXXXXXXX',
    'redirectUri'   =>  'https://your-registered-redirect-uri/'
));

$grant = new \League\OAuth2\Client\Grant\RefreshToken();
$token = $provider->getAccessToken($grant, ['refresh_token' => $refreshToken]);
```

## Testing

``` bash
$ phpunit
```

## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE) for more information.
