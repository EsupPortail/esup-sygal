Authentification via un serveur CAS (SSO)
=========================================

Exemple de configuration dans `config/autoload/xxxx.local.php` :

```php
    'unicaen-auth' => [
        //...
        /**
         * Configuration de l'authentification centralisée (CAS).
         */
        'cas' => [
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
            'description' => "Cliquez sur le bouton ci-dessous pour accéder à l'authentification centralisée.",
        ],
```

Exemple de configuration dans `config/autoload/xxxx.secret.local.php` :

```php
    'unicaen-auth' => [
        //...
        /**
         * Configuration de l'authentification centralisée (CAS).
         */
        'cas' => [
            /**
             * Infos de connexion au serveur CAS.
             */
            'connection' => [
                'default' => [
                    'params' => [
                        'hostname' => 'host.domain.fr',
                        'port'     => 443,
                        'version'  => "2.0",
                        'uri'      => "",
                        'debug'    => false,
                    ],
                ],
            ]
        ],
```

Adaptations à faire selon votre contexte :

- Infos de connexion au serveur CAS.
  