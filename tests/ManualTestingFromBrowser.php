<?php

require_once __DIR__.'/../vendor/autoload.php';

if( isset( $_POST['provider'] ) && isset( $_POST['clientId'] )
        && isset( $_POST['clientSecret'] ) && isset( $_POST['redirectUri'] ) ) {
    
    $providerClassName =
            "League\\OAuth2\\Client\\Provider\\{$_POST['provider']}";
    
    if( !class_exists( $providerClassName ) ) {
        print 'ERROR: provider "'.$providerClassName.'" does not exist!';
        exit();
    }
    
    setcookie( 'provider',      $providerClassName,     time() + 60 );
    setcookie( 'clientId',      $_POST['clientId'],     time() + 60 );
    setcookie( 'clientSecret',  $_POST['clientSecret'], time() + 60 );
    setcookie( 'redirectUri',   $_POST['redirectUri'],  time() + 60 );
    
    $_COOKIE['provider']        = $providerClassName;
    $_COOKIE['clientId']        = $_POST['clientId'];
    $_COOKIE['clientSecret']    = $_POST['clientSecret'];
    $_COOKIE['redirectUri']     = $_POST['redirectUri'];
}

if( !empty( $_COOKIE['provider'] ) && !empty( $_COOKIE['clientId'] )
        && !empty( $_COOKIE['clientSecret'] )
        && !empty( $_COOKIE['redirectUri'] ) ) {
    
    if( !class_exists( $_COOKIE['provider'] ) ) {
        print 'ERROR: provider "'.$_COOKIE['provider'].'" does not exist!';
        exit();
    }
    
    $provider = new $_COOKIE['provider']( array(
        'clientId'      =>  $_COOKIE['clientId'],
        'clientSecret'  =>  $_COOKIE['clientSecret'],
        'redirectUri'   =>  $_COOKIE['redirectUri'],
    ) );
    
    if ( !isset( $_GET['code'] ) ) {
        
        $provider->authorize();
        
    } else {
        
        foreach( array_keys( $_COOKIE ) as $cookie ) {
            setcookie( $cookie, '', time() - 60 );
        }
        
        try {
            
            $token = $provider->getAccessToken( 'authorization_code', array(
                'code' => $_GET['code'],
            ) );
            
            try {
                
                $userDetails = $provider->getUserDetails( $token );
                
            } catch( Exception $ex ) {
                print "Failed to get user details.\n";
                print "Exception:\n";
                var_export( $ex );
                exit();
            }
        } catch( Exception $ex ) {
            print "Failed to get access token.\n";
            print "Exception:\n";
            var_export( $ex );
            exit();
        }
    }
}

$providerClasses = array(
    'Facebook',
    'Github',
    'Google',
    'Instagram',
    'LinkedIn',
    'Microsoft',
    'Vkontakte',
);

?><!DOCTYPE html>
<html>
    <head>
        
        <meta charset="utf-8">
        <title>Manual Testing</title>
        
        <script>
            window.onload = function() {
                var url = document.URL.split("?")[0];
                document.getElementById('redirectUrl').setAttribute('value', url);
                document.getElementById('submitButton').setAttribute('formaction', url);
            };
        </script>
        
    </head>
    <body>
        
        <h3>Manual testing of selected provider</h3>
        
        <form method="post">
            
            <div>Provider:</div>
            <select name="provider">
                <optgroup label="Provider class">
                    <?php
                        foreach( $providerClasses as $className ) {
                            print "<option value=\"$className\">$className</option>\n";
                        }
                    ?>
                </optgroup>
            </select>
            <br><br>
            
            <div>Client ID:</div>
            <input type="text" name="clientId" style="width: 390px;">
            <br>
            
            <div>Client secret:</div>
            <input type="text" name="clientSecret" style="width: 390px;">
            <br>
            
            <div>Redirect URI:</div>
            <input id="redirectUrl" type="text" name="redirectUri" style="width: 390px;">
            <br><br>
            
            <input id="submitButton" type="submit" value="Test">
        </form>
        
        <?php
            if( isset( $userDetails ) ) {
                $data = var_export( $userDetails, true );
                print <<<DATA
<br><br>
<div>User details:</div>
<br>
<pre>
$data
</pre>
DATA
;
            }
        ?>
        
    </body>
</html>