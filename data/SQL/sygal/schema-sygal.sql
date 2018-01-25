--
-- Base de données SYGAL.
--
-- Schéma.
--

create or replace function GEN_SOURCE_CODE(code_etablissement VARCHAR2, string_id VARCHAR2) return VARCHAR2
IS
BEGIN
  return code_etablissement || '_' || string_id;
END;
/


---------------------- SOURCE ---------------------

--drop table TMP_SOURCE;
create table TMP_SOURCE
(
  insert_date date default sysdate,

  ID VARCHAR2(64),

  ETABLISSEMENT_ID VARCHAR2(64 char) not null,

  CODE VARCHAR2(64 char) not null,
  LIBELLE VARCHAR2(128 char) not null,
  IMPORTABLE NUMBER(1) not null
);

create table SOURCE
(
  ID NUMBER not null constraint SOURCE_PK primary key,
  CODE VARCHAR2(64 char) not null constraint SOURCE_CODE_UN unique,
  LIBELLE VARCHAR2(128 char) not null,
  IMPORTABLE NUMBER(1) not null
);

comment on table SOURCE is 'Sources de données, importables ou non, ex: Apogée, Physalis.';


---------------------- ETABLISSEMENT ---------------------

CREATE TABLE ETABLISSEMENT
(
  ID NUMBER constraint ETAB_PK primary key,
  CODE VARCHAR2(32 char) not null constraint ETAB_CODE_UN unique,
  LIBELLE VARCHAR2(128) NOT NULL constraint ETAB_LIBELLE_UN unique
);


--------------------------- API_LOG -----------------------

create table API_LOG
(
  ID              NUMBER CONSTRAINT API_LOG_PK PRIMARY KEY,
  req_uri         VARCHAR2(2000) NOT NULL,
  req_start_date  DATE           NOT NULL,
  req_end_date    DATE,
  req_status      VARCHAR2(32),
  req_response    CLOB
);

comment on table API_LOG is 'Logs des appels aux API des établissements.';

create sequence API_LOG_ID_SEQ;


--------------------------- INDIVIDU -----------------------

--drop table TMP_INDIVIDU;
create table TMP_INDIVIDU
(
  insert_date date default sysdate,

  ID VARCHAR2(64),

  SOURCE_ID VARCHAR2(64 char) not null,
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,

  TYPE varchar2(32), -- 'acteur' ou 'doctorant'
  civ varchar2(5),
  lib_nom_usu_ind VARCHAR2(60 CHAR)    NOT NULL,
  lib_nom_pat_ind VARCHAR2(60 CHAR)    NOT NULL,
  lib_pr1_ind VARCHAR2(60 CHAR)    NOT NULL,
  lib_pr2_ind VARCHAR2(60 CHAR),
  lib_pr3_ind VARCHAR2(60 CHAR),
  email VARCHAR2(255 CHAR),
  dat_nai_per DATE,
  lib_nat VARCHAR2(128 CHAR)
);

CREATE OR REPLACE VIEW SRC_INDIVIDU AS
  SELECT
    NULL                                     AS id,
    GEN_SOURCE_CODE(e.CODE, to_char(tmp.id)) AS SOURCE_CODE,
    src.id                                   AS SOURCE_ID,
    TYPE,
    civ                                      AS CIVILITE,
    lib_nom_usu_ind                          AS NOM_USUEL,
    lib_nom_pat_ind                          AS NOM_PATRONYMIQUE,
    lib_pr1_ind                              AS PRENOM1,
    lib_pr2_ind                              AS PRENOM2,
    lib_pr3_ind                              AS PRENOM3,
    EMAIL,
    dat_nai_per                              AS DATE_NAISSANCE,
    lib_nat                                  AS NATIONALITE
  FROM TMP_INDIVIDU tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = GEN_SOURCE_CODE(e.CODE, tmp.SOURCE_ID);

create table INDIVIDU
(
  ID NUMBER constraint INDIVIDU_PK primary key,

  TYPE VARCHAR2(32), -- 'acteur' ou 'doctorant'

  CIVILITE         VARCHAR2(5 CHAR)     NOT NULL,
  NOM_USUEL        VARCHAR2(60 CHAR)    NOT NULL,
  NOM_PATRONYMIQUE VARCHAR2(60 CHAR)    NOT NULL,
  PRENOM1          VARCHAR2(60 CHAR)    NOT NULL,
  PRENOM2          VARCHAR2(60 CHAR),
  PRENOM3          VARCHAR2(60 CHAR),
  EMAIL            VARCHAR2(255 CHAR),
  DATE_NAISSANCE   DATE                 NOT NULL,
  NATIONALITE      VARCHAR2(128 CHAR),

  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null constraint INDIVIDU_SOURCE_FK references SOURCE on delete cascade,

  HISTO_CREATEUR_ID NUMBER not null constraint INDIVIDU_HC_FK references UTILISATEUR on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint INDIVIDU_HM_FK references UTILISATEUR on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER constraint INDIVIDU_HD_FK references UTILISATEUR on delete cascade,
  HISTO_DESTRUCTION DATE
);

comment on table INDIVIDU is 'Individus: doctorants, acteurs, etc.';

create unique index INDIVIDU_SOURCE_CODE_UNIQ on INDIVIDU (SOURCE_CODE);

create index INDIVIDU_SRC_ID_INDEX on INDIVIDU (SOURCE_ID);
create index INDIVIDU_HCFK_IDX on INDIVIDU (HISTO_CREATEUR_ID);
create index INDIVIDU_HMFK_IDX on INDIVIDU (HISTO_MODIFICATEUR_ID);
create index INDIVIDU_HDFK_IDX on INDIVIDU (HISTO_DESTRUCTEUR_ID);

create sequence INDIVIDU_ID_SEQ;

--------------------------- DOCTORANT -----------------------

--drop table TMP_DOCTORANT;
create table TMP_DOCTORANT
(
  insert_date date default sysdate,

  ID VARCHAR2(64),

  SOURCE_ID VARCHAR2(64 char) not null,
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  INDIVIDU_ID VARCHAR2(64 char) not null
);

CREATE OR REPLACE VIEW SRC_DOCTORANT AS
  SELECT
    NULL                                     AS id,
    GEN_SOURCE_CODE(e.CODE, to_char(tmp.id)) AS SOURCE_CODE,
    src.id                                   AS source_id,
    i.id                                     AS individu_id,
    e.id                                     AS etablissement_id
  FROM TMP_DOCTORANT tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = GEN_SOURCE_CODE(e.CODE, tmp.SOURCE_ID)
    JOIN INDIVIDU i ON i.SOURCE_CODE = GEN_SOURCE_CODE(e.CODE, tmp.INDIVIDU_ID);

--DROP TABLE DOCTORANT cascade constraints;
create table DOCTORANT
(
  ID NUMBER constraint DOCTORANT_PK primary key,

  ETABLISSEMENT_ID NUMBER constraint DOCTORANT_ETAB_FK references ETABLISSEMENT on delete cascade,
  INDIVIDU_ID NUMBER constraint DOCTORANT_INDIV_FK references INDIVIDU on delete cascade,

  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null constraint DOCTORANT_SOURCE_FK references SOURCE on delete cascade,

  HISTO_CREATEUR_ID NUMBER not null constraint DOCTORANT_HC_FK references UTILISATEUR on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint DOCTORANT_HM_FK references UTILISATEUR on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER constraint DOCTORANT_HD_FK references UTILISATEUR on delete cascade,
  HISTO_DESTRUCTION DATE
);

