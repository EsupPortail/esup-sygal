# Installation de SyGAL



## Création de la base de données

Reportez-vous au [README consacré à la création de la base de données](doc/database/README.md).



## Installation 

Pour ce qui est de l'installation du serveur d'application, n'ayant pas à Caen les compétences 
en déploiement Docker autres que pour le développement (d'où la présence d'un `Dockerfile` et d'un `docker-compose.yml`
dans les sources), nous documenterons une installation à l'ancienne.
Si vous voulez déployer l'application avec Docker, faites-le à partir du `Dockerfile` présent et n'hésitez pas à 
proposer des améliorations pour cette doc d'install!

### Première obtention des sources de l'application

*NB: la procédure proposée ici part d'un serveur *Debian Stretch* tout nu et couvre l'installation de tous les packages 
requis.* Si ce n'était pas le cas, merci de le signaler.

Sur un serveur *Debian Stretch*, en `root`, lancez les commandes suivantes pour installer git et obtenir les sources de 
SyGAL :
```bash
apt-get update -qq && apt-get install -y git
git clone https://git.unicaen.fr/open-source/sygal.git /var/www/sygal
```

### Configuration du serveur

Sur le serveur, placez-vous dans le répertoire des sources de SyGAL et jetez un oeil au script `Dockerfile.sh`.
Ce script est en quelque sorte l'équivalent du `Dockerfile` traduit en bash. 
(Vous y verrez que le dépôt git d'une image Docker Unicaen est cloné pour lancer 
son script `Dockerfile.sh` qui est lui aussi l'équivalent du `Dockerfile` de l'image 
traduit en bash.)

Lancez le script `Dockerfile.sh` ainsi :
```bash
cd /var/www/sygal
source Dockerfile.sh
```

Ensuite, vérifiez et ajustez si besoin sur votre serveur les fichiers de configs suivants,
créés par le script `Dockerfile.sh` :
- ${APACHE_CONF_DIR}/ports.conf
- ${APACHE_CONF_DIR}/sites-available/sygal.conf
- ${APACHE_CONF_DIR}/sites-available/sygal-ssl.conf  
- ${PHP_CONF_DIR}/fpm/pool.d/sygal.conf
- ${PHP_CONF_DIR}/fpm/conf.d/

NB: Vérifiez dans le script `Dockerfile.sh` que vous venez de lancer mais normalement 
`APACHE_CONF_DIR=/etc/apache2` et `PHP_CONF_DIR=/etc/php/7.0`.

### Installation d'une version précise de l'application

Normalement, vous ne devez installer que les versions officielles, c'est à dire les versions taguées, du genre `1.0.9`
par exemple.

Placez-vous dans le répertoire des sources de l'application puis lancez les commandes suivantes pour obtenir la liste des
versions officielles (taguées) :
```bash
git fetch && git fetch --tags && git tag
```

Si la version la plus récente est par exemple la `1.0.9`, utilisez les commandes suivantes pour "installer" cette version 
sur votre serveur :
```bash
git checkout --force 1.0.9 && bash install.sh
```

### Configuration du moteur PHP pour SyGAL

Si l'on est sur un serveur de PROD, corrigez les lignes suivantes du fichier de config PHP 
`/etc/php/7.0/fpm/conf.d/sygal.ini` :

    ...
    display_errors = Off
    ...
    opcache.enable = 1
    ...

### Fichiers de config de l'application

Placez-vous dans le répertoire de l'application puis descendez dans le répertoire `config/autoload/`.

Supprimez l'extension `.dist` des fichiers suivants :
- `local.php.dist`
- `secret.local.php.dist`

Dans la suite, vous les adapterez à votre situation...

#### `local.php`

RAS.

#### `secret.local.php`

Concernant la config de connexion au WS, `'UCN'` doit être remplacé par le code établissement choisi lors
de la création de votre établissement dans la base de données (dans le script [`05-init.sql`](04-init.sql)) :

    'import-api' => [
        'etablissements' => [
            // code établissement => [config]
            'UCN' => [
                'url'      => 'https://sygal-import-ws:443',
                'proxy'    => false,
                'verify'   => false, // si true et faux certif : cURL error 60: SSL certificate problem: self signed certificate
                'user'     => 'xxx',
                'password' => 'yyy',

Renseignez les infos de connexion à la BDD :

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

La config fournie permet de simuler l'authentification Shibboleth de l'utilisateur 'premierf@univ.fr' 
créé en base de données (dans le script [`05-init.sql`](04-init.sql)) avec le rôle "Administrateur technique".
Cela permet d'accéder aux pages de gestion des droits d'accès.

    'unicaen-auth' => [
        'shibboleth' => [
            'simulate' => [
                'HTTP_EPPN'           => $eppn = 'premierf@univ.fr',
                'HTTP_SUPANNEMPID'    => '00012345',
                'HTTP_DISPLAYNAME'    => $eppn,
                'HTTP_MAIL'           => $eppn,
                'HTTP_GIVENNAME'      => 'François',
                'HTTP_SN'             => 'Premier',
                'HTTP_SUPANNCIVILITE' => 'M.'

Théoriquement, à ce stade l'application SyGAL devrait être accessible.


## Dans l'application SyGAL elle-même

Si vous n'avez rien changé à la config de l'application concernant Shibboleth et si vous cliquez en haut à droite de
la page d'accueil de SyGAL sur "Connexion" puis sur "Fédération d'identité", vous devriez être dans la peau de 
François Premier, administrateur technique de test créé en base de données (dans le script [`05-init.sql`](04-init.sql)).

### Droits d'accès

Dans l'application SyGAL, allez dans menu "Droits d'accès" > "Gestion des profils de rôle".

Appliquez, svp : 
- le profil `ADMIN_TECH` au rôle *Administrateur technique*
- le profil `OBSERV` au rôle *Observateur*
- le profil `DOCTORANT` au rôle *Doctorant UCN*
- le profil `ADMIN` au rôle *Administrateur UCN*
- le profil `BU` au rôle *Bibliothèque universitaire UCN*
- le profil `BDD` au rôle *Bureau des doctorats UCN*

NB: "UCN" n'est qu'un exemple et pour vous ce sera le code établissement choisi lors
de la création de votre établissement dans la base de données (dans le script [`05-init.sql`](04-init.sql)) 

### Import

Allez dans menu "Import" pour contrôler que l'application parvient à contacter votre web service. 
La version de l'API devrait s'afficher, ex: 1.2.4.

Ne tenez pas compte du menu "Lancement" car il n'est pas possible de lancer l'import des données
depuis l'interface graphique.



## En lignes de commande 

Placez-vous dans le répertoire de SyGAL sur le serveur pour lancer l'import puis la synchro des données.

Ce qui suit n'est possible que si le web service d'import de données est installé, si ce n'est pas le cas,
reportez-vous au [README du projet sygal-import-ws](https://github.com/EsupPortail/sygal-import-ws).

### Lancement de l'import des données

Il s'agit de l'interrogation du WS et du remplissage des tables TMP_*.

    php public/index.php import-all --etablissement=UCN --synchronize=0 --breakOnServiceNotFound=0

### Lancement de la synchro à partir des données importées 

Il s'agit de la synchronisation des tables définitves de travail de l'application avec les tables TMP_* 
contenant les données déjà importées.

    php public/index.php synchronize-all

Pour plus de détails, vous pouvez vous reporter à la documentation sur les [lignes de commandes](doc/cli.md).

Une fois la synchro effectuée, vous devriez voir des thèses apparaître en cliquant sur le menu "Thèses" de 
l'application.
