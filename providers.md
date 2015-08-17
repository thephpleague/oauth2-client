---
layout: default
permalink: /providers/
title: Official Providers
---

# Official Providers

Each of the following providers are maintained by League members and are fully tested.

A [number of unofficial providers](https://github.com/thephpleague/oauth2-client/blob/master/README.PROVIDERS.md)
are also available. If you would like to submit a provider to be included in this
list, please open a [pull request](https://github.com/thephpleague/oauth2-client/pulls).

## Facebook

<https://developers.facebook.com/quickstarts/?platform=web>

~~~ php
$provider = new League\OAuth2\Client\Provider\Facebook([
    'clientId'          => '{facebook-app-id}',
    'clientSecret'      => '{facebook-app-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
    'graphApiVersion'   => 'v2.4',
]);
~~~

## Github

<https://github.com/settings/applications/new>

~~~ php
$provider = new League\OAuth2\Client\Provider\Github([
    'clientId'          => '{github-client-id}',
    'clientSecret'      => '{github-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);
~~~

## Google

<https://console.developers.google.com/project>

~~~ php
$provider = new League\OAuth2\Client\Provider\Google([
    'clientId'     => '{google-app-id}',
    'clientSecret' => '{google-app-secret}',
    'redirectUri'  => 'https://example.com/callback-url',
    'hostedDomain' => 'example.com',
]);
~~~

## Instagram

<https://instagram.com/developer/clients/manage/>

~~~ php
$provider = new League\OAuth2\Client\Provider\Instagram([
    'clientId'          => '{instagram-client-id}',
    'clientSecret'      => '{instagram-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);
~~~

## LinkedIn

<https://www.linkedin.com/secure/developer?newapp=>

~~~ php
$provider = new League\OAuth2\Client\Provider\LinkedIn([
    'clientId'          => '{linkedin-client-id}',
    'clientSecret'      => '{linkedin-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);
~~~