comment on table DOCTORANT is 'Doctorant par établissement.';

create unique index DOCTORANT_SOURCE_CODE_UNIQ on DOCTORANT (SOURCE_CODE);

create index DOCTORANT_ETABLISSEMENT_IDX on DOCTORANT (ETABLISSEMENT_ID);
create index DOCTORANT_INDIVIDU_IDX on DOCTORANT (INDIVIDU_ID);
create index DOCTORANT_SRC_ID_INDEX on DOCTORANT (SOURCE_ID);
create index DOCTORANT_HCFK_IDX on DOCTORANT (HISTO_CREATEUR_ID);
create index DOCTORANT_HMFK_IDX on DOCTORANT (HISTO_MODIFICATEUR_ID);
create index DOCTORANT_HDFK_IDX on DOCTORANT (HISTO_DESTRUCTEUR_ID);


--------------------------- THESE -----------------------

--drop table TMP_THESE;
create table TMP_THESE
(
  insert_date date default sysdate,

  ID VARCHAR2(64),

  SOURCE_ID VARCHAR2(64 char) not null,
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  DOCTORANT_ID VARCHAR2(64 char) not null,
  ECOLE_DOCT_ID VARCHAR2(64 char),
  UNITE_RECH_ID VARCHAR2(64 char),

  correction_possible VARCHAR2(30 char),
  dat_aut_sou_ths DATE,
  dat_fin_cfd_ths DATE,
  dat_deb_ths DATE,
  dat_prev_sou DATE,
  dat_sou_ths DATE,
  eta_ths VARCHAR2(20 char),
  lib_int1_dis VARCHAR2(200 char),
  lib_etab_cotut VARCHAR2(60 char),
  lib_pays_cotut VARCHAR2(40 char),
  cod_neg_tre VARCHAR2(1 char),
  tem_sou_aut_ths VARCHAR2(1 char),
  tem_avenant_cotut VARCHAR2(1 char),
  lib_ths VARCHAR2(2048 char)
);

CREATE OR REPLACE VIEW SRC_THESE AS
  SELECT
    NULL                                     AS id,
    GEN_SOURCE_CODE(e.CODE, to_char(tmp.id)) AS SOURCE_CODE,
    src.ID                                   AS source_id,
    e.id                                     AS etablissement_id,
    d.id                                     AS doctorant_id,
    NULL                                     AS ecole_doct_id,
    NULL                                     AS unite_rech_id,
    tmp.lib_ths                              AS titre,
    tmp.eta_ths                              AS etat_these,
    to_number(tmp.cod_neg_tre)               AS resultat,
    tmp.lib_int1_dis                         AS lib_disc,
    tmp.dat_deb_ths                          AS date_prem_insc,
    tmp.dat_prev_sou                         AS date_prev_soutenance,
    tmp.dat_sou_ths                          AS date_soutenance,
    tmp.dat_fin_cfd_ths                      AS date_fin_confid,
    tmp.lib_etab_cotut                       AS lib_etab_cotut,
    tmp.lib_pays_cotut                       AS lib_pays_cotut,
    tmp.correction_possible                  AS CORREC_AUTORISEE,
    tem_sou_aut_ths                          AS soutenance_autoris,
    dat_aut_sou_ths                          AS date_autoris_soutenance,
    tem_avenant_cotut                        AS tem_avenant_cotut
  FROM TMP_THESE tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = GEN_SOURCE_CODE(e.CODE, tmp.SOURCE_ID)
    JOIN DOCTORANT d ON d.SOURCE_CODE = GEN_SOURCE_CODE(e.CODE, tmp.DOCTORANT_ID);

--DROP TABLE THESE cascade constraints;
create table THESE
(
  ID NUMBER not null constraint THESE_PK primary key,

  ETABLISSEMENT_ID NUMBER constraint THESE_ETAB_FK references ETABLISSEMENT on delete cascade,
  DOCTORANT_ID NUMBER constraint THESE_DOCTORANT_FK references DOCTORANT on delete cascade,
  ECOLE_DOCT_ID NUMBER,-- constraint THESE_ED_FK references ECOLE_DOCT,
  UNITE_RECH_ID NUMBER,-- constraint THESE_UR_FK references UNITE_RECH,

  BESOIN_EXPURGE NUMBER(1) default 0 not null,
  COD_UNIT_RECH VARCHAR2(50 char),
  CORREC_AUTORISEE VARCHAR2(30 char) default NULL,
  DATE_AUTORIS_SOUTENANCE DATE,
  DATE_FIN_CONFID DATE,
  DATE_PREM_INSC DATE,
  DATE_PREV_SOUTENANCE DATE,
  DATE_SOUTENANCE DATE,
  ETAT_THESE VARCHAR2(20 char),
  LIB_DISC VARCHAR2(200 char),
  LIB_ETAB_COTUT VARCHAR2(60 char),
  LIB_PAYS_COTUT VARCHAR2(40 char),
  LIB_UNIT_RECH VARCHAR2(200 char),
  RESULTAT NUMBER(1),
  SOUTENANCE_AUTORIS VARCHAR2(1 char),
  TEM_AVENANT_COTUT VARCHAR2(1 char),
  TITRE VARCHAR2(2048 char),

  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null constraint THESE_SOURCE_FK references SOURCE on delete cascade,

  HISTO_CREATEUR_ID NUMBER not null constraint THESE_HC_FK references UTILISATEUR on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint THESE_HM_FK references UTILISATEUR on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER constraint THESE_HD_FK references UTILISATEUR on delete cascade,
  HISTO_DESTRUCTION DATE
);

comment on table THESE is 'Thèses par établissement.';

-- create index THESE_ED_ID_INDEX on THESE (ECOLE_DOCT_ID);
-- create index THESE_UR_ID_INDEX on THESE (UNITE_RECH_ID);

create unique index THESE_SOURCE_CODE_UNIQ on THESE (SOURCE_CODE);

create index THESE_TITRE_INDEX on THESE (TITRE);
create index THESE_ETAT_INDEX on THESE (ETAT_THESE);

create index THESE_SRC_ID_INDEX on THESE (SOURCE_ID);

create index THESE_HCFK_IDX on THESE (HISTO_CREATEUR_ID);
create index THESE_HMFK_IDX on THESE (HISTO_MODIFICATEUR_ID);
create index THESE_HDFK_IDX on THESE (HISTO_DESTRUCTEUR_ID);


--------------------------- ROLE -----------------------

--drop table TMP_ROLE;
create table TMP_ROLE
(
  insert_date date default sysdate,

  ID VARCHAR2(64),

  SOURCE_ID VARCHAR2(64 char) not null,
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,

  LIB_ROJ VARCHAR2(200 char),
  LIC_ROJ VARCHAR2(50 char)
);

CREATE OR REPLACE VIEW SRC_ROLE AS
  SELECT
    NULL                                     AS id,
    GEN_SOURCE_CODE(e.CODE, to_char(tmp.id)) AS SOURCE_CODE,
    src.ID                                   AS source_id,
    e.id                                     AS etablissement_id,
    tmp.LIB_ROJ                              AS libelle,
    to_char(tmp.id)                          AS code
  FROM TMP_ROLE tmp
    JOIN ETABLISSEMENT e ON e.code = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = GEN_SOURCE_CODE(e.CODE, tmp.SOURCE_ID);

