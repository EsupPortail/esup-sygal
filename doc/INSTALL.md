# Installation de SyGAL



## Création de la base de données

Reportez-vous au [README consacré à la création de la base de données](database/README.md).



## Installation 

Pour ce qui est de l'installation du serveur d'application, n'ayant pas à Caen les compétences 
en déploiement Docker autres que pour le développement (d'où la présence d'un `Dockerfile` et d'un `docker-compose.yml`
dans les sources), nous documenterons une installation à l'ancienne sur un serveur *entièrement dédié à l'application*.
Si vous voulez déployer l'application avec Docker, faites-le à partir du `Dockerfile` présent et n'hésitez pas à 
proposer votre contribution pour améliorer cette doc d'install !

### Première obtention des sources de l'application

*NB: la procédure proposée ici part d'un serveur *Debian Stretch* tout nu et couvre l'installation de tous les packages 
requis.* Si ce n'était pas le cas, merci de contribuer en le signalant.

En `root` sur votre serveur, pour obtenir les sources de SyGAL, lancez l'une des commandes suivantes en fonction 
du site sur lequel vous lisez la présente page :
```bash
# Si vous êtes sur git.unicaen.fr :
git clone https://git.unicaen.fr/open-source/sygal.git /app

# Si vous êtes sur github.com :
git clone https://github.com/EsupPortail/sygal.git /app
```

*NB: merci de respecter dans un premier temps le choix de `/app` comme répertoire d'installation. 
Libre à vous une fois que tout fonctionne de changer d'emplacement et de modifier en conséquence les configs
nécessaires.*

### Configuration du serveur

Sur le serveur, placez-vous dans le répertoire des sources de SyGAL et jetez un oeil au script `Dockerfile.sh`.
Ce script est en quelque sorte l'équivalent du `Dockerfile` traduit en bash. 
(Vous y verrez que le dépôt git d'une image Docker Unicaen est cloné pour lancer 
son script `Dockerfile.sh` qui est lui aussi l'équivalent du `Dockerfile` de l'image 
traduit en bash.)

Lancez le script `Dockerfile.sh` ainsi :
```bash
cd /app
bash Dockerfile.sh 7.0
```

Ensuite, vérifiez et ajustez si besoin sur votre serveur les fichiers de configs suivants,
créés par le script `Dockerfile.sh` :
- ${APACHE_CONF_DIR}/ports.conf
- ${APACHE_CONF_DIR}/sites-available/app.conf
- ${APACHE_CONF_DIR}/sites-available/app-ssl.conf  
- ${PHP_CONF_DIR}/fpm/pool.d/app.conf
- ${PHP_CONF_DIR}/fpm/conf.d/90-app.ini

NB: Vérifiez dans le script `Dockerfile.sh` que vous venez de lancer mais normalement 
`APACHE_CONF_DIR=/etc/apache2` et `PHP_CONF_DIR=/etc/php/7.0`.

### Installation d'une version précise de l'application

Normalement, vous ne devez installer que les versions officielles, c'est à dire les versions taguées, du genre `1.4.2`
par exemple.

Placez-vous dans le répertoire des sources de l'application puis lancez les commandes suivantes pour obtenir la liste des
versions officielles (taguées) :
```bash
git fetch && git fetch --tags && git tag
```

Si la version la plus récente est par exemple la `1.4.2`, utilisez les commandes suivantes pour "installer" cette version 
sur votre serveur :
```bash
git checkout --force 1.4.2 && bash install.sh
```

### Configuration du moteur PHP pour SyGAL

Si vous êtes sur un serveur de PROD, corrigez les lignes suivantes du fichier de config PHP 
`/etc/php/7.0/fpm/conf.d/90-app.ini` :

    display_errors = Off
    ...
    opcache.enable = 1
    ...
    xdebug.remote_enable = 0

### Fichiers de config de l'application

Placez-vous dans le répertoire de l'application puis descendez dans le répertoire `config/autoload/`.

Supprimez l'extension `.dist` des fichiers `local.php.dist` et `secret.local.php.dist`, ex :
```bash
cp -n local.php.dist        local.php 
cp -n secret.local.php.dist secret.local.php
```

Dans la suite, vous adapterez le contenu de ces fichiers à votre situation.

#### `unicaen-app.global.php`

- Adaptez les URL des pages "Mentions légales" et "Informatique et liberté" pour votre établissement :

```php
    'unicaen-app' => [
        'app_infos' => [
            //...
            'mentionsLegales'        => "http://www.unicaen.fr/acces-direct/mentions-legales/",
            'informatiqueEtLibertes' => "http://www.unicaen.fr/acces-direct/informatique-et-libertes/",
```

#### `local.php`

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
                        // NB: Spécifier la classe 'logo-etablissement' sur une page de navigation provoque le "remplacement"
                        //     du label du lien par l'image 'public/logo-etablissement.png' (à créer le cas échéant).
```
*NB: ensuite créez le fichier `public/logo-etablissement.png` correspondant au logo de votre établissement.*

- Adaptez le chemin du répertoire où seront stockés les fichiers uploadés par les utilisateurs de l'application :

```php
    'fichier' => [
        'root_dir_path' => '/app/upload',
    ],
