<?php

return [
    'sygal' => [
        'archivabilite' => [
            'proxy' => [
                'enabled' => false,
                //'proxy_host' => 'http://proxy.domain.fr',
                //'proxy_port' => 3128,
            ],
        ],
        'depot_version_corrigee' => [
            'resaisir_autorisation_diffusion' => false,
            'resaisir_attestations' => false,
        ],
    ],

    'actualite' => [
        'actif' => false,
        'flux' => "https://...",
    ],
    'offre-these' => [
        'actif' => false,
    ],

    'import-api' => [
        // cf. secret.local.php
    ],

    // synchro avec unicaen/db-import
    'import' => [
        'connections' => [
            'default' => 'doctrine.connection.orm_default',
        ],
    ],

    'doctrine' => [
        // cf. secret.local.php
    ],

    'fichier' => [
        'root_dir_path' => '/app/upload',
    ],

    'cli_config' => [
        // cf. secret.local.php
    ],

    'public_files' => [
        'cache_enabled' => false,
    ],

    'unicaen-app' => [
        'mail' => [
            // cf. secret.local.php
            'do_not_send' => false,
        ],
        'ldap' => [
            // cf. secret.local.php
        ],
        'maintenance' => [
            // cf. z.maintenance.local.php (sur le serveur)

            /*
            // activation (true: activé, false: désactivé)
            'enable' => true,
            // message à afficher
            'message' => "SyGAL est indisponible jusqu'à 10h pour des raisons de maintenance, veuillez nous excuser pour la gêne occasionnée.",
            // le mode console est-il aussi concerné (TRUE: oui, FALSE: non)
            'include_cli' => false,
            // liste blanche des adresses IP clientes non concernées
            'white_list' => [
            ],
            */
        ],
    ],

    'unicaen-ldap' => [
        // pas de LDAP
    ],

    'unicaen-auth' => [
        'shib' => [
            'order' => 1,
            'enabled' => false,
            'description' =>
                "<p><span class='glyphicon glyphicon-info-sign'></span> Cliquez sur le bouton ci-dessous pour accéder à l'authentification via la fédération d'identité.</p>" .
                "<p><strong>Attention !</strong> Si vous possédez à la fois un compte Étudiant et un compte Personnel, vous devrez utiliser " .
                "votre compte <em>Étudiant</em> pour vous authentifier...</p>",
            'simulate' => [
                // cf. secret.local.php
            ],
            'aliases' => [
                // cf. secret.local.php
            ],
            'required_attributes' => [
                // cf. secret.local.php
            ],
            'logout_url' => '/Shibboleth.sso/Logout?return=', // NB: '?return=' semble obligatoire!
        ],
        'local' => [
            'order' => 2,
            'description' => "Utilisez ce formulaire si vous possédez un compte local dédié à l'application.",
            'ldap' => [
                'order' => 2,
                'enabled' => false,
            ],
            'db' => [
                'order' => 3,
                'enabled' => true,
                'description' => "Utilisez ce formulaire si vous possédez un compte local dédié à l'application.",
            ],
        ],
        'cas' => [
            'order' => 4,
            'enabled' => false,
            'connection' => [
                'default' => [
                    'params' => [
                        'hostname' => 'cas.domain.fr',
                        'port'     => 443,
                        'version'  => "2.0",
                        'uri'      => "",
                        'debug'    => false,
                    ],
                ],
            ]
        ],

        'usurpation_allowed_usernames' => [
            // cf. secret.local.php
        ],
    ],

    'navigation'   => [
        'default' => [
            'home' => [
                'pages' => [
                    'etab' => [
                        'label' => _("Etablissement"),
                        'title' => _("Page d'accueil du site de l'établissement"),
                        'uri'   => 'http://www.etablissement.demo',
                        'class' => 'logo-etablissement',
                        // NB: Spécifier la classe 'logo-etablissement' sur une page de navigation provoque le "remplacement"
                        //     du label du lien par l'image 'public/logo-etablissement.png' (à créer le cas échéant).
                    ],
                ],
            ],
        ],
    ],
];
