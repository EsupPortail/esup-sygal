# Installation de ESUP-SyGAL



## Création de la base de données

À partir de la version 3.0.0, SyGAL s'appuie sur une base de données PostgreSQL et non plus Oracle.

Reportez-vous au [README consacré à la création de la base de données](database/README.md).



## Installation sur un serveur

Pour ce qui est de l'installation du serveur d'application, n'ayant pas à Caen les compétences 
en déploiement Docker autres que pour le développement, nous documenterons une installation à l'ancienne sur 
un serveur *entièrement dédié à l'application*.
Si vous voulez déployer l'application avec Docker, faites-le à partir du `Dockerfile` présent et n'hésitez pas à 
proposer votre contribution pour améliorer cette doc d'install !


### Obtention des sources de l'application

*NB : la procédure proposée ici part d'un serveur *Debian Stretch* tout nu et couvre l'installation de tous les packages 
requis.* Si ce n'était pas le cas, merci de contribuer en le signalant.

En `root` sur votre serveur, pour obtenir les sources de SyGAL, lancez l'une des commandes suivantes en fonction 
du site sur lequel vous lisez la présente page :
```bash
# Si vous êtes sur git.unicaen.fr :
git clone https://git.unicaen.fr/open-source/sygal.git /app

# Si vous êtes sur github.com :
git clone https://github.com/EsupPortail/sygal.git /app
```

*NB : merci de respecter dans un premier temps le choix de `/app` comme répertoire d'installation. 
Libre à vous une fois que tout fonctionne de changer d'emplacement et de modifier en conséquence les configs
nécessaires.*


### Configuration du serveur

#### Packages, etc.

- Récupérez quelque part sur le serveur l'image Docker "Unicaen" puis placez-vous dans le répertoire créé :
```bash
UNICAEN_IMAGE_TMP_DIR=/tmp/docker-unicaen-image
git clone https://git.unicaen.fr/open-source/docker/unicaen-image.git ${UNICAEN_IMAGE_TMP_DIR}
cd ${UNICAEN_IMAGE_TMP_DIR}
```

- Lancez-y le script `Dockerfile-7.x.sh` conçu pour la version 7.4 de PHP. 
```bash
PHP_VERSION=7.4
bash Dockerfile-7.x.sh ${PHP_VERSION}
```

- Ensuite, quelque part sur le serveur, récupérez cette fois l'image Docker `sygal-image` puis placez-vous dans 
le répertoire créé :
```bash
SYGAL_IMAGE_TMP_DIR=/tmp/sygal-image
git clone https://git.unicaen.fr:open-source/docker/sygal-image.git ${SYGAL_IMAGE_TMP_DIR}
cd ${SYGAL_IMAGE_TMP_DIR}
```

- Lancez-y le script `Dockerfile.sh` avec en argument la version de PHP choisie précédemment :
```bash
bash Dockerfile.sh ${PHP_VERSION}
```

Ensuite, vérifiez et ajustez si besoin sur votre serveur les fichiers de configs suivants,
créés ou modifiés par le script `Dockerfile.sh` :
- ${APACHE_CONF_DIR}/ports.conf
- ${APACHE_CONF_DIR}/sites-available/app.conf
- ${APACHE_CONF_DIR}/sites-available/app-ssl.conf
- ${PHP_CONF_DIR}/fpm/pool.d/www.conf
- ${PHP_CONF_DIR}/fpm/conf.d/99-app.ini
- ${PHP_CONF_DIR}/cli/conf.d/99-app.ini

NB : Vérifiez dans le script `Dockerfile.sh` que vous venez de lancer mais normalement 
`APACHE_CONF_DIR=/etc/apache2` et `PHP_CONF_DIR=/etc/php/${PHP_VERSION}`.

#### Environnement de fonctionnement

La variable `APPLICATION_ENV` déclarée dans la config Apache `${APACHE_CONF_DIR}/sites-available/app-ssl.conf` permet
de spécifier à l'application PHP dans quel "environnement de fonctionnement" elle tourne.
Notamment, lorsque sa valeur est `development`, cela active l'affichage détaillé des erreurs rencontrées par SyGAL :
```apacheconf
<VirtualHost *:443>
     # ...
     SetEnv APPLICATION_ENV "development"
# ...
```

#### Logs d'erreur PHP-FPM

- Prenez connaissance du chemin spécifié par le paramètre `error_log` du fichier de config `/etc/php/${PHP_VERSION}/fpm/php-fpm.conf`, 
exemple :
```conf
error_log = /var/log/php7.4-fpm.log
```

