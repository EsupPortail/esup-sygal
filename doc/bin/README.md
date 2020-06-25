# bin

## [generate-database-scripts.sh](generate-database-scripts.sh) 

Ce script doit être utilisé pour générer une partie des scripts SQL nécessaires à l'installation de 
la base de données de SyGAL *à partir de zéro*. 

Les scripts générés dans le répertoire temporaire `/tmp/sygal-doc/database` sont les suivants :
- `00-oracle-clear-schema-SYGAL.sql`
- `00-oracle-generate-schema-SYGAL.sql`
- `02-inserts/oracle-data-insert-from-SYGAL.*-into-SYGAL.sql`
- `03-oracle-generate-ref-constraints-SYGAL.sql`

Les autres scripts sont gérés à la main : 
- [`00-crea-users.sql`](../database/00-crea-users.sql)
- [`01-bootstrap.sql`](../database/01-bootstrap.sql)
- [`04-crea-comue.sql`](../database/04-crea-comue.sql)
- [`05-init.sql`](../database/05-init.sql)
- [`06-test.sql`](../database/06-test.sql)

Usage :
```bash
# génération des scripts
./generate-database-scripts.sh

# récupération du résultat
cp -r /tmp/sygal-doc/database/* database/
```
