# OAuth 2.0 Client

## Provider Client Libraries

All providers must extend [AbstractProvider](https://github.com/thephpleague/oauth2-client/blob/master/src/Provider/AbstractProvider.php), and implement the declared abstract methods.

The following providers are available:

### Official PHP League providers

These are as many OAuth 2 services as we plan to support officially. Maintaining a wide selection of providers
damages our ability to make this package the best it can be.

Gateway | Composer Package | Maintainer
--- | --- | ---
[Facebook](https://github.com/thephpleague/oauth2-facebook) | league/oauth2-facebook | [Sammy Kaye Powers](https://github.com/sammyk)
[Github](https://github.com/thephpleague/oauth2-github) | league/oauth2-github | [Steven Maguire](https://github.com/stevenmaguire)
[Google](https://github.com/thephpleague/oauth2-google) | league/oauth2-google | [Woody Gilk](https://github.com/shadowhand)
[Instagram](https://github.com/thephpleague/oauth2-instagram) | league/oauth2-instagram | [Steven Maguire](https://github.com/stevenmaguire)
[LinkedIn](https://github.com/thephpleague/oauth2-linkedin) | league/oauth2-linkedin | [Steven Maguire](https://github.com/stevenmaguire)

### Third party providers

If you would like to support other providers, please make them available as a Composer package, then link to them
below.

These providers allow integration with other providers not supported by `oauth2-client`. They may require an older version
so please help them out with a pull request if you notice this.

Gateway | Composer Package | Maintainer
--- | --- | ---
[Amazon](https://github.com/lemonstand/oauth2-amazon/) | lemonstand/oauth2-amazon | [LemonStand](https://github.com/lemonstand)
[Auth0](https://github.com/RiskioFr/oauth2-auth0) | riskio/oauth2-auth0 | [Riskio](https://github.com/RiskioFr)
[Battle.net](https://github.com/tpavlek/oauth2-bnet) | depotwarehouse/oauth2-bnet | [Troy Pavlek](https://github.com/tpavlek)
[Bitbucket](https://github.com/stevenmaguire/oauth2-bitbucket) | stevenmaguire/oauth2-bitbucket | [Steven Maguire](https://github.com/stevenmaguire)
[BookingSync](https://github.com/BookingSync/oauth2-bookingsync-php) | bookingsync/oauth2-bookingsync-php | [BookingSync](https://github.com/BookingSync)
[Box](https://github.com/stevenmaguire/oauth2-box) | stevenmaguire/oauth2-box | [Steven Maguire](https://github.com/stevenmaguire)
[Clover](https://github.com/wheniwork/oauth2-clover) | wheniwork/oauth2-clover | [When I Work](https://github.com/wheniwork)
[Coinbase](https://github.com/openclerk/coinbase-oauth2) | openclerk/coinbase-oauth2 | [Openclerk](https://github.com/openclerk)
[Dropbox](https://github.com/pixelfear/oauth2-dropbox) | pixelfear/oauth2-dropbox | [Jason Varga](https://github.com/jasonvarga)
[Elance](https://github.com/stevenmaguire/oauth2-elance) | stevenmaguire/oauth2-elance | [Steven Maguire](https://github.com/stevenmaguire)
[Envato](https://github.com/dilab/envato-oauth2-provider) | dilab/envato-oauth2-provider | [Xu Ding](https://github.com/dilab)
[Eventbrite](https://github.com/stevenmaguire/oauth2-eventbrite) | stevenmaguire/oauth2-eventbrite | [Steven Maguire](https://github.com/stevenmaguire)
[Foursquare](https://github.com/stevenmaguire/oauth2-foursquare) | stevenmaguire/oauth2-foursquare | [Steven Maguire](https://github.com/stevenmaguire)
[FreeAgent](https://github.com/CloudManaged/oauth2-freeagent) | cloudmanaged/oauth2-freeagent | [Israel Sotomayor](https://github.com/zot24)
[Google Nest](https://github.com/JC5/nest-oauth2-provider) | grumpydictator/nest-oauth2-provider | [James Cole](https://github.com/JC5)
[Imgur](https://github.com/adam-paterson/oauth2-imgur) | adam-paterson/oauth2-imgur | [Adam Paterson](https://github.com/adam-paterson)
[Keycloak](https://github.com/stevenmaguire/oauth2-keycloak) | stevenmaguire/oauth2-keycloak | [Steven Maguire](https://github.com/stevenmaguire)
[Mail.ru](https://packagist.org/packages/aego/oauth2-mailru) | aego/oauth2-mailru | [Alexey](https://github.com/rakeev)
[Meetup](https://github.com/howlowck/meetup-oauth2-provider) | howlowck/meetup-oauth2-provider | [Hao Luo](https://github.com/howlowck)
[Microsoft](https://github.com/stevenmaguire/oauth2-microsoft) | stevenmaguire/oauth2-microsoft | [Steven Maguire](https://github.com/stevenmaguire)
[Mollie](https://github.com/mollie/oauth2-mollie-php) | mollie/oauth2-mollie-php | [Mollie](https://github.com/mollie)
[Naver](https://packagist.org/packages/deminoth/oauth2-naver) | deminoth/oauth2-naver | [SangYeob Bono Yu](https://github.com/deminoth)
[Odnoklassniki](https://packagist.org/packages/aego/oauth2-odnoklassniki) | aego/oauth2-odnoklassniki | [Alexey](https://github.com/rakeev)
[PayPal](https://github.com/stevenmaguire/oauth2-paypal) | stevenmaguire/oauth2-paypal | [Steven Maguire](https://github.com/stevenmaguire)
[Rdio](https://github.com/adam-paterson/oauth2-rdio) | adam-paterson/oauth2-rdio | [Adam Paterson](https://github.com/adam-paterson)
[Reddit](https://github.com/rtheunissen/oauth2-reddit) | rtheunissen/oauth2-reddit | [Rudi Theunissen](https://github.com/rtheunissen)
[Resource Guru](https://github.com/adam-paterson/oauth2-resource-guru) | adam-paterson/oauth2-resource-guru | [Adam Paterson](https://github.com/adam-paterson)
[Salesforce](https://github.com/stevenmaguire/oauth2-salesforce) | stevenmaguire/oauth2-salesforce | [Steven Maguire](https://github.com/stevenmaguire)
[Shotbow](https://packagist.org/packages/shotbow/oauth2-shotbow) | shotbow/oauth2-shotbow | [Navarr Barnier](https://github.com/navarr)
[Slack](https://github.com/adam-paterson/oauth2-slack) | adam-paterson/oauth2-slack | [Adam Paterson](https://github.com/adam-paterson)
[Spotify](https://packagist.org/packages/audeio/spotify-web-api) | audeio/spotify-web-api | [Jonjo McKay](https://github.com/jonjomckay)
[Stripe](https://github.com/adam-paterson/oauth2-stripe) | adam-paterson/oauth2-stripe | [Adam Paterson](https://github.com/adam-paterson)
[Square](https://packagist.org/packages/wheniwork/oauth2-square) | wheniwork/oauth2-square | [Woody Gilk](https://github.com/shadowhand)
[Twitch.tv](https://github.com/tpavlek/oauth2-twitch) | depotwarehouse/oauth2-twitch | [Troy Pavlek](https://github.com/tpavlek)
[Uber](https://github.com/stevenmaguire/oauth2-uber) | stevenmaguire/oauth2-uber | [Steven Maguire](https://github.com/stevenmaguire)
[Vend](https://github.com/wheniwork/oauth2-vend) | wheniwork/oauth2-vend | [When I Work](https://github.com/wheniwork)
[Vkontakte](https://github.com/j4k/oauth2-vkontakte) | j4k/oauth2-vkontakte | [Jack W](https://github.com/j4k)
[Yahoo](https://packagist.org/packages/hayageek/oauth2-yahoo) | hayageek/oauth2-yahoo | [Ravishanker Kusuma](https://github.com/hayageek)
[Yandex](https://packagist.org/packages/aego/oauth2-yandex) | aego/oauth2-yandex | [Alexey](https://github.com/rakeev)
[ZenPayroll](https://packagist.org/packages/wheniwork/oauth2-zenpayroll) | wheniwork/oauth2-zenpayroll | [Woody Gilk](https://github.com/shadowhand)

## Client Packages

Some developers use this library as a base for their own PHP API wrappers, and that seems like a really great idea. It might make it slightly tricky to integrate their provider with an existing generic "OAuth 2.0 All the Things" login system, but it does make working with them easier.

- [Sniply](https://github.com/younes0/sniply)
