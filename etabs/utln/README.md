SyGAL UTLN
==========

- Génération des scripts de création d'une nouvelle bdd :

```bash
PGDATABASE='database' \
PGUSER='user' \
PGHOST='host' \
PGPORT=5432 \
PGPASSWORD='xxxxxxxx' \
etabs/utln/bin/build_new_db.sh
```

- Lancement de l'appli en utilisant une bdd créée/initialisée avec les scripts générés au point précédent :

````bash
docker-compose -f etabs/utln/docker-compose-utln.yml up
````



