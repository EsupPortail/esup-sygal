drop table WF_ETAPE;
drop table WF_ETAPE_DEP;

CREATE TABLE WF_ETAPE
(
  ID                 NUMBER(*, 0) NOT NULL,
--   PARENT_ID          NUMBER(*) NULL,
  CODE               VARCHAR2(64 CHAR) NOT NULL,
  ORDRE              NUMBER(*, 0) DEFAULT 1 NOT NULL,
  CHEMIN             NUMBER(*, 0) DEFAULT 1 NOT NULL,
  OBLIGATOIRE        NUMBER(1, 0) DEFAULT 1 NOT NULL,
  ROUTE              VARCHAR2(200 CHAR) NOT NULL,
  LIBELLE_ACTEUR     VARCHAR2(150 CHAR) NOT NULL,
  LIBELLE_AUTRES     VARCHAR2(150 CHAR) NOT NULL,
  DESC_NON_FRANCHIE  VARCHAR2(250 CHAR) NOT NULL,
  DESC_SANS_OBJECTIF VARCHAR2(250 CHAR)
);

CREATE TABLE WF_ETAPE_DEP
(
  ID            NUMBER(*, 0) NOT NULL,
  ETAPE_PREC_ID NUMBER(*, 0) NOT NULL,
  ETAPE_SUIV_ID NUMBER(*, 0) NOT NULL,
  ACTIVE        NUMBER(1, 0) DEFAULT 1 NOT NULL,
  OBLIGATOIRE   NUMBER(1, 0) DEFAULT 1 NOT NULL,
  LOCALE        NUMBER(1, 0) DEFAULT 0 NOT NULL,
  INTEGRALE     NUMBER(1, 0) DEFAULT 0 NOT NULL,
  PARTIELLE     NUMBER(1, 0) DEFAULT 0 NOT NULL
);

create sequence WF_ETAPE_id_seq;

create sequence WF_ETAPE_DEP_id_seq;

CREATE UNIQUE INDEX WFE_ORDRE_UN
  ON WF_ETAPE (ORDRE);
CREATE UNIQUE INDEX WFE_CODE_UN
  ON WF_ETAPE (CODE);
CREATE UNIQUE INDEX WFE_PK
  ON WF_ETAPE (ID);

CREATE UNIQUE INDEX WFED_PK
  ON WF_ETAPE_DEP (ETAPE_PREC_ID, ETAPE_SUIV_ID);
CREATE UNIQUE INDEX WFED_UN
  ON WF_ETAPE_DEP (ETAPE_SUIV_ID, ETAPE_PREC_ID);
CREATE INDEX WFED_ETAPE_SUIV_FK
  ON WF_ETAPE_DEP (ETAPE_SUIV_ID);
CREATE INDEX WFED_ETAPE_PREC_FK
  ON WF_ETAPE_DEP (ETAPE_PREC_ID);

ALTER TABLE WF_ETAPE
  ADD CONSTRAINT WF_ETAPE_PK PRIMARY KEY (ID);
ALTER TABLE WF_ETAPE
  ADD CONSTRAINT WF_ETAPE_CODE_UN UNIQUE (CODE);
ALTER TABLE WF_ETAPE
  ADD CONSTRAINT WF_ETAPE_ORDRE_UN UNIQUE (ORDRE);

ALTER TABLE WF_ETAPE_DEP
  ADD CONSTRAINT WE_PREC_WE_FK FOREIGN KEY (ETAPE_PREC_ID) REFERENCES WF_ETAPE (ID) ON DELETE CASCADE ENABLE;
ALTER TABLE WF_ETAPE_DEP
  ADD CONSTRAINT WE_SUIV_WE_FK FOREIGN KEY (ETAPE_SUIV_ID) REFERENCES WF_ETAPE (ID) ON DELETE CASCADE ENABLE;

-- delete from WF_ETAPE; commit;
-- drop sequence WF_ETAPE_id_seq;

insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'DEPOT_VERSION_ORIGINALE',
  10,
  1,
  'these/depot-these',
  'Téléversement de votre thèse',
  'Téléversement de la thèse',
  'Téléversement de la thèse non effectué'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'ATTESTATIONS',
  15,
  1,
  'these/depot-these',
  'Attestations',
  'Attestations',
  'Attestations non renseignées'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'AUTORISATION_DIFFUSION_THESE',
  20,
  1,
  'these/depot-these',
  'Autorisation de diffusion de votre thèse',
  'Autorisation de diffusion de la thèse',
  'Autorisation de diffusion non remplie'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'SIGNALEMENT_THESE',
  30,
  1,
  'these/description',
  'Signalement de votre thèse',
  'Signalement de la thèse',
  'Signalement non renseigné'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'ARCHIVABILITE_VERSION_ORIGINALE',
  40,
  1,
  'these/archivage',
  'Archivabilité de votre thèse',
  'Archivabilité de la thèse',
  'Archivabilité non testée'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'DEPOT_VERSION_ARCHIVAGE',
  50,
  2,
  'these/archivage',
  'Téléversement d''une version retraitée de votre thèse',
  'Téléversement d''une version retraitée de la thèse',
  'Téléversement d''une version retraitée non effectué'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'ARCHIVABILITE_VERSION_ARCHIVAGE',
  60,
  2,
  'these/archivage',
  'Archivabilité de la version retraitée de votre thèse',
  'Archivabilité de la version retraitée de la thèse',
  'Archivabilité de la version retraitée non testée'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'VERIFICATION_VERSION_ARCHIVAGE',
  70,
  2,
  'these/archivage',
  'Vérification de la version retraitée de votre thèse',
  'Vérification de la version retraitée de la thèse',
  'Vérification de la version retraitée non effectuée'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'RDV_BU_SAISIE_DOCTORANT',
  80,
  1,
  'these/rdv-bu',
  'Saisie de vos coordonnées et disponibilités pour votre rendez-vous à la BU',
  'Saisie des coordonnées et disponibilités du doctorant pour le rendez-vous à la BU',
  'Saisie des coordonnées et disponibilités pour le rendez-vous à la BU non effectuée'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'RDV_BU_SAISIE_BU',
  90,
  1,
  'these/rdv-bu',
  'Saisie infos BU',
  'Saisie infos BU',
  'Saisie infos BU non effectuée'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'RDV_BU_VALIDATION_BU',
  100,
  1,
  'these/rdv-bu',
  'Validation de la BU',
  'Validation de la BU',
  'Validation de la BU non effectuée'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'DEPOT_VERSION_ORIGINALE_CORRIGEE',
  11,
  1,
  'these/depot-these',
  'Téléversement de votre thèse',
  'Téléversement de la thèse',
  'Téléversement de la thèse non effectué'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE',
  41,
  1,
  'these/archivage',
  'Archivabilité de la version retraitée de votre thèse corrigée',
  'Archivabilité de la version retraitée de la thèse corrigée',
  'Archivabilité de la version retraitée de la thèse corrigée non testée'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'DEPOT_VERSION_ARCHIVAGE_CORRIGEE',
  51,
  1,
  'these/archivage',
  'Téléversement d''une version retraitée de votre thèse corrigée',
  'Téléversement d''une version retraitée de la thèse corrigée',
  'Téléversement d''une version retraitée de la thèse corrigée non effectué'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE',
  61,
  1,
  'these/archivage',
  'Archivabilité de la version retraitée de votre thèse corrigée',
  'Archivabilité de la version retraitée de la thèse corrigée',
  'Archivabilité de la version retraitée de la thèse corrigée non testée'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE',
  71,
  1,
  'these/archivage',
  'Vérification de la version retraitée de votre thèse corrigée',
  'Vérification de la version retraitée de la thèse corrigée',
  'Vérification de la version retraitée de la thèse corrigée non effectuée'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT',
  110,
  1,
  'these/validation-these-corrigee',
  'Validation automatique du dépôt de votre thèse corrigée',
  'Validation automatique du dépôt de la thèse corrigée',
  'Validation automatique du dépôt de la thèse corrigée non effectuée'
);
insert into WF_ETAPE(ID, CODE, ORDRE, CHEMIN, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE) VALUES (
  WF_ETAPE_id_seq.nextval,
  'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR',
  120,
  1,
  'these/validation-these-corrigee',
  'Validation par les directeurs de thèse de votre thèse corrigée',
  'Validation par les directeurs de thèse de la thèse corrigée',
  'Validation par les directeurs de thèse de la thèse corrigée non effectuée'
);

commit;


-- UPDATE WF_ETAPE
-- SET PARENT_ID = (SELECT id
--                  FROM WF_ETAPE
--                  WHERE code = 'ARCHIVABILITE_VERSION_ORIGINALE')
-- WHERE code IN (
--   'DEPOT_VERSION_ARCHIVAGE',
--   'ARCHIVABILITE_VERSION_ARCHIVAGE',
--   'VERIFICATION_VERSION_ARCHIVAGE'
-- );



/**
 * Dépendances entre étapes : maintenues à jour, mais PAS MIS EN PLACE !
 */

delete from WF_ETAPE_DEP;