- Créez le fichier de log avec le propriétaire qui va bien, exemple :
```bash
FPM_PHP_LOG_FILE=/var/log/php7.4-fpm.log
touch ${FPM_PHP_LOG_FILE} && chown www-data:www-data ${FPM_PHP_LOG_FILE}
```


### Installation d'une version précise de l'application

Normalement, vous ne devez installer que les versions officielles, c'est à dire les versions taguées, du genre `4.0.0`
par exemple.

Placez-vous dans le répertoire des sources de l'application puis lancez les commandes suivantes pour obtenir la liste des
versions officielles (taguées) :
```bash
git fetch && git fetch --tags && git tag
```

Si la version la plus récente est par exemple la `4.0.0`, utilisez les commandes suivantes pour "installer" cette version 
sur votre serveur :
```bash
git checkout --force 4.0.0
```


### Mode développement vs. production

Pour commencer, placez l'application en mode "développement" afin d'activer l'affichage détaillé des futures erreurs 
rencontrées. Pour cela, placez-vous dans le répertoire des sources de l'application puis lancez la commande suivante :
```bash
vendor/bin/laminas-development-mode enable
```

Lorsque l'application sera sur un serveur de production, il faudra penser à désactiver le mode "développement" :
```bash
vendor/bin/laminas-development-mode disable
```


### Configuration du moteur PHP pour SyGAL

Si vous êtes sur un serveur de PROD, corrigez les lignes suivantes du fichier de config PHP 
`/etc/php/${PHP_VERSION}/fpm/conf.d/90-app.ini` :
```
    display_errors = Off
    #...
    opcache.enable = 1
    #...
    xdebug.remote_enable = 0
```


### Fichiers de config de l'application

Placez-vous dans le répertoire de l'application puis descendez dans le répertoire `config/autoload/`.

Supprimez l'extension `.dist` des fichiers `local.php.dist` et `secret.local.php.dist`, et préfixez-les pour bien
signifier l'environnement de fonctionnement concerné (version de production, de test, de developement). 
Exemple :
```bash
APPLICATION_ENV="production"
cp -n local.php.dist        ${APPLICATION_ENV}.local.php 
cp -n secret.local.php.dist ${APPLICATION_ENV}.secret.local.php
```

Dans la suite, vous adapterez le contenu de ces fichiers à votre situation.


#### Fichier `unicaen-app.global.php`

- Adaptez les URL des pages "Mentions légales" et "Informatique et liberté" pour votre établissement :

```php
    'unicaen-app' => [
        'app_infos' => [
            //...
            'mentionsLegales'        => "http://www.unicaen.fr/acces-direct/mentions-legales/",
            'informatiqueEtLibertes' => "http://www.unicaen.fr/acces-direct/informatique-et-libertes/",
```

#### Fichier `${APPLICATION_ENV}.local.php`

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
                        // NB : Spécifier la classe 'logo-etablissement' sur une page de navigation provoque le "remplacement"
                        //     du label du lien par l'image 'public/logo-etablissement.png' (à créer le cas échéant).
```
*NB : ensuite créez le fichier `public/logo-etablissement.png` correspondant au logo de votre établissement.*

- Adaptez le chemin du répertoire où seront stockés les fichiers uploadés par les utilisateurs de l'application :

```php
    'fichier' => [
        'root_dir_path' => '/app/upload',
    ],
```
*NB : ce répertoire doit être autorisé en écriture à l'utilisateur `www-data` (ou équivalent).*

#### Fichier `${APPLICATION_ENV}.secret.local.php`

- Dans la config de connexion au WS, `UCN` doit être remplacé par le code établissement choisi lors
de la création de votre établissement dans la base de données (dans le script [`08_init.sql`](database/sql/08_init.sql)) :

```php
    'import' => [
        'connections' => [
            'sygal-import-ws-UCN' => [ // <<<<<<<<<<<<<< remplacer 'UCN' par votre code établissement
                'url' => 'https://host.domain.fr',
                'proxy' => false,
                'verify' => false, // si true et faux certif : cURL error 60: SSL certificate problem: self signed certificate
                'user' => 'xxxxxx',
                'password' => 'yyyyyy',
                'connect_timeout' => 10,
            ],
        ],
```

- Idem dans la config des imports/synchros juste après :

```php
        'imports' => \Application\generateConfigImportsForEtabs($etabs = ['UCN']), // <<<<<<<<<<<<<< remplacer 'UCN' par votre code établissement
        'synchros' => \Application\generateConfigSynchrosForEtabs($etabs),
```

- Renseignez les infos de connexion à la base de données de l'application :

```php
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'host'     => 'host.domain.fr',
                    'dbname'   => 'sygal',
                    'port'     => '5432',
                    'user'     => $user = 'ad_sygal',
                    'password' => 'xxxxxxxxxxx',
                ],
