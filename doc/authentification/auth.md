Authentification
================

L'application propose 4 modes d'authentification différents :

- [via la fédération d'identité Renater (Shibboleth)](auth_shib.md) : `shib` ;
- [via un serveur CAS (SSO)](auth_cas.md) : `cas` ;
- [auprès d'un annuaire LDAP](auth_ldap.md) : `ldap` ;
- [avec un compte local dans la BDD de l'application](auth_db.md) : `db`.

Plusieurs modes d'authentification peuvent être activés/proposés simultanément, 
exemple : "Fédération d'identité" et "Compte local dans la BDD". 

NB : Les modes `ldap` et `db` sont regroupés sous le pseudo-mode `local` du fait qu'il partage le même
formulaire de connexion.

La configuration de l'authentification se trouve dans les fichiers 
`config/autoload/xxxx.local.php` et 
`config/autoload/xxxx.secret.local.php` sous la clé `'unicaen-auth'`.

**NB :** 
  - Le mode d'authentification `shib` est fortement recommandé pour que les personnes extérieures à votre SI mais 
    dont l'établissement fait partie de la fédération d'identité Renater puissent s'authentifier sur ESUP-SyGAL avec 
    leur compte établissement.
  - Le mode d'authentification `db` doit obligatoirement être activé pour garantir que des personnes extérieures 
    à votre SI et dont l'établissement ne faisant pas partie de la fédération d'identité Renater puissent s'authentifier 
    sur ESUP-SyGAL (ex : codirecteurs et rapporteurs étrangers).


Usurpation d'identité
---------------------

L'application permet d'usurper l'identité d'un utilisateur, autrement dit de se faire passer pour lui.

Cette fonctionnalité est intéressante lorsqu'on fait de la documentation et/ou qu'on veut valider le bon fonctionnement 
pour un rôle particulier. **Elle est bien entendu réservée à une instance de test ou de formation et pas à une instance 
de production.**

Seuls les comptes utilisateurs listés dans la configuration seront habilités à usurper une identité.

Exemple de configuration dans `config/autoload/xxxx.secret.local.php` :

```php
    'unicaen-auth' => [
        //...
        /**
         * Identifiants de connexion des utilisateurs autorisés à faire de l'usurpation d'identité.
         * (NB: à réserver exclusivement aux instances de test/formation.)
         */
        'usurpation_allowed_usernames' => [
            'username', // format d'un compte LDAP
            'e.mail@domain.fr', // format d'un compte local (BDD) 
            'eppn@domain.fr', // format Shibboleth
        ],
    ],
```

L'usurpation d'identité est proposée aux utilisateurs habilités à 3 endroits différents dans l'application :
- sur la fiche Thèse (ex : usurpation de l'identité du doctorant) : bouton à cliquer ;
- sur la fiche d'un utilisateur (menu Administration > Utilisateurs, recherche puis sélection de l'utilisateur) : bouton à cliquer ;
- sur l'encart s'affichant lorsqu'on clique sur son nom dans le bandeau supérieur des pages de l'application : champ de saisie d'un identifiant de connexion.

> NB : par définition on usurpe un compte *utilisateur* donc seules les personnes existant dans la table des utilisateurs
peuvent faire l'objet d'une usurpation d'identité.
