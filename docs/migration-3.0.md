---
layout: default
permalink: /migration-3.0/
title: "Preparing for 3.0"
---

Preparing for 3.0
=================

Version 2.10 adds support for any [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client and [PSR-17](https://www.php-fig.org/psr/psr-17/) message factories. **Nothing breaks in 2.x**: Guzzle is still installed and is still the default client. The next major version will no longer require Guzzle, so that you can pick your own HTTP client.

## What to do now

* Pass your PSR-18 client with `setPsrHttpClient()` or the `psrHttpClient` collaborator. See [Using a PSR-18 HTTP client](/usage/).
* Stop relying on this library to install Guzzle. If your code uses `GuzzleHttp\*` directly, require `guzzlehttp/guzzle` yourself.
* Stop calling Guzzle-specific methods on `$provider->getHttpClient()` (`request()`, `getConfig()`, ...). Use your own client instance.
* Catch `Psr\Http\Client\ClientExceptionInterface` for network errors instead of `GuzzleHttp\Exception\GuzzleException`.
* Move `timeout`, `proxy` and `verify` settings to your own client.

## What will change in 3.0

| 2.x | 3.0 |
| --- | --- |
| `guzzlehttp/guzzle` and `guzzlehttp/psr7` are required | Not required. A PSR-18 client and PSR-17 factories are discovered or injected |
| `setHttpClient(GuzzleHttp\ClientInterface)` / `getHttpClient()` (deprecated) | `getHttpClient()` returns a PSR-18 client. `setHttpClient()` takes a PSR-18 client |
| `setPsrHttpClient()` / `getPsrHttpClient()` | Kept as deprecated aliases |
| `getAllowedClientOptions()` and the `timeout` / `proxy` / `verify` options | Removed. Configure your client directly |
| `@throws GuzzleException` | `Psr\Http\Client\ClientExceptionInterface` |
| `RequestFactory` builds Guzzle requests by default | Uses PSR-17 factories |

## Provider authors

Third-party providers should not depend on Guzzle through this library. Avoid overriding `setHttpClient()` with a Guzzle type hint, avoid `GuzzleHttp\*` classes in your code, and use `getResponse()`, `getParsedResponse()` and the request factory to talk to HTTP.
