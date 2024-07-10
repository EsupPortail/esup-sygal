Génération de la doc et des scripts SQL de création d'une bdd pour SyGAL
========================================================================

Ce README s'adresse au développeur souhaitant générer la doc et les scripts de création d'une base de données (bdd)
PostgreSQL pour ESUP-SyGAL. 

Cette génération se fait *à partir d'une bdd PostgreSQL modèle existante* à laquelle on se connecte.

## 1. Génération des fichiers

### Principe

Le script shell [`./build_db_install_files.sh`](build_db_install_files.sh) génère dans un répertoire de votre choix :

  - un répertoire `sql/` contenant des scripts SQL ordonnés dans les sous répertoires suivants :
    - `01_admin/`
    - `02_other/`
      
  - un script bash et un fichier de config pour "préparer" les scripts SQL ayant l'extension `.sql.dist` :    
    - `build_db_files.conf.dist`
    - `build_db_files.sh`
    
  - un fichier expliquant comment procéder pour créer une bdd à l'aide de tous ces scripts :
    - `README.md`

Ce script `./build_db_install_files.sh` :
  - se base sur les fichers `.template.*` situés dans le répertoire [`src/`](src) ;
  - charge le fichier de config [`./conf/build_db_install_files.conf`](conf/build_db_install_files.conf)
    dans lequel sont spécifiés les paramètres de génération.

Le script prend en argument :
  - le chemin d'un fichier de config ;
  - le chemin du répertoire où doivent être générés les fichiers.

La façon de procéder pour créer une bdd à partir des fichiers générés est expliquée dans le fichier
`README.md` (lui aussi généré).

### Exemple

Voici un exemple pour générer la doc et les ressources de création d'une bdd vierge...
  - dans le répertoire destination `/tmp/sygal-db/build`,
  - à partir de la base modèle `sygal` (spécifiée par les variables d'env `PGDATABASE`, etc.),
  - qui permettront de créer la base et l'utilisateur tels que spécifiés dans le fichier de config 
    `./build_db_install_files.conf ` :

- Emplacement du répertoire de destination :

```bash
export DB_BUILD_TMP_DIR="/tmp/sygal-db/build"
```

- Génération de la doc et des ressources de création d'une bdd :

```bash
PGHOST='localhost' \
PGPORT=54322 \
PGDATABASE='sygal' \
PGUSER='ad_sygal' \
PGPASSWORD='xxxxxx' \
./build_db_install_files.sh \
-c ./conf/build_db_install_files.conf \
-o ${DB_BUILD_TMP_DIR}
```


## 2. Mise à jour de la doc à partir des fichiers générés

- Emplacement de la doc concernant l'install de la bdd :

```bash
export DB_DOC_DIR="../../doc/database"
```

- Diff avec la doc actuelle, exemple :

```bash
meld ${DB_BUILD_TMP_DIR} ${DB_DOC_DIR}
```

- Copier les fichiers générés vers le répertoire contenant la documentation, exemple :

```bash
[[ -d ${DB_DOC_DIR} ]] && rm -r ${DB_DOC_DIR}/* && cp -rv ${DB_BUILD_TMP_DIR}/* ${DB_DOC_DIR}/
```
