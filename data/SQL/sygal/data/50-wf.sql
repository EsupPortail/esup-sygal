
-- INSERT INTO WF_ETAPE (
--   ID,
--   CHEMIN,
--   CODE,
--   DESC_NON_FRANCHIE,
--   DESC_SANS_OBJECTIF,
--   LIBELLE_ACTEUR,
--   LIBELLE_AUTRES,
--   OBLIGATOIRE,
--   ORDRE,
--   ROUTE
-- )
--   select
--     ID,
--     CHEMIN,
--     CODE,
--     DESC_NON_FRANCHIE,
--     DESC_SANS_OBJECTIF,
--     LIBELLE_ACTEUR,
--     LIBELLE_AUTRES,
--     OBLIGATOIRE,
--     ORDRE,
--     ROUTE
--   from sodoct.WF_ETAPE@doctprod
-- ;


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
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (11, 'ATTESTATIONS', 15, 1, 1, 'these/depot', 'Attestations', 'Attestations', 'Attestations non renseignées', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (12, 'VALIDATION_PAGE_DE_COUVERTURE', 8, 1, 1, 'these/validation-page-de-couverture', 'Validation de la page de couverture', 'Validation de la page de couverture', 'Validation de la page de couverture non effectuée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (31, 'DEPOT_VERSION_ORIGINALE_CORRIGEE', 200, 1, 1, 'these/depot-version-corrigee', 'Téléversement de la thèse corrigée', 'Téléversement de la thèse corrigée', 'Téléversement de la thèse corrigée non effectué', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (32, 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE', 240, 1, 1, 'these/archivage-version-corrigee', 'Archivabilité de la version originale de la thèse corrigée', 'Archivabilité de la version originale de la thèse corrigée', 'Archivabilité de la version originale de la thèse corrigée non testée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (33, 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE', 250, 2, 1, 'these/archivage-version-corrigee', 'Téléversement d''une version retraitée de la thèse corrigée', 'Téléversement d''une version retraitée de la thèse corrigée', 'Téléversement d''une version retraitée de la thèse corrigée non effectué', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (34, 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE', 260, 2, 1, 'these/archivage-version-corrigee', 'Archivabilité de la version retraitée de la thèse corrigée', 'Archivabilité de la version retraitée de la thèse corrigée', 'Archivabilité de la version retraitée de la thèse corrigée non testée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (35, 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE', 270, 2, 1, 'these/archivage-version-corrigee', 'Vérification de la version retraitée de la thèse corrigée', 'Vérification de la version retraitée de la thèse corrigée', 'Vérification de la version retraitée de la thèse corrigée non effectuée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (38, 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT', 280, 1, 1, 'these/validation-these-corrigee', 'Validation automatique du dépôt de votre thèse corrigée', 'Validation automatique du dépôt de la thèse corrigée', 'Validation automatique du dépôt de la thèse corrigée non effectuée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (39, 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR', 290, 1, 1, 'these/validation-these-corrigee', 'Validation de la thèse corrigée par les directeurs de thèse', 'Validation de la thèse corrigée par les directeurs de thèse', 'Validation de la thèse corrigée par les directeurs de thèse non effectuée', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (51, 'ATTESTATIONS_VERSION_CORRIGEE', 210, 1, 1, 'these/depot-version-corrigee', 'Attestations version corrigée', 'Attestations version corrigée', 'Attestations version corrigée non renseignées', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (52, 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE', 220, 1, 1, 'these/depot-version-corrigee', 'Autorisation de diffusion de la version corrigée', 'Autorisation de diffusion de la version corrigée', 'Autorisation de diffusion de la version corrigée non remplie', null);
INSERT INTO WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (60, 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE', 300, 1, 1, 'these/version-papier', 'Remise de l''exemplaire papier de la thèse corrigée', 'Remise de l''exemplaire papier de la thèse corrigée', 'Remise de l''exemplaire papier de la thèse corrigée', null);


DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from WF_ETAPE;
  loop
    select WF_ETAPE_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/



