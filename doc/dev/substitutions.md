Substitutions
=============

Structures
----------

- Situation initiale

`structure`

| id | source_code | libelle | **code** |
|----|-------------|---------|----------|
| 12 | INSA::550   | ED550   | **550**  |
| 17 | UCN::550    | ED550   | **550**  |

Colonne pour détection de doublon : `code`.


- Leur `code` étant identique, les structures `12` et `17` peuvent faire l'objet d'une substitution :

`structure`

| id  | source_code | libelle                    | **code** |
|-----|-------------|----------------------------|----------|
| 12  | INSA::550   | Ecole Doctorale de Bonheur | **550**  |
| 17  | UCN::550    | Ecole Doctorale du Bonheur | **550**  |
| ... | ...         | ...                        | ...      |
| 32  | SYGAL::550  | Ecole Doctorale du Bonheur | **550**  |

`structure_substit`

| from_id | to_id   |
|---------|---------|
| 12      | 32      |
| 17      | 32      |

Les enregistrements substitués ne doivent plus être modifiables.

Dans toutes les tables où il y a une clé étrangère `structure_id`, il faut faire la jointure avec `structure_substit` 
pour tenir compte de la substitution.


- Si un nouveau doublon apparaît

`structure`

| id  | source_code | libelle                    | **code** |
|-----|-------------|----------------------------|----------|
| 12  | INSA::550   | Ecole Doctorale de Bonheur | **550**  |
| 17  | UCN::550    | Ecole Doctorale du Bonheur | **550**  |
| ... | ...         | ...                        | ...      |
| 32  | SYGAL::550  | Ecole Doctorale du Bonheur | **550**  |
| ... | ...         | ...                        | ...      |
| 61  | ULHN::550   | Ecole Doctrale du Bonheur  | **550**  |

Il faut modifier la substitution pour ajouter la structure `61`.

`structure_substit`

| from_id | to_id |
|---------|-------|
| 12      | 32    |
| 17      | 32    |
| 61      | 32    |



Individus
---------

- Situation initiale

`pre_individu`

| id | source_code | nom    | prenom | date_naissance |
|----|-------------|--------|--------|----------------|
| 12 | INSA::12345 | Hochon | Paul   | null           |
| 17 | UCN::ABCD   | Hochon | Paul   | 2000-01-01     |

Colonnes utilisées pour la détection de doublon : `nom`,  `prenom`,  `date_naissance`.


- Leur `nom`, `prenom`, `date_naissance` étant identiques, les individus `12` et `17` peuvent faire l'objet d'une substitution :

`pre_individu`

| id  | source_code | nom    | prenom | date_naissance |
|-----|-------------|--------|--------|----------------|
| 12  | INSA::12345 | Hochon | Paul   | null           |
| 17  | UCN::ABCD   | Hochon | Paul   | 2000-01-01     |


`individu`

| id  | source_code | nom    | prenom | date_naissance |
|-----|-------------|--------|--------|----------------|
| 32  | SYGAL::8889 | Hochon | Paul   | 2000-01-01     |


`individu_substit`

| from_id | to_id | from_npd               |
|---------|-------|------------------------|
| 12      | 32    | hochon_paul_           |
| 17      | 32    | hochon_paul_2000-01-01 |


Dans toutes les tables où il y a une clé étrangère `individu_id`, il faut faire la jointure avec `individu_substit`
pour tenir compte de la substitution.

Une évolution amélioration (?) ultérieure serait, plutôt que d'utiliser la solution de la jointure, de substituer 
directement dans les tables concernées les fk 'individu_id' par l'id de l'individu substituant.


### Un pre_individu est modifié.

  - Calculer son NPD.

  - Rechercher par son `id` dans `individu_substit` (non historisés) selon le `from_id` s'il est substitué.
    - S'il existe dans `individu_substit`, comparer son NPD à `individu_substit.from_npd`.
      - Si les NPD sont identiques, c'est que la subsitution est toujours justifiée.
        - On met à jour automatiquement les attributs de l'individu substituant à partir des doublons.
        - STOP
      - Sinon, il faut sortir le pre_individu de la substitution.
      - S'il ne reste dans la substitution qu'un seul individu substitué, historiser le substituant et la substitution.

  - Rechercher son NPD dans `individu_substit` pour savoir si le pre_individu doit être ajouté à une substitution existante.
    - Si une substitution existe avec ce NPD
      - Y ajouter le pre_individu.
      - STOP

  - Rechercher dans `v_individu_doublon` pour savoir s'il fabrique un doublon.
    - S'il n'a pas de doublons : STOP.
    - S'il a des doublons :
        - Créer un nouvel individu substituant. Il doit posséder les meilleurs attributs des doublons (cf. Problématiques).
        - Créer la substitution dans `individu_substit`.