--DROP TABLE ROLE cascade constraints;
create table ROLE
(
  ID NUMBER NOT NULL CONSTRAINT ROLE_PK PRIMARY KEY,

  ETABLISSEMENT_ID NUMBER CONSTRAINT ROLE_ETAB_FK REFERENCES ETABLISSEMENT ON DELETE CASCADE,

  CODE VARCHAR2(50 char) not null,
  LIBELLE VARCHAR2(200 char) not null,

  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null constraint ROLE_SOURCE_FK references SOURCE on delete cascade,

  HISTO_CREATEUR_ID NUMBER not null constraint ROLE_HC_FK references UTILISATEUR on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint ROLE_HM_FK references UTILISATEUR on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER constraint ROLE_HD_FK references UTILISATEUR on delete cascade,
  HISTO_DESTRUCTION DATE
);

comment on table ROLE is 'Rôles au titre d''un établissement, ex: directeur de thèse (université de rouen).';

create unique index ROLE_SOURCE_CODE_UNIQ on ROLE (SOURCE_CODE);

create index ROLE_ETABLISSEMENT_IDX on ROLE (ETABLISSEMENT_ID);
create index ROLE_SOURCE_IDX on ROLE (SOURCE_ID);
create index ROLE_HC_IDX on ROLE (HISTO_CREATEUR_ID);
create index ROLE_HM_IDX on ROLE (HISTO_MODIFICATEUR_ID);
create index ROLE_HD_IDX on ROLE (HISTO_DESTRUCTEUR_ID);

--drop sequence ROLE_ID_SEQ;
create sequence ROLE_ID_SEQ;


---------------------- UTILISATEUR ---------------------

create table UTILISATEUR
(
  ID NUMBER not null constraint UTILISATEUR_PK primary key,
  USERNAME VARCHAR2(255 char) constraint UTILISATEUR_USERNAME_UN unique,
  EMAIL VARCHAR2(255 char),
  DISPLAY_NAME VARCHAR2(100 char),
  PASSWORD VARCHAR2(128 char) not null,
  STATE NUMBER default 1 not null,
  LAST_ROLE_ID NUMBER constraint UTILISATEUR_LAST_ROLE_FK references ROLE on delete set null
);

comment on table UTILISATEUR is 'Comptes utilisateurs s''étant déjà connecté à l''application + comptes avec mot de passe local.';

--drop sequence UTILISATEUR_ID_SEQ;
create sequence UTILISATEUR_ID_SEQ;


--------------------------- ACTEUR -----------------------

--drop table TMP_ACTEUR;
create table TMP_ACTEUR
(
  insert_date date default sysdate,

  ID VARCHAR2(64),

  SOURCE_ID VARCHAR2(64 char) not null,
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  INDIVIDU_ID VARCHAR2(64 char) not null,
  THESE_ID VARCHAR2(64 char) not null,
  ROLE_ID VARCHAR2(64 char) not null,

  LIB_CPS VARCHAR2(200 char),
  LIB_ETB VARCHAR2(200 char),
  COD_CPS VARCHAR2(50 char),
  COD_ETB VARCHAR2(50 char),
  COD_ROJ_COMPL VARCHAR2(50 char),
  LIB_ROJ_COMPL VARCHAR2(200 char),
  TEM_HAB_RCH_PER VARCHAR2(1 char),
  TEM_RAP_RECU VARCHAR2(1 char)
);

CREATE OR REPLACE VIEW SRC_ACTEUR AS
  SELECT
    NULL                                     AS id,
    GEN_SOURCE_CODE(e.CODE, to_char(tmp.id)) AS SOURCE_CODE,
    src.ID                                   AS SOURCE_ID,
    i.id                                     AS INDIVIDU_ID,
    t.id                                     AS THESE_ID,
    r.id                                     AS ROLE_ID,
    tmp.LIB_CPS                              AS QUALITE,
    tmp.LIB_ETB                              AS ETABLISSEMENT,
    tmp.LIB_ROJ_COMPL                        AS LIB_ROLE_COMPL
  FROM TMP_ACTEUR tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = GEN_SOURCE_CODE(e.CODE, tmp.SOURCE_ID)
    JOIN INDIVIDU i ON i.SOURCE_CODE = GEN_SOURCE_CODE(e.CODE, tmp.INDIVIDU_ID)
    JOIN THESE t ON t.SOURCE_CODE = GEN_SOURCE_CODE(e.CODE, tmp.THESE_ID)
    JOIN ROLE r ON r.SOURCE_CODE = GEN_SOURCE_CODE(e.CODE, tmp.ROLE_ID);

--DROP TABLE ACTEUR cascade constraints;
create table ACTEUR
(
  ID NUMBER NOT NULL CONSTRAINT ACTEUR_PK PRIMARY KEY,

  ETABLISSEMENT_ID NUMBER CONSTRAINT ACTEUR_ETAB_FK REFERENCES ETABLISSEMENT ON DELETE CASCADE,
  INDIVIDU_ID NUMBER constraint ACTEUR_INDIV_FK references INDIVIDU on delete cascade,
  THESE_ID NUMBER constraint ACTEUR_THESE_FK references THESE on delete cascade,
  ROLE_ID NUMBER constraint ACTEUR_ROLE_FK references ROLE on delete cascade,

  QUALITE VARCHAR2(200 char) not null,
  ETABLISSEMENT VARCHAR2(200 char) not null,
  LIB_ROLE_COMPL VARCHAR2(200 char) not null,

  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null constraint ACTEUR_SOURCE_FK references SOURCE on delete cascade,

  HISTO_CREATEUR_ID NUMBER not null constraint ACTEUR_HC_FK references UTILISATEUR on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint ACTEUR_HM_FK references UTILISATEUR on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER constraint ACTEUR_HD_FK references UTILISATEUR on delete cascade,
  HISTO_DESTRUCTION DATE
);

comment on table ACTEUR is 'Individus jouant un rôle sur une thèse, ex: directeurs de thèse.';

create unique index ACTEUR_SOURCE_CODE_UNIQ on ACTEUR (SOURCE_CODE);

create index ACTEUR_ETABLISSEMENT_IDX on ACTEUR (ETABLISSEMENT_ID);
create index ACTEUR_INDIVIDU_IDX on ACTEUR (INDIVIDU_ID);
create index ACTEUR_THESE_IDX on ACTEUR (THESE_ID);
create index ACTEUR_ROLE_IDX on ACTEUR (ROLE_ID);
create index ACTEUR_SOURCE_IDX on ACTEUR (SOURCE_ID);
create index ACTEUR_HC_IDX on ACTEUR (HISTO_CREATEUR_ID);
create index ACTEUR_HM_IDX on ACTEUR (HISTO_MODIFICATEUR_ID);
create index ACTEUR_HD_IDX on ACTEUR (HISTO_DESTRUCTEUR_ID);


--------------------------- VARIABLE -----------------------

--drop table TMP_VARIABLE;
create table TMP_VARIABLE
(
  insert_date date default sysdate,

  ID VARCHAR2(64),

  SOURCE_ID VARCHAR2(64 char) not null,
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,

  cod_vap VARCHAR2(50 char),
  lib_vap VARCHAR2(300 char),
  par_vap VARCHAR2(200 char)
);

