Installation de ESUP-SyGAL
==========================


Création de la base de données
------------------------------

Reportez-vous au [README consacré à la création de la base de données](database/README.md).



Installation du serveur d'application
-------------------------------------

Pour ce qui est de l'installation du serveur d'application, n'ayant pas à Caen les compétences 
en déploiement Docker (autres que pour le développement), nous documenterons une installation manuelle sur 
un serveur *entièrement dédié à l'application*.
Si vous voulez déployer l'application avec Docker, faites-le à partir du `Dockerfile` présent et n'hésitez pas à 
contribuer en améliorant cette doc d'install !

**NB :** La procédure proposée ici part d'un serveur *Debian Bullseye* tout nu. Elle couvre normalement l'installation
de tous les packages requis, mais si ce n'était pas le cas merci de contribuer en le signalant.


### Case départ : obtention des sources de l'application

En `root` sur votre serveur, obtenez les sources d'ESUP-SyGAL en lançant l'une des commandes suivantes en fonction
du site sur lequel vous lisez la présente doc :
```bash
# Si vous êtes sur git.unicaen.fr :
git clone https://git.unicaen.fr/open-source/sygal.git /app

# Si vous êtes sur github.com :
git clone https://github.com/EsupPortail/sygal.git /app
```

*NB : merci de respecter dans un premier temps le choix de `/app` comme répertoire d'installation.
Si vous êtes à l'aise, libre à vous une fois que tout fonctionne de changer d'emplacement et de modifier en conséquence
les configs nécessaires sur le serveur.*

Normalement, vous ne devez installer que les versions officielles, c'est à dire les versions taguées, du genre `8.2.0`
par exemple.

Placez-vous dans le répertoire des sources de l'application puis lancez les commandes suivantes pour obtenir la liste des
versions officielles (taguées) :
```bash
git fetch && git fetch --tags && git tag
```

Si la version la plus récente est par exemple la `8.2.0`, utilisez les commandes suivantes pour "installer" cette version
sur votre serveur :
```bash
git checkout --force 8.2.0
```


### Script `Dockerfile.sh`

- Vous trouverez dans le répertoire des sources d'ESUP-SyGAL récupérées à l'instant un script `Dockerfile.sh`, 
  sorte de version sh du Dockerfile, contenant de quoi mettre à niveau et/ou installer les packages nécessaires.

- Vous ne devez pas le lancer d'un seul bloc, ouvrez-le dans un autre terminal pour l'avoir sous la main.

- Lisez et appliquez les pré-requis mentionnés dans les commentaires en entête du script.

- Copiez-collez-lancez les commandes qu'il contient par petits groupes.



Configuration du serveur
------------------------

### Apache

- Vérifiez et ajustez si besoin sur votre serveur les fichiers de configs suivants,
  créés par le script `Dockerfile.sh` (vérifiez dans le script mais normalement
  `APACHE_CONF_DIR=/etc/apache2`) :
  - ${APACHE_CONF_DIR}/ports.conf
  - ${APACHE_CONF_DIR}/sites-available/app.conf
  - ${APACHE_CONF_DIR}/sites-available/app-ssl.conf

- La variable `APPLICATION_ENV` doit être déclarée dans la config Apache `${APACHE_CONF_DIR}/sites-available/app-ssl.conf` 
  pour spécifier à l'application PHP dans quel "environnement de fonctionnement" elle tourne. 
  Les valeurs possibles sont `development`, `testing` et `production`.
```apacheconf
<VirtualHost *:443>
     # ...
     SetEnv APPLICATION_ENV "testing"
# ...
```

### PHP

- Vérifiez et ajustez si besoin sur votre serveur les fichiers de configs suivants,
  créés par le script `Dockerfile.sh` (vérifiez dans le script mais normalement
  `PHP_CONF_DIR=/etc/php/${PHP_VERSION}`) :
  - ${PHP_CONF_DIR}/fpm/pool.d/99-sygal.conf
  - ${PHP_CONF_DIR}/fpm/conf.d/99-sygal.ini

- Si vous êtes sur un serveur de PROD, corrigez les lignes suivantes du fichier de config PHP
  `/etc/php/${PHP_VERSION}/fpm/conf.d/99-sygal.ini` :
```
display_errors = Off
display_startup_errors = Off
display_errors = Off
#...
opcache.enable = 1
#...
xdebug.mode = off
```

- Ajoutez ceci à la fin du fichier de config `/etc/php/${PHP_VERSION}/fpm/pool.d/www.conf` et
  adaptez les valeurs selon que vous souhaitez activer les logs PHP-FPM ou non :
```conf
catch_workers_output = yes
php_flag[display_errors] = on
php_admin_value[error_log] = /var/log/php-fpm.log
php_admin_flag[log_errors] = on
```

### ImageMagick

Pour que l'appli soit en mesure de générer l'aperçu de la page de couverture d'une thèse, il est nécessaire de
configurer l'outil ImageMagick sur le serveur d'application.
Dans le fichier de config `/etc/ImageMagick-6/policy.xml` (à la version près), décommentez (le cas échéant) ou 
ajoutez la ligne suivante :

