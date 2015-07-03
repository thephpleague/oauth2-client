# OAuth 2.0 Client

[![Gitter Chat](https://img.shields.io/badge/gitter-join_chat-brightgreen.svg?style=flat-square)](https://gitter.im/thephpleague/oauth2-client)
[![Source Code](http://img.shields.io/badge/source-thephpleague/oauth2--client-blue.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client)
[![Latest Version](https://img.shields.io/github/release/thephpleague/oauth2-client.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/thephpleague/oauth2-client/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/oauth2-client)
[![HHVM Status](https://img.shields.io/hhvm/league/oauth2-client.svg?style=flat-square)](http://hhvm.h4cc.de/package/league/oauth2-client)
[![Coverage Status](https://img.shields.io/coveralls/thephpleague/oauth2-client/master.svg?style=flat-square)](https://coveralls.io/r/thephpleague/oauth2-client?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth2-client.svg?style=flat-square)](https://packagist.org/packages/league/oauth2-client)

This package makes it simple to integrate your application with OAuth 2.0 identity providers.

Everyone is used to seeing those "Connect with Facebook/Google/etc" buttons around the Internet and social network
integration is an important feature of most web-apps these days. Many of these sites use an Authentication and Authorization standard called OAuth 2.0.

It will work with any OAuth 2.0 provider (be it an OAuth 2.0 Server for your own API or Facebook) and provides support
for popular systems out of the box. This package abstracts out some of the subtle but important differences between various providers, handles access tokens and refresh tokens, and allows you easy access to profile information on these other sites.

This package is compliant with [PSR-1][], [PSR-2][], [PSR-4][], and [PSR-7][]. If you notice compliance oversights,
please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-7]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md


## Requirements

The following versions of PHP are supported.

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

Once your application is authorized, you can refresh an expired token using a refresh token rather than going through the entire process of obtaining a brand new token. To do so, simply reuse this refresh token from your data store to request a refresh.

```php
$provider = new League\OAuth2\Client\Provider\<ProviderName>([
    'clientId'      => 'XXXXXXXX',
    'clientSecret'  => 'XXXXXXXX',
    'redirectUri'   => 'https://your-registered-redirect-uri/',
]);

$grant = new \League\OAuth2\Client\Grant\RefreshToken();
$token = $provider->getAccessToken($grant, ['refresh_token' => $refreshToken]);
```

## Providers

All providers must extend [AbstractProvider](https://github.com/thephpleague/oauth2-client/blob/master/src/Provider/AbstractProvider.php), and implement the declared abstract methods.

The following providers are available:

### Official providers

These are as many OAuth 2 services as we plan to support officially. Maintaining a wide selection of providers
damages our ability to make this package the best it can be, especially as we progress towards v1.0.

Gateway | Composer Package | Maintainer
--- | --- | ---
[Facebook](https://github.com/thephpleague/oauth2-facebook) | league/oauth2-facebook | [Sammy Kaye Powers](https://github.com/sammyk)
[Github](https://github.com/thephpleague/oauth2-github) | league/oauth2-github | [Steven Maguire](https://github.com/stevenmaguire)
[Google](https://github.com/thephpleague/oauth2-google) | league/oauth2-google | [Woody Gilk](https://github.com/shadowhand)
[Instagram](https://github.com/thephpleague/oauth2-instagram) | league/oauth2-instagram | [Steven Maguire](https://github.com/stevenmaguire)
[LinkedIn](https://github.com/thephpleague/oauth2-linkedin) | league/oauth2-linkedin | [Steven Maguire](https://github.com/stevenmaguire)

### Third party providers

If you would like to support other providers, please make them available as a Composer package, then link to them
below.

These providers allow integration with other providers not supported by `oauth2-client`. They may require an older version
so please help them out with a pull request if you notice this.

Gateway | Composer Package | Maintainer
--- | --- | ---
[Amazon](https://github.com/lemonstand/oauth2-amazon/) | lemonstand/oauth2-amazon | [LemonStand](https://github.com/lemonstand)
[Auth0](https://github.com/RiskioFr/oauth2-auth0) | riskio/oauth2-auth0 | [Riskio](https://github.com/RiskioFr)
[Battle.net](https://github.com/tpavlek/oauth2-bnet) | depotwarehouse/oauth2-bnet | [Troy Pavlek](https://github.com/tpavlek)
[BookingSync](https://github.com/BookingSync/oauth2-bookingsync-php) | bookingsync/oauth2-bookingsync-php | [BookingSync](https://github.com/BookingSync)
[Clover](https://github.com/wheniwork/oauth2-clover) | wheniwork/oauth2-clover | [When I Work](https://github.com/wheniwork)
[Coinbase](https://github.com/openclerk/coinbase-oauth2) | openclerk/coinbase-oauth2 | [Openclerk](https://github.com/openclerk)
[Dropbox](https://github.com/pixelfear/oauth2-dropbox) | pixelfear/oauth2-dropbox | [Jason Varga](https://github.com/jasonvarga)
[Envato](https://github.com/dilab/envato-oauth2-provider) | dilab/envato-oauth2-provider | [Xu Ding](https://github.com/dilab)
[Eventbrite](https://github.com/stevenmaguire/oauth2-eventbrite) | stevenmaguire/oauth2-eventbrite | [Steven Maguire](https://github.com/stevenmaguire)
[FreeAgent](https://github.com/CloudManaged/oauth2-freeagent) | cloudmanaged/oauth2-freeagent | [Israel Sotomayor](https://github.com/zot24)
[Google Nest](https://github.com/JC5/nest-oauth2-provider) | grumpydictator/nest-oauth2-provider | [James Cole](https://github.com/JC5)
[Mail.ru](https://packagist.org/packages/aego/oauth2-mailru) | aego/oauth2-mailru | [Alexey](https://github.com/rakeev)
[Meetup](https://github.com/howlowck/meetup-oauth2-provider) | howlowck/meetup-oauth2-provider | [Hao Luo](https://github.com/howlowck)
[Microsoft](https://github.com/stevenmaguire/oauth2-microsoft) | stevenmaguire/oauth2-microsoft | [Steven Maguire](https://github.com/stevenmaguire)
[Naver](https://packagist.org/packages/deminoth/oauth2-naver) | deminoth/oauth2-naver | [SangYeob Bono Yu](https://github.com/deminoth)
[Odnoklassniki](https://packagist.org/packages/aego/oauth2-odnoklassniki) | aego/oauth2-odnoklassniki | [Alexey](https://github.com/rakeev)
[Reddit](https://github.com/rtheunissen/oauth2-reddit) | rtheunissen/oauth2-reddit | [Rudi Theunissen](https://github.com/rtheunissen)
[Spotify](https://packagist.org/packages/audeio/spotify-web-api) | audeio/spotify-web-api | [Jonjo McKay](https://github.com/jonjomckay)
[Square](https://packagist.org/packages/wheniwork/oauth2-square) | wheniwork/oauth2-square | [Woody Gilk](https://github.com/shadowhand)
[Twitch.tv](https://github.com/tpavlek/oauth2-twitch) | depotwarehouse/oauth2-twitch | [Troy Pavlek](https://github.com/tpavlek)
[Uber](https://github.com/stevenmaguire/oauth2-uber) | stevenmaguire/oauth2-uber | [Steven Maguire](https://github.com/stevenmaguire)
[Vend](https://github.com/wheniwork/oauth2-vend) | wheniwork/oauth2-vend | [When I Work](https://github.com/wheniwork)
[Vkontakte](https://github.com/j4k/oauth2-vkontakte) | j4k/oauth2-vkontakte | [Jack W](https://github.com/j4k)
[Yandex](https://packagist.org/packages/aego/oauth2-yandex) | aego/oauth2-yandex | [Alexey](https://github.com/rakeev)
[ZenPayroll](https://packagist.org/packages/wheniwork/oauth2-zenpayroll) | wheniwork/oauth2-zenpayroll | [Woody Gilk](https://github.com/shadowhand)

### Build your own providers

New providers can be created by cloning the layout of an existing package. When choosing a name for your package, please don’t use the `league` vendor prefix, as this implies that it is officially supported.

You should use your own username as the vendor prefix, and prepend `oauth2-` to the package name to make it clear that your package works with OAuth2 Client. For example, if your GitHub username was santa, and you were implementing the giftpay OAuth2 library, a good name for your composer package would be `santa/oauth2-giftpay`.

#### Implementing your own provider

If you are working with an oauth2 service not supported out-of-the-box or by an existing package, it is quite simple to implement your own. Simply extend `League\OAuth2\Client\Provider\AbstractProvider` and implement the required abstract methods:

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

#### Make your gateway official

If you want to transfer your provider to the `thephpleague` GitHub organization and add it to the list of officially supported providers, please open a pull request on the thephpleague/oauth2-client package. Before new providers will be accepted, they must have 100% unit test code coverage, and follow the conventions and code style used in other OAuth2 Client providers.


## Client Packages

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
- [Ben Ramsey](https://github.com/ramsey)
- [James Mills](https://github.com/jamesmills)
- [Phil Sturgeon](https://github.com/philsturgeon)
- [Rudi Theunissen](https://github.com/rtheunissen)
- [Tom Anderson](https://github.com/TomHAnderson)
- [Woody Gilk](https://github.com/shadowhand)
- [All Contributors](https://github.com/thephpleague/oauth2-client/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE) for more information.