### Un individu est ajouté.

  - Calculer son NPD.

  - Rechercher ce NPD dans `individu_substit` pour savoir si l'individu doit être ajouté à une substitution existante.
    - Si une substitution existe avec ce NPD, y ajouter l'individu. 
    - Sinon, rechercher dans `v_individu_doublon` pour savoir s'il fabrique un doublon.
      - S'il n'a pas de doublons : stop.
      - S'il a des doublons :
        - Créer un nouvel individu substituant. Il doit posséder les meilleurs attributs des doublons (cf. Problématiques).
        - Créer la substitution dans `individu_substit`.


### Un individu est historisé.

- Rechercher dans `individu_substit` s'il est substitué.
    - S'il n'est pas substitué, stop.
    - S'il est substitué, le retirer de la substitution.
    - S'il ne reste dans la substitution qu'un seul individu substitué, historiser le substituant et la substitution.


### Un individu est supprimé.

Remarque : Ne pas gérer la suppression avec un "delete cascade" sur la fk 'individu_substit.individu_substitue_id'
car cela retire la possibilité d'amélioration évoquée plus haut (substitution réelle des fk).

- Idem historisation.


### Un individu est restauré (dé-historisé).

Idem ajout.



Problématiques
--------------

### Comment choisir les meilleurs valeurs d'attributs parmi les doublons ?

Utiliser `mode()` : "the most frequent value of the aggregated argument"
`mode() WITHIN GROUP (ORDER BY i.nom_usuel) as nom_usuel`
(https://www.postgresql.org/docs/current/functions-aggregate.html#FUNCTIONS-ORDEREDSET-TABLE)


### Mettre à jour automatiquement les attributs de l'individu substituant ?

Quand un individu est ajouté automatiquement à une substitution, faut-il mettre à jour automatiquement les attributs 
de l'individu substituant ?
Si oui, ça écraserait les attributs éventuellement modifiés à la main par un utilisateur. 

=> On décide d'interdire la modification manuelle d'un individu.


### Créer une colonne `individu.npd_force`

Une colonne "NPD forcé" permet de forcer l'inclusion d'un individu à une substitution.

Exemples :
- Individu sans date de naissance (NPD `bernaudin_myriam_`) mais qui en fait est bien un doublon (NPD `bernaudin_myriam_1972-06-03`).
- Individu pour lequel on ne peut pas demander de correction dans la source mais dont on sait que c'est un doublon.


### Normalisation pour NPD

S'inspirer de la fonction utilisée dans Octopus.




Scenarios
---------

L'ordre des synchros n'est pas anodin !

### 1) Synchro SRC_PRE_INDIVIDU => PRE_INDIVIDU 

  - PRE_INDIVIDU (id,nom,prenom,source) :
    - `239,hochon,paul,INSA`
    - `475,hochon,paul,UCN`
  - Recherche de subsitutions...
  - INDIVIDU_SUBSTIT (from,to) :
    - `239,877`
    - `475,877`
  - Nouveau INDIVIDU substituant :
    - `877,hochon,paul,SYGAL`

### 2) Synchro SRC_PRE_DOCTORANT => PRE_DOCTORANT

NB : Il s'agit ici de données ayant une clé étrangère vers des données potentiellement
substituées (individus).

  - PRE_DOCTORANT (id,ine,individu_id,source) :
    - `12,ABCD,239,INSA`
    - `27,ABCD,475,UCN`
  - Recherche de subsitutions... 
    - Meilleures valeurs : jointure avec INDIVIDU_SUBSTIT pour obtenir 
      l'id de l'individu substituant, ici `877`.
    - Il est nécessaire de faire cette manipulation lors du calcul des meilleures
      valeurs car la synchro SRC_DOCTORANT => DOCTORANT ne prend en compte que 
      les sources importables, or le substituant est dans la source 
      non importable SYGAL.
  - INDIVIDU_SUBSTIT (from,to) :
    - `12,48`
    - `27,48`
  - Nouveau DOCTORANT substituant :
    - `48,ABCD,877,SYGAL`

### 3) Synchro SRC_INDIVIDU => INDIVIDU

  - INDIVIDU substituant déjà présent :
    - `877,hochon,paul,SYGAL`
  - SRC_INDIVIDU sélectionne les individus non substitués.

### 4) Synchro SRC_DOCTORANT => DOCTORANT

  - DOCTORANT substituant déjà présent :
    - `48,ABCD,877,SYGAL`
  - SRC_DOCTORANT sélectionne les doctorants non substitués.

