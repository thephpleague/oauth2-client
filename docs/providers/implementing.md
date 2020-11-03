---
layout: default
title: Implementing a Provider Client
permalink: /providers/implementing/
---

Implementing a Provider Client
==============================

> 💡 **TIP** You may create new provider clients by copying the layout of an existing package. See the [first party](/providers/league/) and [third party](/providers/thirdparty/) provider clients for good examples.

> ⚠️ **Attention!** When choosing a name for your package, please don’t use the `league` vendor prefix or the `League` vendor namespace, as this implies it is officially supported. You should use your own username as the vendor prefix, and prepend `oauth2-` to the package name to make it clear your package works with `league/oauth2-client`.
>
> For example, if your GitHub username is *santa*, and you are implementing the *giftpay* OAuth 2.0 client library, a good name for your Composer package would be `santa/oauth2-giftpay`.

If you are working with an OAuth 2.0 service provider not supported out-of-the-box or by an existing package, you may implement your own. To do so, extend [`League\OAuth2\Client\Provider\AbstractProvider`](https://github.com/thephpleague/oauth2-client/blob/master/src/Provider/AbstractProvider.php) and implement the required abstract methods:

```php
public function getBaseAuthorizationUrl();
public function getBaseAccessTokenUrl(array $params);
public function getResourceOwnerDetailsUrl(AccessToken $token);
protected function getDefaultScopes();
protected function checkResponse(ResponseInterface $response, $data);
protected function createResourceOwner(array $response, AccessToken $token);
```

Each of these abstract methods has a comment block defining their expectations and typical behavior. Once you have extended this class, you may follow the [basic usage example](/usage/) using your new provider client class.

If you wish to use your provider client class to make authenticated requests to the provider, you will also need to define how you provide the token to the service. If this is done via headers, you should override this method:

```php
protected function getAuthorizationHeaders($token = null);
```

This package includes a trait for implementing [Bearer authorization](https://tools.ietf.org/html/rfc6750). To use the trait, include it in your provider client class with a `use` statement:

```php
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

class SomeProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /** ... **/
}
```

Resource Owner Identifiers in Access Token Responses
-----------------------------------------------------

In services where the resource owner is a person, the resource owner is sometimes referred to as an *end-user*.

We have abstracted away as much of the resource owner details as possible, since these are not part of the OAuth 2.0 specification and are very specific to each service provider. This provides greater flexibility to each provider, allowing them to handle the implementation details for resource owners.

As such, the `AbstractProvider` does not specify an access token resource owner identifier. Since OAuth 2.0 does not define the resource owner identifier, the `AbstractProvider` cannot understand what to do with a resource owner identifier if it receives one. The provider client class is responsible for setting the name of this identifier, using the `ACCESS_TOKEN_RESOURCE_OWNER_ID` constant. The name is different for each provider, so check your provider's documentation.

```php
/**
 * Name of the resource owner identifier field that is
 * present in the access token response (if applicable)
 */
const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;
```

After setting `ACCESS_TOKEN_RESOURCE_OWNER_ID` on your provider client class, the `AccessToken` returned from `AbstractProvider::getAccessToken()` will have its `$resourceOwnerId` property set, which you may retrieve by calling `AccessToken::getResourceOwnerId()`.

Next, implement the `AbstractProvider::createResourceOwner()` method. This method accepts as parameters a response array and an `AccessToken` object. You may use this information to request resource owner details from your service, returning an object that implements [`League\OAuth2\Client\Provider\ResourceOwnerInterface`](https://github.com/thephpleague/oauth2-client/blob/master/src/Provider/ResourceOwnerInterface.php). `AbstractProvider::getResourceOwner()` returns this object.

Make It Available
------------------

If you find a package for a certain provider useful, chances are someone else will too! Make your package available by putting it on [Packagist](https://packagist.org) and [GitHub](https://github.com). After it's available, submit a pull request to the [oauth2-client](https://github.com/thephpleague/oauth2-client) repository, adding your provider client to the [provider client list](https://github.com/thephpleague/oauth2-client/blob/master/docs/providers/thirdparty.md).

Make It Official
----------------

If you want to transfer your provider client to the `thephpleague` GitHub organization and add it to the list of officially-supported provider clients, please open a pull request on the thephpleague/oauth2-client package. Before new provider clients will be accepted, they must have 100% unit test code coverage and follow the conventions and code style used in the other [official PHP League OAuth 2.0 Client provider clients](/providers/league/).
