<?php

namespace Application;

use Application\Entity\Db\Source;
use Application\Navigation\NavigationFactoryFactory;
use Import\Filter\PrefixEtabColumnValueFilter;
use Import\Model\ImportObserv;
use Import\Model\ImportObservResult;
use Retraitement\Filter\Command\RetraitementShellCommandMines;

$config = [
    'api-tools-content-negotiation' => [
        'selectors' => [],
    ],
    'db' => [
        'adapters' => [
            'dummy' => [],
        ],
    ],
    'api-tools-mvc-auth' => [
        'authentication' => [
            'map' => [
                'Api\\V1' => 'basic',
            ],
        ],
    ],
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
                'phtml_file_path' => APPLICATION_DIR . '/module/Depot/view/depot/depot/page-de-couverture/pagedecouverture.phtml',
                // feuille de styles
                'css_file_path' => APPLICATION_DIR . '/module/Depot/view/depot/depot/page-de-couverture/pagedecouverture.css',
            ],
        ],
        //
        // Options pour le test d'archivabilité du manuscrit de thèse (fait appel au web service "Facile" du CINES).
        //
        'archivabilite' => [
            // URL du web service "Facile" du CINES
            'ws_url' => 'https://facile.cines.fr/xml',
            // emplacement du script bash testant la réponse du ws
            'check_ws_script_path' => __DIR__ . '/../../bin/from_cines/check_webservice_response.sh',
            // emplacement du script bash réalisant l'appel du ws
            'script_path' => __DIR__ . '/../../bin/validation_cines.sh',
            // activation et config du proxy éventuel
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
        // Options concernant les rapports (CSI, mi-parcours)
        'rapport' => [
            // Page de couverture des rapports d'activité déposés
            'page_de_couverture' => [
                'template' => [
                    // template .phtml
                    'phtml_file_path' => APPLICATION_DIR . '/module/Application/view/application/rapport/page-de-couverture/pagedecouverture.phtml',
                    // feuille de styles
                    'css_file_path' => APPLICATION_DIR . '/module/Application/view/application/rapport/page-de-couverture/pagedecouverture.css',
                ],
            ],
        ],
    ],
    'import' => [

        'import_observ_entity_class' => ImportObserv::class,
        'import_observ_result_entity_class' => ImportObservResult::class,

        'connections' => [
            // Cf. config locale
        ],

        //
        // Classe de l'entité Doctrine représentant une "Source".
        //
        'source_entity_class' => Source::class,

        //
        // Code de la Source par défaut (injectée dans les entités implémentant SoucreAwareInterface).
        //
        'default_source_code' => 'SYGAL::sygal',

        //
        // Alias éventuels des noms de colonnes d'historique.
        //
        'histo_columns_aliases' => [
            'created_on' => 'histo_creation',     // date/heure de création de l'enregistrement
            'updated_on' => 'histo_modification', // date/heure de modification
            'deleted_on' => 'histo_destruction',  // date/heure de suppression

            'created_by' => 'histo_createur_id',     // auteur de la création de l'enregistrement
            'updated_by' => 'histo_modificateur_id', // auteur de la modification
            'deleted_by' => 'histo_destructeur_id',  // auteur de la suppression
        ],

        //
        // Forçage éventuel de valeur pour les colonnes d'historique.
        //
        'histo_columns_values' => [
            'created_by' => 1, // auteur de la création de l'enregistrement
            'updated_by' => 1, // auteur de la modification
            'deleted_by' => 1, // auteur de la suppression
        ],

        //
        // Témoin indiquant si la table IMPORT_OBSERV doit être prise en compte.
        // La table IMPORT_OBSERV permet d'inscrire les changements de valeurs à détecter pendant la synchro.
        // Les changements détectés sont consignés dans la table IMPORT_OBSERV_RESULT.
        //
        'use_import_observ' => true,

        //
        // Imports.
        //
        'imports' => [
            [
                'name' => 'domaine-hal',
                'order' => 160,
                'source' => [
                    'name' => 'HAL',
                    'connection' => 'api-archives-ouvertes',
                    'select' => '/domain/?q=*:*&wt=json&fl=*&rows=500',
                    'code' => 'HAL',
                    'columns' => [
                        'docid',
                        'code_s',
                        'haveNext_bool',
                        'en_domain_s',
                        'fr_domain_s',
                        'level_i',
                        'parent_i',
                    ],
                    'column_name_filter' => [
                        'docid' => 'docid',
                        'haveNext_bool' => 'havenext_bool',
                        'code_s' => 'source_code',
                        'en_domain_s' => 'en_domain_s',
                        'fr_domain_s' => 'fr_domain_s',
                        'level_i' => 'level_i',
                        'parent_i' => 'parent_id'
                    ],
                    'source_code_column' => 'source_code',
                ],
                'destination' => [
                    'name' => 'application',
                    'table' => 'tmp_domaine_hal',
                    'connection' => 'default',
                    'source_code_column' => 'source_code',
                    'id_strategy' => null,
                    'id_sequence' => null,
                ],
            ]
        ],

        //
        // Synchros.
        //
        'synchros' => [
            // <==== la config des synchros sera injectée ici
            ////////////////////////////////////////////// DOMAINES HAL //////////////////////////////////////////////
            [
                'name' => 'domaine-hal',
                'order' => 160,
                'source' => [
                    'name' => 'sygal',
                    'code' => 'app',
                    'table' => 'src_domaine_hal',
                    'connection' => 'default',
                    'source_code_column' => 'source_code',
                ],
                'destination' => [
                    'name' => 'application',
                    'table' => 'domaine_hal',
                    'connection' => 'default',
                    'source_code_column' => 'source_code',
                    'id_strategy' => 'SEQUENCE',
                    'id_sequence' => null,
                    'where' => NULL,
                ],
            ],
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
                'proxy_dir'        => 'data/DoctrineORMModule/Proxy',
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

return $config;
