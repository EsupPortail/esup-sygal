<?php

use Zend\Mail\Transport\Smtp;

return [

    'import-api' => [
        'etablissements' => [
            'UCN' => [
                'url'      => 'https://sygal-pp.unicaen.fr:443',
                'verify'   => false,
                'user'     => 'sygal-app',
                'password' => '3eO7/B6Et40tUd3&', // Basic c3lnYWwtYXBwOjNlTzcvQjZFdDQwdFVkMyY=
                'timeout'  => 10,
            ],
            'URN' => [
                'url'      => 'https://sygal-ws.univ-rouen.fr:8443',
                'verify'   => false, // si true: cURL error 60: SSL certificate problem: self signed certificate
                'user'     => 'sygal-app',
                'password' => 'sygal2018Ws!', // Basic c3lnYWwtYXBwOnN5Z2FsMjAxOFdzIQ==
                'timeout'  => 10,
            ],
            'ULHN' => [
                'url'      => 'https://www-apps-pp.univ-lehavre.fr/sygal/', //! ne pas toucher au slash final
                'verify'   => false, // si true: cURL error 60: SSL certificate problem: self signed certificate
                'user'     => 'sygal-app',
                'password' => 'Mdp4Sygal!', // Basic c3lnYWwtYXBwOk1kcDRTeWdhbCE=
                'timeout'  => 10,
            ],
            'INSA' => [
                'url'      => 'https://ws-sygal.insa-rouen.fr:443',
                'verify'   => false,
                'user'     => 'sygal-app',
                'password' => '1234', // Basic c3lnYWwtYXBwOjEyMzQ=
                'timeout'  => 10,
            ],
        ],
    ],

    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'host'     => 'sygaldb.unicaen.fr',
                    'dbname'   => 'SYGLTEST',
                    'port'     => '1524',
                    'user'     => $user = 'sygal_test',
                    'password' => 'AuWPZFGApe',
                    'charset'  => 'AL32UTF8',
                    'CURRENT_SCHEMA' => $user,
                ],
            ],
        ],
    ],

    'cli_config' => [
        'scheme' => 'https',
        'domain' => 'sygal-test.normandie-univ.fr',
    ],

    'unicaen-app' => [
        'mail' => [
            'transport' => Smtp::class,
            'transport_options' => [
                'host' => 'smtp.unr-runn.fr',
                'port' => 25,
            ],
            'from' => 'ne_pas_repondre@normandie-univ.fr',
            'redirect_to' => ['bertrand.gauthier@unicaen.fr', 'jean-philippe.metivier@unicaen.fr'],
        ],
        'ldap' => [
            'connection' => [
                'default' => [
                    'params' => [
                        // pas de LDAP
                    ]
                ]
            ]
        ],
    ],

    'unicaen-auth' => [
        'shib' => [
            'simulate' => [
            ],
            'aliases' => [
                'eppn'                   => 'HTTP_EPPN',
                'mail'                   => 'HTTP_MAIL',
                'eduPersonPrincipalName' => 'HTTP_EPPN',
                'supannEtuId'            => 'HTTP_SUPANNETUID',
                'supannEmpId'            => 'HTTP_SUPANNEMPID',
                'supannCivilite'         => 'HTTP_SUPANNCIVILITE',
                'displayName'            => 'HTTP_DISPLAYNAME',
                'sn'                     => 'HTTP_SN',
                'surname'                => 'HTTP_SURNAME',
                'givenName'              => 'HTTP_GIVENNAME',
            ],
            'required_attributes' => [
                'eppn',
                'mail',
                'eduPersonPrincipalName',
                //'supannCivilite',
                'displayName',
                'sn|surname', // i.e. 'sn' ou 'surname'
                'givenName',
                'supannEtuId|supannEmpId',
            ],
        ],
        'usurpation_allowed_usernames' => [
            'gauthierb@unicaen.fr',
            'bernardb@unicaen.fr',
            'metivier@unicaen.fr',
            'massonj@unicaen.fr',
            'camuse@unicaen.fr',
            'fery@unicaen.fr',
            'leroupa1@univ-rouen.fr',
        ],
    ],

//    'unicaen-leocarte' => [
//        'soap_client_config' => [
//            'wsdl' => [
//                'file'     => __DIR__ . '/../externalEasyIDWebServices.wsdl',
//                //'username' => 'ws-unicaen',
//                //'password' => 'zdbyPt1hvd',
//                'username' => 'ws-unicaen-sodoct',
//                'password' => 'CJiNXksUDbAUXt_eSorD',
//            ],
//            'soap' => [
//                'version' => SOAP_1_1,
//                //'proxy_host' => "proxy.unicaen.fr",
//                //'proxy_port' => 3128,
//            ],
//        ],
//    ],

//    'unicaen-test' => [
//        'numero_etudiant_test' => '21009539',
//        'source_id_test' => 2,
//    ],

    'liste-diffusion' => [
        'email_domain' => 'liste.normandie-univ.fr',
        'sympa' => [
            'url' => 'https://liste.normandie-univ.fr',
        ],
        'proprietaires' => [
            'patrice.lerouge@univ-rouen.fr' => "LEROUGE Patrice",
            'matthieu.leuillier@normandie-univ.fr' => "LEUILLIER Matthieu",
            'pierrick.gandolfo@univ-rouen.fr' => "GANDOLFO Pierrick",
            'jean-philippe.metivier@unicaen.fr' => "MÉTIVIER Jean-Philippe",
            'bertrand.gauthier@unicaen.fr' => "GAUTHIER Bertrand",
        ],
    ],
];