CREATE OR REPLACE VIEW SRC_VARIABLE AS
  SELECT
    NULL                                     AS id,
    GEN_SOURCE_CODE(e.CODE, to_char(tmp.id)) AS SOURCE_CODE,
    src.ID                                   AS SOURCE_ID,
    e.id                                     AS ETABLISSEMENT_ID,
    tmp.cod_vap,
    tmp.lib_vap,
    tmp.par_vap
  FROM TMP_VARIABLE tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = GEN_SOURCE_CODE(e.CODE, tmp.SOURCE_ID);

--DROP TABLE VARIABLE cascade constraints;
create table VARIABLE
(
  ID NUMBER NOT NULL CONSTRAINT VARIABLE_PK PRIMARY KEY,

  ETABLISSEMENT_ID NUMBER CONSTRAINT VARIABLE_ETAB_FK REFERENCES ETABLISSEMENT ON DELETE CASCADE,

  DESCRIPTION VARCHAR2(300 char) not null,
  VALEUR VARCHAR2(200 char) not null,

  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null constraint VARIABLE_SOURCE_FK references SOURCE on delete cascade,

  HISTO_CREATEUR_ID NUMBER not null constraint VARIABLE_HC_FK references UTILISATEUR on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER constraint VARIABLE_HM_FK references UTILISATEUR on delete cascade,
  HISTO_DESTRUCTION DATE,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint VARIABLE_HD_FK references UTILISATEUR on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null
);

comment on table VARIABLE is 'Variables d''environnement concernant un établissement, ex: nom de l''établissement, nom du président, etc.';

create unique index VARIABLE_SOURCE_CODE_UNIQ on VARIABLE (SOURCE_CODE);

create index VARIABLE_ETABLISSEMENT_IDX on VARIABLE (ETABLISSEMENT_ID);
create index VARIABLE_SOURCE_IDX on VARIABLE (SOURCE_ID);
create index VARIABLE_HC_IDX on VARIABLE (HISTO_CREATEUR_ID);
create index VARIABLE_HM_IDX on VARIABLE (HISTO_MODIFICATEUR_ID);
create index VARIABLE_HD_IDX on VARIABLE (HISTO_DESTRUCTEUR_ID);





--------------------------- INDIVIDU_ROLE -----------------------

create table INDIVIDU_ROLE
(
  ID NUMBER NOT NULL CONSTRAINT INDIVIDU_ROLE_PK PRIMARY KEY,

  INDIVIDU_ID NUMBER constraint INDIVIDU_ROLE_INDIV_FK references INDIVIDU on delete cascade,
  ROLE_ID NUMBER constraint INDIVIDU_ROLE_ROLE_FK references ROLE on delete cascade
);

comment on table INDIVIDU_ROLE is 'Attributions à des individus de rôles sans lien avec une thèse en particulier, ex: bureau des doctorats.';

create index INDIVIDU_ROLE_INDIVIDU_IDX on INDIVIDU_ROLE (INDIVIDU_ID);
create index INDIVIDU_ROLE_ROLE_IDX on INDIVIDU_ROLE (ROLE_ID);

create sequence INDIVIDU_ROLE_ID_SQ;


--------------------------- Autres -----------------------

--DROP TABLE ATTESTATION cascade constraints;
create table ATTESTATION
(
  ID NUMBER not null constraint ATTESTATION_PK primary key,
  THESE_ID NUMBER not null constraint ATTESTATION_THESE_FK references THESE on delete cascade,
  VER_DEPO_EST_VER_REF NUMBER(1) default 0 not null,
  EX_IMPR_CONFORM_VER_DEPO NUMBER(1) default 0 not null,

  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null constraint ATTESTATION_HC_FK references UTILISATEUR on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint ATTESTATION_HM_FK references UTILISATEUR on delete cascade,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER constraint ATTESTATION_HD_FK references UTILISATEUR on delete cascade
);

create index ATTESTATION_THESE_IDX on ATTESTATION (THESE_ID);
create index ATTESTATION_HC_IDX on ATTESTATION (HISTO_CREATEUR_ID);
create index ATTESTATION_HM_IDX on ATTESTATION (HISTO_MODIFICATEUR_ID);
create index ATTESTATION_HD_IDX on ATTESTATION (HISTO_DESTRUCTEUR_ID);

create sequence ATTESTATION_ID_SEQ;

------

create table CATEGORIE_PRIVILEGE
(
  ID NUMBER not null primary key,
  CODE VARCHAR2(150 char) not null,
  LIBELLE VARCHAR2(200 char) not null,
  ORDRE NUMBER
);

create unique index CATEGORIE_PRIVILEGE_UNIQUE on CATEGORIE_PRIVILEGE (CODE);

------

create table NATURE_FICHIER
(
  ID NUMBER not null primary key,
  CODE VARCHAR2(50 char) default NULL not null,
  LIBELLE VARCHAR2(100 char) default NULL
);

create unique index NATURE_FICHIER_UNIQ_CODE on NATURE_FICHIER (CODE);

------

create table VERSION_FICHIER
(
  ID NUMBER not null
    constraint VERSION_FICHIER_PK
    primary key,
  CODE VARCHAR2(16 char) not null,
  LIBELLE VARCHAR2(128 char) not null
);

create unique index VERSION_FICHIER_UNIQ_CODE on VERSION_FICHIER (CODE);

-------

create table FAQ
(
  ID NUMBER not null
    constraint FAQ_PK
    primary key,
  QUESTION VARCHAR2(2000 char) not null,
  REPONSE VARCHAR2(2000 char) not null,
  ORDRE NUMBER
);

create sequence FAQ_ID_SEQ;

-----

create table PRIVILEGE
(
  ID NUMBER not null primary key,
  CATEGORIE_ID NUMBER not null references CATEGORIE_PRIVILEGE on delete cascade,
  CODE VARCHAR2(150 char) not null,
  LIBELLE VARCHAR2(200 char) not null,
  ORDRE NUMBER
);

create index PRIVILEGE_CATEG_IDX on PRIVILEGE (CATEGORIE_ID);
create unique index PRIVILEGE_UNIQUE on PRIVILEGE (CATEGORIE_ID, CODE);

-------

create table TYPE_VALIDATION
(
  ID NUMBER not null  constraint TYPE_VALIDATION_PK primary key,
  CODE VARCHAR2(50 char) not null,
  LIBELLE VARCHAR2(100 char)
);

create unique index TYPE_VALIDATION_UN on TYPE_VALIDATION (CODE);

------

create table WF_ETAPE
(
  ID NUMBER not null constraint WF_ETAPE_PK primary key,
  CODE VARCHAR2(128 char) not null constraint WF_ETAPE_CODE_UN unique,
  ORDRE NUMBER default 1 not null constraint WF_ETAPE_ORDRE_UN unique,
  CHEMIN NUMBER default 1 not null,
  OBLIGATOIRE NUMBER(1) default 1 not null,
  ROUTE VARCHAR2(200 char) not null,
  LIBELLE_ACTEUR VARCHAR2(150 char) not null,
  LIBELLE_AUTRES VARCHAR2(150 char) not null,
  DESC_NON_FRANCHIE VARCHAR2(250 char) not null,
  DESC_SANS_OBJECTIF VARCHAR2(250 char)
);



-----




