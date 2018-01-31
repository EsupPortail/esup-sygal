<?php

$modules = [
    'ZfcBase', 'DoctrineModule', 'DoctrineORMModule', 'ZfcUser', 'ZfcUserDoctrineORM', 'BjyAuthorize',
    'UnicaenApp', 'UnicaenAuth', 'UnicaenLdap', 'UnicaenOracle', 'UnicaenImport', 'UnicaenFaq', 'UnicaenLeocarte',
    'Application', 'Import', 'Retraitement', 'Notification',
];

if ('development' === (getenv('APPLICATION_ENV') ?: 'production')) {
    $modules = array_merge($modules, [
//        'ZendDeveloperTools',
        'UnicaenCode',
        'UnicaenTest',
    ]);
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
