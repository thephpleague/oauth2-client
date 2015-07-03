# OAuth 2.0 Client

## Provider Guide

New providers may be created by copying the layout of an existing package. See
the [list of providers](README.PROVIDERS.md) for good examples.

When choosing a name for your package, please don’t use the `league` vendor
prefix, as this implies that it is officially supported. You should use your own
username as the vendor prefix, and prepend `oauth2-` to the package name to make
it clear that your package works with OAuth2 Client. For example, if your GitHub
username was "santa," and you were implementing the "giftpay" OAuth2 library, a
good name for your composer package would be `santa/oauth2-giftpay`.

### Implementing your own provider

If you are working with an oauth2 service not supported out-of-the-box or by an
existing package, it is quite simple to implement your own. Simply extend
[`League\OAuth2\Client\Provider\AbstractProvider`](src/Provider/AbstractProvider.php)
and implement the required abstract methods:

```php
abstract public function getBaseAuthorizationUrl();
abstract public function getBaseAccessTokenUrl(array $params);
abstract public function getUserDetailsUrl(AccessToken $token);
abstract protected function getDefaultScopes();
abstract protected function checkResponse(ResponseInterface $response, $data);
abstract protected function createUser(array $response, AccessToken $token);
```

Each of these abstract methods contain a docblock defining their expectations
and typical behavior. Once you have extended this class, you can simply follow
the [usage example in the README](README.md#usage) using your new `Provider`.

### Account identifiers in access token responses

We have decided to abstract away as much of the user details as possible, since
these are not part of the OAuth 2.0 specification and are very specific to each
service provider. This provides greater flexibility to each provider, allowing
them to handle the implementation details for service users.

The `AbstractProvider` does not specify an access token user identifier. It is
the responsibility of the provider class to set the `ACCESS_TOKEN_UID` constant
to the string value of the key used in the access token response to identify the
user.

```php
/**
 * @var string Key used in the access token response to identify the user.
 */
const ACCESS_TOKEN_UID = null;
```

Once this is set on your provider, when calling `AbstractProvider::getAccessToken()`,
the `AccessToken` returned will have its `$uid` property set, which you may
retrieve by calling `AccessToken::getUid()`.

The next step is to implement the `AbstractProvider::createUser()` method. This
method accepts as parameters a response array and an `AccessToken`. You may use
this information in order to request user details from your service and
construct and return an object that implements
[`League\OAuth2\Client\Provider\UserInterface`](src/Provider/UserInterface.php).
This object is returned when calling `AbstractProvider::getUser()`.

### Make your gateway official

If you want to transfer your provider to the `thephpleague` GitHub organization
and add it to the list of officially supported providers, please open a pull
request on the thephpleague/oauth2-client package. Before new providers will be
accepted, they must have 100% unit test code coverage, and follow the
conventions and code style used in other OAuth2 Client providers.
