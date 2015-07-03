# OAuth 2.0 Client

## Provider Guide

New providers can be created by cloning the layout of an existing package. When choosing a name for your package, please don’t use the `league` vendor prefix, as this implies that it is officially supported.

You should use your own username as the vendor prefix, and prepend `oauth2-` to the package name to make it clear that your package works with OAuth2 Client. For example, if your GitHub username was santa, and you were implementing the giftpay OAuth2 library, a good name for your composer package would be `santa/oauth2-giftpay`.

### Implementing your own provider

If you are working with an oauth2 service not supported out-of-the-box or by an existing package, it is quite simple to implement your own. Simply extend `League\OAuth2\Client\Provider\AbstractProvider` and implement the required abstract methods:

```php
abstract public function urlAuthorize();
abstract public function urlAccessToken();
abstract public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token);
abstract public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token);
```

Each of these abstract methods contain a docblock defining their expectations and typical behaviour. Once you have
extended this class, you can simply follow the example above using your new `Provider`.

### Custom account identifiers in access token responses

Some OAuth2 Server implementations include a field in their access token response defining some identifier
for the user account that just requested the access token. In many cases this field, if present, is called "uid", but
some providers define custom identifiers in their response. If your provider uses a nonstandard name for the "uid" field,
when extending the AbstractProvider, in your new class, define a property `public $uidKey` and set it equal to whatever
your provider uses as its key. For example, Battle.net uses `accountId` as the key for the identifier field, so in that
provider you would add a property:

```php
public $uidKey = 'accountId';
```

### Make your gateway official

If you want to transfer your provider to the `thephpleague` GitHub organization and add it to the list of officially supported providers, please open a pull request on the thephpleague/oauth2-client package. Before new providers will be accepted, they must have 100% unit test code coverage, and follow the conventions and code style used in other OAuth2 Client providers.