--DROP TABLE FICHIER cascade constraints;
create table FICHIER
(
  ID VARCHAR2(40 char) not null constraint FICHIER_PK primary key,
  NOM VARCHAR2(255 char) not null,
  TYPE_MIME VARCHAR2(128 char) not null,
  TAILLE NUMBER not null,
  DESCRIPTION VARCHAR2(256 char),
  THESE_ID NUMBER not null constraint FICHIER_THESE_FK references THESE on delete cascade,
  VERSION_FICHIER_ID NUMBER not null constraint FICHIER_VERSION_FK references VERSION_FICHIER on delete cascade,
  HISTO_CREATION DATE default SYSDATE  not null,
  HISTO_CREATEUR_ID NUMBER not null constraint FICHIER_HCFK references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE  not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint FICHIER_HMFK references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER constraint FICHIER_HDFK references UTILISATEUR,
  EST_ANNEXE NUMBER(1) default 0  not null,
  NOM_ORIGINAL VARCHAR2(255 char) default NULL  not null,
  EST_CONVERTI NUMBER(1) default 0  not null,
  EST_EXPURGE NUMBER(1) default 0  not null,
  EST_CONFORME NUMBER(1),
  RETRAITEMENT VARCHAR2(50 char),
  NATURE_ID NUMBER default 1  not null constraint FICHIER_NATURE_FICHIER_ID_FK references NATURE_FICHIER
);

create index FICHIER_THESE_FK_IDX on FICHIER (THESE_ID);
create index FICHIER_VERSION_FK_IDX on FICHIER (VERSION_FICHIER_ID);
create index FICHIER_HCFK_IDX on FICHIER (HISTO_CREATEUR_ID);
create index FICHIER_HMFK_IDX on FICHIER (HISTO_MODIFICATEUR_ID);
create index FICHIER_HDFK_IDX on FICHIER (HISTO_DESTRUCTEUR_ID);

create sequence FICHIER_ID_SEQ;

------

create table CONTENU_FICHIER
(
  ID NUMBER not null constraint CONTENU_FICHIER_PK primary key,
  FICHIER_ID VARCHAR2(38 char) not null constraint CONTENU_FICHIER_FICHIER_FK references FICHIER on delete cascade,
  DATA BLOB not null
);

create index CONTENU_FICHIER_FIDX on CONTENU_FICHIER (FICHIER_ID);

create sequence CONTENU_FICHIER_ID_SEQ;

------

--DROP TABLE DIFFUSION cascade constraints;
create table DIFFUSION
(
  ID NUMBER not null constraint MISE_EN_LIGNE_PK primary key,
  THESE_ID NUMBER not null constraint MISE_EN_LIGNE_THESE_FK references THESE on delete cascade,
  DROIT_AUTEUR_OK NUMBER(1) default 0 not null,
  AUTORIS_MEL NUMBER(1) default 0 not null,
  AUTORIS_EMBARGO_DUREE VARCHAR2(20 char),
  AUTORIS_MOTIF VARCHAR2(2000 char),
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null constraint DIFFUSION_HC_FK references UTILISATEUR on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint DIFFUSION_HM_FK references UTILISATEUR on delete cascade,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER constraint DIFFUSION_HD_FK references UTILISATEUR on delete cascade,
  CERTIF_CHARTE_DIFF NUMBER(1) default 0 not null,
  CONFIDENT NUMBER(1) default 0 not null,
  CONFIDENT_DATE_FIN DATE,
  ID_HAL VARCHAR2(200 char)
);

comment on column DIFFUSION.DROIT_AUTEUR_OK is 'Je garantis que tous les documents de la version mise en ligne sont libres de droits ou que j''ai acquis les droits afférents pour la reproduction et la représentation sur tous supports';
comment on column DIFFUSION.AUTORIS_MEL is 'J''autorise la mise en ligne de la version de diffusion de la thèse sur Internet';
comment on column DIFFUSION.AUTORIS_EMBARGO_DUREE is 'Durée de l''embargo éventuel';
comment on column DIFFUSION.CERTIF_CHARTE_DIFF is 'En cochant cette case, je certifie avoir pris connaissance de la charte de diffusion des thèses en vigueur à la date de signature de la convention de mise en ligne';
comment on column DIFFUSION.CONFIDENT is 'La thèse est-elle confidentielle ?';

create index DIFFUSION_THESE_IDX on DIFFUSION (THESE_ID);
create index DIFFUSION_HC_IDX on DIFFUSION (HISTO_CREATEUR_ID);
create index DIFFUSION_HM_IDX on DIFFUSION (HISTO_MODIFICATEUR_ID);
create index DIFFUSION_HD_IDX on DIFFUSION (HISTO_DESTRUCTEUR_ID);

create sequence DIFFUSION_ID_SEQ;

-----

create table ECOLE_DOCT
(
  ID NUMBER not null
    constraint ECOLE_DOCT_PK
    primary key,
  LIBELLE VARCHAR2(200 char) not null,
  SIGLE VARCHAR2(40 char),
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null constraint ECOLE_DOCT_HCFK references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint ECOLE_DOCT_HMFK references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER constraint ECOLE_DOCT_HDFK references UTILISATEUR,
  SOURCE_ID NUMBER not null
    constraint ECOLE_DOCT_SOURCE_FK
    references SOURCE,
  SOURCE_CODE VARCHAR2(64 char)
);

create unique index ECOLE_DOCT_SOURCE_CODE_UN on ECOLE_DOCT (SOURCE_CODE);

create index ECOLE_DOCT_SOURCE_IDX on ECOLE_DOCT (SOURCE_ID);

create index ECOLE_DOCT_HC_IDX on ECOLE_DOCT (HISTO_CREATEUR_ID);
create index ECOLE_DOCT_HM_IDX on ECOLE_DOCT (HISTO_MODIFICATEUR_ID);
create index ECOLE_DOCT_HD_IDX on ECOLE_DOCT (HISTO_DESTRUCTEUR_ID);

------

create table ECOLE_DOCT_IND
(
  ID NUMBER not null constraint ECOLE_DOCT_IND_PK primary key,
  ECOLE_DOCT_ID NUMBER not null constraint ECOLE_DOCT_IND_EDFK references ECOLE_DOCT,
  INDIVIDU_ID NUMBER not null constraint ECOLE_DOCT_IND_IFK references INDIVIDU,
  ROLE_ID NUMBER not null constraint ECOLE_DOCT_IND_RFK references ROLE,
  constraint ECOLE_DOCT_IND_UN unique (ECOLE_DOCT_ID, INDIVIDU_ID, ROLE_ID)
);

create index ECOLE_DOCT_IND_ECOLE_IDX on ECOLE_DOCT_IND (ECOLE_DOCT_ID);
create index ECOLE_DOCT_IND_INDIVIDU_IDX on ECOLE_DOCT_IND (INDIVIDU_ID);
create index ECOLE_DOCT_IND_ROLE_IDX on ECOLE_DOCT_IND (ROLE_ID);

------

create table METADONNEE_THESE
(
  ID NUMBER not null constraint METADONNEE_THESE_PK primary key,
  THESE_ID NUMBER not null constraint METADONNEE_THESE_TFK references THESE on delete cascade,
  TITRE VARCHAR2(2048 char) not null,
  LANGUE VARCHAR2(40 char) not null,
  RESUME CLOB not null,
  RESUME_ANGLAIS CLOB not null,
  MOTS_CLES_LIBRES_FR VARCHAR2(1024 char) not null,
  MOTS_CLES_RAMEAU VARCHAR2(1024 char),
  TITRE_AUTRE_LANGUE VARCHAR2(2048 char) not null,
  MOTS_CLES_LIBRES_ANG VARCHAR2(1024 char)
);

