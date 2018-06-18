<?php

$env = getenv('APPLICATION_ENV') ?: 'production';

$modules = [
    'ZfcBase',
    'DoctrineModule',
    'DoctrineORMModule',
    'ZfcUser',
    'ZfcUserDoctrineORM',
    'BjyAuthorize' => 'BjyAuthorize',
    'UnicaenApp',
    'UnicaenAuth',
    'UnicaenLdap',
    'UnicaenOracle',
    'UnicaenImport',
    'UnicaenFaq',
    'UnicaenLeocarte',
    'Application',
    'Import',
    'Retraitement',
    'Notification',
];
if (php_sapi_name() === 'cli') {
    unset($modules['BjyAuthorize']);
}

$devModules =  [
    'ZendDeveloperTools',
    'UnicaenCode',
    'UnicaenTest',
];

if ('development' === $env) {
    $modules = array_merge($modules, $devModules);
}

$moduleListenerOptions = [
    'config_glob_paths'    => [
        'config/autoload/{,*.}{global,local}.php',
    ],
    'module_paths' => [
        __DIR__ . '/../module',
        __DIR__ . '/../vendor',
    ],
];

return [
    'modules' => $modules,
    'module_listener_options' => $moduleListenerOptions,
];
