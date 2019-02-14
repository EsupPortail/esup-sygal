# Création de la base de données


Au préalable, charge à vous de créer une base de données Oracle vide quelque part. 
Ou pourquoi pas 2 bases de données : une base de PROD (ex: `SYGAL_PROD`) et une base de TEST (ex: `SYGAL_TEST`).
Voire même une 3e si vous envisagez de collaborer au développement (ex: `SYGAL_DEV`) !


## Script SQL [`00-oracle-generate-schema-SYGAL.sql`](00-oracle-generate-schema-SYGAL.sql)

Vous pouvez lancer ce script sans modifier quoi que ce soit.

## Script SQL [`01-bootstrap.sql`](01-bootstrap.sql)

Vous pouvez lancer ce script sans modifier quoi que ce soit.

## Scripts SQL dans le répertoire [`02-inserts`](02-inserts)

Vous pouvez lancer ces scripts sans les modifier et dans l'ordre que vous voulez.

Si l'erreur Oracle `ORA-00922: option erronée ou absente` est rencontrée à la première ligne d'un script, 
c'est que `set define off ;` n'est pas supporté par le client SQL que vous utilisez. 
Si cette erreur a bloqué les INSERT situés après, supprimez cette ligne et relancez le script.

## Script SQL [`03-crea-comue.sql`](03-crea-comue.sql)

Lisez ce script pour décider si vous devez le lancer.

## Script SQL [`04-init.sql`](04-init.sql)

Avant de lancer ce script vous devez décider d'un code représentant votre établissement dans l'application, 
ex: 'UCN', 'UNILIM', 'UTLN'.

Si votre client SQL ne supporte pas l'utilisation de paramètre du genre :codeEtablissement, remplacez dans ce script
toutes les occurences de ":codeEtablissement" par le code choisi *entre apostrophe*, ex: `'UCN'`.

Si votre client SQL supporte les motifs du genre :codeEtablissement, il vous demandera de saisir une valeur 
pour ce parameètre : entrez le code choisi *entre apostrophe*, ex: `'UCN'`.