```

- Les lignes de config suivantes permettent de simuler l'authentification Shibboleth d'un premier utilisateur
  'premierf@xxxxx.fr' créé en base de données avec le rôle "Administrateur technique" (par le script
  [`09_create_fixture.sql`](database/sql/09_create_fixture.sql)).
  Cela permet d'avoir un accès quasi omnipotent à l'application, notamment aux pages de gestion des droits d'accès.

```php
    'unicaen-auth' => [
        'shib' => [
            'simulate' => [
                'HTTP_EPPN'           => $eppn = 'premierf@domaine.fr',
                'HTTP_SUPANNEMPID'    => '00012345',
                'HTTP_DISPLAYNAME'    => $eppn,
                'HTTP_MAIL'           => $eppn,
                'HTTP_GIVENNAME'      => 'François',
                'HTTP_SN'             => 'Premier',
                'HTTP_SUPANNCIVILITE' => 'M.',
```

- Théoriquement, à ce stade l'application SyGAL devrait être accessible.

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



## Script d'install et d'init des dépendances

Lancez le script suivant :

```bash
bash install.sh
```



## Dans l'application SyGAL elle-même

Si vous n'avez rien changé à la config de l'application concernant Shibboleth et si vous cliquez en haut à droite de
la page d'accueil de SyGAL sur "Connexion" puis sur "Fédération d'identité", vous devriez être dans la peau de 
François Premier, administrateur technique de test créé en base de données (dans le script [`08_init.sql`](database/sql/08_init.sql)).


### Droits d'accès

Dans l'application SyGAL, allez dans menu "Administration" > "Droits d'accès" > "Gestion des profils de rôle".

Appliquez, svp : 
- le profil `ADMIN_TECH` au rôle *Administrateur technique*
- le profil `OBSERV` au rôle *Observateur*
- le profil `DOCTORANT` au rôle *Doctorant UCN*
- le profil `ADMIN` au rôle *Administrateur UCN*
- le profil `BU` au rôle *Bibliothèque universitaire UCN*
- le profil `BDD` au rôle *Bureau des doctorats UCN*

Par exemple, pour appliquer le profil `ADMIN_TECH` au rôle *Administrateur technique*, il faut :
- cliquer sur l'icône bleu en forme de maillon de chaîne tout au bout de la ligne "ADMIN_TECH" du tableau "Liste des profils" ;
- dans la page qui s'ouvre, sélectionner "Administrateur technique" dans la liste déroulante de droite ;
- appuyer sur le bouton "Ajouter un rôle".

NB : "UCN" n'est qu'un exemple et pour vous ce sera le code établissement choisi lors
de la création de votre établissement dans la base de données (dans le script [`08_init.sql`](database/sql/08_init.sql)) 



## Import des données

ESUP-Sygal doit pouvoir importer les thèses, acteurs des thèses, etc. depuis le SI Scolarité de votre établissement 
(soit Apogée, soit Physalis). Pour cela ESUP-SyGAL fournit un web service (API REST) que vous devez installer.


### Installation du web service

Vous devez à présent installer le web service d'import de données. Reportez-vous au projet `sygal-import-ws` 
sur [sur github.com/EsupPortail](https://github.com/EsupPortail/sygal-import-ws).


### Lancement de l'import seul

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


### Programmation des tâches périodiques

Un certains nombres de tâches périodiques doivent être programmées sur le serveur. 
Pour cela, créez le fichier `/etc/cron.d/sygal` et adaptez le contenu suivant à votre contexte :

```cron
#
# Application SyGAL.
#

APP_DIR=/app

##### Import des données des établissements #####
0,30 7-19 * * 1-5 root ETAB=UCN $APP_DIR/bin/run-import.sh >>/tmp/cron-sygal-import-ws.log  2>&1

##### Traitements en fonction des résultats de l'import #####
25,55 7-19 * * 1-5 root /usr/bin/php $APP_DIR/public/index.php process-observed-import-results --etablissement=UCN >>/tmp/sygal-process-observed-import-results.log 2>&1

##### Ménage dans /tmp #####
0 4 * * * root bash $APP_DIR/bin/purge_temp_files.sh 1> /tmp/sygal_purge_temp_files.sh.log 2>&1
```

*NB : `UCN` doit être remplacé par le code établissement choisi lors de la création de votre établissement.*