`<policy domain="coder" rights="read|write" pattern="PDF" />`

Si ce n'est pas fait vous rencontrerez une erreur du genre "attempt to perform an operation not allowed by the security policy `PDF'"
dans la fenêtre sensée affichée un aperçu d'une page de couverture.



Configuration de l'application
------------------------------

Placez-vous dans le répertoire des sources de l'application.

### Mode développement vs. production

Pour commencer, placez l'application en mode "développement" afin d'activer l'affichage détaillé des futures erreurs
rencontrées par l'application. Pour cela, placez-vous dans le répertoire des sources de l'application puis lancez 
la commande suivante :
```bash
vendor/bin/laminas-development-mode enable
```

Lorsque l'application sera sur un serveur de production, il faudra penser à désactiver le mode "développement" :
```bash
vendor/bin/laminas-development-mode disable
```

### Fichier `config/autoload/{dev|test|prod}.local.php`

- Supprimez l'extension `.dist` du fichier [`config/autoload/local.php.dist`](../config/autoload/local.php.dist), 
  et préfixez-le par `prod.`, `test.` ou `dev.` pour bien signifier l'environnement de fonctionnement
  (*n'utilisez pas le préfixe `development.` qui est réservé*), exemple :
```bash
cp local.php.dist prod.local.php
```

- Dans ce fichier, adaptez les URL des pages "Mentions légales" et "Informatique et liberté" pour votre établissement :

```php
    'unicaen-app' => [
        //...
        'app_infos' => [
            'mentionsLegales'        => "http://www.unicaen.fr/acces-direct/mentions-legales/",
            'informatiqueEtLibertes' => "http://www.unicaen.fr/acces-direct/informatique-et-libertes/",
```

- Adaptez le `'label'`, `'title'` et `'uri'` du lien mentionnant votre établissement dans le pied de page de 
  l'application :

```php
    'navigation'   => [
        'default' => [
            'home' => [
                'pages' => [
                    'etab' => [
                        'label' => _("Normandie Université"),
                        'title' => _("Page d'accueil du site de Normandie Université"),
                        'uri'   => 'http://www.normandie-univ.fr',
                        'class' => 'logo-etablissement',
                        // NB : Spécifier la classe 'logo-etablissement' sur la page de navigation provoque le "remplacement"
                        //     du label du lien par l'image 'public/logo-etablissement.png' (à créer).
```
*NB : ensuite créez le fichier `public/logo-etablissement.png` correspondant au logo de votre établissement.*


### Fichier `config/autoload/{dev|test|prod}.secret.local.php`

- Supprimez l'extension `.dist` du fichier [`config/autoload/secret.local.php.dist`](../config/autoload/secret.local.php.dist),
  et préfixez-le par `prod.`, `test.` ou `dev.` pour bien signifier l'environnement de fonctionnement
  (*n'utilisez pas le préfixe `development.` qui est réservé*), exemple :
```bash
cp secret.local.php.dist prod.secret.local.php
```

- Dans ce fichier, adaptez le chemin du répertoire où seront stockés les fichiers uploadés par les utilisateurs
  de l'application :

```php
    'fichier' => [
        'storage' => [
            'adapters' => [
                \Fichier\Service\Storage\Adapter\FilesystemStorageAdapter::class => [
                    'root_path' => '/app/upload',
                ],
            ],
        ],
    ],
```
*NB : ce répertoire doit être autorisé en écriture à l'utilisateur `www-data`.*

- Renseignez les infos de connexion à la base de données de l'application :

```php
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'host'     => 'host.domain.fr',
                    'dbname'   => 'sygal',
                    'port'     => '5432',
                    'user'     => 'ad_sygal',
                    'password' => 'xxxxxxxxxxx',
                ],
```

- Ajustez si besoin la config concernant l'envoi de mails et dans un premier temps activez la "redirection" de tous 
  les mails envoyés par l'application vers une ou plusieurs adresses existantes, exemple :
  
```php
    'unicaen-app' => [
        'mail' => [
            // ...
            'transport_options' => [
                'host' => 'smtp.domaine.fr',
                'port' => 25,
            ],
            'from' => 'ne-pas-repondre@domaine.fr',
            'redirect_to' => [
                'votre.email@domaine.fr',
            ],
            // ...
```


### Fichier `config/users.htpasswd`

ESUP-SyGAL expose une API permettant d'obtenir de Pégase les inscriptions en 3e cycle.
Un fichier `users.htpasswd` contient les comptes utilisateurs / mots de passe autorisés à interroger cette API
au regard de l'authentification HTTP Basic.

Placez-vous dans le répertoire [`config`](config) des sources et lancez la
commande suivante pour créer le fichier `users.htpasswd` contenant un utilisateur `sygal-app` dont le mot de passe
vous sera demandé :
```bash
htpasswd -c users.htpasswd sygal-app
```

Si vous manquez d'idée pour le mot de passe, utilsez la commande suivante :
```bash
pwgen 16 1 --symbols --secure
```



Script `install.sh`
-------------------

Le script `install.sh` situé à la racine des sources du web service doit être lancé à chaque fois
qu'une nouvelle version de l'application est téléchargée/installée :

```bash
bash ./install.sh
```




Test
----

Théoriquement, à ce stade la page d'accueil de l'application devrait être accessible dans un navigateur web.



Authentification
----------------

L'un des scripts de création de la base de données a créé un compte utilisateur de test local possédant le rôle
"Administrateur technique".

- Reprenez le fichier de config dans lequel vous avez renseigné certains paramètres de ce compte local de test,
  retrouvez la valeur choisie pour le token `TEST_USER_PASSWORD_RESET_TOKEN` puis ouvrez ESUP-SyGAL
  à l'adresse `https://<server>/utilisateur/init-compte/<token>` (en remplaçant `<server>` par l'adresse de votre
  serveur et `<token>` par le token en question. Vous serez invité·e à choisir le mot de passe du compte local de test.

- Une fois le mot de passe choisi, vous pourrez vous authentifier avec ce compte en cliquant sur "Connexion" en haut
  à droite de la page d'accueil de l'appli puis en choisissant la connexion "Avec un compte local".

- Attention, le rôle "Administrateur technique" permet d'avoir un accès quasi omnipotent à l'application, notamment
  aux pages de gestion des droits d'accès et de création de comptes utilisateurs locaux.

La [documentation consacrée à l'authentification](authentification/auth.md) aborde les autres modes d'authentification
possibles ainsi que l'usurpation d'identité.



Import des données depuis le SI Scolarité de votre établissement
----------------------------------------------------------------

ESUP-Sygal doit pouvoir importer les thèses, acteurs des thèses, etc. depuis le SI Scolarité de votre établissement 
(soit Apogée, soit Physalis). Pour cela ESUP-SyGAL s'accompagne d'un web service (API REST) que vous devez installer.


### Installation du web service

Vous devez à présent installer le web service d'import de données. Reportez-vous au projet `sygal-import-ws` 
sur [sur github.com/EsupPortail](https://github.com/EsupPortail/sygal-import-ws).


### Configuration d'accès au web service

Dans le fichier de config `{dev|test|prod}.secret.local.php` :

- Dans la config de connexion au WS, `UCN` doit être remplacé par le code établissement choisi lors
  de la création de votre établissement par le script d'init de la base de données (cf. scripts de création de la bdd) :

```php
    'import' => [
        'connections' => [
            'sygal-import-ws-UCN' => [ // <<<<<<< remplacer 'UCN' par votre code établissement
                //...
            ],
        ],
```

- Idem dans la config des imports/synchros juste après :

```php
        'imports' => \Application\Config::generateConfigImportsForEtabs($etabs = ['UCN']), // <<<<<<<< remplacer 'UCN' par votre code établissement
        'synchros' => \Application\Config::generateConfigSynchrosForEtabs($etabs),
```

- La config de connexion au WS doit être complétée :

```php
    'import' => [
        'connections' => [
            'sygal-import-ws-UCN' => [
                'url' => 'https://host.domain.fr/v2',   // URL d'accès au WS. 'v2' signifie que vous avez installé la version 2.x.x du WS.
                'proxy' => false,
                'verify' => false,                      // Si true et faux certif : cURL error 60: SSL certificate problem: self signed certificate.
                'user' => 'xxxxxx',                     // Utilisateur autorisé à interroger le WS et...
                'password' => 'yyyyyy',                 // son mot de passe associé.
                'connect_timeout' => 10,
            ],
        ],
```


### Ligne de commande

#### Lancement de l'import seul

Il s'agit de l'interrogation du web service pour remplir les tables temporaires TMP_*.

    php public/index.php import-all --etablissement=UCN --synchronize=0 --breakOnServiceNotFound=0

*NB : `UCN` doit être remplacé par le code établissement choisi lors de la création de votre établissement.*

#### Lancement de la synchro seule

Il s'agit de la synchronisation des tables définitives de travail de l'application avec les tables TMP_* 
contenant les données déjà importées.

    php public/index.php run synchro --all

Pour plus de détails, vous pouvez vous reporter à la documentation sur les [lignes de commandes](cli.md).

Une fois la synchro effectuée, vous devriez voir des thèses apparaître en cliquant sur le menu "Thèses" de 
l'application.

#### Lancement des 2

Pour lancer l'interrogation du web service *puis* la synchronisation des tables définitives de travail, faites :

    ETAB=UCN bin/run-import.sh
    
*NB : `UCN` doit être remplacé par le code établissement choisi lors de la création de votre établissement.*



Programmation des tâches périodiques
------------------------------------

Un certains nombres de tâches périodiques doivent être programmées sur le serveur d'application. 

- Créez sur le serveur d'application le fichier de config CRON `/etc/cron.d/sygal`
  identique au fichier [doc/cron/sygal](cron/sygal) fourni.

- Adaptez obligatoirement les éléments suivants :
  - variable `APP_DIR` : chemin vers le répertoire d'installation de l'application.
  - variable `ETAB` : code établissement choisi lors de la création de la bdd, ex 'UCN'.

- Adaptez éventuellement :
  - les chemins vers les différents fichiers de log (cf. redirections).
  - les périodicités d'exécution.
