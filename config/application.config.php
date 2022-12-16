<?php

if (!defined('APPLICATION_DIR')) {
    define('APPLICATION_DIR', realpath(__DIR__ . '/../'));
}

return [
    'modules' => [
        'DoctrineModule',
        'DoctrineORMModule',

        'ApiSkeletons\DoctrineORMHydrationModule',
        'Laminas\ApiTools',
        'Laminas\ApiTools\Provider',
        'Laminas\ApiTools\Doctrine\Server',
        'Laminas\ApiTools\Doctrine\QueryBuilder',
        'Laminas\ApiTools\Documentation',
        'Laminas\ApiTools\Documentation\Swagger',
        'Laminas\ApiTools\ApiProblem',
        'Laminas\ApiTools\MvcAuth',
        'Laminas\ApiTools\OAuth2',
        'Laminas\ApiTools\Hal',
        'Laminas\ApiTools\ContentNegotiation',
        'Laminas\ApiTools\ContentValidation',
        'Laminas\ApiTools\Rest',
        'Laminas\ApiTools\Rpc',
        'Laminas\ApiTools\Versioning',

        'Laminas\Cache',
        'Laminas\Filter',
        'Laminas\Form',
        'Laminas\Hydrator',
        'Laminas\I18n',
        'Laminas\InputFilter',
        'Laminas\Log',
        'Laminas\Mail',
        'Laminas\Mvc\I18n',
        'Laminas\Mvc\Plugin\FilePrg',
        'Laminas\Mvc\Plugin\FlashMessenger',
        'Laminas\Mvc\Plugin\Identity',
        'Laminas\Mvc\Plugin\Prg',
        'Laminas\Navigation',
        'Laminas\Paginator',
        'Laminas\Router',
        'Laminas\Session',
        'Laminas\Validator',

        'ZfcUser',
        'BjyAuthorize',
        'UnicaenApp',
        'UnicaenAlerte',
        'UnicaenAuth',
        'UnicaenAuthToken',
        'UnicaenAvis',
        'UnicaenLdap',
//        'UnicaenDbAnonym',
        'UnicaenDbImport',
        'UnicaenFaq',
        'UnicaenIdref',
//        'UnicaenLivelog',
        'UnicaenPdf',
        'UnicaenRenderer',
        'Unicaen\Console',
        'UnicaenParametre',

        'Horodatage',
        'Structure',
        'These',
        'Fichier',
        'Import',
        'Indicateur',
        'Individu',
        'Retraitement',
        'Soutenance',
        'Depot',
        'Formation',
        'ComiteSuiviIndividuel',
        'RapportActivite',
        'Notification',
        'Information',
        'Doctorant',
        'StepStar',
//        'InscriptionAdministrative',
        'Application',
        'SygalApi',
        'SygalApiImpl',
    ],
    'module_listener_options' => [
        'config_glob_paths'    => [
            'config/autoload/{,*.}{global,local}.php',
        ],
        'module_paths' => [
            __DIR__ . '/../module',
            __DIR__ . '/../vendor',
        ],
        'config_cache_key' => 'application.config.cache',
        'config_cache_enabled' => true,
        'module_map_cache_key' => 'application.module.cache',
        'module_map_cache_enabled' => true,
        'cache_dir' => 'data/cache/',
    ],
];
