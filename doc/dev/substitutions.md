Substitutions
=============

On parle de substitution d'enregistrements en doublon (les *substitués*) par un enregistrement supplémentaire (le *substituant*).

**C'est parce que nous importons des données de plusieurs sources que nous avons un pb de doublons à régler.**
Certaines données importées ne posent pas de pb de doublon, ex : these, titre_acces, role.
On ne recherche donc pas de doublons partout.

Les données importées ayant un pb de doublon : individu, doctorant, structure, etab, ed, ur. 

Les substitués et le substituant ne résident pas dans le même "espace" : 
- substitués : table PRE_XXX et source externe ;
- substituant : table XXX et source SYGAL.

La détection de doublon se veut automatique (déclenchement sur insert, update, delete, historisation).
Et dans les tables référençant un substitué, la clé étrangère est automatiquement remplacée par l'id du substituant.

Une table consigne quel substituant substitue quels substitués (XXX_SUBSTIT).

Pour un enregistrement, on appelle "NPD" une valeur calculée à partir de ses attributs discriminant ; pour un
individu par exemple les attributs discriminants choisis sont `nom_patronymique`, `prenom1` et `date_naissance`.
L'origine du terme "N P D" vient des 3 premières lettres de ces attributs.
Pour les structures, on a gardé le terme "NPD".

Des enregistrements sont considérés en doublons lorsque leur NPD est le même.


Principes de la recherche de doublons
-------------------------------------

### Exemple : structures

Structures : établissements, écoles doctorales, unités de recherche.

- Situation initiale

`pre_structure`

| id | source_code | libelle             | code    |
|----|-------------|---------------------|---------|
| 12 | INSA::550   | Ecole doctorale 550 | **550** |
| 17 | UCN::550    | Ed 550              | **550** |

Colonne pour détection de doublon (NPD) : `code`.
Valeur du NPD : `550`

- Leur NPD étant identique, les structures `12` et `17` peuvent faire l'objet d'une substitution par 
  un substituant `32` créé à l'occasion (source SYGAL) :

`structure`

| id  | source_code | libelle             | code    |
|-----|-------------|---------------------|---------|
| 32  | SYGAL::550  | Ecole doctorale 550 | **550** |

Quelle valeur choisir pour chaque attribut du substituant (i.e. pourquoi "Ecole doctorale 550" et pas "ED 550") ? 
La plus fréquente ou la première dans l'ordre alpha : utilisation de la fonction `mode()`.

`structure_substit`

| from_id | to_id | from_npd |
|---------|-------|----------|
| 12      | 32    | 550      |
| 17      | 32    | 550      |

**NB** : `from_id` pointe vers une `pre_structure`, `to_id` pointe vers une `structure`.

- Dans les tables référençant un substitué, l'id de ce dernier est remplacé par l'id du substituant, 
  ex : 

- Si un nouveau doublon apparaît, il est ajouté à la substitution existante, et le substituant est mis à jour.

`pre_structure`

| id  | source_code | libelle             | code    |
|-----|-------------|---------------------|---------|
| 12  | INSA::550   | Ecole doctorale 550 | **550** |
| 17  | UCN::550    | Ed 550              | **550** |
| 61  | ULHN::550   | Ed 550              | **550** |

`structure_substit`

| from_id | to_id | from_npd |
|---------|-------|----------|
| 12      | 32    | 550      |
| 17      | 32    | 550      |
| 61      | 32    | 550      |

`structure`

| id  | source_code | libelle | code    |
|-----|-------------|---------|---------|
| 32  | SYGAL::550  | Ed 550  | **550** |


### Exemple : individus

- Situation initiale

`pre_individu`

| id | source_code | nom_patronymique | prenom1 | date_naissance |
|----|-------------|------------------|---------|----------------|
| 12 | INSA::12345 | Hochon           | Paul    | 2000-01-01     |
| 17 | UCN::ABCD   | Hochon           | Paul    | 2000-01-01     |

