# Base de données : génération de la doc d'installation

Ce README s'adresse au développeur souhaitant générer les scripts de création d'une base de données PostgreSQL
toute neuve. 

Cette génération se fait *à partir d'une base de données PostgreSQL modèle existante* à laquelle on se connecte.


## Génération des fichiers

Le script shell [`./build_db_install_files.sh`](build_db_install_files.sh) génère dans un répertoire de votre choix :

  - un répertoire [`sql/`](sql) contenant un ensemble de scripts SQL ordonnés :
    - [01_create_db_user.sql`](sql/01_create_db_user.sql)
    - [02_create_schema.sql`](sql/02_create_schema.sql)
    - [03_insert_bootstrap_data.sql`](sql/03_insert_bootstrap_data.sql)
    - [04_insert_data.sql`](sql/04_insert_data.sql)
    - [05_prepare_data.sql`](sql/05_prepare_data.sql)
    - [06_create_constraints.sql`](sql/06_create_constraints.sql)
    - [07_create_comue.sql.dist`](sql/07_create_comue.sql.dist)
    - [08_init.sql.dist`](sql/08_init.sql.dist)
    - [09_create_fixture.sql.dist`](sql/09_create_fixture.sql.dist)
      
  - un script bash et un fichier de config pour "préparer" les scripts SQL ayant l'extension `.sql.dist` :    
    - [`build_db_files.conf.dist`](build_db_files.conf.dist)
    - [`build_db_files.sh`](build_db_files.sh)
    
  - un fichier expliquant comment procéder pour créer une base de données à l'aide de tous ces scripts :
    - [`README.md`](README.md)

Ce script [`./build_db_install_files.sh`](build_db_install_files.sh) :
  - se base sur les fichers `.template.*` situés dans le répertoire [`src/`](src) ;
  - charge le fichier de config [`./conf/build_db_install_files.conf`](confuild_db_install_files.conf)
    dans lequel sont spécifiés les paramètres de génération.

Le script prend en argument le chemin d'un fichier de config et le chemin du répertoire où doivent être générés 
les fichiers.

Voici un exemple pour générer les fichiers de création d'une base de données...
  - dans le répertoire destination `/tmp/build`,
  - à partir de la base modèle `sygal` (spécifiée par les variables d'env `PGDATABASE`, etc.),
  - qui permettront de créer une base et un utilisateur spécifiés dans le fichier de config 
    `./build_db_install_files.conf ` :
```bash
PGHOST='localhost' PGPORT=54322 PGDATABASE='sygal' PGUSER='ad_sygal' PGPASSWORD='xxxxxx' \
./build_db_install_files.sh -c ./conf/build_db_install_files.conf -o /tmp/build
```

La façon de procéder pour créer une base de données à partir des fichiers générés est expliquée dans le fichier 
`README.md` (lui aussi généré).


## Mise à jour éventuelle de la doc

Copier les fichiers générés vers le répertoire contenant la documentation :

```bash
cp -r /tmp/build/* ../../doc/database/
```
