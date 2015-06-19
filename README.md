# OAuth 2.0 Client

[![Join the chat at https://gitter.im/thephpleague/oauth2-client](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/thephpleague/oauth2-client?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Build Status](https://travis-ci.org/thephpleague/oauth2-client.svg?branch=master)](https://travis-ci.org/thephpleague/oauth2-client)
[![Coverage Status](https://coveralls.io/repos/thephpleague/oauth2-client/badge.svg?branch=master)](https://coveralls.io/r/thephpleague/oauth2-client?branch=master)
[![Latest Stable Version](https://poser.pugx.org/league/oauth2-client/v/stable)](https://packagist.org/packages/league/oauth2-client)
[![Total Downloads](https://poser.pugx.org/league/oauth2-client/downloads)](https://packagist.org/packages/league/oauth2-client)
[![Latest Unstable Version](https://poser.pugx.org/league/oauth2-client/v/unstable)](https://packagist.org/packages/league/oauth2-client)
[![License](https://poser.pugx.org/league/oauth2-client/license)](https://packagist.org/packages/league/oauth2-client)

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


## Requirements

The following versions of PHP are supported.

* PHP 5.4
* PHP 5.5
* PHP 5.6
* PHP 7.0
* HHVM

## Usage

### Authorization Code Flow

*Note: This example code requires the Google+ API to be enabled in your developer console*

```php
$provider = new League\OAuth2\Client\Provider\<ProviderName>([
    'clientId'      => 'XXXXXXXX',
    'clientSecret'  => 'XXXXXXXX',
    'redirectUri'   => 'https://your-registered-redirect-uri/',
    'scopes'        => ['email', '...', '...'],
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->state;
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
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
    echo $token->accessToken;

    // Use this to get a new access token if the old one expires
    echo $token->refreshToken;

    // Unix timestamp of when the token will expire, and need refreshing
    echo $token->expires;
}
```

### Refreshing a Token

Once and as long as your application is authorized, you then only need to refresh an expired access token. To do so, simply reuse this refresh token from your data store to request a refresh.

```php
$provider = new League\OAuth2\Client\Provider\<ProviderName>([
    'clientId'      => 'XXXXXXXX',
    'clientSecret'  => 'XXXXXXXX',
    'redirectUri'   => 'https://your-registered-redirect-uri/',
]);

$grant = new \League\OAuth2\Client\Grant\RefreshToken();
$token = $provider->getAccessToken($grant, ['refresh_token' => $refreshToken]);
```


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

#### Managing LinkedIn Scopes

The LinkedIn provider included in this package does not include scopes by default. When creating your LinkedIn provider, you can specify the scopes your application may authorize.

```php
$provider = new League\OAuth2\Client\Provider\LinkedIn([
    'clientId'          => '{linkedin-client-id}',
    'clientSecret'      => '{linkedin-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
    'scopes'            => ['r_basicprofile','r_emailaddress'],
]);
```

At the time of authoring this documentation, the following scopes are available.

- r_basicprofile
- r_emailaddress
- rw_company_admin
- w_share

### Third-Party Providers

If you would like to support other providers, please make them available as a Composer package, then link to them
below.

These providers allow integration with other providers not supported by `oauth2-client`. They may require an older version
so please help them out with a pull request if you notice this.

- [Amazon](https://github.com/lemonstand/oauth2-amazon/)
- [Auth0](https://github.com/RiskioFr/oauth2-auth0)
- [Battle.net](https://packagist.org/packages/depotwarehouse/oauth2-bnet)
- [BookingSync](https://github.com/BookingSync/oauth2-bookingsync-php)
- [Clover](https://github.com/wheniwork/oauth2-clover)
- [Coinbase](https://github.com/openclerk/coinbase-oauth2)
- [Dropbox](https://github.com/pixelfear/oauth2-dropbox)
- [FreeAgent](https://github.com/CloudManaged/oauth2-freeagent)
- [Google Nest](https://github.com/JC5/nest-oauth2-provider)
- [Mail.ru](https://packagist.org/packages/aego/oauth2-mailru)
- [Meetup](https://github.com/howlowck/meetup-oauth2-provider)
- [Naver](https://packagist.org/packages/deminoth/oauth2-naver)
- [Odnoklassniki](https://packagist.org/packages/aego/oauth2-odnoklassniki)
- [Reddit](https://github.com/rtheunissen/oauth2-reddit)
- [Square](https://packagist.org/packages/wheniwork/oauth2-square)
- [Twitch.tv](https://github.com/tpavlek/oauth2-twitch)
- [Uber](https://github.com/stevenmaguire/oauth2-uber)
- [Vend](https://github.com/wheniwork/oauth2-vend)
- [Vkontakte](https://packagist.org/packages/j4k/oauth2-vkontakte)
- [Yandex](https://packagist.org/packages/aego/oauth2-yandex)
- [ZenPayroll](https://packagist.org/packages/wheniwork/oauth2-zenpayroll)
- [Envato](https://github.com/dilab/envato-oauth2-provider)

### Implementing your own provider

If you are working with an oauth2 service not supported out-of-the-box or by an existing package, it is quite simple to
implement your own. Simply extend `League\OAuth2\Client\Provider\AbstractProvider` and implement the required abstract
methods:

```php
abstract public function urlAuthorize();
abstract public function urlAccessToken();
abstract public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token);
abstract public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token);
```

Each of these abstract methods contain a docblock defining their expectations and typical behaviour. Once you have
extended this class, you can simply follow the example above using your new `Provider`.

#### Custom account identifiers in access token responses

Some OAuth2 Server implementations include a field in their access token response defining some identifier
for the user account that just requested the access token. In many cases this field, if present, is called "uid", but
some providers define custom identifiers in their response. If your provider uses a nonstandard name for the "uid" field,
when extending the AbstractProvider, in your new class, define a property `public $uidKey` and set it equal to whatever
your provider uses as its key. For example, Battle.net uses `accountId` as the key for the identifier field, so in that
provider you would add a property:

```php
public $uidKey = 'accountId';
```

### Client Packages

Some developers use this library as a base for their own PHP API wrappers, and that seems like a really great idea. It might make it slightly tricky to integrate their provider with an existing generic "OAuth 2.0 All the Things" login system, but it does make working with them easier.

- [Sniply](https://github.com/younes0/sniply)

## Install

Via Composer

``` bash
$ composer require league/oauth2-client
```

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/thephpleague/oauth2-client/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Alex Bilbie](https://github.com/alexbilbie)
- [Ben Corlett](https://github.com/bencorlett)
- [James Mills](https://github.com/jamesmills)
- [Phil Sturgeon](https://github.com/philsturgeon)
- [Tom Anderson](https://github.com/TomHAnderson)
- [All Contributors](https://github.com/thephpleague/oauth2-client/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE) for more information.
