# Fonctionnement du fichier CSV

Un fichier CSV est utilisé pour spécifier de manière "visuelle" à quelles conditions "métier" un privilège est accordé 
ou non. (L'ambition initiale était aussi de permettre de déléguer ces spécifications à des non développeurs, mais cela
n'a pas été mis en place.)

Si un module utilise un fichier CSV, il est placé dans son répertoire `data`.
Exemple : `module/Depot/data/TheseEntityAssertion.csv`.

Ce fichier est utilisé pour générer une classe d'assertion abstraite que le développeur devra sous-classer afin
d'implémenter les méthodes abstraites mises en jeu.
Exemple : `module/Depot/src/Depot/Assertion/These/GeneratedTheseEntityAssertion.php`

C'est le script `bin/assertions/generate-assertion` qui réalise cette opération.


## Description du fichier CSV

Le format d'un fichier CSV de spécifications est précis.

- La ligne 1 désigne la classe d'assertion qui sera générée à partir du fichier CSV.
  Par exemple, `class;Depot\Assertion\These\GeneratedTheseEntityAssertion` pour la classe `GeneratedTheseEntityAssertion`.

- La ligne 2 liste des prédicats qui peuvent être utilisés et qui doivent être définis dans la
  classe mère `TheseEntityAssertion`.

- La colonne A correspond à des références de lignes qui seront inscrites dans le PHP généré sous
  la forme `/* line N */`.

- La colonne B correspond aux témoins indiquant si la ligne concernée doit être prise en compte (valeur `1`)
  ou non (valeur `0`).

- La colonne C correspond aux privilèges concernés (FQDN de la constante PHP).

- Les colonnes suivantes `isXxxxxx` permettent de lister les prédicats (méthodes `isXxxxxx()` de la classe d'assertion
  spécifiée en ligne 1) et de spécifier comment chacun est utilisé en fonction du privilège concerné.

- La colonne `return` contient la valeur que retournera l'assertion à l'issue des interrogations des prédicats.

- Chaque ligne faisant référence à un privilège générera un `if` dans l'assertion générée.
  Si un même privilège figure 3 fois, 3 `if` seront générés.

### Exemple d'interprétation

|line|enabled| privilege                     | isRoleDoctorantSelected | isTheseEnCours | isTheseSoutenue | isUtilisateurEstAuteurDeLaThese |...| return |
|----|-------|-------------------------------|-------------------------|----------------|-----------------|---------------------------------|---|--------| 
|... |       |                               |                         |                |                 |                                 |   |        |
|4   |1      |THESE_DEPOT_VERSION_INITIALE   |                         | 1:0            |                 |                                 |...| 0      |
|5   |1      |THESE_DEPOT_VERSION_INITIALE   |                         |                | 1:1             |                                 |...| 0      |
|6   |1      |THESE_DEPOT_VERSION_INITIALE   |                         |                |                 |                                 |...| 1      |
|... |       |                               |                         |                |                 |                                 |   |        |
|10  |1      |DOCTORANT_AFFICHER_MAIL_CONTACT| 1:1                     |                |                 | 2:1                             |...| 0      |
|11  |1      |DOCTORANT_AFFICHER_MAIL_CONTACT|                         |                |                 |                                 |...| 1      |

#### Privilège `THESE_DEPOT_VERSION_INITIALE`

- Line 4 :
    - La cellule au croisement du privilège `THESE_DEPOT_VERSION_INITIALE` et du prédicat `isTheseEnCours` vaut `1:0`.
    - La cellule dans la colonne `return` mentionne un `0`.
    - Cela s'interprète : "Si `isTheseEnCours` est faux (`:0`) alors l'assertion retourne faux (`0`)".

- Line 5 :
    - La cellule au croisement de `THESE_DEPOT_VERSION_INITIALE` et de `isTheseSoutenue` vaut `1:1`.
    - La cellule dans la colonne `return` mentionne un `0`.
    - Cela s'interprète : "Si `isTheseSoutenue` est vrai (`:1`) alors l'assertion retourne faux (`0`)".

- Line 6 :
    - La ligne ne spécifie aucune valeur en face des prédicats et un `1` dans la colonne `return`.
    - La cellule dans la colonne `return` mentionne un `1`.
    - Cela s'interprète : "Sinon, l'assertion retourne vrai (`1`)".

Voici le PHP que ces 3 lignes généreraient dans la classe d'assertion :

```php
if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 4 */
            $this->linesTrace[] = '/* line 4 */';
            if (! $this->isTheseEnCours()) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 5 */
            $this->linesTrace[] = '/* line 5 */';
            if ($this->isTheseSoutenue()) {
                $this->failureMessage = "Le dépôt initial n'est plus autorisé car la date de soutenance est passée.";
                return false;
            }
            /* line 6 */
            $this->linesTrace[] = '/* line 6 */';
            return true;
        }
```

#### Privilège `DOCTORANT_AFFICHER_MAIL_CONTACT`

Ici, dans les valeurs `1:1` et `2:1`, le chiffre avant `:` spécifie l'ordre d'utilisation du prédicat
concerné : le prédicat `isRoleDoctorantSelected` est testé en 1er, puis `isUtilisateurEstAuteurDeLaThese` en 2e.

Voici le PHP que ces 2 lignes généreraient dans la classe d'assertion :

 ```php
if ($privilege === \Doctorant\Provider\Privilege\DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT) {
        //--------------------------------------------------------------------------------------
            /* line 10 */
            $this->linesTrace[] = '/* line 10 */';
            if ($this->isRoleDoctorantSelected() && ! $this->isUtilisateurEstAuteurDeLaThese()) {
                $this->failureMessage = "Vous ne pouvez pas visualiser l’adresse de contact car vous n’êtes pas l’auteur de la thèse";
                return false;
            }
            /* line 11 */
            $this->linesTrace[] = '/* line 11 */';
            return true;
        }
```


## Génération de la classe d'assertion abstraite

Le fichier CSV est utilisé pour générer une classe d'assertion abstraite.

Pour cela, une fois les modifications effectuées sur le fichier CSV, il faut (re)générer la classe d'assertion 
abstraite en utilisant le script `bin/assertions/generate-assertion`.

Exemple :
```bash
bin/assertions/generate-assertion --file module/Depot/data/TheseEntityAssertion.csv
```


## Implémentation de la classe d'assertion

Le fichier CSV est utilisé pour générer une classe d'assertion abstraite que le développeur devra sous-classer afin
d'implémenter les méthodes abstraites mises en jeu.

Par exemple, la classe `module/Depot/src/Depot/Assertion/These/TheseEntityAssertion.php` hérite de la classe générée
`module/Depot/src/Depot/Assertion/These/GeneratedTheseEntityAssertion.php` et se doit donc d'implémenter toutes les 
méthodes abstraites utilisées (`isTheseEnCours()`, `isTheseSoutenue()`, `isUtilisateurEstAuteurDeLaThese()`, 
`isExisteValidationRdvBu()`, etc.)

 
## Ajouter une nouvelle assertion

***TODO***


## Générer la classe d'assertion à partir d'un fichier CSV