INSERT INTO WF_ETAPE_DEP (ID, ETAPE_PREC_ID, ETAPE_SUIV_ID)
  WITH d AS (
    SELECT 'DEPOT_VERSION_ORIGINALE'          prec, 'ATTESTATIONS'                     suiv FROM dual UNION
    SELECT 'ATTESTATIONS'                     prec, 'AUTORISATION_DIFFUSION_THESE'     suiv FROM dual UNION
    SELECT 'AUTORISATION_DIFFUSION_THESE'     prec, 'SIGNALEMENT_THESE'                suiv FROM dual UNION
    SELECT 'SIGNALEMENT_THESE'                prec, 'ARCHIVABILITE_VERSION_ORIGINALE'  suiv FROM dual UNION
    SELECT 'ARCHIVABILITE_VERSION_ORIGINALE'  prec, 'RDV_BU_SAISIE_DOCTORANT'          suiv FROM dual UNION
    SELECT 'RDV_BU_SAISIE_DOCTORANT'          prec, 'RDV_BU_SAISIE_BU'                 suiv FROM dual UNION
    SELECT 'RDV_BU_SAISIE_BU'                 prec, 'RDV_BU_VALIDATION_BU'             suiv FROM dual
  )
  SELECT WF_ETAPE_DEP_id_seq.nextval, ep.id, es.id
  FROM wf_etape ep, wf_etape es, d
  where ep.CODE = d.prec and es.CODE = d.suiv
;
INSERT INTO WF_ETAPE_DEP (ID, ETAPE_PREC_ID, ETAPE_SUIV_ID)
  WITH d AS (
    SELECT 'DEPOT_VERSION_ORIGINALE_CORRIGEE'               prec, 'ATTESTATIONS'                                  suiv FROM dual UNION
    SELECT 'SIGNALEMENT_THESE'                              prec, 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'      suiv FROM dual UNION
    SELECT 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'       prec, 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'   suiv FROM dual
  )
  SELECT WF_ETAPE_DEP_id_seq.nextval, ep.id, es.id
  FROM wf_etape ep, wf_etape es, d
  where ep.CODE = d.prec and es.CODE = d.suiv
;
INSERT INTO WF_ETAPE_DEP (ID, ETAPE_PREC_ID, ETAPE_SUIV_ID)
  WITH d AS (
    SELECT 'ARCHIVABILITE_VERSION_ORIGINALE'    prec, 'DEPOT_VERSION_ARCHIVAGE'           suiv FROM dual UNION
    SELECT 'DEPOT_VERSION_ARCHIVAGE'            prec, 'ARCHIVABILITE_VERSION_ARCHIVAGE'   suiv FROM dual UNION
    SELECT 'ARCHIVABILITE_VERSION_ARCHIVAGE'    prec, 'VERIFICATION_VERSION_ARCHIVAGE'    suiv FROM dual UNION
    SELECT 'VERIFICATION_VERSION_ARCHIVAGE'     prec, 'RDV_BU_SAISIE_DOCTORANT'           suiv FROM dual
  )
  SELECT WF_ETAPE_DEP_id_seq.nextval, ep.id, es.id
  FROM wf_etape ep, wf_etape es, d
  where ep.CODE = d.prec and es.CODE = d.suiv
;
INSERT INTO WF_ETAPE_DEP (ID, ETAPE_PREC_ID, ETAPE_SUIV_ID)
  WITH d AS (
    SELECT 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'       prec, 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'              suiv FROM dual UNION
    SELECT 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'               prec, 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'      suiv FROM dual UNION
    SELECT 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'       prec, 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'       suiv FROM dual UNION
    SELECT 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'        prec, 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'   suiv FROM dual
  )
  SELECT WF_ETAPE_DEP_id_seq.nextval, ep.id, es.id
  FROM wf_etape ep, wf_etape es, d
  where ep.CODE = d.prec and es.CODE = d.suiv
;
INSERT INTO WF_ETAPE_DEP (ID, ETAPE_PREC_ID, ETAPE_SUIV_ID)
  WITH d AS (
      SELECT 'ARCHIVABILITE_VERSION_ARCHIVAGE'    prec, 'RDV_BU_SAISIE_DOCTORANT'           suiv FROM dual
  )
  SELECT WF_ETAPE_DEP_id_seq.nextval, ep.id, es.id
  FROM wf_etape ep, wf_etape es, d
  where ep.CODE = d.prec and es.CODE = d.suiv
;
INSERT INTO WF_ETAPE_DEP (ID, ETAPE_PREC_ID, ETAPE_SUIV_ID)
  WITH d AS (
      SELECT 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'       prec, 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'   suiv FROM dual
  )
  SELECT WF_ETAPE_DEP_id_seq.nextval, ep.id, es.id
  FROM wf_etape ep, wf_etape es, d
  where ep.CODE = d.prec and es.CODE = d.suiv
