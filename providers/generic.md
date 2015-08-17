---
layout: default
permalink: /providers/generic/
title: Generic Provider
---

# Generic Provider

The generic provider can be used to quickly connect to a provider that does not
have a specific implementation. All of the provider configuration is defined at
runtime and the resource owner implementation is unstructured.

~~~ php
$provider = new League\OAuth2\Client\Provider\GenericProvider([

    // Your client ID and secret provided by the other website,
    // allowing you to access information on behalf of their users.
    'clientId' => '{provider-client-id}',
    'clientSecret' => '{provider-client-secret}',

    // Where in your website should the other website redirect the
    // user after they authorize access?
    'redirectUri' => '{your-website-url-for-authorization}',

    // The URL at the other website that will handle authorization.
    'urlAuthorize' => '{provider-website-url-to-start-authorization}',

    // The URL at the other website where you may obtain an access token
    // after you have received an authorization code.
    'urlAccessToken' => '{provider-website-url-for-tokens}',

    // A URL at the other website that returns information about the
    // resource owner.
    'urlResourceOwnerDetails' => '{provider-website-url-for-resource-owner}',

]);
~~~