create unique index METADONNEE_THESE_UNIQ on METADONNEE_THESE (THESE_ID);

------

--DROP TABLE RDV_BU cascade constraints;
create table RDV_BU
(
  ID NUMBER not null constraint RDV_BU_PK primary key,
  THESE_ID NUMBER not null constraint RDV_BU_FK references THESE on delete cascade,
  COORD_DOCTORANT VARCHAR2(2000 char),
  DISPO_DOCTORANT VARCHAR2(2000 char),
  MOTS_CLES_RAMEAU VARCHAR2(1024 char),
  CONVENTION_MEL_SIGNEE NUMBER(1) default 0 not null,
  EXEMPL_PAPIER_FOURNI NUMBER(1) default 0 not null,
  VERSION_ARCHIVABLE_FOURNIE NUMBER(1) default 0 not null,
  PAGE_TITRE_CONFORME NUMBER(1) default 0 not null,
  DIVERS CLOB,

  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null constraint RDV_BU_HC_FK references UTILISATEUR on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint RDV_BU_HM_FK references UTILISATEUR on delete cascade,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER constraint RDV_BU_HD_FK references UTILISATEUR on delete cascade
);

comment on column RDV_BU.CONVENTION_MEL_SIGNEE is 'Convention de mise en ligne signée ?';
comment on column RDV_BU.EXEMPL_PAPIER_FOURNI is 'Exemplaire papier remis ?';
comment on column RDV_BU.VERSION_ARCHIVABLE_FOURNIE is 'Témoin indiquant si une version archivable de la thèse existe';

create index RDV_BU_THESE_IDX on RDV_BU (THESE_ID);

create index RDV_BU_HC_IDX on RDV_BU (HISTO_CREATEUR_ID);
create index RDV_BU_HM_IDX on RDV_BU (HISTO_MODIFICATEUR_ID);
create index RDV_BU_HD_IDX on RDV_BU (HISTO_DESTRUCTEUR_ID);

----

create table ROLE_PRIVILEGE
(
  ROLE_ID NUMBER not null references ROLE on delete cascade,
  PRIVILEGE_ID NUMBER not null references PRIVILEGE on delete cascade,
  constraint ROLE_PRIVILEGE_PK primary key (ROLE_ID, PRIVILEGE_ID)
);

create index ROLE_PRIVILEGE_ROLE_IDX on ROLE_PRIVILEGE (ROLE_ID);
create index ROLE_PRIVILEGE_PRIVILEGE_IDX on ROLE_PRIVILEGE (PRIVILEGE_ID);

------

create table DOCTORANT_COMPL
(
  ID NUMBER not null constraint THESARD_COMPL_PK primary key,
  DOCTORANT_ID NUMBER not null constraint DOCTORANT_COMPL_DOCTORANT_FK references DOCTORANT on delete cascade,
  PERSOPASS VARCHAR2(50 char),
  EMAIL_PRO VARCHAR2(100 char),

  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null constraint THESARD_COMPL_HCFK references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint THESARD_COMPL_HMFK references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER constraint THESARD_COMPL_HDFK references UTILISATEUR
);

create index DOCTORANT_COMPL_DOCTORANT_IDX on DOCTORANT_COMPL (DOCTORANT_ID);
create unique index DOCTORANT_COMPL_UN on DOCTORANT_COMPL (PERSOPASS, HISTO_DESTRUCTION);

create index DOCTORANT_COMPL_HC_IDX on DOCTORANT_COMPL (HISTO_CREATEUR_ID);
create index DOCTORANT_COMPL_HM_IDX on DOCTORANT_COMPL (HISTO_MODIFICATEUR_ID);
create index DOCTORANT_COMPL_HD_IDX on DOCTORANT_COMPL (HISTO_DESTRUCTEUR_ID);

------

create table UNITE_RECH
(
  ID NUMBER not null constraint UNITE_RECH_PK primary key,
  LIBELLE VARCHAR2(200 char) not null,
  SIGLE VARCHAR2(50 char),
  ETAB_SUPPORT VARCHAR2(500 char),
  AUTRES_ETAB VARCHAR2(500 char),

  SOURCE_ID NUMBER not null constraint UNITE_RECH_SOURCE_FK references SOURCE,
  SOURCE_CODE VARCHAR2(64 char),

  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null constraint UNITE_RECH_COMPL_HCFK references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint UNITE_RECH_COMPL_HMFK references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER constraint UNITE_RECH_COMPL_HDFK references UTILISATEUR
);

create unique index UNITE_RECH_SOURCE_CODE_UN on UNITE_RECH (SOURCE_CODE);

create index UNITE_RECH_SOURCE_IDX on UNITE_RECH (SOURCE_ID);

create index UNITE_RECH_HC_IDX on UNITE_RECH (HISTO_CREATEUR_ID);
create index UNITE_RECH_HM_IDX on UNITE_RECH (HISTO_MODIFICATEUR_ID);
create index UNITE_RECH_HD_IDX on UNITE_RECH (HISTO_DESTRUCTEUR_ID);

-----

create table UNITE_RECH_IND
(
  ID NUMBER not null constraint UNITE_RECH_IND_PK primary key,
  UNITE_RECH_ID NUMBER not null constraint UNITE_RECH_IND_URFK references UNITE_RECH,
  INDIVIDU_ID NUMBER not null constraint UNITE_RECH_IND_IFK references INDIVIDU,
  ROLE_ID NUMBER not null constraint UNITE_RECH_IND_RFK references ROLE,
  constraint UNITE_RECH_IND_UN unique (UNITE_RECH_ID, INDIVIDU_ID, ROLE_ID)
);

create index UNITE_RECH_IND_ECOLE_IDX on UNITE_RECH_IND (UNITE_RECH_ID);
create index UNITE_RECH_IND_INDIVIDU_IDX on UNITE_RECH_IND (INDIVIDU_ID);
create index UNITE_RECH_IND_ROLE_IDX on UNITE_RECH_IND (ROLE_ID);

-----

create table UTILISATEUR_ROLE
(
  UTILISATEUR_ID NUMBER constraint UTILISATEUR_ROLE_U_FK references UTILISATEUR on delete cascade,
  ROLE_ID NUMBER constraint UTILISATEUR_ROLE_R_FK references ROLE on delete cascade,
  constraint UTILISATEUR_ROLE_PK primary key (UTILISATEUR_ID, ROLE_ID)
);

create index UTILISATEUR_ROLE_USER_IDX on UTILISATEUR_ROLE (UTILISATEUR_ID);
create index UTILISATEUR_ROLE_ROLE_IDX on UTILISATEUR_ROLE (ROLE_ID);

-----

create table VALIDATION
(
  ID NUMBER not null constraint VALIDATION_PK primary key,
  TYPE_VALIDATION_ID NUMBER not null constraint VALIDATION_TYPE_VALIDATION_FK references TYPE_VALIDATION on delete cascade,
  THESE_ID NUMBER not null constraint VALIDATION_THESE_FK references THESE on delete cascade,
  INDIVIDU_ID NUMBER default NULL constraint VALIDATION_INDIVIDU_ID_FK references INDIVIDU on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER default 1 not null constraint VALIDATION_HCFK references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER default 1 not null constraint VALIDATION_HMFK references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER constraint VALIDATION_HDFK references UTILISATEUR
);

