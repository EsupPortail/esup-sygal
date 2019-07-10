<?php

use Application\Navigation\NavigationFactoryFactory;
use Retraitement\Filter\Command\MinesCommand;

$env = getenv('APPLICATION_ENV') ?: 'production';

return [
    'translator' => [
        'locale' => 'fr_FR',
    ],
    'sygal' => [
        // Préfixe par défaut à utiliser pour générer le SOURCE_CODE d'un enregistrement créé dans l'application
        'default_prefix_for_source_code' => 'SyGAL',
        // Options pour le test d'archivabilité
        'archivabilite' => [
            'check_ws_script_path' => __DIR__ . '/../../bin/from_cines/check_webservice_response.sh',
            'script_path'          => __DIR__ . '/../../bin/validation_cines.sh',
        ],
        // Options pour le retraitement des fichiers PDF
        'retraitement' => [
            // Commande utilisée pour retraiter les fichiers PDF, et ses options.
            'command' => [
                'class' => MinesCommand::class,
                'options' => [
                    'pdftk_path' => 'pdftk',
                    'gs_path'    => 'gs',
                    //'gs_args' => '-dPDFACompatibilityPolicy=1'
                ],
            ],
            // Durée au bout de laquelle le retraitement est interrompu pour le relancer en tâche de fond.
            // Valeurs possibles: '30s', '1m', etc. (cf. "man timeout").
            'timeout' => '20s',
        ],
    ],
    // Options pour le service de notification par mail
    'notification' => [
        // préfixe à ajouter systématiquement devant le sujet des mails
        'subject_prefix' => '[SyGAL]',
        // destinataires à ajouter systématiquement en copie simple ou cachée de tous les mails
        'cc' => ['suivi-mail-sodoct@unicaen.fr'],
        //'bcc' => [],
    ],
    'module_listener_options' => [
        'config_cache_enabled'     => ($env === 'production'),
        'config_cache_key'         => 'app_config',
        'module_map_cache_enabled' => ($env === 'production'),
        'module_map_cache_key'     => 'module_map',
        'cache_dir'                => 'data/config/',
        'check_dependencies'       => ($env !== 'production'),
    ],
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'metadata_cache'   => ($env === 'production' ? 'memcached' : 'array'),
                'query_cache'      => ($env === 'production' ? 'memcached' : 'array'),
                'result_cache'     => ($env === 'production' ? 'memcached' : 'array'),
                'hydration_cache'  => ($env === 'production' ? 'memcached' : 'array'),
                'generate_proxies' => ($env !== 'production'),
            ],
        ],
    ],
    'languages' => [
        'language-list' => ['fr_FR', 'en_US', 'de_DE', 'it_IT'],
    ],
    'service_manager' => [
        'factories' => [
            'navigation' => NavigationFactoryFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => ($env !== 'production'),
        'display_exceptions'       => ($env !== 'production'),
    ],
];
