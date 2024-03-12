Principes du moteur de substitutions
====================================

## Tables mises en jeu

- Tables "x" :
  - structure
  - etablissement
  - ecole_doctorale
  - unite_recherche
  - individu
  - doctorant

- Tables "substit_x"

## Triggers

- Triggers sur les tables "x" : "substit_trigger_x" (after insert or delete or update)

- Triggers sur les tables "substit_x" : "substit_trigger_on_substit_x" (after insert or delete)


## Événements scrutés sur la table "x"

### Après historisation

- Calcul du NPD de l'enregistrement (sauf si le NPD est forcé)
- Recherche et mise à jour éventuelle d'une substitution existante de cet enregistrement.

### Après suppression

- Calcul du NPD de l'enregistrement (sauf si le NPD est forcé)
- Recherche et mise à jour éventuelle d'une substitution existante de cet enregistrement.

### Après insertion

- Calcul du npd de l'enregistrement (sauf si le npd est forcé).
- Recherche d'une substitution existante pour le npd de l'enregistrement.
  - Si une subsitution existe, ajout de l'enregistrement à celle-ci.
  - Mise à jour de l'enregistrement substituant <<<<<<<<<<<<<<<<<<<<
  - Stop
- (À ce stade, aucune substitution n'existe avec ce NPD)
- Création de la substitution si le nouvel enregistrement est un doublon d'un enregistrement existant.

### Après mise à jour

- Calcul du NPD de l'enregistrement (sauf si le NPD est forcé)
- Recherche et mise à jour éventuelle d'une substitution existante de cet enregistrement avec ce NPD
  - Si retour true, ok, STOP.
- Recherche d'une substitution existante pour le NPD de l'enregistrement.
  - Si une subsitution existe, ajout de l'enregistrement à celle-ci
  - Mise à jour de l'enregistrement substituant <<<<<<<<<<<<<<<
  - Stop
- (À ce stade, aucune substitution n'existe avec ce NPD)
- Création de la substitution si l'enregistrement en cours de modif est un doublon d'un enregistrement existant.
- 