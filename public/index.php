<?php
/* Fermeture du service *
if (php_sapi_name() === 'cli') {
    exit(0);
}
$whiteList = [
    ['127.0.0.1'], // localhost
    ['10.60.11.88'], // Bertrand
];
$passed = false;
foreach( $whiteList as $ip ){
    $passed = $ip[0] === $_SERVER['REMOTE_ADDR'];
    if ($passed && isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $passed = isset($ip[1]) && $ip[1] === $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if ($passed) break;
}
if (!$passed){
    $maintenanceText = 'Suite à un problème matériel, SoDoct est inaccessible. La résolution est en cours. Veuillez nous excuser pour la gêne occasionnée.';
    include 'maintenance.php';
}
/* Fin de fermeture du service */



define('REQUEST_MICROTIME', microtime(true));

define('APPLICATION_DIR', realpath(__DIR__ . '/..'));

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
