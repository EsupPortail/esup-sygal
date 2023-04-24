Création d'une base de données pour ESUP-SyGAL
==============================================

Les fichiers fournis permettant de créer une base de données pour ESUP-SyGAL sont :

- d'une part, le fichier de config et le script bash de construction :
    - [`build_db_files.conf.dist`](build_db_files.conf.dist) (à adpater et à renommer en `.conf`)
    - [`build_db_files.sh`](build_db_files.sh)
    
- d'autre part, les scripts SQL situés dans le répertoire [`sql/`](sql) :
    - [`admin/01_create_db_user.sql`](sql/admin/01_create_db_user.sql)
    - [`02_create_schema.sql`](sql/02_create_schema.sql)
    - [`03_insert_bootstrap_data.sql`](sql/03_insert_bootstrap_data.sql)
    - [`04_insert_data.sql`](sql/04_insert_data.sql)
    - [`05_prepare_data.sql`](sql/05_prepare_data.sql)
    - [`06_create_constraints.sql`](sql/06_create_constraints.sql)
    - [`07_create_comue.sql.dist`](sql/07_create_comue.sql.dist)
    - [`08_create_ced.sql.dist`](sql/08_create_ced.sql.dist)
    - [`09_init.sql.dist`](sql/09_init.sql.dist)
    - [`10_create_fixture.sql.dist`](sql/10_create_fixture.sql.dist)
    

## Case départ

Ouvez un shell et placez-vous dans le répertoire contenant le présent README et le script bash 
[`build_db_files.sh`](build_db_files.sh).


## Préparation de certains scripts SQL

Les scripts SQL à "préparer" portent l'extension `.sql.dist` :
  - [`07_create_comue.sql.dist`](sql/07_create_comue.sql.dist)
  - [`08_init.sql.dist`](sql/08_init.sql.dist)
  - [`09_create_fixture.sql.dist`](sql/09_create_fixture.sql.dist)

Pour les préparer, vous devez dans l'ordre :

- Renommer ou copier le fichier de config [`build_db_files.conf.dist`](build_db_files.conf.dist) en
  `build_db_files.conf` (i.e. suppression du `.dist`).

- Modifier le fichier `build_db_files.conf` selon votre situation (cf. commentaires inclus).
   
- Lancer le script bash [`build_db_files.sh`](build_db_files.sh), en lui spécifiant le fichier de config
   et le répertoire des scripts SQL à préparer. 
   Exemple :
    ```bash
    ./build_db_files.sh -c ./build_db_files.conf -i ./sql/
    ```

Une fois le script bash exécuté, vous devriez vous retrouver avec 3 scripts SQL supplémentaires dans le répertoire 
[`sql/`](sql) :
  - [`07_create_comue.sql`](sql/07_create_comue.sql)
  - [`08_init.sql`](sql/08_init.sql)
  - [`09_create_fixture.sql`](sql/09_create_fixture.sql)

À présent, tout est prêt pour lancer la création de la base de données.


## Adresse du serveur de base de données

- Adaptez et exportez les 2 variables d'environnement suivantes :

```bash
export \
PGHOST=localhost \
PGPORT=5432
```


## Nom de la base de données SyGAL à créer, de l'utilisateur et de son mot de passe

- Adaptez et exportez les 3 variables d'environnement suivantes :

```bash
export \
SYGAL_DB='sygal' \
SYGAL_USER='ad_sygal' \
SYGAL_PASSWORD='xxxxxxxxx'
```


## Création de la base de données et de l'utilisateur

- Dans les lignes de commande suivantes, renseignez correctement les variables d'environnement `PG*` permettant 
  de se connecter au serveur Postgres en tant que super-utilisateur puis lancez-les :

```bash
export \
ON_ERROR_STOP=1 \
PGDATABASE=postgres \
PGUSER=postgres \
PGPASSWORD=admin

psql \
  -v "dbname=${SYGAL_DB}" \
  -v "dbuser=${SYGAL_USER}" \
  -v "dbpassword='${SYGAL_PASSWORD}'" \
  -f sql/admin/01_create_db_user.sql
```


## Création des objets et insertion des données de fonctionnement

*Les scripts suivants sont lancés en étant connecté à la base SyGAL avec l'utilisateur SyGAL
créés à l'étape précédente (et non plus avec le super-utilisateur).*

- Lancez les lignes de commande suivantes :

```bash
export \
ON_ERROR_STOP=1 \
PGDATABASE=${SYGAL_DB} \
PGUSER=${SYGAL_USER} \
PGPASSWORD=${SYGAL_PASSWORD}

psql -v "dbuser=${SYGAL_USER}" -f sql/02_create_schema.sql && \
psql -f sql/03_insert_bootstrap_data.sql && \
psql -f sql/04_insert_data.sql && \
psql -f sql/05_prepare_data.sql && \
psql -f sql/06_create_constraints.sql && \
psql -f sql/07_create_comue.sql && \
psql -f sql/08_init.sql && \
psql -f sql/09_create_fixture.sql
```

Par précaution, effacez éventuellement les variables d'environnement exportées :

```bash
unset ON_ERROR_STOP PGHOST PGPORT PGDATABASE PGUSER PGPASSWORD # précaution
```

## Si besoin

- Suppression de la base de données !!

```bash
PGDATABASE=postgres \
PGUSER=postgres \
PGPASSWORD=admin \
psql -c "drop database ${SYGAL_DB}"
```
