<?php

use Laminas\Session\Storage\SessionArrayStorage;
use Laminas\Session\Validator\HttpUserAgent;
use Laminas\Session\Validator\RemoteAddr;

return [
    'unicaen-app' => [
        // Informations concernant l'application.
        'app_infos' => [
            'nom'     => "ESUP-SyGAL",
            'desc'    => "SYstème de Gestion et d'Accompagnement doctoraL",
            'version' => '?', // surchargée dans un autre fichier de config (ex: 'config/autoload/auto.version.local.php')
            'date'    => '?', // idem
            'contact' => [
                'mail' => "assistance-sygal@unicaen.fr",
                //'tel' => "01 02 03 04 05",
            ],
            'mentionsLegales'        => "http://www.unicaen.fr/acces-direct/mentions-legales/",
            'informatiqueEtLibertes' => "http://www.unicaen.fr/acces-direct/informatique-et-libertes/",
        ],

        // Période d'exécution de la requête de rafraîchissement de la session utilisateur, en millisecondes.
        // 0 <=> aucune requête exécutée
        'session_refresh_period' => 0,

        // Paramètres de fonctionnement LDAP.
        'ldap' => [
            'dn' => [
                'UTILISATEURS_BASE_DN'                  => 'ou=people,dc=unicaen,dc=fr',
                'UTILISATEURS_DESACTIVES_BASE_DN'       => 'ou=deactivated,dc=unicaen,dc=fr',
                'GROUPS_BASE_DN'                        => 'ou=groups,dc=unicaen,dc=fr',
                'STRUCTURES_BASE_DN'                    => 'ou=structures,dc=unicaen,dc=fr',
            ],
            'filters' => [
                'LOGIN_FILTER'                          => '(supannAliasLogin=%s)',
                'UTILISATEUR_STD_FILTER'                => '(|(uid=p*)(&(uid=e*)(eduPersonAffiliation=student)))',
                'CN_FILTER'                             => '(cn=%s)',
                'NAME_FILTER'                           => '(cn=%s*)',
                'UID_FILTER'                            => '(uid=%s)',
                'NO_INDIVIDU_FILTER'                    => '(supannEmpId=%08s)',
                'AFFECTATION_FILTER'                    => '(&(uid=*)(eduPersonOrgUnitDN=%s))',
                'AFFECTATION_CSTRUCT_FILTER'            => '(&(uid=*)(|(ucbnSousStructure=%s;*)(supannAffectation=%s;*)))',
                'LOGIN_OR_NAME_FILTER'                  => '(|(supannAliasLogin=%s)(cn=%s*))',
                'MEMBERSHIP_FILTER'                     => '(memberOf=%s)',
                'AFFECTATION_ORG_UNIT_FILTER'           => '(eduPersonOrgUnitDN=%s)',
                'AFFECTATION_ORG_UNIT_PRIMARY_FILTER'   => '(eduPersonPrimaryOrgUnitDN=%s)',
                'ROLE_FILTER'                           => '(supannRoleEntite=[role={SUPANN}%s][type={SUPANN}%s][code=%s]*)',
                'PROF_STRUCTURE'                        => '(&(eduPersonAffiliation=teacher)(eduPersonOrgUnitDN=%s))',
                'FILTER_STRUCTURE_DN'		            => '(%s)',
                'FILTER_STRUCTURE_CODE_ENTITE'	        => '(supannCodeEntite=%s)',
                'FILTER_STRUCTURE_CODE_ENTITE_PARENT'   => '(supannCodeEntiteParent=%s)',
            ],
        ],
    ],

    'navigation'   => [
        // The DefaultNavigationFactory we configured uses 'default' as the sitemap key
        'default' => [
            // And finally, here is where we define our page hierarchy
            'home' => [
                'pages' => [
                    'contact'                  => [
                        'label'    => _("Assistance"),
                        'title'    => _("Assistance concernant l'application"),
                        'route'    => 'contact',
                        'class'    => 'contact',
                        'visible'  => false,
                        'footer'   => true, // propriété maison pour inclure cette page dans le menu de pied de page
                        'sitemap'  => true, // propriété maison pour inclure cette page dans le plan
                        'resource' => 'controller/UnicaenApp\Controller\Application:contact',
                        'order'    => 1002,
                    ],
                ],
            ],
        ],
    ],

    //
    // Session configuration.
    //
    'session_config' => [
        'name' => md5('ESUP-SyGAL'),
        'cookie_lifetime' => 0,
        'gc_maxlifetime' => 60*60*2,
        'gc_probability' => 0,
        //'gc_divisor' => 1000,
    ],
    //
    // Session manager configuration.
    //
    'session_manager' => [
        // Session validators (used for security).
        'validators' => [
            RemoteAddr::class,
            HttpUserAgent::class,
        ]
    ],
    //
    // Session storage configuration.
    //
    'session_storage' => [
        'type' => SessionArrayStorage::class
    ],

];
