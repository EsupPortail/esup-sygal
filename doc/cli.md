# Ligne de commande de SyGAL

Pour afficher l'aide de toutes les commandes fournies par l'application, se placer à la racine du projet 
et faire :

    $ php public/index.php


## Import

En fait, on peut désigner par "import" l'enchainement des actions suivantes :
- "importer" les données d'un établissement, càd :
    - interroger le WS d'un établissement (projet "sygal-import-ws") ; 
    - enregistrer les données reçues dans des tables temporaires `TMP_*` ; 
- "synchroniser" les tables définitives à partir des tables temporaires (module "unicaen/import").


### Les services disponibles

Les données importées sont fractionnées en domaines ou "services", qui reflètent en réalité chacun une table dans 
la base de données de l'application.

Voici les codes des services existants :
- structure
- ecole-doctorale
- unite-recherche
- etablissement
- individu
- doctorant
- these
- role
- acteur
- variable
- financement
- titre-acces

Lorsqu'on importe les écoles doctorales, on référence le service dont le code est `ecole-doctorale`.
C'est ce code du service qui devra être spécifié via l'argument `--etablissement` des commandes présentées ici.


### Commandes d'import
 
L'import se fait pour *un seul établissement à la fois*. Les établissements dont on veut importer les données
doivent être déclarés dans la config PHP de l'appli : cf. [local.php.dist](../config/autoload/local.php.dist)

C'est le "code établissement" choisi dans la config qui devra être spécifié via l'argument `--etablissement` 
des commandes présentées ici.

#### `import`

On parle ici de lancer l'import d'un service précis.

Par exemple, pour lancer l'import des variables de l'UCN, mais sans lancer la synchro des tables définitives :
    
    $ php public/index.php import --service=variable --etablissement=UCN --synchronize=0

Pour lancer en plus la synchro après l'interrogation du WS, mettre `--synchronize=1`.

#### `import-all`

Cette commande lance l'import de tous les services.

Par exemple, pour lancer l'import complet des données de l'UCN, sans lancer la synchro des tables définitives :
    
    $ php public/index.php import-all --etablissement=UCN --synchronize=0

Pour que la synchro se fasse après l'interrogation du WS, mettre `--synchronize=1`.


### Commandes de synchro

On parle ici de synchroniser les données des tables définitives de l'application avec celles des tables 
temporaires `TMP_*`. 

Même s'il s'agit de tables, on raisonne tout de même en service. Par exemple, la synchronisation de la table des 
unités de recherches (`UNITE_RECH`) se fait en spécifiant le code de service `unite-recherche`.

Dans ces commandes, il n'est pas possible de spécifier un établissement précis.

#### `synchronize`

Cette commande lance la synchro d'une table définitive `TABLE` à partir de la table temporaire `TMP_TABLE`
correspondante.

Par exemple, pour lancer la synchro des variables :
    
    $ php public/index.php synchronize --service=variable

#### `synchronize-all`

Cette commande lance la synchro de toutes les tables définitives :

    $ php public/index.php synchronize-all
