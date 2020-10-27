---
layout: default
title: Implementing Your Own Provider
permalink: /providers/implementing/
---

Implementing a Provider
========================

> Hint: New providers may be created by copying the layout of an existing package. See
the [first party](/providers/league) and [third party](/providers/thirdparty) providers for good examples.

When choosing a name for your package, please don’t use the `league` vendor
prefix, as this implies that it is officially supported. You should use your own
username as the vendor prefix, and prepend `oauth2-` to the package name to make
it clear that your package works with OAuth2 Client. For example, if your GitHub
username was "santa," and you were implementing the "giftpay" OAuth2 library, a
good name for your composer package would be `santa/oauth2-giftpay`.

If you are working with an oauth2 service not supported out-of-the-box or by an
existing package, it is quite simple to implement your own. Simply extend
[`League\OAuth2\Client\Provider\AbstractProvider`](https://github.com/thephpleague/oauth2-client/blob/master/src/Provider/AbstractProvider.php)
and implement the required abstract methods:

~~~ php
<?php

abstract public function getBaseAuthorizationUrl();
abstract public function getBaseAccessTokenUrl(array $params);
abstract public function getResourceOwnerDetailsUrl(AccessToken $token);
abstract protected function getDefaultScopes();
abstract protected function checkResponse(ResponseInterface $response, $data);
abstract protected function createResourceOwner(array $response, AccessToken $token);
~~~

Each of these abstract methods contain a docblock defining their expectations
and typical behavior. Once you have extended this class, you can simply follow
the [basic usage example](/usage) using your new `Provider`.

Resource owner identifiers in access token responses
-----------------------------------------------------

In services where the resource owner is a person, the resource owner is sometimes
referred to as an end-user.

We have decided to abstract away as much of the resource owner details as possible,
since these are not part of the OAuth 2.0 specification and are very specific to each
service provider. This provides greater flexibility to each provider, allowing
them to handle the implementation details for resource owners.

The `AbstractProvider` does not specify an access token resource owner identifier. It is
the responsibility of the provider class to set the `ACCESS_TOKEN_RESOURCE_OWNER_ID` constant
to the string value of the key used in the access token response to identify the
resource owner.

~~~ php
<?php

/**
 * @var string Key used in the access token response to identify the resource owner.
 */
const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;
~~~

Once this is set on your provider, when calling `AbstractProvider::getAccessToken()`,
the `AccessToken` returned will have its `$resourceOwnerId` property set, which you may
retrieve by calling `AccessToken::getResourceOwnerId()`.

The next step is to implement the `AbstractProvider::createResourceOwner()` method. This
method accepts as parameters a response array and an `AccessToken`. You may use
this information in order to request resource owner details from your service and
construct and return an object that implements
[`League\OAuth2\Client\Provider\ResourceOwnerInterface`](https://github.com/thephpleague/oauth2-client/blob/master/src/Provider/ResourceOwnerInterface.php).
This object is returned when calling `AbstractProvider::getResourceOwner()`.

Make it available
------------------

If you find a package for a certain provider useful, chances are someone else will too! Make your package available by
putting it on [packagist](https://packagist.org) and [GitHub](https://github.com)! After it's available, submit a pull request
to the [oauth2-client](https://github.com/thephpleague/oauth2-client) repository adding your provider to the provider list.

Make your gateway official
---------------------------

If you want to transfer your provider to the `thephpleague` GitHub organization
and add it to the list of officially supported providers, please open a pull
request on the thephpleague/oauth2-client package. Before new providers will be
accepted, they must have 100% unit test code coverage, and follow the
conventions and code style used in other OAuth2 Client providers.
