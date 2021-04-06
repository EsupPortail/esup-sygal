Migration de la base de données Oracle de SyGAL vers PostgreSQL
===============================================================

Le projet de migration *ora2pg* a été créé dans le sous-répertoire `ora2pg` en suivant la doc du dépôt
[ora2pg](https://git.unicaen.fr/dsi/ora2pg).


Pré-requis
----------

- Se placer dans le répertoire racine du projet SyGAL. 
  Dans la suite, `${PWD}` correspond donc à ce répertoire racine.


Génération des scripts SQL de migration
---------------------------------------

### Structure

1) Lancement de *ora2pg* pour générer les scripts de création du schéma PostgreSQL

```bash
docker run --rm \
-v ${PWD}/migration:/migration \
-w /migration/ora2pg \
ora2pg-image \
./export_schema.sh -c ./config/ora2pg.conf
```

Les scripts sont générés dans `${PWD}/migration/ora2pg/schema/`.

Remarques : 
- il est possible de restriendre la liste des "objets" à prendre en compte en modifiant les variables 
`EXPORT_TYPE` et `SOURCE_TYPE` dans le script `${PWD}/migration/ora2pg/export_schema.sh`.
Exemple pour ne générer que les tables, indexes, constraints et foreign keys : 
```
EXPORT_TYPE="TABLE"
SOURCE_TYPE="TABLE"
```
- il est aussi possible d'exclure certains objets selon leur nom en modifiant le variable `EXCLUDE` dans le fichier
de config `${PWD}/migration/ora2pg/config/ora2pg.conf`. Exemple :
```
EXCLUDE	V_IMPORT_TAB_COLS STR_REDUCE MV_INDICATEUR_.*
```


2) Correction nécessaire des scripts SQL générés

```bash
docker run \
--rm \
-v ${PWD}/migration:/migration \
-w /migration \
ora2pg-image \
./bin/prepare_schema_scripts.sh
```


### 2/ Données

*ora2pg* génère des scripts d'insertion de données qu'il faudra importer avec la ligne de commande `psql`.
*NB : ça mouline grave pour les tables volumineuses.*

```bash
docker run \
--rm \
-v ${PWD}/migration:/migration \
-w /migration/ora2pg \
ora2pg-image \
ora2pg -t COPY -o data.sql -b ./data -c ./config/ora2pg.conf
```

Les scripts sont générés dans `${PWD}/migration/ora2pg/data/`.


Pour ne prendre en compte QUE les tables contenant les données obligatoires de fonctionnement de l'appli :
```bash
docker run \
--rm \
-v ${PWD}/migration:/migration \
-w /migration/ora2pg \
ora2pg-image \
ora2pg -t COPY -o data.sql -b ./data -c ./config/ora2pg.conf --allow \
CATEGORIE_PRIVILEGE,\
DOMAINE_SCIENTIFIQUE,\
IMPORT_OBSERV,\
INFORMATION_LANGUE,\
NATURE_FICHIER,\
PRIVILEGE,\
PROFIL,\
PROFIL_PRIVILEGE,\
SOUTENANCE_CONFIGURATION,\
SOUTENANCE_ETAT,\
SOUTENANCE_QUALITE,\
TYPE_STRUCTURE,\
TYPE_VALIDATION,\
VERSION_FICHIER,\
WF_ETAPE
```



Vidage éventuel de la base PostgreSQL
-------------------------------------

**Si besoin**, le script `migration/pg_clean.sql` permet de vider entièrement la base de données PostgreSQL.


Import dans la base PostgreSQL
------------------------------

1) Exporter les variables d'environnement requises pour se connecter à la base de données PostgreSQL

```bash
export \
PGDATABASE=xxxxxx \
PGUSER=xxxxxx \
PGHOST=host.unicaen.fr \
PGPORT=5432 \
PGPASSWORD=xxxxxx
```

2) Lancer l'import

```bash
docker run -it --rm \
-v ${PWD}/migration/ora2pg:/ora2pg \
-v ${PWD}/migration/docker/pg_before_import.sql:/tmp/pg_before_import.sql \
-w /ora2pg \
--env PGDATABASE \
--env PGUSER \
--env PGHOST \
--env PGPORT \
--env PGPASSWORD \
ora2pg-image \
./import_all.sh -f -x -d ${PGDATABASE} -h ${PGHOST} -U ${PGUSER} -o ${PGUSER} -b /tmp/pg_before_import.sql
```

NB : 
- Le script SQL "pg_before_import.sql" sera exécuté juste avant l'import.
- Votre feu vert explicite vous est demandé pour chaque type d'objet à importer. Ajouter `-y` pour éviter ça.
- Les indexes, constraints et foreign keys ne sont créés qu'une fois les données importées.

3) Lancer le script SQL post-import

```bash
docker run --rm \
-v ${PWD}/migration/docker/pg_after_import.sql:/tmp/pg_after_import.sql \
--env PGDATABASE \
--env PGUSER \
--env PGHOST \
--env PGPORT \
--env PGPASSWORD \
ora2pg-image \
psql -d ${PGDATABASE} -h ${PGHOST} -U ${PGUSER} -f /tmp/pg_after_import.sql
```


Collecte des scripts d'install
------------------------------

Lancer le script suivant :
```
bash ${PWD}/migration/bin/prepare_distrib_scripts.sh 
```

Les scripts sont générés dans `${PWD}/migration/distrib/`.

Ils complètent les scripts situés dans `${PWD}/doc/database/`.
```
cp -rv ${PWD}/migration/distrib/* ${PWD}/doc/database/
```


The end
-------

:-)






Suppression des scripts SQL générés par *ora2pg*
------------------------------------------------

1) Structure

```bash
sudo rm -f \
migration/ora2pg/reports/* \
migration/ora2pg/schema/dblinks/* \
migration/ora2pg/schema/directories/* \
migration/ora2pg/schema/functions/* \
migration/ora2pg/schema/grants/* \
migration/ora2pg/schema/mviews/* \
migration/ora2pg/schema/packages/* \
migration/ora2pg/schema/partitions/* \
migration/ora2pg/schema/procedures/* \
migration/ora2pg/schema/sequences/* \
migration/ora2pg/schema/synonyms/* \
migration/ora2pg/schema/tables/* \
migration/ora2pg/schema/tablespaces/* \
migration/ora2pg/schema/triggers/* \
migration/ora2pg/schema/types/* \
migration/ora2pg/schema/views/* \
migration/ora2pg/sources/functions/* \
migration/ora2pg/sources/mviews/* \
migration/ora2pg/sources/packages/* \
migration/ora2pg/sources/partitions/* \
migration/ora2pg/sources/procedures/* \
migration/ora2pg/sources/triggers/* \
migration/ora2pg/sources/types/* \
migration/ora2pg/sources/views/*
```

2) Données

```bash
sudo rm -rf migration/ora2pg/data/*
```
