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
[![Build Status](https://img.shields.io/travis/thephpleague/oauth2-client/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/oauth2-client)
[![Coverage Status](https://img.shields.io/coveralls/thephpleague/oauth2-client/master.svg?style=flat-square)](https://coveralls.io/r/thephpleague/oauth2-client?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth2-client.svg?style=flat-square)](https://packagist.org/packages/league/oauth2-client)

The OAuth2 login flow, seen commonly around the web in the form of "Connect with Facebook/Google/etc." buttons, is a very
common integration added to web applications, that can be a bit tricky and tedious to do right.

The `league/oauth2-client` package provides an easy base for integration with various OAuth 2.0 Providers around the web,
without overburdening your application with the concerns of [RFC 6749](http://tools.ietf.org/html/rfc6749).

Installation
-------------

This package establishes a convenient base of interfaces and abstract classes that allow developers to easily create
OAuth2 Clients to interface with a wide-variety of providers on the web. There are many clients that exist that you may
use on the [Third-Party Providers](/providers/thirdparty) page.

This base package also includes a `GenericProvider` that can be used with any OAuth 2.0 Server that uses [Bearer tokens](http://tools.ietf.org/html/rfc6750).

If you would like to simply use that, you can install this package via composer.

~~~ bash
$ composer require league/oauth2-client
~~~


