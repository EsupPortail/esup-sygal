Création d'une base de données PostgreSQL pour ESUP-SyGAL
=========================================================

Les fichiers fournis dans ce répertoire permettent de créer une base de données neuve pour ESUP-SyGAL :

- d'une part, le fichier de config et le script bash de construction :
    - [`build_db_files.conf.dist`](build_db_files.conf.dist) (à adapter et à renommer en `.conf`)
    - [`build_db_files.sh`](build_db_files.sh)
    
- d'autre part, des fichiers `.sql` ou `.sql.dist` situés dans les sous-répertoires du répertoire [`sql/`](sql) suivants :
    - [`01_admin`](sql/01_admin/)
    - [`02_other`](sql/02_other/)


## Pré-requis

Vous devez disposer d'un serveur de base de données PostgreSQL en **version 15**.


## Case départ

- Ouvrez un shell, placez-vous dans le répertoire contenant le présent README, faites une copie de ce répertoire
  dans `/tmp/sygal/database` par exemple, et placez-vous dedans :

```bash
mkdir -p /tmp/sygal && cp -r . /tmp/sygal/database && cd /tmp/sygal/database
```


## Préparation de certains scripts SQL

Les scripts SQL à "préparer" de trouvent dans le sous-répertoire `02_other` portent l'extension `.sql.dist`.

Pour les préparer, vous devez dans l'ordre :

- Renommer ou copier le fichier de config [`build_db_files.conf.dist`](build_db_files.conf.dist) en
  `build_db_files.conf` (i.e. suppression du `.dist`).

- Modifier le fichier `build_db_files.conf` selon votre situation (cf. commentaires inclus).
  Pensez bien à changer le paramètre `TEST_USER_PASSWORD_RESET_TOKEN` avec une valeur que vous génèrerez à l'aide
  de la commande `pwgen 64 1 --secure` par exemple. Ce token vous permettra de choisir le mot de passe du compte 
  utilisateur de test local via l'application.
   
- Lancer le script bash [`build_db_files.sh`](build_db_files.sh), en lui spécifiant le fichier de config
   et le répertoire des scripts SQL à préparer. 
   Exemple :
    ```bash
    ./build_db_files.sh -c ./build_db_files.conf -i ./sql/
    ```

Une fois le script bash exécuté, vous devriez vous retrouver avec des scripts SQL supplémentaires dans le répertoire 
[`sql/`](sql).

À présent, tout est prêt pour lancer la création de la base de données.


## Nom de la base de données SyGAL à créer, de l'utilisateur et son mot de passe

- Adaptez et exportez les 3 variables d'environnement suivantes :

```bash
export \
SYGAL_DB='sygal_test' \
SYGAL_USER='ad_sygal_test' \
SYGAL_PASSWORD='azerty'
```


## Création de la base de données et de l'utilisateur

- Dans les lignes de commande suivantes, renseignez correctement les variables d'environnement `PG*` permettant 
  de se connecter au serveur Postgres en tant que super-utilisateur puis lancez-les :

```bash
PGHOST=localhost \
PGPORT=5432 \
PGDATABASE=postgres \
PGUSER=postgres \
PGPASSWORD=admin \
psql \
  -v ON_ERROR_STOP=on \
  -v dbname=${SYGAL_DB} \
  -v dbuser=${SYGAL_USER} \
  -v dbpassword="'${SYGAL_PASSWORD}'" \
  -f sql/admin/01_create_db_user.sql
```


## Création des objets et insertion des données de fonctionnement

*Les scripts suivants sont lancés en étant connecté à la base SyGAL avec l'utilisateur SyGAL
créés à l'étape précédente (et non plus avec le super-utilisateur).*

- Lancez les lignes de commande suivantes :

```bash
for f in sql/*.sql; do \
  PGHOST=localhost \
  PGPORT=5432 \
  PGDATABASE=${SYGAL_DB} \
  PGUSER=postgres \
  PGPASSWORD=admin \
  psql \
    -v ON_ERROR_STOP=on \
    -v "dbuser=${SYGAL_USER}" \
    -f "$f"; \
done
```

Par précaution, effacez éventuellement les variables d'environnement exportées :

```bash
unset SYGAL_DB SYGAL_USER SYGAL_PASSWORD # précaution
```


## Uniquement en cas de besoin

- Suppression de la base de données et du user !!

```bash
PGHOST=localhost \
PGPORT=5432 \
PGDATABASE=postgres \
PGUSER=postgres \
PGPASSWORD=admin \
psql -c "drop database if exists ${SYGAL_DB}; drop user if exists ${SYGAL_USER};"
```

