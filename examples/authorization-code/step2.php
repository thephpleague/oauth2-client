<?php

$title = 'Step 2: Request Token and User Info | Authorization Code';
require_once '../common/header.php';

$isStateSame = false;
$accessToken = null;
$accessTokenErrorMessage = null;
$resourceOwner = null;
$authorizationCode = $_GET['code'];

// If the state stored in the session is the same as that returned to use by
// the other website, then this is a legitimate response to our request.
if ($_SESSION['oauth2state'] === $_GET['state']) {
    $isStateSame = true;

    try {

        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $authorizationCode
        ]);

        $resourceOwner = $provider->getResourceOwner($accessToken);

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        $accessTokenErrorMessage = $e->getMessage();
    }
}

?>
<div class="page-header">
    <h1>
        Step 2: Request Token and User Info
        <small>Authorization Code Grant</small>
    </h1>
</div>

<p class="lead">
    The next step in the authorization code grant type is really a combination of
    several steps that all happen behind the scenes.
</p>

<p>Here&rsquo;s what&rsquo;s happening:</p>

<ol>
    <li>The user (you) authorized this website to access <strong>your information</strong>
        on the other website.</li>
    <li>The other website has created an authorization code for us to use (see below).</li>
    <li>The other website has also sent back the same <code>state</code> value
        we sent to it. (Take a look at the query string in your web browser; it should be there.)
        We&rsquo;ll check this state value to make sure the response is legitimate and we
        aren&rsquo;t falling victim to a
        <a href="https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)" target="_blank">CSRF</a>
        attack.</li>
    <li>If the state checks out, we&rsquo;ll exchange the authorization code for an access token.</li>
    <li>Using the access token, we can make a request to the other website on your behalf
        for <strong>your information</strong>.</li>
</ol>

<h2>Checking State</h2>

<?php if ($isStateSame): ?>
    <div class="alert alert-success" role="alert">
        <strong>State Matches!</strong>
        We&rsquo;ve checked the state, and it matches. This is a legitimate response.
    </div>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        <strong>State Does Not Match!</strong>
        We&rsquo;ve checked the state, and it doesn&rsquo;t match. This could
        indicate a problem or an attack.
    </div>
<?php endif; ?>

<p>It&rsquo;s important (but not required) to send a <code>state</code> parameter
    in your requests. By default, this library will create and send a state parameter
    in the initial authorization code request, but you are responsible for
    getting that state value and storing it to the session. Step 1 illustrated
    this.</p>

<p>You&rsquo;re also responsible for checking the state returned from the
    authorization code response. Here&rsquo;s how we do that:</p>

<pre><code>&lt;?php
if ($_SESSION['oauth2state'] === $_GET['state']) {
    // Our state matches, so we may continue processing to get the access token
}</code></pre>

<h2>We Are Authorized!</h2>

<p>The other website has granted us an authorization code that we may now use to exchange
    for a request token.</p>

<p>Here&rsquo;s the authorization code:</p>

<?php if ($isStateSame): ?>
    <div class="alert alert-info" role="alert">
        <strong><?php echo $authorizationCode; ?></strong>
    </div>
<?php else: ?>
    <div class="alert alert-warning" role="alert">
        <strong>Warning!</strong>
        Since the state doesn&rsquo;t match, we can&rsquo;t trust the authorization code.
    </div>
<?php endif; ?>

<h2>Exchange the Authorization Code for an Access Token</h2>

<p>Now that we have an an authorization code, we can exchange it for an access
    token that we may use to request <strong>your information</strong> from the
    other website. This happens in the background.</p>

<?php if ($accessToken): ?>
    <p>Here&rsquo;s our access token:</p>

    <div class="alert alert-info" role="alert">
        <strong><?php echo $accessToken->getToken(); ?></strong>
    </div>
