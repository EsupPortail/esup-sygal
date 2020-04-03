Création de la base de données
==============================

## Création de la base de données

Charge à vous de créer une base de données Oracle vide quelque part. 
Ou pourquoi pas 2 bases de données : une base de PROD (ex: `SYGLPROD`) et une base de TEST (ex: `SYGLTEST`).
Voire même une 3e si vous envisagez de collaborer au développement !

## Création de tablespace, user, etc.

- Script [`00-crea-users.sql`](00-crea-users.sql)

Vous devez adapter ce script avant de le lancer, lisez bien les commentaires en début de script. 
Une fois ces adaptations faites, vous pourrez lancer le script.

## Création des objets de la base de données

- Script [`00-oracle-generate-schema-SYGAL.sql`](00-oracle-generate-schema-SYGAL.sql)
 
Vous pouvez lancer ce script sans modifier quoi que ce soit.

Si l'erreur Oracle `ORA-00922: option erronée ou absente` est rencontrée, 
c'est que `SET SQLBLANKLINES ON;` n'est pas compris par le client SQL que vous utilisez. 
Supprimez cette première ligne et relancez le script.

## Insertion des données de base

- Script [`01-bootstrap.sql`](01-bootstrap.sql)

Vous pouvez lancer ce script sans modifier quoi que ce soit.

- Scripts du répertoire [`02-inserts`](02-inserts)

Vous pouvez lancer ces scripts sans les modifier et dans l'ordre que vous voulez puisque les contraintes de références
entre tables ne sont pas encore créées.

Si l'erreur Oracle `ORA-00922: option erronée ou absente` est rencontrée à la première ligne d'un script, 
c'est que `set define off ;` n'est pas supporté par le client SQL que vous utilisez. 
Si cette erreur a bloqué les INSERT situés après, supprimez cette ligne et relancez le script.

## Création des contraintes de référence 

- Script [`03-oracle-generate-ref-constraints-SYGAL.sql`](03-oracle-generate-ref-constraints-SYGAL.sql)

Vous pouvez lancer ce script sans modifier quoi que ce soit.

## Création éventuelle d'une COMUE

- Script [`04-crea-comue.sql`](04-crea-comue.sql)

Lisez ce script pour décider si vous devez l'utiliser.

## Insertion des données de base (suite et fin)

- Script [`05-init.sql`](05-init.sql)

Vous devez adapter ce script avant de le lancer, lisez bien les commentaires en début de script. 
Une fois ces adaptations faites, vous pourrez lancer le script.

## Insertion des données de test

- Script [`06-test.sql`](06-test.sql)

Lancez ce script pour créer un utilisateur de test "premierf@univ.fr " et lui attribuer le rôle 
"Administrateur technique" (!)