;
INSERT INTO WF_ETAPE_DEP (ID, ETAPE_PREC_ID, ETAPE_SUIV_ID)
  WITH d AS (
    SELECT 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'    prec, 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'   suiv FROM dual
  )
  SELECT WF_ETAPE_DEP_id_seq.nextval, ep.id, es.id
  FROM wf_etape ep, wf_etape es, d
  where ep.CODE = d.prec and es.CODE = d.suiv
;

--rollback;
commit;


/**
 * Vues de situation
 * -----------------
 * Elles fournissent les données nécessaires au calcul de l'état de chaque étape.
 */

CREATE OR REPLACE VIEW V_SITU_DEPOT_VO AS
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND RETRAITEMENT is null and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER vf ON f.VERSION_FICHIER_ID = vf.ID AND vf.CODE = 'VO';

CREATE OR REPLACE VIEW V_SITU_DEPOT_VOC AS
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND RETRAITEMENT is null and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER vf ON f.VERSION_FICHIER_ID = vf.ID AND vf.CODE = 'VOC';

CREATE OR REPLACE VIEW V_SITU_ATTESTATIONS AS
  SELECT
    t.id AS these_id,
    a.id AS attestation_id
  FROM these t
    JOIN ATTESTATION a ON a.THESE_ID = t.id and a.HISTO_DESTRUCTEUR_ID is null;

CREATE OR REPLACE VIEW V_SITU_AUTORIS_DIFF_THESE AS
  SELECT
    t.id AS these_id,
    d.id AS diffusion_id
  FROM these t
    JOIN DIFFUSION d ON d.THESE_ID = t.id and d.HISTO_DESTRUCTEUR_ID is null;

CREATE OR REPLACE VIEW V_SITU_SIGNALEMENT_THESE AS
  SELECT
    t.id AS these_id,
    d.id AS description_id
  FROM these t
    JOIN METADONNEE_THESE d ON d.THESE_ID = t.id;

CREATE OR REPLACE VIEW V_SITU_ARCHIVAB_VO AS
  SELECT
    t.id AS these_id,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VO'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id;

CREATE OR REPLACE VIEW V_SITU_ARCHIVAB_VOC AS
  SELECT
    t.id AS these_id,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id;

CREATE OR REPLACE VIEW V_SITU_DEPOT_VA AS
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA';

CREATE OR REPLACE VIEW V_SITU_DEPOT_VAC AS
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC';

CREATE OR REPLACE VIEW V_SITU_ARCHIVAB_VA AS
  SELECT
    t.id AS these_id,
    f.RETRAITEMENT,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id;

CREATE OR REPLACE VIEW V_SITU_ARCHIVAB_VAC AS
  SELECT
    t.id AS these_id,
    f.RETRAITEMENT,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id;

CREATE OR REPLACE VIEW V_SITU_VERIF_VA AS
  SELECT
    t.id AS these_id,
    f.EST_CONFORME
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA';

CREATE OR REPLACE VIEW V_SITU_VERIF_VAC AS
  SELECT
    t.id AS these_id,
    f.EST_CONFORME
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC';

CREATE OR REPLACE VIEW V_SITU_RDV_BU_SAISIE_DOCT AS
  SELECT
    t.id AS these_id,
    CASE WHEN r.COORD_DOCTORANT IS NOT NULL AND r.DISPO_DOCTORANT IS NOT NULL
      THEN 1 ELSE 0 END ok
  FROM these t
    JOIN RDV_BU r ON r.THESE_ID = t.id;

CREATE OR REPLACE VIEW V_SITU_RDV_BU_SAISIE_BU AS
  SELECT
    t.id AS these_id,
    CASE WHEN r.VERSION_ARCHIVABLE_FOURNIE = 1 and r.CONVENTION_MEL_SIGNEE = 1 and r.EXEMPL_PAPIER_FOURNI = 1
              and r.PAGE_TITRE_CONFORME = 1 and r.MOTS_CLES_RAMEAU is not null
      THEN 1 ELSE 0 END ok
  FROM these t
    JOIN RDV_BU r ON r.THESE_ID = t.id ;

CREATE OR REPLACE VIEW V_SITU_RDV_BU_VALIDATION_BU AS
  SELECT
    t.id AS these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id and v.HISTO_DESTRUCTEUR_ID is null
    JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'RDV_BU';

CREATE OR REPLACE VIEW V_SITU_DEPOT_VC_VALID_DOCT AS
  SELECT
    t.id AS these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id and v.HISTO_DESTRUCTEUR_ID is null
    JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'DEPOT_THESE_CORRIGEE';

