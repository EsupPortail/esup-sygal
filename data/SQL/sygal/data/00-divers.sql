
---------------------- ETABLISSEMENT ---------------------

INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (1, 'COMUE', 'Normandie Université');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (2, 'UCN',   'Université de Caen Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (3, 'URN',   'Université de Rouen Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (4, 'ULHN',  'Université Le Havre Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (5, 'INSA',  'INSA de Rouen');

---------------------- SOURCE ---------------------

INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE)
  with ds(id, code_etab, code_source, lib, importable) as (
    select 1, 'UCN',   'apogee', 'Apogée UCN',       1 from dual union all
    select 2, 'COMUE', 'SYGAL',  'SYGAL COMUE',      1 from dual
  )
  select ds.id, ds.code_etab||'::'||ds.code_source, ds.lib, ds.importable
  from ds;


------

INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (1, 'THESE_PDF', 'Thèse au format PDF');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (2, 'FICHIER_NON_PDF', 'Fichier non PDF');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (3, 'PV_SOUTENANCE', 'PV de soutenance');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (4, 'RAPPORT_SOUTENANCE', 'Rapport de soutenance');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (5, 'DEMANDE_CONFIDENT', 'Demande de confidentialité');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (6, 'PROLONG_CONFIDENT', 'Demande de prolongation de confidentialité');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (7, 'PRE_RAPPORT_SOUTENANCE', 'Pré-rapport de soutenance');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (8, 'CONV_MISE_EN_LIGNE', 'Convention de mise en ligne');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (9, 'AVENANT_CONV_MISE_EN_LIGNE', 'Avenant à la convention de mise en ligne');

INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (1, 'VA', 'Version d''archivage');
INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (2, 'VD', 'Version de diffusion');
INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (3, 'VO', 'Version originale');
INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (4, 'VAC', 'Version d''archivage corrigée');
INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (5, 'VDC', 'Version de diffusion corrigée');
INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (6, 'VOC', 'Version originale corrigée');

INSERT INTO FAQ (ID, QUESTION, REPONSE, ORDRE) VALUES (1, 'Qu''est-ce qu''une question ?', 'C''est une phrase appelant une réponse.', 10);

INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (2, 'DEPOT_THESE_CORRIGEE', 'Validation automatique du dépôt de la thèse corrigée');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (3, 'CORRECTION_THESE', 'Validation par le(s) directeur(s) de thèse des corrections de la thèse');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (1, 'RDV_BU', 'Validation suite au rendez-vous avec le doctorant');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (4, 'VERSION_PAPIER_CORRIGEE', 'Confirmation dépot de la version papier corrigée');

INSERT INTO ENV(
  ID,
  ANNEE_ID,
  LIB_ETAB,
  LIB_ETAB_A,
  LIB_ETAB_LE,
  LIB_ETAB_DE,
  LIB_PRESID_LE,
  LIB_PRESID_DE,
  NOM_PRESID,
  EMAIL_ASSISTANCE,
  EMAIL_BU,
  EMAIL_BDD,
  LIB_COMUE
) VALUES (
  1,
  NULL,
  'Université de Caen Normandie',
  'à l''Université de Caen Normandie',
  'l''Université de Caen Normandie',
  'de l''Université de Caen Normandie',
  'le Président',
  'du Président',
  'Pierre DENISE',
  'assistance-sodoct@unicaen.fr',
  'scd.theses@unicaen.fr',
  'recherche.doctorat@unicaen.fr',
  'Normandie Université'
)
;