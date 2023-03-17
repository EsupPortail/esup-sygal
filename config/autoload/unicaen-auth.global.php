<?php

use Application\Authentication\Storage\AppStorage;
use Application\Authentication\Storage\AppStorageFactory;
use Application\Entity\Db\Utilisateur;
use Application\Provider\IdentityProvider;
use Application\Provider\IdentityProviderFactory;

return [
    'unicaen-auth' => [
        // Identifiants de connexion LDAP autorisés à faire de l'usurpation d'identité.
        // NB: à réserver exclusivement aux tests.
        'usurpation_allowed_usernames' => [],

        // Flag indiquant si l'utilisateur authenitifié avec succès via l'annuaire LDAP doit
        // être enregistré/mis à jour dans la table des utilisateurs de l'appli.
        'save_ldap_user_in_database' => true,

        'enable_privileges' => true,

        // Nom du gestionnaire d'entités à utiliser
        'entity_manager_name' => 'doctrine.entitymanager.orm_default',

        // Fournisseurs d'identité.
        'identity_providers'  => [
            300 => \UnicaenAuthentification\Provider\Identity\Basic::class,   // en 1er
            200 => \UnicaenAuthentification\Provider\Identity\Db::class,      // en 2e
            110 => \Application\Provider\IdentityProvider::class, // en 3e
            100 => \Formation\Provider\IdentityProvider::class,   // en 4e
        ],
    ],

    'bjyauthorize' => [
        // this module uses a meta-role that inherits from any roles that should
        // be applied to the active user. the identity provider tells us which
        // roles the "identity role" should inherit from.
        // for ZfcUser, this will be your default identity provider
        'identity_provider' => \UnicaenAuthentification\Provider\Identity\Chain::class,

        // role providers simply provide a list of roles that should be inserted
        // into the Zend\Acl instance. the module comes with two providers, one
        // to specify roles in a config file and one to load roles using a
        // Laminas\Db adapter.
        'role_providers'    => [
            //
            // Fournit les rôles issus de la base de données éventuelle de l'appli.
            // NB: si le rôle par défaut 'guest' est fourni ici, il ne sera pas ajouté en double dans les ACL.
            // NB: si la connexion à la base échoue, ce n'est pas bloquant!
            \UnicaenUtilisateur\Provider\Role\DbRole::class   => [],
            // Fournit le rôle correspondant à l'identifiant de connexion de l'utilisateur.
            // Cela est utile lorsque l'on veut gérer les habilitations d'un utilisateur unique
            // sur des ressources.
            //\UnicaenUtilisateur\Provider\Role\Username:class => [],
        ],
    ],
    'zfcuser'      => [
        // Classe de l'entité représentant un utilisateur.
        // Décommenter pour spécifier une autre classe que celle par défaut (\UnicaenUtilisateur\Entity\Db\User).
        // NB: la classe spécifiée doit hériter de \UnicaenUtilisateur\Entity\Db\AbstractUser.
        'user_entity_class' => Utilisateur::class,

        // Enable registration
        // Allows users to register through the website.
        // Accepted values: boolean true or false
        'enable_registration' => true,
    ],

    'service_manager' => [
        'invokables' => [
        ],
        'factories' => [
            AppStorage::class => AppStorageFactory::class,
            \UnicaenAuthentification\Authentication\Storage\Chain::class => 'Application\Authentication\Storage\ChainServiceFactory',
            IdentityProvider::class => IdentityProviderFactory::class,
        ]
    ],
];

//
// Config des privilèges déplacée dans module/Application/config/others/droits.config.php
//
//if ($settings['enable_privileges']) {
//    $privileges = [
//        // ... déplacé dans module/Application/config/others/droits.config.php
//    ];
//} else {
//    $privileges = [];
//}
//
//return array_merge_recursive($config, $privileges);