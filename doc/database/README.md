# Création de la base de données


Au préalable, charge à vous de créer une base de données Oracle vide quelque part. 
Ou pourquoi pas 2 bases de données : une base de PROD (ex: `SYGAL_PROD`) et une base de TEST (ex: `SYGAL_TEST`).
Voire même une 3e si vous envisagez de collaborer au développement (ex: `SYGAL_DEV`) !


## Script SQL [`00-oracle-generate-schema-SYGAL.sql`](00-oracle-generate-schema-SYGAL.sql)

Vous pouvez lancer ce script sans modifier quoi que ce soit.

Si l'erreur Oracle `ORA-00922: option erronée ou absente` est rencontrée, 
c'est que `SET SQLBLANKLINES ON;` n'est pas compris par le client SQL que vous utilisez. 
Supprimez cette première ligne et relancez le script.

## Script SQL [`01-bootstrap.sql`](01-bootstrap.sql)

Vous pouvez lancer ce script sans modifier quoi que ce soit.

## Scripts SQL dans le répertoire [`02-inserts`](02-inserts)

Vous pouvez lancer ces scripts sans les modifier et dans l'ordre que vous voulez puisque les contraintes de références
entre tables ne sont pas encore créées.

Si l'erreur Oracle `ORA-00922: option erronée ou absente` est rencontrée à la première ligne d'un script, 
c'est que `set define off ;` n'est pas supporté par le client SQL que vous utilisez. 
Si cette erreur a bloqué les INSERT situés après, supprimez cette ligne et relancez le script.

## Script SQL [`03-oracle-generate-ref-constraints-SYGAL.sql`](03-oracle-generate-ref-constraints-SYGAL.sql)

Vous pouvez lancer ce script sans modifier quoi que ce soit.

## Script SQL [`04-crea-comue.sql`](04-crea-comue.sql)

Lisez ce script pour décider si vous devez l'utiliser.

## Script SQL [`05-init.sql`](05-init.sql)

Vous devez adapter ce script avant de le lancer :

- Remplacez `'Unicaen'` par le sigle ou raccourci de votre établissement, 
  ex 'URN', 'Unilim'.

- Remplacez `'Université de Caen Normandie'` par le nom complet de votre étblissement, 
  ex: 'Université de Rouen Normandie', 'Université de Limoges'

- Décidez d'un code identifiant votre établissement dans l'application, ex: 'UCN', 'UNILIM'.
  
  Si vous préférez ou si votre client SQL ne supporte pas l'utilisation de paramètre du genre :codeEtablissement, 
  remplacez dans ce script toutes les occurences de ":codeEtablissement" par le code choisi *entre apostrophe*, 
  ex: `'UCN'`. Si votre client SQL supporte les motifs du genre :codeEtablissement, il vous demandera de saisir 
  une valeur pour ce paramètre lorsque vous lancerez le script : entrez le code choisi *entre apostrophe*.
  
Une fois ces adaptations faites, vous pouvez lancer le script.
