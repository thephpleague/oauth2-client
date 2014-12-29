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
    'redirectUri'   =>  'https://your-registered-redirect-uri/',
    'scopes' => array('email', '...', '...'),
));

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
    echo $token->accessToken;

    // Use this to get a new access token if the old one expires
    echo $token->refreshToken;

    // Number of seconds until the access token will expire, and need refreshing
    echo $token->expires;
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

These providers allow integration with other providers not supported by `oauth2-client`. They may require an older version
so please help them out with a pull request if you notice this.

- [Battle.net](https://packagist.org/packages/depotwarehouse/oauth2-bnet)
- [Mail.ru](https://packagist.org/packages/aego/oauth2-mailru)
- [Meetup](https://github.com/howlowck/meetup-oauth2-provider)
- [Odnoklassniki](https://packagist.org/packages/aego/oauth2-odnoklassniki)
- [Yandex](https://packagist.org/packages/aego/oauth2-yandex)
- [Vkontakte](https://packagist.org/packages/j4k/oauth2-vkontakte)
- [Naver](https://packagist.org/packages/deminoth/oauth2-naver)

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
