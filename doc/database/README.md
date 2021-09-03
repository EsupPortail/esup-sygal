Création d'une base de données PostgreSQL pour SyGAL
====================================================

Les fichiers fournis permettant de créer une base de données PostgreSQL pour SyGAL sont :

- d'une part, le fichier de config et le script bash de construction :
    - [`build_db_files.conf.dist`](build_db_files.conf.dist) (à adpater puis à renommer en `.conf`)
    - [`build_db_files.sh`](build_db_files.sh)
    
- d'autre part, les scripts SQL situés dans le répertoire [`sql/`](sql) :
    - [`01_create_db_user.sql`](sql/01_create_db_user.sql)
    - [`02_create_schema.sql`](sql/02_create_schema.sql)
    - [`03_insert_bootstrap_data.sql`](sql/03_insert_bootstrap_data.sql)
    - [`04_insert_data.sql`](sql/04_insert_data.sql)
    - [`05_prepare_data.sql`](sql/05_prepare_data.sql)
    - [`06_create_constraints.sql`](sql/06_create_constraints.sql)
    - [`07_create_comue.sql.dist`](sql/07_create_comue.sql.dist) (à "préparer" avec le script) 
    - [`08_init.sql.dist`](sql/08_init.sql.dist) (idem)
    - [`09_create_fixture.sql.dist`](sql/09_create_fixture.sql.dist) (idem)
    

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


## Création de la base de données `sygal` et de l'utilisateur `ad_sygal`

- Lancer le script [`01_create_db_user.sql`](sql/01_create_db_user.sql) *en étant connecté en tant que 
  super-utilisateur*, exemple :

```bash
ON_ERROR_STOP=1 \
PGHOST=localhost \
PGPORT=5432 \
PGDATABASE=postgres \
PGUSER=postgres \
PGPASSWORD=admin \
psql -f sql/01_create_db_user.sql
```

Ce 1er script SQL crée la base de données `sygal` et l'utilisateur `ad_sygal`. 

**Attention, l'utilisateur est créé avec un mot de passe par défaut** donc vous devez le modifier en faisant un 
truc du genre :
```bash
PGHOST=localhost \
PGPORT=5432 \
PGDATABASE=postgres \
PGUSER=postgres \
PGPASSWORD=admin \
psql -c "alter user ad_sygal with encrypted password 'VRAI_MOT_DE_PASSE'"
```


## Création des objets et insertion des données de fonctionnement

*Les scripts suivants doivent être lancés en étant connecté à la base `sygal` avec le user `ad_sygal`
créés à l'étape précédente (et non plus avec l'utilisateur `postgres`).*

Pour cela, exportez les variables d'environnement PostgreSQL comme suit :
```bash
export \
ON_ERROR_STOP=1 \
PGHOST=localhost \
PGPORT=5432 \
PGDATABASE=sygal \
PGUSER=ad_sygal \
PGPASSWORD=VRAI_MOT_DE_PASSE
```

Lancez ensuite *dans l'ordre* chacun des scripts suivants :

```bash
psql -f sql/02_create_schema.sql && \
psql -f sql/03_insert_bootstrap_data.sql && \
psql -f sql/04_insert_data.sql && \
psql -f sql/05_prepare_data.sql && \
psql -f sql/06_create_constraints.sql && \
psql -f sql/07_create_comue.sql && \
psql -f sql/08_init.sql && \
psql -f sql/09_create_fixture.sql
```

Par précaution, effacez éventuellement les variables d'environnement PostgreSQL :

```bash
unset ON_ERROR_STOP PGHOST PGPORT PGDATABASE PGUSER PGPASSWORD # précaution
```
