---
layout: default
title: Official Provider Clients
permalink: /providers/league/
---

Official Provider Clients
=========================

You may use [Composer](https://getcomposer.org) to install any of these packages:

~~~ bash
$ composer require league/<package-name>
~~~

Gateway | Composer Package | Maintainer
--- | --- | ---
[Facebook](https://github.com/thephpleague/oauth2-facebook) | league/oauth2-facebook | [Sammy Kaye Powers](https://github.com/sammyk)
[Github](https://github.com/thephpleague/oauth2-github) | league/oauth2-github | [Steven Maguire](https://github.com/stevenmaguire)
[Google](https://github.com/thephpleague/oauth2-google) | league/oauth2-google | [Woody Gilk](https://github.com/shadowhand)
[Instagram](https://github.com/thephpleague/oauth2-instagram) | league/oauth2-instagram | [Steven Maguire](https://github.com/stevenmaguire)
[LinkedIn](https://github.com/thephpleague/oauth2-linkedin) | league/oauth2-linkedin | [Steven Maguire](https://github.com/stevenmaguire)

Due to the vast (and ever-growing) number of OAuth 2.0 services that exist, it is impossible to maintain first-party support for every OAuth 2.0 provider without damaging our ability to make this package the best it can be. Therefore, we will only accept very high-quality provider clients into the `league` namespace on a case-by-case basis. We list criteria for acceptance on the  [provider client implementation guide](/providers/implementing/).

There are a [large number of community packages](/providers/thirdparty/) that integrate with other providers.
