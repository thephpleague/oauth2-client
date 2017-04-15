<?php

$title = 'league/oauth2-client Examples';
require_once 'common/header.php';

?>

<div class="page-header">
    <h1>
        league/oauth2-client Examples
    </h1>
</div>

<p class="lead">
    The OAuth 2.0 Authorization Framework (<a href="http://tools.ietf.org/html/rfc6749">RFC
    6749</a>) defines four authorization grant types, and this library supports each
    of these grant types.
</p>

<dl class="dl-horizontal">

    <dt><a href="/authorization-code/step1.php">Authorization Code</a></dt>
    <dd>
        The <a href="http://tools.ietf.org/html/rfc6749#section-1.3.1">authorization
        code</a> grant type uses an authorization server as an intermediary
        between the client and the resource owner. Instead of requesting authorization
        directly from the resource owner (i.e. by asking for their username and
        password), the client redirects the resource owner to an authorization
        server&mdash;where they may provide their credentials&mdash;and, in turn,
        the authorization server redirects back to the client with an authorization code.
    </dd>

</dl>

<?php
require_once 'common/footer.php';
?>
