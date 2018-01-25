--
-- Base de données SYGAL.
--
-- Données.
--


---------------------- SOURCE ---------------------

INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (1, 'UCN_apogee', 'Apogée Université de Caen Normandie', 1);
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (2, 'SYGAL',      'SYGAL',                                 0);


---------------------- ETABLISSEMENT ---------------------

INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (1, 'COMUE', 'Normandie Université');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (2, 'UCN',   'Université de Caen Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (3, 'URN',   'Université de Rouen Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (4, 'ULHN',  'Université Le Havre Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (5, 'INSA',  'INSA de Rouen');


---------------------- UTILISATEUR ---------------------

--truncate table UTILISATEUR;
INSERT INTO UTILISATEUR(ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD)
    with ds (USERNAME, EMAIL, DISPLAY_NAME) as (
      select 'sygal-app', 'contact.sygal@unicaen.fr',          'SYGAL' from dual union all
      select 'bernardb',  'bruno.bernard@unicaen.fr',          'BB'    from dual union all
      select 'gauthierb', 'bertrand.gauthier@unicaen.fr',      'BG'    from dual union all
      select 'metivier',  'jean-philippe.metivier@unicaen.fr', 'JPM'   from dual
    )
  select UTILISATEUR_ID_SEQ.nextval, ds.USERNAME,  ds.EMAIL, ds.DISPLAY_NAME, 'ldap' from ds;


--------------------------- ROLE -----------------------

--delete from ROLE;
INSERT INTO ROLE (
  ID,
  ETABLISSEMENT_ID,
  LIBELLE,
  CODE,
  SOURCE_CODE,
  SOURCE_ID,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID)
with ds (LIBELLE, CODE) as (
  SELECT 'Administrateur technique',   'ADMIN_TECH' from dual union all
  SELECT 'Administrateur',             'ADMIN'      from dual union all
  SELECT 'Bureau des doctorats',       'BDD'        from dual union all
  SELECT 'Bibliothèque universitaire', 'BU'         from dual
)
SELECT
  ROLE_ID_SEQ.nextval,
  etab.id,
  ds.LIBELLE,
  ds.CODE,
  GEN_SOURCE_CODE(etab.id, ds.CODE),
  src.id,
  u.id,
  u.id
FROM ds
  join ETABLISSEMENT etab on etab.CODE <> 'COMUE'
  join SOURCE src on src.CODE = 'SYGAL'
  join UTILISATEUR u on u.USERNAME = 'sygal-app'
;

--------------------------- INDIVIDU -----------------------

--delete from INDIVIDU;
insert into INDIVIDU (
  ID,
  TYPE,
  CIVILITE,
  NOM_USUEL,
  NOM_PATRONYMIQUE,
  PRENOM1,
  EMAIL,
  DATE_NAISSANCE,
  NATIONALITE,
  SOURCE_CODE,
  SOURCE_ID,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID
)
with ds(TYPE, CIVILITE, NOM_USUEL, PRENOM1, EMAIL, NATIONALITE, SOURCE_CODE) as (
  select 'autre', 'M.', 'Gauthier', 'Bertrand',      'bertrand.gauthier@unicaen.fr',      'Français(e)', '?' from dual union all
  select 'autre', 'M.', 'Métivier', 'Jean-Philippe', 'jean-philippe.metivier@unicaen.fr', 'Français(e)', '#' from dual union all
  select 'autre', 'M.', 'Bernard',  'Bruno',         'bruno.bernard@unicaen.fr',          'Français(e)', '@' from dual
)
select
  INDIVIDU_ID_SEQ.nextval,
  ds.TYPE,
  ds.CIVILITE,
  ds.NOM_USUEL,
  ds.NOM_USUEL,
  ds.PRENOM1,
  ds.EMAIL,
  sysdate,
  ds.NATIONALITE,
  ds.SOURCE_CODE,
  s.id,
  u.id,
  u.id
from ds
  join OTH.SOURCE s on s.CODE = 'SYGAL'
  join OTH.UTILISATEUR u on u.username = 'sygal-app'
;

--------------------------- INDIVIDU_ROLE -----------------------

--delete from INDIVIDU_ROLE;
insert into INDIVIDU_ROLE(ID, INDIVIDU_ID, ROLE_ID)
  with ds(email, code_role, code_etab) as (
    select 'bertrand.gauthier@unicaen.fr',      'ADMIN_TECH', 'UCN' from dual union all
    select 'jean-philippe.metivier@unicaen.fr', 'ADMIN_TECH', 'UCN' from dual union all
    select 'bruno.bernard@unicaen.fr',          'ADMIN_TECH', 'UCN' from dual
  )
  select INDIVIDU_ROLE_ID_SQ.nextval, i.id, r.id from ds
  join INDIVIDU i on i.EMAIL = ds.email
  join ETABLISSEMENT e on e.CODE = ds.code_etab
  join ROLE r on r.SOURCE_CODE = GEN_SOURCE_CODE(e.id, ds.code_role)
;

------

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (21, 'ecole-doctorale', 'École doctorale', 100);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (23, 'faq', 'FAQ', 10);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (1, 'droit', 'Gestion des droits', 1);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (2, 'import', 'Import', 10);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (3, 'these', 'Thèse', 20);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (4, 'doctorant', 'Doctorant', 30);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (5, 'utilisateur', 'Utilisateur', 5);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (22, 'unite-recherche', 'Unité de Recherche', 200);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (24, 'validation', 'Validations', 25);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (44, 'fichier-divers', 'Fichier divers', 40);

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

INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (81, 3, 'telechargement-fichier', 'Téléchargement de fichier déposé', 3060);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (82, 3, 'consultation-fiche', 'Consultation de la fiche d''identité de la thèse', 3025);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (83, 3, 'consultation-depot', 'Consultation du dépôt de la thèse', 3026);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (84, 3, 'consultation-description', 'Consultation de la description de la thèse', 3027);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (85, 3, 'consultation-archivage', 'Consultation de l''archivage de la thèse', 3028);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (86, 3, 'consultation-rdv-bu', 'Consultation du rendez-vous BU', 3029);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (87, 3, 'creation-zip', 'Création de l''archive ZIP', 3200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (88, 24, 'rdv-bu', 'Validation suite au rendez-vous à la BU', 3035);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (91, 21, 'consultation', 'Consultation d''école doctorale', 100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (92, 21, 'modification', 'Modification d''école doctorale', 110);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (96, 23, 'modification', 'Modification de la FAQ', 10);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (1, 1, 'role-visualisation', 'Rôles - Visualisation', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2, 1, 'role-edition', 'Rôles - Édition', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (3, 1, 'privilege-visualisation', 'Privilèges - Visualisation', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (4, 1, 'privilege-edition', 'Privilèges - Édition', 4);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (6, 2, 'ecarts', 'Écarts entre les données de l''application et ses sources', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (9, 2, 'vues-procedures', 'Mise à jour des vues différentielles et des procédures de mise à jour', 4);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (8, 2, 'tbl', 'Tableau de bord principal', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (7, 2, 'maj', 'Mise à jour des données à partir de leurs sources', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (11, 4, 'modification-persopass', 'Modification du persopass', 10);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (14, 5, 'attribution-role', 'Attribution de rôle aux utilisateurs', 20);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (41, 3, 'saisie-description', 'Saisie de la description', 3040);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (42, 3, 'saisie-autorisation-diffusion', 'Saisie du formulaire d''autorisation de diffusion', 3090);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (43, 3, 'depot-version-initiale', 'Dépôt de la version initiale de la thèse', 3050);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (44, 3, 'edition-convention-mel', 'Edition de la convention de mise en ligne', 3070);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (45, 3, 'saisie-mot-cle-rameau', 'Saisie des mots-clés RAMEAU', 3030);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (46, 5, 'consultation', 'Consultation des utilisateurs', 10);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (47, 3, 'recherche', 'Recherche de thèses', 3010);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (61, 3, 'saisie-conformite-archivage', 'Juger de la conformité de la thèse pour archivage', 3080);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (90, 24, 'rdv-bu-suppression', 'Suppression de la validation concernant le rendez-vous à la BU', 3036);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (93, 22, 'consultation', 'Consultation d''Unité de Recherche', 100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (94, 22, 'modification', 'Modification d''Unité de Recherche', 110);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (95, 5, 'modification', 'Modification d''utilisateur', 110);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (217, 3, 'version-papier-corrigee', 'Validation de la remise de la version papier corrigée', 3300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (137, 3, 'depot-version-corrigee', 'Dépôt de la version corrigée de la thèse', 3055);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (127, 24, 'depot-these-corrigee', 'Validation du dépôt de la thèse corrigée', 4000);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (128, 24, 'depot-these-corrigee-suppression', 'Suppression de la validation du dépôt de la thèse corrigée', 4120);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (157, 44, 'televerser', 'Téléverser un fichier comme le PV ou le rapport de soutenance, la demande de confidentialité, etc.', 100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (160, 44, 'consulter', 'Télécharger/consulter un fichier comme le PV ou le rapport de soutenance, la demande de confidentialité, etc.', 150);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (177, 3, 'export-csv', 'Export des thèses au format CSV', 3020);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (197, 3, 'saisie-rdv-bu', 'Modification des informations rendez-vous BU', 3029);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (129, 24, 'correction-these', 'Validation des corrections de la thèse', 4100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (130, 24, 'correction-these-suppression', 'Suppression de la validation des corrections de la thèse', 4120);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (198, 3, 'saisie-attestations', 'Modification des attestations', 3030);

INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (2, 'DEPOT_THESE_CORRIGEE', 'Validation automatique du dépôt de la thèse corrigée');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (3, 'CORRECTION_THESE', 'Validation par le(s) directeur(s) de thèse des corrections de la thèse');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (1, 'RDV_BU', 'Validation suite au rendez-vous avec le doctorant');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (4, 'VERSION_PAPIER_CORRIGEE', 'Confirmation dépot de la version papier corrigée');

INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (1, 'DEPOT_VERSION_ORIGINALE', 10, 1, 1, 'these/depot', 'Téléversement de la thèse', 'Téléversement de la thèse', 'Téléversement de la thèse non effectué', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (2, 'AUTORISATION_DIFFUSION_THESE', 20, 1, 1, 'these/depot', 'Autorisation de diffusion de la thèse', 'Autorisation de diffusion de la thèse', 'Autorisation de diffusion non remplie', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (3, 'SIGNALEMENT_THESE', 30, 1, 1, 'these/description', 'Signalement de la thèse', 'Signalement de la thèse', 'Signalement non renseigné', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (4, 'ARCHIVABILITE_VERSION_ORIGINALE', 40, 1, 1, 'these/archivage', 'Archivabilité de la thèse', 'Archivabilité de la thèse', 'Archivabilité non testée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (5, 'DEPOT_VERSION_ARCHIVAGE', 50, 2, 1, 'these/archivage', 'Téléversement d''une version retraitée de la thèse', 'Téléversement d''une version retraitée de la thèse', 'Téléversement d''une version retraitée non effectué', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (6, 'ARCHIVABILITE_VERSION_ARCHIVAGE', 60, 2, 1, 'these/archivage', 'Archivabilité de la version retraitée de la thèse', 'Archivabilité de la version retraitée de la thèse', 'Archivabilité de la version retraitée non testée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (7, 'VERIFICATION_VERSION_ARCHIVAGE', 70, 2, 1, 'these/archivage', 'Vérification de la version retraitée de la thèse', 'Vérification de la version retraitée de la thèse', 'Vérification de la version retraitée non effectuée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (8, 'RDV_BU_SAISIE_DOCTORANT', 80, 1, 1, 'these/rdv-bu', 'Saisie des coordonnées et disponibilités du doctorant pour le rendez-vous à la BU', 'Saisie des coordonnées et disponibilités du doctorant pour le rendez-vous à la BU', 'Saisie des coordonnées et disponibilités pour le rendez-vous à la BU non effectuée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (9, 'RDV_BU_SAISIE_BU', 90, 1, 1, 'these/rdv-bu', 'Saisie infos BU', 'Saisie infos BU', 'Saisie infos BU non effectuée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (10, 'RDV_BU_VALIDATION_BU', 100, 1, 1, 'these/rdv-bu', 'Validation', 'Validation', 'Validation de la BU non effectuée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (51, 'ATTESTATIONS_VERSION_CORRIGEE', 210, 1, 1, 'these/depot-version-corrigee', 'Attestations version corrigée', 'Attestations version corrigée', 'Attestations version corrigée non renseignées', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (52, 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE', 220, 1, 1, 'these/depot-version-corrigee', 'Autorisation de diffusion de la version corrigée', 'Autorisation de diffusion de la version corrigée', 'Autorisation de diffusion de la version corrigée non remplie', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (60, 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE', 300, 1, 1, 'these/version-papier', 'Remise de l''exemplaire papier de la thèse corrigée', 'Remise de l''exemplaire papier de la thèse corrigée', 'Remise de l''exemplaire papier de la thèse corrigée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (31, 'DEPOT_VERSION_ORIGINALE_CORRIGEE', 200, 1, 1, 'these/depot-version-corrigee', 'Téléversement de la thèse corrigée', 'Téléversement de la thèse corrigée', 'Téléversement de la thèse corrigée non effectué', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (32, 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE', 240, 1, 1, 'these/archivage-version-corrigee', 'Archivabilité de la version originale de la thèse corrigée', 'Archivabilité de la version originale de la thèse corrigée', 'Archivabilité de la version originale de la thèse corrigée non testée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (33, 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE', 250, 2, 1, 'these/archivage-version-corrigee', 'Téléversement d''une version retraitée de la thèse corrigée', 'Téléversement d''une version retraitée de la thèse corrigée', 'Téléversement d''une version retraitée de la thèse corrigée non effectué', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (34, 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE', 260, 2, 1, 'these/archivage-version-corrigee', 'Archivabilité de la version retraitée de la thèse corrigée', 'Archivabilité de la version retraitée de la thèse corrigée', 'Archivabilité de la version retraitée de la thèse corrigée non testée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (35, 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE', 270, 2, 1, 'these/archivage-version-corrigee', 'Vérification de la version retraitée de la thèse corrigée', 'Vérification de la version retraitée de la thèse corrigée', 'Vérification de la version retraitée de la thèse corrigée non effectuée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (11, 'ATTESTATIONS', 15, 1, 1, 'these/depot', 'Attestations', 'Attestations', 'Attestations non renseignées', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (38, 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT', 280, 1, 1, 'these/validation-these-corrigee', 'Validation automatique du dépôt de votre thèse corrigée', 'Validation automatique du dépôt de la thèse corrigée', 'Validation automatique du dépôt de la thèse corrigée non effectuée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (39, 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR', 290, 1, 1, 'these/validation-these-corrigee', 'Validation de la thèse corrigée par les directeurs de thèse', 'Validation de la thèse corrigée par les directeurs de thèse', 'Validation de la thèse corrigée par les directeurs de thèse non effectuée', null);