create index VALIDATION_TYPE_IDX on VALIDATION (TYPE_VALIDATION_ID);
create index VALIDATION_THESE_IDX on VALIDATION (THESE_ID);
create index VALIDATION_INDIVIDU_IDX on VALIDATION (INDIVIDU_ID);

create unique index VALIDATION_UN on VALIDATION (TYPE_VALIDATION_ID, THESE_ID, HISTO_DESTRUCTION, INDIVIDU_ID);

create index VALIDATION_HCFK_IDX on VALIDATION (HISTO_CREATEUR_ID);
create index VALIDATION_HMFK_IDX   on VALIDATION (HISTO_MODIFICATEUR_ID);
create index VALIDATION_HDFK_IDX on VALIDATION (HISTO_DESTRUCTEUR_ID);

------

create table VALIDITE_FICHIER
(
  ID NUMBER not null constraint CONFORMITE_FICHIER_PK primary key,
  FICHIER_ID VARCHAR2(38 char) not null constraint CONFORMITE_FICHIER_FFK references FICHIER on delete cascade,
  EST_VALIDE VARCHAR2(1 char) default NULL,
  MESSAGE CLOB default NULL,
  LOG CLOB,
  HISTO_CREATEUR_ID NUMBER not null constraint VALIDITE_FICHIER_HCFK references UTILISATEUR,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint VALIDITE_FICHIER_HMFK references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER constraint VALIDITE_FICHIER_HDFK references UTILISATEUR,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATION DATE default SYSDATE not null
);

create index VALIDITE_FICHIER_FICHIER_IDX on VALIDITE_FICHIER (FICHIER_ID);

create index VALIDITE_FICHIER_HCFK_IDX on VALIDITE_FICHIER (HISTO_CREATEUR_ID);
create index VALIDITE_FICHIER_HMFK_IDX on VALIDITE_FICHIER (HISTO_MODIFICATEUR_ID);
create index VALIDITE_FICHIER_HDFK_IDX on VALIDITE_FICHIER (HISTO_DESTRUCTEUR_ID);

-------

create table NOTIF
(
  ID NUMBER not null constraint NOTIF_PK primary key,
  CODE VARCHAR2(100) not null constraint NOTIF_UNIQ unique,
  DESCRIPTION VARCHAR2(255) not null,
  DESTINATAIRES VARCHAR2(500) not null,
  TEMPLATE CLOB not null,
  ENABLED NUMBER default 1 not null
);

create table NOTIF_RESULT
(
  ID NUMBER not null constraint NOTIF_RESULT_PK primary key,
  NOTIF_ID NUMBER not null constraint NOTIF_RESULT__NOTIF_FK references NOTIF on delete cascade,
  SUJET VARCHAR2(255) not null,
  CORPS CLOB not null,
  DATE_ENVOI DATE not null,
  ERREUR CLOB
);

create index NOTIF_RESULT_NOTIF_IDX on NOTIF_RESULT (NOTIF_ID);

create table IMPORT_OBSERV
(
  ID NUMBER not null constraint IMPORT_OBSERV_PK primary key,
  CODE VARCHAR2(50 char) not null constraint IMPORT_OBSERV_CODE_UN unique,
  TABLE_NAME VARCHAR2(50 char) not null,
  COLUMN_NAME VARCHAR2(50 char) not null,
  OPERATION VARCHAR2(50 char) default 'UPDATE' not null,
  TO_VALUE VARCHAR2(1000 char),
  DESCRIPTION VARCHAR2(200 char),
  ENABLED NUMBER(1) default 0 not null,
  constraint IMPORT_OBSERV_UN unique (TABLE_NAME, COLUMN_NAME, OPERATION, TO_VALUE)
);

create table IMPORT_OBSERV_RESULT
(
  ID NUMBER not null constraint IMPORT_OBSERV_RESULT_PK primary key,
  IMPORT_OBSERV_ID NUMBER not null constraint IMPORT_OBSERV_RESULT__IO_FK references IMPORT_OBSERV on delete cascade,
  DATE_CREATION DATE default SYSDATE not null,
  SOURCE_CODE VARCHAR2(64 char) not null,
  RESULTAT CLOB not null,
  DATE_NOTIF DATE
);

create index IMPORT_OBSERV_RESULT_IO_IDX on IMPORT_OBSERV_RESULT (IMPORT_OBSERV_ID);

create table IMPORT_OBS_NOTIF
(
  ID NUMBER not null constraint IOND_PK primary key,
  IMPORT_OBSERV_ID NUMBER not null constraint IOND__IO_FK references IMPORT_OBSERV on delete cascade,
  NOTIF_ID NUMBER not null constraint IOND__N_FK references NOTIF on delete cascade
);

create index IMPORT_OBS_NOTIF_N_IDX on IMPORT_OBS_NOTIF (NOTIF_ID);
create index IMPORT_OBS_NOTIF_IO_IDX on IMPORT_OBS_NOTIF (IMPORT_OBSERV_ID);

create table IMPORT_OBS_RESULT_NOTIF
(
  ID NUMBER not null constraint IORNR_PK primary key,
  IMPORT_OBSERV_RESULT_ID NUMBER not null constraint IORNR__IOR_FK references IMPORT_OBSERV_RESULT on delete cascade,
  NOTIF_RESULT_ID NUMBER not null constraint IORNR__NR_FK references NOTIF_RESULT on delete cascade
);

create index IMPORT_OBS_NOTIF_IOR_IDX on IMPORT_OBS_RESULT_NOTIF (IMPORT_OBSERV_RESULT_ID);
create index IMPORT_OBS_NOTIF_NR_IDX on IMPORT_OBS_RESULT_NOTIF (NOTIF_RESULT_ID);






----------------------------- Vues ------------------------------

create view V_SITU_ARCHIVAB_VO as
  SELECT
    t.id AS these_id,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VO'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_ARCHIVAB_VOC as
  SELECT
    t.id AS these_id,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_ARCHIVAB_VA as
  SELECT
    t.id AS these_id,
    f.RETRAITEMENT,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_ARCHIVAB_VAC as
  SELECT
    t.id AS these_id,
    f.RETRAITEMENT,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_RDV_BU_VALIDATION_BU as
  SELECT
    t.id AS these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id and v.HISTO_DESTRUCTEUR_ID is null
    JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'RDV_BU'
/

create view V_SITU_AUTORIS_DIFF_THESE as
  SELECT
    t.id AS these_id,
    d.id AS diffusion_id
  FROM these t
    JOIN DIFFUSION d ON d.THESE_ID = t.id and d.HISTO_DESTRUCTEUR_ID is null
/

create view V_SITU_SIGNALEMENT_THESE as
  SELECT
    t.id AS these_id,
    d.id AS description_id
  FROM these t
    JOIN METADONNEE_THESE d ON d.THESE_ID = t.id
/

create view V_SITU_RDV_BU_SAISIE_DOCT as
  SELECT
    t.id AS these_id,
    CASE WHEN r.COORD_DOCTORANT IS NOT NULL AND r.DISPO_DOCTORANT IS NOT NULL
      THEN 1 ELSE 0 END ok
  FROM these t
    JOIN RDV_BU r ON r.THESE_ID = t.id
