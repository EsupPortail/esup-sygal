# Fonctionnement du fichier CSV

## Interprétation du fichier CSV

Dans le fichier CSV, la première ligne désigne la classe d'assertion liée au fichier CSV.
Par exemple, `class;Application\Assertion\These\GeneratedTheseEntityAssertion` pour la classe `GeneratedTheseEntityAssertion`.

Les lignes correspondent au privilièges et les colonnes aux prédicats utilisés. 
Pour lire comment interpréter le fichier, on regarde pour un privilège les lignes (consécutives?) qui lui sont associées et les prédicats pour lesquel la cellule est non vide.


Par exemple, pour le privilège `THESE_DEPOT_INITIAL` :

| privilege                   | isTheseEnCours | isTheseSoutenue |...| return |
|-----------------------------|----------------|-----------------|---|--------|
|THESE_DEPOT_VERSION_INITIALE |1:0             |                 |...| 0      |
|THESE_DEPOT_VERSION_INITIALE |                | 1:1             |...| 0      |
| ... | ... | ... | ... | ... |
|THESE_DEPOT_VERSION_INITIALE |                |                 |...| 1      |

La cellule au croissement de `THESE_DEPOT_VERSION_INITIALE` et de `isTheseEnCours` vaut `1:0`.
Elle s'interprète comme si le prédicat est `faux` alors l'assertion retourne `0`.

La cellule au croissement de `THESE_DEPOT_VERSION_INITIALE` et de `isTheseSoutenue` vaut `1:1`.
Elle s'interprète comme si le prédicat est `vrai` alors l'assertion retourne `0`.
 
La dernière ligne indique que si on a franchi toutes les étapes précédentes alors l'assertion retourne `1`.

## Ajouter une nouvelle assertion

***TODO***
 
## Génrer le nouveau ficher assertion

Pour que les modifications effectuées sur le fichier CSV soit appliquées, il est nécessaire d'executer la commande `generate-assertion`. 
 
```bash
# Se positionner dans le repertoire data/assertion 
./generate-assertion --file ./TheseEntityAssertion.csv
``` 