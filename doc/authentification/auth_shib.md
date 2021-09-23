Authentification via la fédération d'identité Renater (Shibboleth)
==================================================================

> TODO : documenter avec JBP/AH comment installer Shibboleth ? Pfffffffffff


Exemple de configuration dans `config/autoload/xxxx.local.php` :

```php
    'unicaen-auth' => [
        //...
        'shib' => [
            /**
             * Ordre d'affichage du formulaire de connexion.
             */
            'order' => 1,

            /**
             * Activation ou non de ce mode d'authentification.
             */
            'enabled' => true,

            /**
             * Description facultative de ce mode d'authentification qui apparaîtra sur la page de connexion.
             */
            'description' =>
                "<p><span class='glyphicon glyphicon-info-sign'></span> Cliquez sur le bouton ci-dessous pour accéder à l'authentification via la fédération d'identité.</p>" .
                "<p><strong>Attention !</strong> Si vous possédez à la fois un compte Étudiant et un compte Personnel, vous devrez utiliser " .
                "votre compte <em>Étudiant</em> pour vous authentifier...</p>",
                
            /**
             * Simulation d'authentification d'un utilisateur.
             */
            'simulate' => [
                // cf. secret.local.php
            ],
            
            /**
             * Alias éventuels des clés renseignées par Shibboleth dans la variable superglobale $_SERVER
             * une fois l'authentification réussie.
             */
            'aliases' => [
                'eppn'                   => 'HTTP_EPPN',
                'mail'                   => 'HTTP_MAIL',
                'eduPersonPrincipalName' => 'HTTP_EPPN',
                'supannRefId'            => 'HTTP_SUPANNREFID',
                'supannEtuId'            => 'HTTP_SUPANNETUID',
                'supannEmpId'            => 'HTTP_SUPANNEMPID',
                'supannCivilite'         => 'HTTP_SUPANNCIVILITE',
                'displayName'            => 'HTTP_DISPLAYNAME',
                'sn'                     => 'HTTP_SN',
                'surname'                => 'HTTP_SURNAME',
                'givenName'              => 'HTTP_GIVENNAME',
            ],
            
            /**
             * Clés dont la présence sera requise par l'application dans la variable superglobale $_SERVER
             * une fois l'authentification réussie.
             */
            'required_attributes' => [
                'eppn',
                'mail',
                'eduPersonPrincipalName',
                'displayName',
                'sn|surname', // i.e. 'sn' ou 'surname'
                'givenName',
                'supannRefId|supannEtuId|supannEmpId',
            ],
            
            /**
             * Configuration de la stratégie d'extraction d'un identifiant utile parmi les données d'authentification
             * shibboleth.
             * Ex: identifiant de l'usager au sein du référentiel établissement, transmis par l'IDP via le supannRefId.
             */
            'shib_user_id_extractor' => [
                // domaine de l'EPPN (ex: hochonp@unicaen.fr')
                'unicaen.fr' => [
                    'supannEtuId' => [
                        // nom du 1er attribut recherché
                        'name' => 'supannEtuId',
                        // pas de pattern donc valeur brute utilisée
                        'preg_match_pattern' => null,
                    ],
                    'supannRefId' => [
                        // nom du 2e attribut recherché
                        'name' => 'supannRefId', // ex: '{OCTOPUS:ID}1234;{ISO15693}044D137A7A5E65480'
                        // pattern éventuel pour extraire la partie intéressante
                        'preg_match_pattern' => '|\{OCTOPUS:ID\}(\d+)|', // ex: permet d'extraire '1234'
                    ],
                    /*'supannEmpId' => [
                        // nom du 3e attribut recherché
                        'name' => 'supannEmpId',
                        // pas de pattern donc valeur brute utilisée
                    ],*/
                ],
                /*
                // autres domaines
                'univ-rouen.fr' => [
                    'supannEtuId' => ['name' => 'supannEtuId'],
                    'supannEmpId' => ['name' => 'supannEmpId'],
                ],
                */
                // config de repli pour tous les autres domaines
                'default' => [
                    'supannEtuId' => ['name' => 'supannEtuId'],
                    'supannEmpId' => ['name' => 'supannEmpId'],
                ],
            ],
            
            /**
             * URL de déconnexion.
             */
            'logout_url' => '/Shibboleth.sso/Logout?return=', // NB: '?return=' semble obligatoire!
        ],
```
