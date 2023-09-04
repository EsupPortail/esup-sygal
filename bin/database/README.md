Génération de la doc et des scripts SQL de création d'une bdd pour SyGAL
========================================================================

Ce README s'adresse au développeur souhaitant générer la doc et les scripts de création d'une base de données 
PostgreSQL pour ESUP-SyGAL. 

Cette génération se fait *à partir d'une base de données PostgreSQL modèle existante* à laquelle on se connecte.

Génération des fichiers
-----------------------

### Principe

Le script shell [`./build_db_install_files.sh`](build_db_install_files.sh) génère dans un répertoire de votre choix :

  - un répertoire `sql/` contenant un ensemble de scripts SQL ordonnés :
    - `01_create_db_user.sql`
    - `02_create_schema.sql`
    - `03_insert_bootstrap_data.sql`
    - `04_insert_data.sql`
    - `05_prepare_data.sql`
    - `06_create_constraints.sql`
    - `07_create_comue.sql`
    - `08_create_ced.sql`
    - `09_init.sql`
    - `10_create_fixture.sql`
      
  - un script bash et un fichier de config pour "préparer" les scripts SQL ayant l'extension `.sql.dist` :    
    - `build_db_files.conf.dist`
    - `build_db_files.sh`
    
  - un fichier expliquant comment procéder pour créer une base de données à l'aide de tous ces scripts :
    - `README.md`

Ce script `./build_db_install_files.sh` :
  - se base sur les fichers `.template.*` situés dans le répertoire [`src/`](src) ;
  - charge le fichier de config [`./conf/build_db_install_files.conf`](conf/build_db_install_files.conf)
    dans lequel sont spécifiés les paramètres de génération.

Le script prend en argument :
  - le chemin d'un fichier de config ;
  - le chemin du répertoire où doivent être générés les fichiers.

La façon de procéder pour créer une base de données à partir des fichiers générés est expliquée dans le fichier
`README.md` (lui aussi généré).

### Exemple

Voici un exemple pour générer les fichiers de création d'une base de données...
  - dans le répertoire destination `/tmp/build`,
  - à partir de la base modèle `sygal` (spécifiée par les variables d'env `PGDATABASE`, etc.),
  - qui permettront de créer la base et l'utilisateur spécifiés dans le fichier de config 
    `./build_db_install_files.conf ` :

```bash
PGHOST='localhost' \
PGPORT=54322 \
PGDATABASE='sygal' \
PGUSER='ad_sygal' \
PGPASSWORD='xxxxxx' \
./build_db_install_files.sh \
-c ./conf/build_db_install_files.conf \
-o /tmp/build
```


Mise à jour de la documentation à partir des fichiers générés
-------------------------------------------------------------

Diff avec la doc actuelle, exemple :

```bash
meld /tmp/build/ ../../doc/database/
```

Copier les fichiers générés vers le répertoire contenant la documentation, exemple :

```bash
cp -r /tmp/build/* ../../doc/database/
```