CREATE OR REPLACE VIEW V_SITU_DEPOT_VC_VALID_DIR AS
  WITH validations_attendues AS (
    SELECT a.THESE_ID, a.INDIVIDU_ID, tv.ID as TYPE_VALIDATION_ID
    FROM ACTEUR a
      JOIN USER_ROLE r on a.ROLE_ID = r.ID and r.SOURCE_CODE = 'D' -- directeur de thèse
      JOIN TYPE_VALIDATION tv on tv.code = 'CORRECTION_THESE'
  )
  SELECT
    ROWNUM as id,
    t.id AS these_id,
    va.INDIVIDU_ID,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM validations_attendues va
    JOIN these t on va.THESE_ID = t.id
    LEFT JOIN VALIDATION v ON v.THESE_ID = t.id and
                              v.INDIVIDU_ID = va.INDIVIDU_ID and -- suppose que l'INDIVIDU_ID soit enregistré lors de la validation
                              v.HISTO_DESTRUCTEUR_ID is null and
                              v.TYPE_VALIDATION_ID = va.TYPE_VALIDATION_ID;

CREATE OR REPLACE VIEW V_SITU_DEPOT_PV_SOUT AS
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id and f.HISTO_DESTRUCTION is null and f.HISTO_DESTRUCTION is null
    JOIN NATURE_FICHIER nf on f.NATURE_ID = nf.id and nf.CODE = 'PV_SOUTENANCE';

CREATE OR REPLACE VIEW V_SITU_DEPOT_RAPPORT_SOUT AS
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id and f.HISTO_DESTRUCTION is null and f.HISTO_DESTRUCTION is null
    JOIN NATURE_FICHIER nf on f.NATURE_ID = nf.id and nf.CODE = 'RAPPORT_SOUTENANCE';





SELECT *
FROM V_SITU_DEPOT_VC_VALID_DIR
where THESE_ID = 28395
;


/**
 * Vues de pertinence
 * ------------------
 * Elles fournissent les étapes pertinentes pour chacune des thèses.
 */

CREATE OR REPLACE VIEW V_WF_ETAPE_PERTIN AS
  SELECT
    these_id,
    etape_id,
    code,
    ORDRE,
    ROWNUM id
  FROM (
    --
    -- DEPOT_VERSION_ORIGINALE : étape pertinente ssi correction non autorisée
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE'
    WHERE t.CORREC_AUTORISEE = 0
    UNION ALL
    --
    -- DEPOT_VERSION_ORIGINALE_CORRIGEE : étape pertinente ssi correction autorisée
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'
      WHERE t.CORREC_AUTORISEE = 1
    UNION ALL
    --
    -- SIGNALEMENT_THESE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'SIGNALEMENT_THESE'
    UNION ALL
    --
    -- ATTESTATIONS : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS'
    UNION ALL
    --
    -- AUTORISATION_DIFFUSION_THESE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE'
    UNION ALL
    --
    -- ARCHIVABILITE_VERSION_ORIGINALE : étape pertinente ssi correction non autorisée
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE'
    WHERE t.CORREC_AUTORISEE = 0
    UNION ALL
    --
    -- ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE : étape pertinente ssi correction autorisée
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'
    WHERE t.CORREC_AUTORISEE = 1
    UNION ALL
    --
    -- DEPOT_VERSION_ARCHIVAGE : étape pertinente si version originale non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE'
      JOIN V_SITU_ARCHIVAB_VO situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0
    WHERE t.CORREC_AUTORISEE = 0
    UNION ALL
    --
    -- DEPOT_VERSION_ARCHIVAGE_CORRIGEE : étape pertinente si version originale corrigée non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'
      JOIN V_SITU_ARCHIVAB_VOC situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0
    WHERE t.CORREC_AUTORISEE = 1
    UNION ALL
    --
    -- ARCHIVABILITE_VERSION_ARCHIVAGE : étape pertinente si version originale non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE'
      JOIN V_SITU_ARCHIVAB_VO situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0
    WHERE t.CORREC_AUTORISEE = 0
    UNION ALL
    --
    -- ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE : étape pertinente si version originale corrigée non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'
      JOIN V_SITU_ARCHIVAB_VOC situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0
    WHERE t.CORREC_AUTORISEE = 1
    UNION ALL
    --
    -- VERIFICATION_VERSION_ARCHIVAGE : étape pertinente si version d'archivage archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE'
      JOIN V_SITU_ARCHIVAB_VA situ ON situ.these_id = t.id AND situ.EST_VALIDE = 1
    WHERE t.CORREC_AUTORISEE = 0
    UNION ALL
    --
    -- VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE : étape pertinente si version d'archivage corrigée archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'
      JOIN V_SITU_ARCHIVAB_VAC situ ON situ.these_id = t.id AND situ.EST_VALIDE = 1
    WHERE t.CORREC_AUTORISEE = 1
    UNION ALL
    --
    -- RDV_BU_SAISIE_DOCTORANT : étape pertinente ssi correction non autorisée
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_DOCTORANT'
    WHERE t.CORREC_AUTORISEE = 0
    UNION ALL
    --
    -- RDV_BU_SAISIE_BU : étape pertinente ssi correction non autorisée
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_BU'
    WHERE t.CORREC_AUTORISEE = 0
    UNION ALL
    --
    -- RDV_BU_VALIDATION_BU : étape pertinente ssi correction non autorisée
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_VALIDATION_BU'
    WHERE t.CORREC_AUTORISEE = 0
    UNION ALL
    --
    -- DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT : étape pertinente ssi correction autorisée
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'
    WHERE t.CORREC_AUTORISEE = 1
    UNION ALL
    --
    -- DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR : étape pertinente ssi correction autorisée
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'
    WHERE t.CORREC_AUTORISEE = 1
  );







