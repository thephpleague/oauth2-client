<?php

$title = 'Step 1: Request Authorization | Authorization Code';
require_once '../common/header.php';

$authorizeUrl = $provider->getAuthorizationUrl();

// Set the state to the session to check it later for protection against CSRF attacks
$_SESSION['oauth2state'] = $provider->getState();

?>

<div class="page-header">
    <h1>
        Step 1: Request Authorization
        <small>Authorization Code</small>
    </h1>
</div>

<p class="lead">
    The first step in this grant type is to request an authorization code.
</p>

<p>This is an application that wants to access <strong>your information</strong>
    on <strong>another website</strong>. So, we&rsquo;ll ask you to grant us access to that
    information. You&rsquo;ll first be taken to the other website where you have an account,
    and that website will ask you to confirm whether you want to grant us access to
    your information.</p>

<p>Once you confirm on that website, you&rsquo;ll be redirected back to this website, where
    we&rsquo;re given a token that we may use in API requests to access
    <strong>your information</strong>. We can only access the information to which
    you&rsquo;ve granted us permission and to which the other website will allow using the
    token.</p>

<h2>Let&rsquo;s See It In Action</h2>

<p>Click the button below to be redirected to <a href="http://brentertainment.com" target="_blank">Brent
    Shaffer&rsquo;s</a> demo OAuth 2.0 application named <strong>Lock&rsquo;d In</strong>, where you
    will authorize this application, granting it permission to make requests to Lock&rsquo;d In
    on your behalf.</p>

<p>Now, you don&rsquo;t really have an account on Lock&rsquo;d In, but for the sake of this example,
    imagine that you are already logged in on Lock&rsquo;d In when you are redirected there.</p>

<p><a href="<?php echo $authorizeUrl; ?>" class="btn btn-primary">Click here to grant us access to your Lock&rsquo;d
    In account</a></p>

<h2>The Code</h2>

<p>Here&rsquo;s the code that makes <strong>Step 1</strong> possible. In this example, we&rsquo;re
    using the <code>League\OAuth2\Client\Provider\GenericProvider</code> to create
    an authorization URL that may be used to redirect the user to the other website, or
    may be placed in a link that the user clicks, as shown in our example here.</p>

<pre><code>&lt;?php
use League\OAuth2\Client\Provider\GenericProvider;

session_start();

$provider = new GenericProvider([

    // Your client ID and secret provided by the other website,
    // allowing you to access information on behalf of their users.
    'clientId' =&gt; 'demoapp',
    'clientSecret' =&gt; 'testpass',

    // Where in your website should the other website redirect the
    // user after they authorize access?
    'redirectUri' =&gt; 'http://localhost:8000/authorization-code/step2.php',

    // The URL at the other website that will handle authorization.
    'urlAuthorize' =&gt; 'http://brentertainment.com/oauth2/lockdin/authorize',

    // The URL at the other website where you may obtain an access token
    // after you have received an authorization code.
    'urlAccessToken' =&gt; 'http://brentertainment.com/oauth2/lockdin/token',

    // A URL at the other website that returns information about the
    // resource owner.
    'urlResourceOwnerDetails' =&gt; 'http://brentertainment.com/oauth2/lockdin/resource',

]);

$authorizeUrl = $provider-&gt;getAuthorizationUrl();

// Set the state to the session to check it later for protection against CSRF attacks.
// Please note that getState() only returns a value after you have called getAuthorizationUrl().
$_SESSION['oauth2state'] = $provider-&gt;getState();
?&gt;

&lt;a href=&quot;&lt;?php echo $authorizeUrl; ?&gt;&quot;&gt;Click here to grant access&lt;/a&gt;</code></pre>

<?php
require_once '../common/footer.php';
?>
