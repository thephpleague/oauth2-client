---
layout: default
permalink: /
title: Introduction
---

# Introduction

[![Gitter Chat](https://img.shields.io/badge/gitter-join_chat-brightgreen.svg?style=flat-square)](https://gitter.im/thephpleague/oauth2-client)
[![Source Code](http://img.shields.io/badge/source-thephpleague/oauth2--client-blue.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client)
[![Latest Version](https://img.shields.io/github/release/thephpleague/oauth2-client.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/thephpleague/oauth2-client/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/oauth2-client)
[![HHVM Status](https://img.shields.io/hhvm/league/oauth2-client.svg?style=flat-square)](http://hhvm.h4cc.de/package/league/oauth2-client)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/thephpleague/oauth2-client/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth2-client/)
[![Coverage Status](https://img.shields.io/coveralls/thephpleague/oauth2-client/master.svg?style=flat-square)](https://coveralls.io/r/thephpleague/oauth2-client?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth2-client.svg?style=flat-square)](https://packagist.org/packages/league/oauth2-client)

## What is OAuth2 Client?

It makes it simple to integrate your application with [OAuth 2.0](http://oauth.net/2/) service providers.

We are all used to seeing those "Connect with Facebook/Google/etc." buttons around
the internet, and social network integration is an important feature of most web
applications these days. Many of these sites use an authentication and authorization
standard called OAuth 2.0 ([RFC 6749](http://tools.ietf.org/html/rfc6749)).

This OAuth 2.0 client library will work with any OAuth provider that conforms to the
OAuth 2.0 standard. Out-of-the-box, we provide a GenericProvider that may be used to
connect to any service provider that uses [Bearer tokens](http://tools.ietf.org/html/rfc6750).

Many service providers provide additional functionality above and beyond the OAuth 2.0
standard. For this reason, this library may be easily extended and wrapped to support
this additional behavior.

### Official Providers

Gateway | Composer Package | Maintainer
--- | --- | ---
[Facebook](https://github.com/thephpleague/oauth2-facebook) | `league/oauth2-facebook` | [Sammy Kaye Powers](https://github.com/sammyk)
[Github](https://github.com/thephpleague/oauth2-github) | `league/oauth2-github` | [Steven Maguire](https://github.com/stevenmaguire)
[Google](https://github.com/thephpleague/oauth2-google) | `league/oauth2-google` | [Woody Gilk](https://github.com/shadowhand)
[Instagram](https://github.com/thephpleague/oauth2-instagram) | `league/oauth2-instagram` | [Steven Maguire](https://github.com/stevenmaguire)
[LinkedIn](https://github.com/thephpleague/oauth2-linkedin) | `league/oauth2-linkedin` | [Steven Maguire](https://github.com/stevenmaguire)

A [number of unofficial providers](https://github.com/thephpleague/oauth2-client/blob/master/README.PROVIDERS.md)
are also available. If you would like to submit a provider to be included in this
list, please open a [pull request](https://github.com/thephpleague/oauth2-client/pulls).

## Questions?

OAuth2 Client was created by Alex Bilbie and is maintained by Ben Ramsey and a
[community of contributors](https://github.com/thephpleague/oauth2-client/graphs/contributors).
Find Alex and Ben on Twitter at [@alexbilbie](https://twitter.com/alexbilbie) and [@ramsey](https://twitter.com/ramsey).

There is also [Gitter](https://gitter.im/thephpleague/oauth2-client) chat for user
support and discussion.