CREATE OR REPLACE VIEW V_WORKFLOW AS
  SELECT
    ROWNUM id,
    t.*
  FROM (
         --
         -- DEPOT_VERSION_ORIGINALE : franchie si version originale déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE'
           LEFT JOIN V_SITU_DEPOT_VO v ON v.these_id = t.id
         UNION ALL
         --
         -- DEPOT_VERSION_ORIGINALE_CORRIGEE : franchie si version originale corrigée déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'
           LEFT JOIN V_SITU_DEPOT_VOC v ON v.these_id = t.id
         UNION ALL
         --
         -- ATTESTATIONS : franchie si données saisies
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.attestation_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.attestation_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS'
           LEFT JOIN V_SITU_ATTESTATIONS v ON v.these_id = t.id
         UNION ALL
         --
         -- AUTORISATION_DIFFUSION_THESE : franchie si données saisies
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.diffusion_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.diffusion_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE'
           LEFT JOIN V_SITU_AUTORIS_DIFF_THESE v ON v.these_id = t.id
         UNION ALL
         --
         -- SIGNALEMENT_THESE : franchie si données saisies
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.description_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.description_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'SIGNALEMENT_THESE'
           LEFT JOIN V_SITU_SIGNALEMENT_THESE v ON v.these_id = t.id
         UNION ALL
         --
         -- ARCHIVABILITE_VERSION_ORIGINALE : franchie si l'archivabilité de la version originale a été testée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.THESE_ID IS NULL THEN 0 ELSE 1 END franchie,
--            CASE WHEN v.THESE_ID IS NULL THEN
--              0 -- test d'archivabilité inexistant
--            ELSE
--              CASE WHEN v.EST_VALIDE IS NULL THEN
--                1 -- test d'archivabilité existant mais résultat indéterminé (plantage)
--              ELSE
--                CASE WHEN v.EST_VALIDE = 1 THEN
--                  1 -- test d'archivabilité réussi
--                ELSE
--                  0 -- test d'archivabilité échoué
--                END
--              END
--            END franchie,
           CASE WHEN v.EST_VALIDE IS NULL OR v.EST_VALIDE = 0 THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE'
           LEFT JOIN V_SITU_ARCHIVAB_VO v ON v.these_id = t.id
         UNION ALL
         --
         -- ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE : franchie si l'archivabilité de la version originale corrigée a été testée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.THESE_ID IS NULL THEN 0 ELSE 1 END franchie,
           CASE WHEN v.EST_VALIDE IS NULL OR v.EST_VALIDE = 0 THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'
           LEFT JOIN V_SITU_ARCHIVAB_VOC v ON v.these_id = t.id
         UNION ALL
         --
         -- DEPOT_VERSION_ARCHIVAGE : franchie si version d'archivage déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE'
           LEFT JOIN V_SITU_DEPOT_VA v ON v.these_id = t.id
           LEFT JOIN fichier f ON f.id = v.fichier_id
         UNION ALL
         --
         -- DEPOT_VERSION_ARCHIVAGE_CORRIGEE : franchie si version d'archivage corrigée déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'
           LEFT JOIN V_SITU_DEPOT_VAC v ON v.these_id = t.id
           LEFT JOIN fichier f ON f.id = v.fichier_id
         UNION ALL
         --
         -- ARCHIVABILITE_VERSION_ARCHIVAGE : franchie si l'archivabilité de la version d'archivage a été testée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_VALIDE IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.EST_VALIDE IS NULL OR v.EST_VALIDE = 0
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE'
           LEFT JOIN V_SITU_ARCHIVAB_VA v ON v.these_id = t.id
         UNION ALL
         --
         -- ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE : franchie si la version d'archivage corrigée est archivable
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_VALIDE = 1 THEN 1 ELSE 0 END franchie,
           CASE WHEN v.EST_VALIDE = 1 THEN 1 ELSE 0 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'
           LEFT JOIN V_SITU_ARCHIVAB_VAC v ON v.these_id = t.id
         UNION ALL
         --
         -- VERIFICATION_VERSION_ARCHIVAGE : franchie si vérification de la version originale effectuée (peu importe la réponse)
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_CONFORME IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.EST_CONFORME IS NULL OR v.EST_CONFORME = 0
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE'
           LEFT JOIN V_SITU_VERIF_VA v ON v.these_id = t.id
         UNION ALL
         --
         -- VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE : franchie si la version corrigée est certifiée conforme
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_CONFORME = 1 THEN 1 ELSE 0 END franchie,
           CASE WHEN v.EST_CONFORME = 1 THEN 1 ELSE 0 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'
           LEFT JOIN V_SITU_VERIF_VAC v ON v.these_id = t.id
         UNION ALL
         --
         -- RDV_BU_SAISIE_DOCTORANT : franchie si données doctorant saisies
         --
         SELECT
           t.id AS                      these_id,
           e.id AS                      etape_id,
           e.code,
           e.ORDRE,
           coalesce(v.ok, 0)            franchie,
           (CASE WHEN rdv.COORD_DOCTORANT IS NULL
             THEN 0
            ELSE 1 END +
            CASE WHEN rdv.DISPO_DOCTORANT IS NULL
              THEN 0
            ELSE 1 END)                 resultat,
           2                            objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_DOCTORANT'
           LEFT JOIN V_SITU_RDV_BU_SAISIE_DOCT v ON v.these_id = t.id
           LEFT JOIN RDV_BU rdv ON rdv.THESE_ID = t.id
         UNION ALL
--          --
--          -- RDV_BU_SAISIE_BU : franchie si données BU saisies
--          --
--          SELECT
--            t.id AS                                                                          these_id,
--            e.id AS                                                                          etape_id,
--            e.code,
--            e.ORDRE,
--            coalesce(v.ok, 0)                                                                franchie,
--            CASE WHEN rdv.MOTS_CLES_RAMEAU IS NULL THEN 0 ELSE 1 END +
--            coalesce(rdv.VERSION_ARCHIVABLE_FOURNIE, 0) +
--            coalesce(rdv.EXEMPL_PAPIER_FOURNI, 0) +
--            coalesce(rdv.CONVENTION_MEL_SIGNEE, 0)                                           resultat,
--            4                                                                                objectif
--          FROM these t
--            JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_BU'
--            LEFT JOIN V_SITU_RDV_BU_SAISIE_BU v ON v.these_id = t.id
--            LEFT JOIN RDV_BU rdv ON rdv.THESE_ID = t.id
--          UNION ALL
         --
         -- RDV_BU_VALIDATION_BU : franchie si données BU saisies ET une validation BU existe
         --
         SELECT
           t.id AS               these_id,
           e.id AS               etape_id,
           e.code,
           e.ORDRE,
           coalesce(vs.ok, 0) * coalesce(v.valide, 0) franchie,
           coalesce(vs.ok, 0) + coalesce(v.valide, 0) resultat,
           2 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'RDV_BU_VALIDATION_BU'
           LEFT JOIN V_SITU_RDV_BU_SAISIE_BU vs ON vs.these_id = t.id
           LEFT JOIN V_SITU_RDV_BU_VALIDATION_BU v ON v.these_id = t.id
         UNION ALL
         --
         -- DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT : franchie si la validation attendue existe
         --
         SELECT
           t.id AS               these_id,
           e.id AS               etape_id,
           e.code,
           e.ORDRE,
           coalesce(v.valide, 0) franchie,
           coalesce(v.valide, 0) resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'
           LEFT JOIN V_SITU_DEPOT_VC_VALID_DOCT v ON v.these_id = t.id
         UNION ALL
         --
         -- DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR : franchie si toutes les validations attendues existent
         --
         select * from (
           WITH tmp AS (
               SELECT
                 these_id,
                 sum(valide)   AS resultat,
                 count(valide) AS objectif
               FROM V_SITU_DEPOT_VC_VALID_DIR
               GROUP BY these_id
           )
           SELECT
             t.id AS                 these_id,
             e.id AS                 etape_id,
             e.code,
             e.ORDRE,
             coalesce(v.resultat, 0) franchie,
             coalesce(v.resultat, 0) resultat,
             v.objectif
           FROM these t
             JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'
             LEFT JOIN tmp v ON v.these_id = t.id
         )

       ) t
    JOIN V_WF_ETAPE_PERTIN v ON t.these_id = v.these_id AND t.etape_id = v.etape_id
