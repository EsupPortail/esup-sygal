<?php

use Zend\Mail\Transport\Smtp;

return [

    'import-api' => [
        'etablissements' => [
            'DEMO' => [
                'url'      => 'https://sygal-import-ws:443', // https://{nom du service docker-compose}:443
                'proxy'    => false, // false indispensable pour docker-compose run/exec
                'verify'   => false, // si true : cURL error 60: SSL certificate problem: self signed certificate
                'user'     => 'sygal-app',
                'password' => 'azerty',
                'sources'  => ['apogee'],
                'connect_timeout' => 10,
            ],
        ],
    ],

    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'host'           => 'sygal-db', // {nom du service docker-compose}
                    'dbname'         => 'sygal',
                    'port'           => '5432',
                    'user'           => $user = 'ad_sygal',
                    'password'       => 'azerty',
                    'CURRENT_SCHEMA' => 'sygal',
                ],
            ],
        ],
    ],

    'cli_config' => [
        'scheme' => 'https',
        'domain' => 'localhost:8443',
    ],

    'unicaen-app' => [
        'mail' => [
            'transport' => Smtp::class,
            'transport_options' => [
                'host' => 'smtp.domain.fr',
                'port' => 25,
            ],
            'from' => 'ne_pas_repondre@etablissement.fr',
            'redirect_to' => ['e.mail@etablissement.fr'],
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
                'HTTP_EPPN'           => $eppn = 'premierf@univ.fr',
                'HTTP_SUPANNEMPID'    => '00012345',
                'HTTP_DISPLAYNAME'    => $eppn,
                'HTTP_MAIL'           => $eppn,
                'HTTP_GIVENNAME'      => 'François',
                'HTTP_SN'             => 'Premier',
                'HTTP_SUPANNCIVILITE' => 'M.'
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
//                'supannEtuId|supannEmpId',
            ],
        ],
        'usurpation_allowed_usernames' => [
        ],
    ],

    'liste-diffusion' => [
        'email_domain' => 'liste.etablissement.fr',
        'sympa' => [
            'url' => 'https://liste.etablissement.fr',
        ],
        'proprietaires' => [
            'e.mail@etablissement.fr' => "HOCHON Paule",
        ],
    ],
];
