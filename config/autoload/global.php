<?php

use Application\Navigation\NavigationFactoryFactory;
use Retraitement\Filter\Command\RetraitementShellCommandMines;

define('APPLICATION_DIR', __DIR__ . '/../..');

return [
    'translator' => [
        'locale' => 'fr_FR',
    ],
    'sygal' => [
        // Préfixe par défaut à utiliser pour générer le SOURCE_CODE d'un enregistrement créé dans l'application
        'default_prefix_for_source_code' => 'SyGAL',
        // Page de couverture
        'page_de_couverture' => [
            'template' => [
                // template .phtml
                'phtml_file_path' => APPLICATION_DIR . '/module/Application/src/Application/Service/PageDeCouverture/pagedecouverture.phtml',
                // feuille de styles
                'css_file_path' => APPLICATION_DIR . '/module/Application/src/Application/Service/PageDeCouverture/pagedecouverture.css',
            ],
        ],
        // Options pour le test d'archivabilité
        'archivabilite' => [
            'check_ws_script_path' => __DIR__ . '/../../bin/from_cines/check_webservice_response.sh',
            'script_path'          => __DIR__ . '/../../bin/validation_cines.sh',
            'proxy' => [
                'enabled' => false,
                //'proxy_host' => 'http://proxy.unicaen.fr',
                //'proxy_port' => 3128,
            ],
        ],
        // Options pour le retraitement des fichiers PDF
        'retraitement' => [
            // Commande utilisée pour retraiter les fichiers PDF, et ses options.
            'command' => [
                'class' => RetraitementShellCommandMines::class,
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
        // Options concernant le dépôt de la version corrigée
        'depot_version_corrigee' => [
            // Resaisir l'autorisation de diffusion ? Sinon celle saisie au 1er dépôt est reprise/dupliquée.
            'resaisir_autorisation_diffusion' => true,
            // Resaisir les attestations ? Sinon celles saisies au 1er dépôt sont reprises/dupliquées.
            'resaisir_attestations' => true,
        ],
    ],
    // Options pour le service de notification par mail
    'notification' => [
        // préfixe à ajouter systématiquement devant le sujet des mails
        'subject_prefix' => '[ESUP-SyGAL]',
        // destinataires à ajouter systématiquement en copie simple ou cachée de tous les mails
        //'cc' => [],
        //'bcc' => [],
    ],
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'metadata_cache'   => 'filesystem',
                'query_cache'      => 'filesystem',
                'result_cache'     => 'filesystem',
                'hydration_cache'  => 'filesystem',
                'generate_proxies' => false,
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
        'display_not_found_reason' => false,
        'display_exceptions'       => false,
    ],
];