;



;




update these set CORREC_AUTORISEE=1 where id=28395; commit;

select * from v_wf_etape_pertin where these_id in (
  28395
)
order by ordre;


select
  id,
  these_id,
  etape_id,
  code,
  ORDRE,
  franchie,
  resultat,
  objectif,
  SODOCT_WORKFLOW.ATTEIGNABLE(etape_id, these_id) atteignable
from V_WORKFLOW
where these_id = 28395
;





SELECT
  prec.CODE, suiv.CODE
FROM
  WF_ETAPE_DEP d
  join WF_ETAPE prec on d.ETAPE_PREC_ID = prec.id
  join WF_ETAPE suiv on d.ETAPE_SUIV_ID = suiv.id
ORDER BY
  prec.ordre
;


SELECT
  prec.CODE, suiv.CODE
FROM
  WF_ETAPE_DEP d
  join V_WF_ETAPE_PERTIN p on p.etape_id = d.ETAPE_SUIV_ID and these_id = 26614
  join WF_ETAPE prec on d.ETAPE_PREC_ID = prec.id
  join WF_ETAPE suiv on d.ETAPE_SUIV_ID = suiv.id
ORDER BY
  prec.ordre
;


BEGIN
  for r in (
  with pertin as (
      select * from V_WF_ETAPE_PERTIN
      where these_id = 26614
  )
  SELECT
    d.ETAPE_PREC_ID as etape_id, prec.CODE, suiv.CODE
  FROM
    WF_ETAPE_DEP d
    join WF_ETAPE prec on d.ETAPE_PREC_ID = prec.id
    join WF_ETAPE suiv on d.ETAPE_SUIV_ID = suiv.id
    join pertin p1 on p1.etape_id = d.ETAPE_PREC_ID
    join pertin p2 on p2.etape_id = d.ETAPE_SUIV_ID
    join V_WORKFLOW wf on wf.etape_id = d.ETAPE_PREC_ID
  ORDER BY
    prec.ordre
  ) loop
    --DBMS_OUTPUT.PUT_LINE(rpad(row.ordre, 5) || ' ' || row.code || ' : ' || row.franchie);
    if r.franchie = 0 then
      return 0;
    end if;
  end loop;
