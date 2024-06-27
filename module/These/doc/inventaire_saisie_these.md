Infos importées qu'il faudra pouvoir saisir dans SyGAL
======================================================

Ceci est l'inventaire des infos importées d'Apogée/Physalis qu'il faudra pouvoir saisir/modifier manuellement 
dans SyGAL.

**Figurent seulement les colonnes pour lesquelles on a des choses à dire.**

## individu

Existant :
- civilite
- nom_usuel
- nom_patronymique
- prenom1
- prenom2
- email
- date_naissance
- nationalite
- supann_id
- pays_id_nationalite
- id_ref : attention, disparu suite aux conflits

Importé et à ajouter :
- type : chercher les usages (y compris dans des requêtes) pour suppression ?
- prenom3
- supann_id : correspond au numero employé dans le SI, modifiable
  nom_patronymique : encourager à le saisir pour le moteur substitutions
- email : actuellement l'email est modifible dans individu_compl,
  supprimer individu_compl lorsque la modif sera faite dans individu (penser à la reprise de données)
- date_naissance : saisie recommandé (cf. moteur substitutions)
- nationalite : libellé à abandonner car pays_id_nationalite
- pays_id_naissance : à ajouter ? car déjà dans le module Admission

## doctorant

Existant :
Néant

Importé et à ajouter :
- etablissement_id : redondant avec these.etablissement_id mais not null, à supprimer
- ine : valider avec l'API INES (soumise à inscription) ?
- code_apprenant_in_source (obtenu de Pégase via le flux Inscriptions Administratives, non modifiable)

## acteur

Existant :
  - role_id
  - qualite : cf. rem plus bas
  - etablissement_id
  - etablissement_force_id : disparaitra puisque etablissement_id sera modifiable
  - lib_role_compl : à supprimer (toujours null)
  - unite_rech_id

Importé et à ajouter :
  - qualite : remplacer par un qualite_id, utiliser la table soutenance_qualite

## structure

Existant :
- sigle :
    - actuellement le contenu c'est du n'importe quoi
    - conseiller de renseigner un vrai sigle dans le formulaire ?
- libelle
- chemin_logo
- type_structure_id
- code :
    - il s'agit du code national si ED (ex: 508, obligatoire pour le moteur substitutions),
    - du code labo (ex : EA7263) pour les UR (obligatoire pour le moteur substitutions),
    - du code national si etablissement français (ex : université)
- est_ferme
- adresse
- telephone
- fax
- email
- site_web
- id_ref
- id_hal

Importé et à ajouter :
Néant

## etablissement

Existant :
- domaine : a priori utile seulement pour les étab d'inscription pour déterminer l'établissement auquel appartient l'utilisateur connecté
- est_etab_inscription
- est_membre
- est_associe
- est_comue
- est_ced
- signature_convocation_id
- email_assistance
- email_bibliotheque
- email_doctorat

Importé et à ajouter :
Néant

## ecole_doct

Existant :
- theme
- offre_these : URL

Importé et à ajouter :
Néant

## unite_rech

Existant :
- etab_support : à supprimer car toujours null
- autres_etab : à supprimer car toujours null
- rnsr_id

Importé et à ajouter :
Néant

## these

Existant :
- etablissement_id
- doctorant_id
- ecole_doct_id
- unite_rech_id
- date_fin_confid
- date_prem_insc
- titre

Importé et à ajouter :
- etat_these : 
  - 4 valeurs stables seulement (A,E,S,U), pas besoin de créer une table a priori
  - calculable selon workflow ?
- resultat : 2 valeurs stables seulement (0,1), pas besoin de créer une table a priori
- code_sise_disc :
  - remplacer par un discipline_sise_id car (table discipline_sise), migration possible car que des codes sise
  - comment la tenir à jour (API?) ?
- lib_disc : à supprimer, cf. code_sise_disc
- date_prev_soutenance : utilité à confirmer avec fonctionnels
- date_soutenance : est-ce la bonne table ?
- date_fin_confid
- lib_etab_cotut : remplacer par un etablissement_id (période de transition : on remplace pas, on ajoute)
- lib_pays_cotut : remplacer par un pays_id_cotut (période de transition : on remplace pas, on ajoute)
- correc_autorisee :
    - actuellement forçage possible via la colonne these.correc_autorisee_forcee,
    - à terme on abandonnera le forçage et ne restera que correc_autorisee
- correc_effectuee : calculable d'après le dépôt d'une version corrigée ?
- soutenance_autoris : est-ce la bonne table ?
- date_autoris_soutenance : est-ce la bonne table ?
- tem_avenant_cotut : calculable d'après le dépôt d'un avenant de cotutelle ? ou bien autorise le dépôt ?
- date_abandon : readonly si source importable
- date_transfert : readonly si source importable

A ajouter :
- resultat : readonly si source importable
- correc_date_butoir_avec_sursis : demander utilité aux fonctionnels

Colonnes abandonnées à supprimer :
- besoin_expurge
- cod_unit_rech
- lib_unit_rech
- source_code_sav

## these_annee_univ

Existant :
- Le formulaire de saisie des généralités d'un thèse crée automatiquement la 1ere année dans la table these_annee_univ.
  Comment seront créées les années suivantes ? Lors de la réception du flux Inscriptions Administratives Pégase ?
  COmment ça se passe dans Apogée ? C'est saisi manuellement ou pas ?

## financement

Importé et à ajouter :
  - date_debut, date_fin : nullable, utile ?
  - annee : quel rapport avec dates début/fin ?
  - origine_financement_id
  - complement_financement : texte libre
  - quotite_financement : valeurs possibles ?
  - code_type_financement : 
    - idée de créer une table type_financement(id, code, libelle) et remplacer par un type_financement_id ?
    - mais plusieurs libellés différents pour un même code ! ex: Sans Contrat Doctoral, Contrat Doctoral  Autres organismes, 10-Doct salarié fontion publique
  - libelle_type_financement : cf. code_type_financement

## titre_acces

Importé et à ajouter :
- titre_acces_interne_externe
- libelle_titre_acces : créer une nomenclature ? demander aux fonctionnels
- type_etb_titre_acces : actuellement y a du n'importe quoi, trop de merde pour passer à une table de nomenclature
- libelle_etb_titre_acces : remplacer par un etablissement_id ?
- code_dept_titre_acces : demander aux fonctionnels si utile
- code_pays_titre_acces : demander aux fonctionnels si utile, si oui remplacer par pays_id

## role

Existant :
  - déjà un formulaire unicaen/auth mais trop générique et incomplet : en hériter ?

Importé et à ajouter :
- libelle
- code
- role_id
- these_dep
- structure_id
- type_structure_dependant_id

## domaine_hal

Aucune modification autorisée, import pur.
  
## variable

Importé et à ajouter :
- etablissement_id
- description
- valeur
- date_deb_validite : mettre la date du jour de création systématiquement
- date_fin_validite : mettre une date très éloignée
