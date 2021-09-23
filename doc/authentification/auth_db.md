Authentification avec un compte local en base de données
========================================================

Ce mode d'authentification **doit** être activé.

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
            'description' => "Utilisez ce formulaire si vous possédez un compte local dédié à l'application.",
            
            /**
             * Mode d'authentification à l'aide d'un compte dans la BDD de l'application.
             */
            'db' => [
                /**
                 * Activation ou non de ce mode d'authentification.
                 */
                'enabled' => true, // doit être activé 
            ],
            
            //...
        ],
```