END;



with tmp as (
    select p.etape_id, wf.franchie
    from V_WF_ETAPE_PERTIN p
      join V_WORKFLOW wf on wf.etape_id = p.etape_id and wf.these_id = p.these_id
    where p.these_id = 26614
)
SELECT
  prec.id as etape_id, prec.CODE, p1.franchie, suiv.CODE
FROM
  WF_ETAPE_DEP d
  join WF_ETAPE prec on d.ETAPE_PREC_ID = prec.id
  join WF_ETAPE suiv on d.ETAPE_SUIV_ID = suiv.id
  join tmp p1 on p1.etape_id = d.ETAPE_PREC_ID
  join tmp p2 on p2.etape_id = d.ETAPE_SUIV_ID
ORDER BY
  prec.ordre
;






SELECT
  prec.id etape_id,
  level || ' ' || lpad(' ', 2 * level, ' ') || prec.CODE lib,
  sys_connect_by_path(prec.CODE, ' > ') path
FROM
  WF_ETAPE_DEP d
  JOIN WF_ETAPE prec ON d.ETAPE_PREC_ID = prec.id
  JOIN WF_ETAPE suiv ON d.ETAPE_SUIV_ID = suiv.id
CONNECT BY
  ETAPE_SUIV_ID = PRIOR ETAPE_PREC_ID
START WITH
  --   suiv.id = (SELECT id FROM WF_ETAPE WHERE ordre = (SELECT max(ordre) FROM WF_ETAPE))
  --   suiv.id = (SELECT id FROM WF_ETAPE WHERE code = 'VERIFICATION_VERSION_ARCHIVAGE')
  suiv.id = (SELECT id FROM WF_ETAPE WHERE code = 'RDV_BU_SAISIE_DOCTORANT')
ORDER SIBLINGS BY
  prec.ordre
;





with p as (
    select etape_id
    from V_WF_ETAPE_PERTIN
    where these_id = 26614
)
SELECT
  level||' '||lpad(' ',2*level,' ') || prec.CODE, sys_connect_by_path(prec.CODE, ' > ')
FROM
  WF_ETAPE_DEP d
  join WF_ETAPE prec on d.ETAPE_PREC_ID = prec.id
  join WF_ETAPE suiv on d.ETAPE_SUIV_ID = suiv.id
  join p pp on pp.etape_id = d.ETAPE_PREC_ID
  join p ps on ps.etape_id = d.ETAPE_SUIV_ID
CONNECT BY
  PRIOR ETAPE_SUIV_ID = ETAPE_PREC_ID
start with prec.id = (select id from WF_ETAPE where ordre = (select min(ordre) from WF_ETAPE))
ORDER SIBLINGS BY
  suiv.ordre
;




select * from RDV_BU where THESE_ID=26924;