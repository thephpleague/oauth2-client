---
layout: default
permalink: /simple-example/
title: Simple Example
---

# Simple Example

The following example is only an example and should not be used in production.
Each of the steps should be logically separated and tokens should be stored
whenever possible.

~~~ php
$provider = new League\OAuth2\Client\Provider\<ProviderName>([
    'clientId'      => 'XXXXXXXX',
    'clientSecret'  => 'XXXXXXXX',
    'redirectUri'   => 'https://your-registered-redirect-uri/',
    'scopes'        => ['email', '...', '...'],
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $userDetails = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $userDetails->firstName);

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();

    // Use this to get a new access token if the old one expires
    echo $token->getRefreshToken();

    // Unix timestamp of when the token will expire, and need refreshing
    echo $token->getExpires();
}
~~~
