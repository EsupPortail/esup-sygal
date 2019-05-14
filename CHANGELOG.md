Journal des modifications
=========================

1.1.9 (14/05/2019)
------------------

### Ajout

- Role Rapporteur non membre du jury pour les cas particuliers. Seulement sur les pages de couverture.


1.1.8 (30/04/2019)
------------------

### Corrections

- Correction du nom de fichier généré lors d'un téléversement : application du formatter à tous les types 
  de fichiers téléversés pour éviter les collisions.


1.1.7 (23/04/2019)
------------------

### Corrections

- Abandon du TitreFormatter redondant et rétablissement du TitreApogeeFilter corrigé (pour ne plus subtituer les 
  guillemets français).
- Correction du bug dans le module unicaen/auth empêchant de s'authentifier via la fédération d'identité Renater.   


1.1.6 (15/04/2019)
------------------

### Corrections

- Remplacement des caractères spéciaux d'apogée pour les guillements par le biais d'un TitreFormatter.


1.1.5 (09/04/2019)
------------------

### Corrections

- Correction du requêtage des années universitaires de 1ère inscription pour le pavé de filtrage des thèses.


1.1.4 (03/04/2019)
------------------

### Nouveautés

- Ouverture à toutes personnes identifiées de la consultation de la liste des thèses, à la recherche de thèse et à la 
  visualisation de la page d'information associée à une thèse. 

### Améliorations

- Suppression dans la table `THESE` de la redondance de l'année universitaire de 1ere inscription avec la table
  `THESE_ANNEE_UNIV`. La colonne `ANNEE_UNIV_1ERE_INSC` n'est plus utilisée, vaut toujours NULL et disparaîtra dans 
  une version ultérieure. 
- Possibilité de changer le lien/logo affiché dans le pied des pages de l'application.

### Corrections

- Résolution du problème de l'année universitaire de 1ere inscription erronée en cas de changement de discipline.
  Requiert la version 1.2.5 du web service d'import.


Versions antérieures
--------------------

Avant la version 1.1.4, le journal des modifications n'avait pas encore été découvert.