<?php else: ?>
    <div class="alert alert-warning" role="alert">
        <strong>Error!</strong>
        <?php if ($accessTokenErrorMessage): ?>
            <?php echo htmlentities($accessTokenErrorMessage); ?>
            (Probably because you refreshed the page; go back and authorize again.)
        <?php else: ?>
            We don&rsquo;t have an access token because our state check failed.
        <?php endif; ?>
    </div>
<?php endif; ?>

<p>And here&rsquo;s the code used to exchange the authorization code for
    an access token:</p>

<pre><code>&lt;?php
$accessToken = $provider-&gt;getAccessToken('authorization_code', [
    'code' =&gt; $_GET['code'],
]);</code></pre>

<p>The access token is returned in the form of a <code>League\OAuth2\Client\Token\AccessToken</code>
    object. This object contains several methods of crucial importantance:</p>

<ol>
    <li><code>AccessToken::getToken()</code></li>
    <li><code>AccessToken::getRefreshToken()</code></li>
    <li><code>AccessToken::getExpires()</code></li>
    <li><code>AccessToken::hasExpired()</code></li>
</ol>

<p>Use these methods to inject the access token into API calls to retrieve data
    from the other website.</p>

<h2>Use the Access Token to Request a Resource</h2>

<p>Now that we have an access token, we can use it to request a resource from
    the other website on behalf of the user who authorized us.</p>

<p>For example, here&rsquo;s our friendship information found on the resource
    owner object returned to us by Lock&rsquo;d In:</p>

<?php if ($resourceOwner): ?>

    <pre><code><?php echo htmlspecialchars(print_r($resourceOwner->toArray(), true)); ?></code></pre>

<?php else: ?>
    <div class="alert alert-warning" role="alert">
        <strong>Oops!</strong>
        We can&rsquo;t request a resource because we don&rsquo;t have an access token.
    </div>
<?php endif; ?>

<div class="alert alert-info" role="alert">
    <strong>Please note:</strong>
    OAuth 2.0 does not define what a resource owner object looks like or even
    that there must be a resource owner object. Most OAuth 2.0 providers have
    a concept of a resource owner, though, so this library provides minimal
    facilities to aid providers. This is in the form of a resource owner ID,
    which many OAuth 2.0 providers return with the access token (this is not
    part of the OAuth 2.0 specificiation).<br><br>
    Since this is not part of the specification, not all providers will include
    this information, and in this example Lock&rsquo;d In does not have a
    resource owner ID.
</div>

<p>Here&rsquo;s how to request the provider&rsquo;s resource owner object:</p>

<pre><code>&lt;?php
$resourceOwner = $provider->getResourceOwner($accessToken);</code></pre>

<p>You&rsquo;ll use the data on the <code>AccessToken</code> to send authenticated
    requests to the other website. Often, you may use another third-party library,
    the cURL extension functions, or streams to make these requests.</p>

<p>To be helpful, this library provides the <code>AbstractProvider::getAuthenticatedRequest()</code>
    method that may be used to construct a <code>Psr\Http\Message\RequestInterface</code>
    object for making API requests to the other website. For example:</p>

<pre><code>&lt;?php
$request = $provider-&gt;getAuthenticatedRequest(
    'GET',
    'http://brentertainment.com/oauth2/lockdin/resource',
    $accessToken
);
</code></pre>

<p>This <code>$request</code> object may now be sent by any client that
    understands <a href="http://www.php-fig.org/psr/psr-7/" target="_blank">PSR-7</a> request
    interfaces, like <a href="http://guzzlephp.org/" target="_blank">Guzzle</a>.</p>

<h2>That&rsquo;s It!</h2>

<p>We hope this working example has shown you how to integrate your application
    with an OAuth 2.0 provider, using the authorization code grant type. If you
    have any questions, feel free to use our
    <a href="https://github.com/thephpleague/oauth2-client/issues" target="_blank">project
    issue tracker on GitHub</a> to open a request for help from the community.</p>

<p>Thanks for using the <a href="https://github.com/thephpleague/oauth2-client" target="_blank">OAuth
    2.0 Client by the League of Extraordinary Packages</a>!</p>

<?php
require_once '../common/footer.php';
?>
