<?php

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

use League\OAuth2\Client\Provider\GenericProvider;

session_start();

$provider = new GenericProvider([
    'clientId' => 'demoapp',
    'clientSecret' => 'demopass',
    'redirectUri' => 'http://localhost:8000/authorization-code/step2.php',
    'urlAuthorize' => 'http://brentertainment.com/oauth2/lockdin/authorize',
    'urlAccessToken' => 'http://brentertainment.com/oauth2/lockdin/token',
    'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource',
]);

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="https://raw.githubusercontent.com/thephpleague/thephpleague.github.io/master/favicon.ico">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.7/styles/default.min.css">
        <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.7/highlight.min.js"></script>
        <script>hljs.initHighlightingOnLoad();</script>

        <title><?php echo htmlentities($title); ?></title>

    </head>
    <body role="document">
        <div class="container" role="main">