/

create view V_SITU_RDV_BU_SAISIE_BU as
  SELECT
    t.id AS these_id,
    CASE WHEN r.VERSION_ARCHIVABLE_FOURNIE = 1 and r.CONVENTION_MEL_SIGNEE = 1 and r.EXEMPL_PAPIER_FOURNI = 1
              and r.PAGE_TITRE_CONFORME = 1 and r.MOTS_CLES_RAMEAU is not null
      THEN 1 ELSE 0 END ok
  FROM these t
    JOIN RDV_BU r ON r.THESE_ID = t.id
/

create view V_SITU_ATTESTATIONS as
  SELECT
    t.id AS these_id,
    a.id AS attestation_id
  FROM these t
    JOIN ATTESTATION a ON a.THESE_ID = t.id and a.HISTO_DESTRUCTEUR_ID is null
/

create view V_SITU_DEPOT_VO as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND RETRAITEMENT IS NULL AND
                      f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER vf ON f.VERSION_FICHIER_ID = vf.ID AND vf.CODE = 'VO'
/

create view V_SITU_DEPOT_VOC as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND RETRAITEMENT IS NULL AND
                      f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER vf ON f.VERSION_FICHIER_ID = vf.ID AND vf.CODE = 'VOC'
/

create view V_SITU_DEPOT_VA as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
/

create view V_SITU_DEPOT_VAC as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
/

create view V_SITU_VERIF_VA as
  SELECT
    t.id AS these_id,
    f.EST_CONFORME
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
/

create view V_SITU_VERIF_VAC as
  SELECT
    t.id AS these_id,
    f.EST_CONFORME
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
/

create view V_SITU_DEPOT_VC_VALID_DOCT as
  SELECT
    t.id AS these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id and v.HISTO_DESTRUCTEUR_ID is null
    JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'DEPOT_THESE_CORRIGEE'
/

create view V_SITU_DEPOT_VC_VALID_DIR as
  WITH validations_attendues AS (
      SELECT a.THESE_ID, a.INDIVIDU_ID, tv.ID as TYPE_VALIDATION_ID
      FROM ACTEUR a
        JOIN ROLE r on a.ROLE_ID = r.ID and r.SOURCE_CODE = 'D' -- directeur de thèse
        JOIN TYPE_VALIDATION tv on tv.code = 'CORRECTION_THESE'
      where a.HISTO_DESTRUCTION is null
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
                              v.TYPE_VALIDATION_ID = va.TYPE_VALIDATION_ID
/

create view V_SITU_DEPOT_PV_SOUT as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id and f.HISTO_DESTRUCTION is null and f.HISTO_DESTRUCTION is null
    JOIN NATURE_FICHIER nf on f.NATURE_ID = nf.id and nf.CODE = 'PV_SOUTENANCE'
/

create view V_SITU_DEPOT_RAPPORT_SOUT as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id and f.HISTO_DESTRUCTION is null and f.HISTO_DESTRUCTION is null
    JOIN NATURE_FICHIER nf on f.NATURE_ID = nf.id and nf.CODE = 'RAPPORT_SOUTENANCE'
/

create view V_SITU_ATTESTATIONS_VOC as
  SELECT
    t.id AS these_id,
    a.id AS attestation_id
  FROM these t
    JOIN ATTESTATION a ON a.THESE_ID = t.id and a.HISTO_DESTRUCTEUR_ID is null
    -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
/

create view V_SITU_AUTORIS_DIFF_THESE_VOC as
  SELECT
    t.id AS these_id,
    d.id AS diffusion_id
  FROM these t
    JOIN DIFFUSION d ON d.THESE_ID = t.id and d.HISTO_DESTRUCTEUR_ID is null
    -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
/

create view V_SITU_VERSION_PAPIER_CORRIGEE as
  SELECT
    t.id AS these_id,
    v.id as validation_id
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id
    JOIN TYPE_VALIDATION tv ON tv.ID = v.TYPE_VALIDATION_ID
  WHERE tv.CODE='VERSION_PAPIER_CORRIGEE'
/


create view V_WF_ETAPE_PERTIN as
  SELECT
    to_number(these_id) these_id,
    to_number(etape_id) etape_id,
    code,
    ORDRE,
    ROWNUM id
  FROM (
    --
    -- DEPOT_VERSION_ORIGINALE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE'

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
    -- ARCHIVABILITE_VERSION_ORIGINALE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE'

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

    UNION ALL

    --
    -- RDV_BU_SAISIE_DOCTORANT : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_DOCTORANT'

    UNION ALL

    --
    -- RDV_BU_SAISIE_BU : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_BU'

    UNION ALL

    --
    -- RDV_BU_VALIDATION_BU : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_VALIDATION_BU'

    UNION ALL





    --
    -- DEPOT_VERSION_ORIGINALE_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- ATTESTATIONS_VERSION_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS_VERSION_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

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
    WHERE t.CORREC_AUTORISEE is not null

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
    WHERE t.CORREC_AUTORISEE is not null

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
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

  )
/

create view V_WORKFLOW as
  SELECT
    ROWNUM id,
    t."THESE_ID",t."ETAPE_ID",t."CODE",t."ORDRE",t."FRANCHIE",t."RESULTAT",t."OBJECTIF"
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
         --
         --       UNION ALL

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
         -- ATTESTATIONS_VERSION_CORRIGEE : franchie si données saisies
         --
         SELECT
           t.id AS these_id,
           e.id AS etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.attestation_id IS NULL THEN 0 ELSE 1 END franchie,
           CASE WHEN v.attestation_id IS NULL THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS_VERSION_CORRIGEE'
           LEFT JOIN V_SITU_ATTESTATIONS_VOC v ON v.these_id = t.id

         UNION ALL

         --
         -- AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE : franchie si données saisies
         --
         SELECT
           t.id AS these_id,
           e.id AS etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.diffusion_id IS NULL THEN 0 ELSE 1 END franchie,
           CASE WHEN v.diffusion_id IS NULL THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE'
           LEFT JOIN V_SITU_AUTORIS_DIFF_THESE_VOC v ON v.these_id = t.id

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

         UNION ALL
         --
         -- REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE  : franchie pas pour le moment
         --

         select * from (
           WITH tmp_last AS (
               SELECT
                 THESE_ID as these_id,
                 count(THESE_ID) AS resultat
               FROM V_SITU_VERSION_PAPIER_CORRIGEE
               GROUP BY THESE_ID
           )
           SELECT
             t.id AS                 these_id,
             e.id AS                 etape_id,
             e.code,
             e.ORDRE,
             coalesce(tl.resultat, 0) franchie,
             0,
             1
           FROM these t
             JOIN WF_ETAPE e ON e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'
             LEFT JOIN tmp_last tl ON tl.these_id = t.id
         )
         --          e.code,
         --          e.ORDRE,
         --          0 franchie,
         --          0 resultat,
         --          1 objectif
         --        FROM V_SITU_VERSION_PAPIER_CORRIGEE
         --          JOIN WF_ETAPE e ON e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'



         -- LEFT JOIN V_SITU_DEPOT_VO v ON v.these_id = t.id



       ) t
    JOIN V_WF_ETAPE_PERTIN v ON t.these_id = v.these_id AND t.etape_id = v.etape_id
/

