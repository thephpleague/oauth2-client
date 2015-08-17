---
layout: default
permalink: installation/
title: Installation
---

Installation
============

## System Requirements

You need **PHP >= 5.5.0** to use `league/oauth2-client` but the latest stable version of PHP is recommended.

## Composer

OAuth2-Client is available on [Packagist](https://packagist.org/packages/league/oauth2-client) and can be installed using [Composer](https://getcomposer.org/):

~~~ sh
$ composer require league/oauth2-client
~~~

Most modern frameworks will include the Composer autoloader by default, but ensure the following file is included:

~~~ php
<?php

// Include the Composer autoloader
require 'vendor/autoload.php';
~~~

## Going Solo

You can also use OAuth2-Client without using Composer by registering an autoloader function:

~~~ php
spl_autoload_register(function ($class) {
    $prefix = 'League\\OAuth2\\Client\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
~~~

Or, use any other [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible autoloader.
