<?php

if (!defined('APPLICATION_DIR')) {
    define('APPLICATION_DIR', realpath(__DIR__ . '/../'));
}

define('REQUEST_MICROTIME', microtime(true));

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

// Config
$appConfig = include APPLICATION_DIR . '/config/application.config.php';
if (file_exists(APPLICATION_DIR . '/config/development.config.php')) {
    $appConfig = Laminas\Stdlib\ArrayUtils::merge($appConfig, include APPLICATION_DIR . '/config/development.config.php');
}

// Run the application!
Laminas\Mvc\Application::init($appConfig)->run();