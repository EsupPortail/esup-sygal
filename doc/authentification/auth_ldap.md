Authentification auprès d'un annuaire LDAP
==========================================

Exemple de configuration dans `config/autoload/xxxx.local.php` :

```php
    'unicaen-auth' => [
        //...
        /**
         * Configuration de l'authentification locale (compte LDAP établissement, ou compte BDD application).
         */
        'local' => [
            /**
             * Ordre d'affichage du formulaire de connexion.
             */
            'order' => 2,

            /**
             * Description facultative de ce mode d'authentification qui apparaîtra sur la page de connexion.
             */
            'description' => "Utilisez ce formulaire si vous possédez un compte LDAP établissement ou un compte local dédié à l'application.",
            
            /**
             * Mode d'authentification à l'aide d'un compte LDAP.
             */
            'ldap' => [
                'enabled' => true,
            ],
            
            //...
        ],
```

En cas d'activation du mode d'authentification LDAP, la connexion à l'annuaire LDAP *avec un compte admin* ainsi
que certains paramètres de fonctionnement doivent être configurés dans `config/autoload/xxxx.secret.local.php` 
sous la clé `'unicaen-app'`, exemple :

```php
    'unicaen-app' => [
        //...
        /**
         * Paramètres de fonctionnement LDAP.
         */
        'ldap' => [
            /**
             * Connexion à l'annuaire LDAP (NB: compte admin requis)
             */
            'connection' => [
                'default' => [
                    'params' => [
                        'host'                => 'host.domain.fr',
                        'username'            => "uid=xxxxxxxxx,ou=xxxxxxxxxx,dc=domain,dc=fr",
                        'password'            => "xxxxxxxxxxxx",
                        'baseDn'              => "ou=xxxxxxxxxxx,dc=domain,dc=fr",
                        'bindRequiresDn'      => true,
                        'accountFilterFormat' => '(&(objectClass=posixAccount)(supannAliasLogin=%s))',
                    ]
                ]
            ],
            
            /**
             * Paramètres de fonctionnement.
             */
            'dn' => [
                'UTILISATEURS_BASE_DN'                  => 'ou=people,dc=domain,dc=fr',
                'UTILISATEURS_DESACTIVES_BASE_DN'       => 'ou=deactivated,dc=domain,dc=fr',
//                'GROUPS_BASE_DN'                        => 'ou=groups,dc=domain,dc=fr',
//                'STRUCTURES_BASE_DN'                    => 'ou=structures,dc=domain,dc=fr',
            ],
            'filters' => [
                'LOGIN_FILTER'                          => '(supannAliasLogin=%s)',
                //'LOGIN_FILTER'                          => '(uid=%s)',
//                'UTILISATEUR_STD_FILTER'                => '(|(uid=p*)(&(uid=e*)(eduPersonAffiliation=student)))',
//                'CN_FILTER'                             => '(cn=%s)',
//                'NAME_FILTER'                           => '(cn=%s*)',
//                'UID_FILTER'                            => '(uid=%s)',
//                'NO_INDIVIDU_FILTER'                    => '(supannEmpId=%08s)',
//                'AFFECTATION_FILTER'                    => '(&(uid=*)(eduPersonOrgUnitDN=%s))',
//                'AFFECTATION_CSTRUCT_FILTER'            => '(&(uid=*)(|(ucbnSousStructure=%s;*)(supannAffectation=%s;*)))',
//                'LOGIN_OR_NAME_FILTER'                  => '(|(supannAliasLogin=%s)(cn=%s*))',
//                'MEMBERSHIP_FILTER'                     => '(memberOf=%s)',
//                'AFFECTATION_ORG_UNIT_FILTER'           => '(eduPersonOrgUnitDN=%s)',
//                'AFFECTATION_ORG_UNIT_PRIMARY_FILTER'   => '(eduPersonPrimaryOrgUnitDN=%s)',
//                'ROLE_FILTER'                           => '(supannRoleEntite=[role={SUPANN}%s][type={SUPANN}%s][code=%s]*)',
//                'PROF_STRUCTURE'                        => '(&(eduPersonAffiliation=teacher)(eduPersonOrgUnitDN=%s))',
//                'FILTER_STRUCTURE_DN'		            => '(%s)',
//                'FILTER_STRUCTURE_CODE_ENTITE'	        => '(supannCodeEntite=%s)',
//                'FILTER_STRUCTURE_CODE_ENTITE_PARENT'   => '(supannCodeEntiteParent=%s)',
            ],
        ],
        
        //...
    ],
```

Toujours dans `config/autoload/xxxx.secret.local.php`, mais cette fois sous la clé `'unicaen-auth'`, vous devez spécifier 
l'attribut LDAP à utiliser pour extraire l'identifiant de connexion (login) d'un utilisateur, exemple :

```php
    'unicaen-auth' => [
        /**
         * Attribut LDAP utilisé pour extraire le username/login d'un utilisateur, *en minuscules*.
         */
        'ldap_username' => 'supannaliaslogin',
        //'ldap_username' => 'uid',
        
        //...
    ],
```

Adaptations à faire selon votre contexte
----------------------------------------

- Infos de connexion à l'annuaire LDAP **avec un compte admin** pouvant accéder à tous les attributs LDAP : 
  - `'host'`
  - `'username'`
  - `'password'`

- Config pour le bind LDAP (vérification identifiant + mot de passe de l'utilisateur) :  
  - `'baseDn'` : base pour le bind, adaptez à votre annuaire.
  - `'bindRequiresDn'`: laisser à `true`.
  - `'accountFilterFormat'` : corriger par exemple en `'(&(objectClass=posixAccount)(uid=%s))'` si l'identifiant de 
    connexion de vos utilisateurs est un "uid".

- Paramètres et filtres de recherche LDAP :
  - `'UTILISATEURS_BASE_DN'` : adaptez à votre annuaire, c'est sans doute pareil au `'baseDn'`ci-dessus.
  - `'UTILISATEURS_DESACTIVES_BASE_DN'` : mettez la même valeur que pour `'UTILISATEURS_BASE_DN'`.
  - `'LOGIN_FILTER'` : corriger par exemple en `'(uid=%s)'` si l'identifiant de 
    connexion de vos utilisateurs est un "uid".
