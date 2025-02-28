Bases de données
================

Faire un dump d'une base de prod/test pour créer une base de dev sur notre machine
-----------------------------------------

- Installer postgresql-client :

```bash
sudo apt install postgresql-client
```

- Ouvrir si besoin un tunnel vers la base :

```bash
ssh root@gateway.domain.fr -L 54321:pg.domain.fr:5432
```


- Se placer dans le répertoire de SyGAL.


- Créer les répertoires suivants si besoin :

```bash
DUMP_DEST_DIR=./tmp/docker/db/dev/sql
DB_DATA_DIR=./data/db/dev

mkdir -p ${DUMP_DEST_DIR}
mkdir -p ${DB_DATA_DIR}
```

Pourquoi ces répertoires ? Cf. `./docker-compose.yml`.

Ces répertoires DOIVENT être ignorés par git. Normalement c'est le cas, mais vérifier quand même.


- Faire le dump dans le répertoire `${DUMP_DEST_DIR}` :

```bash
PGDATABASE='dbname' \
PGUSER='username' \
PGHOST='localhost' \
PGPORT=54321 \
pg_dump sygal > ${DUMP_DEST_DIR}/01_sygal_db_dump.sql
```


- Le répertoire `${DUMP_DEST_DIR}` peut contenir d'autres scripts SQL que `01_sygal_db_dump.sql` : ils seront exécutés 
  dans l'ordre de leur nom pour créer/initialiser la base de données.


- Lancer les services.

```bash
docker-compose up
```

Si le répertoire `${DB_DATA_DIR}` N'EXISTE PAS, les scripts SQL présents dans `${DUMP_DEST_DIR}` seront automatiquement
exécutés dans l'ordre de leur nom au démarrage du service `sygal-db`.

S'il existe, aucun script SQL n'est exécuté et la base démarrée est celle persistée dans `${DB_DATA_DIR}`.



Accéder à la base de données de prod/test depuis un container
------------------------------------------

Pour que sygal puisse accéder à la base de prod/test depuis un container :

- Il faut avoir Docker en version 20.


- Ajouter à la config du service "sygal" dans `docker-compose.yml` :

```yml
    extra_hosts:
    - "host.docker.internal:host-gateway"
```

Cela automatisera l'ajout dans le `/etc/hosts` d'une ligne du genre `172.17.0.1 host.docker.internal` où 
172.17.0.1 est l'IP de notre machine vue de l'intérieur du container Docker.


- Ouvrir si besoin un tunnel comme ça :

```bash
ssh root@gateway.domain.fr -L *:54321:db.domain.fr:5432
```

Remarquez le `*:`.


- Configurer la connexion à la bdd dans la config de SyGAL :

```php
                'params' => [
                    'host'     => 'host.docker.internal', // càd docker host (cf. extra_hosts dans docker-compose.yml)
                    'port'     => '54321',
                    'dbname'   => 'dbname',
                    'user'     => 'username',
                    'password' => 'xxxxxxxxxxxx',
                ],
```
