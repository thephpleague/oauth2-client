---
layout: default
permalink: /
title: oauth2-client
---

League/oauth2-client
======================

[![Gitter Chat](https://img.shields.io/badge/gitter-join_chat-brightgreen.svg?style=flat-square)](https://gitter.im/thephpleague/oauth2-client)
[![Source Code](https://img.shields.io/badge/source-thephpleague/oauth2--client-blue.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client)
[![Latest Version](https://img.shields.io/github/release/thephpleague/oauth2-client.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE)
[![Build Status](https://img.shields.io/github/workflow/status/thephpleague/oauth2-client/CI?label=CI&logo=github&style=flat-square)](https://github.com/thephpleague/oauth2-client/actions?query=workflow%3ACI)
[![Codecov Code Coverage](https://img.shields.io/codecov/c/gh/thephpleague/oauth2-client?label=codecov&logo=codecov&style=flat-square)](https://codecov.io/gh/thephpleague/oauth2-client)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth2-client.svg?style=flat-square)](https://packagist.org/packages/league/oauth2-client)

The OAuth 2.0 login flow, seen commonly around the web in the form of "Connect with Facebook/Google/etc." buttons, is a common integration added to web applications, but it can be tricky and tedious to do right. To help, we've created the `league/oauth2-client` package, which provides a base for integrating with various OAuth 2.0 providers, without overburdening your application with the concerns of [RFC 6749](http://tools.ietf.org/html/rfc6749).

Installation
-------------

This package establishes a convenient base of interfaces and abstract classes, allowing developers to create OAuth 2.0 clients that interface with a wide-variety of OAuth 2.0 providers.

> ⚠️ **Attention!** There are already many [official](/providers/league/) or [third-party](/providers/thirdparty/) provider clients available. Check these before using this base package. A client might already exist for your provider.

This base package also includes a `GenericProvider` class, which works out-of-the-box with many OAuth 2.0 providers who use [Bearer tokens](http://tools.ietf.org/html/rfc6750). If you would like to use the `GenericProvider` instead of one of the specific provider clients, you may require this package directly with [Composer](https://getcomposer.org):

~~~ bash
$ composer require league/oauth2-client
~~~

You do not need to require this package directly if using one of the [official](/providers/league/) or [third-party](/providers/thirdparty/) provider clients.
