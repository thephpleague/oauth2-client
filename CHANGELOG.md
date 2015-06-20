# OAuth 2.0 Client Changelog

## 0.12.1

_Released: 2015-06-20_

* FIX: Scope separators for LinkedIn and Instagram are now correctly a single space

## 0.12.0

_Released: 2015-06-15_

* BREAK: LinkedIn Provider: Default scopes removed from LinkedIn Provider. See "[Managing LinkedIn Scopes](https://github.com/thephpleague/oauth2-client/blob/9cea9864c2e89bce1b922d1e37ba5378b3b0b264/README.md#managing-linkedin-scopes)" in the README for information on how to set scopes. See [#327](https://github.com/thephpleague/oauth2-client/pull/327) and [#307](https://github.com/thephpleague/oauth2-client/pull/307) for details on this change.
* FIX: LinkedIn Provider: A scenario existed in which `publicProfileUrl` was not set, generating a PHP notice; this has been fixed.
* FIX: Instagram Provider: Fixed scope separator.
* Documentation updates and corrections.


## 0.11.0

_Released: 2015-04-25_

* Identity Provider: Better handling of error responses
* Documentation updates


## 0.10.1

_Released: 2015-04-02_

* FIX: Invalid JSON triggering fatal error
* FIX: Sending headers along with auth `getAccessToken()` requests
* Now running Travis CI tests on PHP 7
* Documentation updates


## 0.10.0

_Released: 2015-03-10_

* Providers: Added `getHeaders()` to ProviderInterface and updated AbstractProvider to provide the method
* Providers: Updated all bundled providers to support new `$authorizationHeader` property
* Identity Provider: Update IDPException to account for empty strings
* Identity Provider: Added `getResponseBody()` method to IDPException
* Documentation updates, minor bug fixes, and coding standards fixes


## 0.9.0

_Released: 2015-02-24_

* Add `AbstractProvider::prepareAccessTokenResult()` to provide additional token response preparation to providers
* Remove custom provider code from AccessToken
* Add links to README for Dropbox and Square providers


## 0.8.1

_Released: 2015-02-12_

* Allow `approval_prompt` to be set by providers. This fixes an issue where some providers have problems if the `approval_prompt` is present in the query string.


## 0.8.0

_Released: 2015-02-10_

* Facebook Provider: Upgrade to Graph API v2.2
* Google Provider: Add `access_type` parameter for Google authorization URL
* Get a more reliable response body on errors


## 0.7.2

_Released: 2015-02-03_

* GitHub Provider: Fix regression
* Documentation updates


## 0.7.1

_Released: 2015-01-06_

* Google Provider: fixed issue where Google API was not returning the user ID


## 0.7.0

_Released: 2014-12-29_

* Improvements to Provider\AbstractProvider (addition of `userUid()`, `userEmail()`, and `userScreenName()`)
* GitHub Provider: Support for GitHub Enterprise
* GitHub Provider: Methods to allow fetching user email addresses
* Google Provider: Updated scopes and endpoints to remove deprecated values
* Documentation updates, minor bug fixes, and coding standards fixes


## 0.6.0

_Released: 2014-12-03_

* Added ability to specify a redirect handler for providers through use of a callback (see [Provider\AbstractProvider::setRedirectHandler()](https://github.com/thephpleague/oauth2-client/blob/55de45401eaa21f53c0b2414091da6f3b0f3fcb7/src/Provider/AbstractProvider.php#L314-L317))
* Updated authorize and token URLs for the Microsoft provider; the old URLs had been phased out and were no longer working (see #146)
* Increased test coverage
* Documentation updates, minor bug fixes, and coding standards fixes


## 0.5.0

_Released: 2014-11-28_

* Added `ClientCredentials` and `Password` grants
* Added support for providers to set their own `uid` parameter key name
* Added support for Google's `hd` (hosted domain) parameter
* Added support for providing a custom `state` parameter to the authorization URL
* LinkedIn `pictureUrl` is now an optional response element
* Added Battle.net provider package link to README
* Added Meetup provider package link to README
* Added `.gitattributes` file
* Increased test coverage
* A number of documentation fixes, minor bug fixes, and coding standards fixes


## 0.4.0

_Released: 2014-10-28_

* Added  `ProviderInterface` and removed `IdentityProvider`.
* Expose generated state to allow for CSRF validation.
* Renamed `League\OAuth2\Client\Provider\User` to `League\OAuth2\Client\Entity\User`.
* Entity: User: added `gender` and `locale` properties
* Updating logic for populating the token expiration time.


## 0.3.0

_Released: 2014-04-26_

* This release made some huge leaps forward, including 100% unit-coverage and a bunch of new features.


## 0.2.0

_Released: 2013-05-28_

* No release notes available.


## 0.1.0

_Released: 2013-05-25_

* Initial release.