Colonnes utilisées pour la détection de doublon (NPD) : `nom_patronymique`,  `prenom1`,  `date_naissance`.
Valeur du NPD : `hochon_paul_2000-01-01`

- Leur NPD étant identique, les individus `12` et `17` peuvent faire l'objet d'une substitution par
  un substituant `32` créé à l'occasion (source SYGAL) :

`individu`

| id  | source_code | nom_patronymique | prenom1 | date_naissance |
|-----|-------------|------------------|---------|----------------|
| 32  | SYGAL::8889 | Hochon           | Paul    | 2000-01-01     |

`individu_substit`

| from_id | to_id | from_npd               |
|---------|-------|------------------------|
| 12      | 32    | hochon_paul_2000-01-01 |
| 17      | 32    | hochon_paul_2000-01-01 |

**NB** : `from_id` pointe vers un `pre_individu`, `to_id` pointe vers un `individu`.


### Principe

- La recherche de doublon est déclenchée par un trigger en cas de :
    - insertion (ou restauration),
    - suppression (ou historisation),
    - modification d'attribut : ceux participant au NPD ; mais aussi les autres pour màj le substituant éventuel),
    - changement de source : arrivée dans la source SYGAL ou départ de celle-ci).





### Si un `pre_individu` est modifié.

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
    - S'il n'est pas substitué, STOP.
    - S'il est substitué, le retirer de la substitution.
    - S'il ne reste dans la substitution qu'un seul individu substitué, historiser le substituant et la substitution.


### Un individu est supprimé.

Remarque : Ne pas gérer la suppression avec un "delete cascade" sur la fk 'individu_substit.from_id'
car cela retire la possibilité d'amélioration évoquée plus haut (remplacement des fk).

- Idem historisation.


### Un individu est restauré (dé-historisé).

Idem ajout.



Problématiques
--------------

### Comment choisir les meilleures valeurs d'attributs parmi les doublons ?

Utiliser `mode()` : "the most frequent value of the aggregated argument"
`mode() WITHIN GROUP (ORDER BY i.nom_usuel) as nom_usuel`
(https://www.postgresql.org/docs/current/functions-aggregate.html#FUNCTIONS-ORDEREDSET-TABLE)

Autrement dit, fini le choix manuel des meilleures valeurs d'attributs comme actuellement pour les structures.


### Mettre à jour automatiquement les attributs de l'individu substituant ?

Quand un individu est ajouté automatiquement à une substitution, faut-il mettre à jour automatiquement les attributs 
de l'individu substituant ?
Si oui, ça écraserait les attributs éventuellement modifiés à la main par un utilisateur. 

=> Oui. Donc on décide d'interdire la modification manuelle d'un individu.


### Créer une colonne `individu.npd_force`

Une colonne "NPD forcé" permet de forcer l'inclusion d'un individu à une substitution.

Exemples :
- Individu sans date de naissance (NPD `bernaudin_myriam_`) mais qui en fait est bien un doublon (NPD `bernaudin_myriam_1972-06-03`).
- Individu pour lequel on ne peut pas demander de correction dans la source mais dont on sait que c'est un doublon.


### Normalisation pour NPD

Inspirée de la fonction utilisée dans Octopus.




Scenarios
---------

L'ordre des synchros est important !

### 1) Synchro SRC_PRE_INDIVIDU => PRE_INDIVIDU 

  - PRE_INDIVIDU (id,nom_patronymique,prenom1,source) :
    - `239,hochon,paul,INSA`
    - `475,hochon,paul,UCN`
  - Recherche de subsitutions...
  - INDIVIDU_SUBSTIT (from,to) :
    - `239,877`
    - `475,877`
  - Nouveau INDIVIDU substituant :
    - `877,hochon,paul,SYGAL`

### 2) Synchro SRC_PRE_DOCTORANT => PRE_DOCTORANT

NB : Les données ont une clé étrangère vers des données potentiellement
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

