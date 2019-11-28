# Fonctionnement du fichier CSV

## Interprétation du fichier CSV

Dans le fichier CSV, la première ligne désigne la classe d'assertion qui sera générée à partir du fichier CSV.
Par exemple, `class;Application\Assertion\These\GeneratedTheseEntityAssertion` pour la classe `GeneratedTheseEntityAssertion`.

La ligne 2 liste des prédicats qui peuvent être utilisés  et qui doivent être définit dans le classe mère `TheseEntityAssertion`.  

La colonne C correspond aux privilèges concernés.
 
Pour lire comment interpréter le fichier, on regarde pour un privilège les lignes (consécutives?) qui lui sont associées 
et les prédicats pour lesquel la cellule est non vide.


Par exemple, pour le privilège `THESE_DEPOT_INITIAL` :

| privilege                     | isRoleDoctorantSelected | isTheseEnCours | isTheseSoutenue | isUtilisateurEstAuteurDeLaThese |...| return |
|-------------------------------|-------------------------|----------------|-----------------|---------------------------------|---|--------| 
|THESE_DEPOT_VERSION_INITIALE   |                         | 1:0            | 1:0             |                                 |...| 0      |
|THESE_DEPOT_VERSION_INITIALE   |                         |                | 1:1             |                                 |...| 0      |
|THESE_DEPOT_VERSION_INITIALE   |                         |                |                 |                                 |...| 1      |
|DOCTORANT_AFFICHER_MAIL_CONTACT| 1:1                     |                |                 | 2:1                             |...| 0      |
|DOCTORANT_AFFICHER_MAIL_CONTACT|                         |                |                 |                                 |...| 1      |

La cellule au croissement de `THESE_DEPOT_VERSION_INITIALE` et de `isTheseEnCours` vaut `1:0`.
Elle s'interprète comme si le prédicat est `faux` alors l'assertion retourne `0`.

La cellule au croissement de `THESE_DEPOT_VERSION_INITIALE` et de `isTheseSoutenue` vaut `1:1`.
Elle s'interprète comme si le prédicat est `vrai` alors l'assertion retourne `0`.

La dernière ligne indique que si on a franchi toutes les étapes précédentes alors l'assertion retourne `1`.

```php
if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 4 */
            $this->linesTrace[] = '/* line 4 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 5 */
            $this->linesTrace[] = '/* line 5 */';
            if ($this->isTheseSoutenue() /* test 6 */) {
                $this->failureMessage = "Le dépôt initial n'est plus autorisé car la date de soutenance est passée.";
                return false;
            }
            /* line 9 */
            $this->linesTrace[] = '/* line 9 */';
            return true;
        }
```

Dans le cas `DOCTORANT_AFFICHER_MAIL_CONTACT` l'ordre indiqué par le premier nombre va impacter l'ordre d'utilisation des prédicats.
 
 ```php
if ($privilege === \Application\Provider\Privilege\DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT) {
        //--------------------------------------------------------------------------------------
            /* line 77 */
            $this->linesTrace[] = '/* line 77 */';
            if ($this->isRoleDoctorantSelected() /* test 1 */ && 
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 22 */) {
                $this->failureMessage = "Vous ne pouvez pas visualiser l’adresse de contact car vous n’êtes pas l’auteur de la thèse";
                return false;
            }
            /* line 78 */
            $this->linesTrace[] = '/* line 78 */';
            return true;
        }
```


## Ajouter une nouvelle assertion

***TODO***
 
## Génrer le nouveau ficher assertion

Pour que les modifications effectuées sur le fichier CSV soit appliquées, il est nécessaire d'executer la commande `generate-assertion`. 
 
```bash
# Se positionner dans le repertoire data/assertion 
./generate-assertion --file ./TheseEntityAssertion.csv
``` 