```
*NB: ce répertoire doit être autorisé en écriture à l'utilisateur `www-data` (ou équivalent).*

#### `secret.local.php`

- Dans la config de connexion au WS suivante, `UCN` doit être remplacé par le code établissement choisi lors
de la création de votre établissement dans la base de données (dans le script `05-init.sql`) :

```php
    'import-api' => [
        'etablissements' => [
            // code établissement => [config]
            'UCN' => [
                'url'      => 'https://sygal-import-ws:443',
                'proxy'    => false,
                'verify'   => false, // si true et faux certif : cURL error 60: SSL certificate problem: self signed certificate
                'user'     => 'xxx',
                'password' => 'yyy',
```

- Renseignez les infos de connexion à la base de données :

```php
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'host'     => 'host.domain.fr',
                    'dbname'   => 'DBNAME',
                    'port'     => '1523',
                    'user'     => $user = 'sygal',
                    'password' => 'xxxxxxxxxxx',
                    'charset'  => 'AL32UTF8',
                    'CURRENT_SCHEMA' => $user,
```

- La config fournie permet de simuler l'authentification Shibboleth de l'utilisateur 'premierf@univ.fr' 
créé en base de données (dans le script `06-test.sql`) avec le rôle "Administrateur technique".
Cela permet d'accéder aux pages de gestion des droits d'accès.

```php
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
```

- Théoriquement, à ce stade l'application SyGAL devrait être accessible.


## Dans l'application SyGAL elle-même

Si vous n'avez rien changé à la config de l'application concernant Shibboleth et si vous cliquez en haut à droite de
la page d'accueil de SyGAL sur "Connexion" puis sur "Fédération d'identité", vous devriez être dans la peau de 
François Premier, administrateur technique de test créé en base de données (dans le script `05-init.sql`).

### Droits d'accès

Dans l'application SyGAL, allez dans menu "Droits d'accès" > "Gestion des profils de rôle".

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

NB: "UCN" n'est qu'un exemple et pour vous ce sera le code établissement choisi lors
de la création de votre établissement dans la base de données (dans le script `05-init.sql`) 

### Import

Allez dans menu "Import" pour contrôler que l'application parvient à contacter votre web service. 
La version de l'API devrait s'afficher, ex: 1.2.4.

Ne tenez pas compte du menu "Lancement" car il n'est pas possible de lancer l'import des données
depuis l'interface graphique.



## En ligne de commande 

### Import de données

Placez-vous dans le répertoire de SyGAL sur le serveur.

Ce qui suit n'est possible que si le web service d'import de données est installé, si ce n'est pas le cas, 
reportez-vous au projet `sygal-import-ws` [sur github.com/EsupPortail](https://github.com/EsupPortail/sygal-import-ws)
ou [sur git.unicaen.fr](https://git.unicaen.fr/open-source/sygal-import-ws).

#### Lancement de l'import

Il s'agit de l'interrogation du web service pour remplir les tables temporaires TMP_*.

    php public/index.php import-all --etablissement=UCN --synchronize=0 --breakOnServiceNotFound=0

*NB: `UCN` doit être remplacé par le code établissement choisi lors
de la création de votre établissement dans la base de données (dans le script `05-init.sql`).*

#### Lancement de la synchro 

Il s'agit de la synchronisation des tables définitives de travail de l'application avec les tables TMP_* 
contenant les données déjà importées.

    php public/index.php synchronize-all

Pour plus de détails, vous pouvez vous reporter à la documentation sur les [lignes de commandes](cli.md).

Une fois la synchro effectuée, vous devriez voir des thèses apparaître en cliquant sur le menu "Thèses" de 
l'application.

#### Lancement des 2

Pour lancer l'interrogation du web service *puis* la synchronisation des tables définitives de travail, faites :

    php public/index.php import-all --etablissement=UCN --synchronize=1 --breakOnServiceNotFound=0
    
*NB: `UCN` doit être remplacé par le code établissement choisi lors
de la création de votre établissement dans la base de données (dans le script `05-init.sql`).*


### Programmation des tâches périodiques

Un certains nombres de tâches périodiques doivent être programmées sur le serveur. 
Pour cela, créez le fichier `/etc/cron.d/sygal` et adaptez le contenu suivant à votre contexte :

```cron
MAILTO=nom.prenom@domaine.fr

#
# Application SyGAL.
#

APP_DIR=/app

##### Traitements en fonction des résultats de l'import #####
0 5-17 * * 1-5 root /usr/bin/php $APP_DIR/public/index.php process-observed-import-results --etablissement=UCN 1> /tmp/sygal-process-observed-import-results.log 2>&1
# (du lundi au vendredi, chaque heure de 5h à 17h)

##### Import des données des établissements #####
*/20 * * * * root /usr/bin/php $APP_DIR/public/index.php import-all --etablissement=UCN --synchronize=1 --breakOnServiceNotFound=0 1> /tmp/cron-sygal-import.log  2>&1
## (toutes les 20 minutes)

##### Ménage dans /tmp #####
0 4 * * * root bash $APP_DIR/bin/purge_temp_files.sh 1> /tmp/sygal_purge_temp_files.sh.log 2>&1
## (tous les jours à 4h du mat')
```

*NB: `UCN` doit être remplacé par le code établissement choisi lors
de la création de votre établissement dans la base de données (dans le script `05-init.sql`).*
