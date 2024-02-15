Principes du moteur de substitutions
====================================

## Trigger sur la table xxxx (on insert, update, delete)

### Si modification

- calcul du NPD de l'enregistrement (sauf si le NPD est forcé)
- recherche et mise à jour éventuelle d'une substitution existante de cet enregistrement avec ce NPD
  - si retour true, ok, STOP.
- recherche d'une substitution existante pour le NPD de l'enregistrement.
  - si une subsitution existe, ajout de l'enregistrement à celle-ci
  - mise à jour de l'enregistrement substituant
- (à ce stade, aucune substitution n'existe avec ce NPD)
- création de la substitution si l'enregistrement en cours de modif est un doublon d'un enregistrement existant.
- 