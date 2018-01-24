<?php

if (! file_exists('vendor/autoload.php')) {
    throw new RuntimeException('File vendor/autoload.php not found. Run `php composer.phar install`.');
}
require_once 'vendor/autoload.php';

if (! class_exists('Zend\Loader\AutoloaderFactory')) {
    throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
}
