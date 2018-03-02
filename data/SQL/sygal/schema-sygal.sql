--
-- Base de données SYGAL.
--
-- Schéma.
--


create or replace function GEN_SOURCE_CODE(code_etablissement VARCHAR2, string_id VARCHAR2) return VARCHAR2
IS
  BEGIN
    return code_etablissement || '::' || string_id;
  END;
/

---------------------- SOURCE ---------------------

create table SOURCE
(
  ID NUMBER not null constraint SOURCE_PK primary key,
  CODE VARCHAR2(64 char) not null constraint SOURCE_CODE_UN unique,
  LIBELLE VARCHAR2(128 char) not null,
  IMPORTABLE NUMBER(1) not null
);

comment on table SOURCE is 'Sources de données, importables ou non, ex: Apogée, Physalis.';


---------------------- Type structure ---------------------

create table TYPE_STRUCTURE
(
  ID NUMBER not null constraint TYPE_STRUCTURE_PK primary key,
  CODE VARCHAR2(50 char) not null,
  LIBELLE VARCHAR2(100 char)
);

create unique index TYPE_STRUCTURE_UN on TYPE_STRUCTURE (CODE);


---------------------- structure ---------------------

--drop table structure
CREATE TABLE STRUCTURE
(
  ID NUMBER constraint STRUCTURE_PK primary key,
  SIGLE VARCHAR2(40 char),
  LIBELLE VARCHAR2(200 char) NOT NULL constraint STRUCTURE_LIBELLE_UN unique,
  CHEMIN_LOGO VARCHAR2(200 char)
);
--drop sequence STRUCTURE_ID_SEQ;
CREATE SEQUENCE STRUCTURE_ID_SEQ;

ALTER TABLE STRUCTURE ADD TYPE_STRUCTURE_ID NUMBER NULL;
ALTER TABLE STRUCTURE ADD CONSTRAINT STRUCTURE_TYPE_STRUCTURE_ID_fk FOREIGN KEY (TYPE_STRUCTURE_ID) REFERENCES TYPE_STRUCTURE (ID) ON DELETE CASCADE;

create index STRUCTURE_TYPE_STRUCT_ID_IDX on STRUCTURE (TYPE_STRUCTURE_ID);


---------------------- ETABLISSEMENT ---------------------

CREATE TABLE ETABLISSEMENT
(
  ID NUMBER constraint ETAB_PK primary key,
  CODE VARCHAR2(32 char) not null constraint ETAB_CODE_UN unique,
  LIBELLE VARCHAR2(128) NOT NULL constraint ETAB_LIBELLE_UN unique,
  CHEMIN_LOGO VARCHAR2(200) NOT NULL
);
CREATE SEQUENCE ETABLISSEMENT_ID_SEQ;

alter table ETABLISSEMENT add structure_id NUMBER not null constraint ETAB_STRUCT_FK references STRUCTURE(id);
alter table ETABLISSEMENT MODIFY structure_id not null;
alter table ETABLISSEMENT drop COLUMN LIBELLE;
alter table ETABLISSEMENT drop COLUMN CHEMIN_LOGO;

create index ETABLISSEMENT_STRUCT_ID_IDX on ETABLISSEMENT (structure_id);


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
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,

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

create index TMP_INDIVIDU_SOURCE_CODE_IDX on TMP_INDIVIDU (SOURCE_CODE);
create index TMP_INDIVIDU_SOURCE_ID_IDX on TMP_INDIVIDU (SOURCE_ID);
CREATE UNIQUE INDEX TMP_INDIVIDU_UNIQ ON TMP_INDIVIDU(ID, ETABLISSEMENT_ID);


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
  DATE_NAISSANCE   DATE,
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
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,

  INDIVIDU_ID VARCHAR2(64 char) not null
);

create index TMP_DOCTORANT_SOURCE_CODE_IDX on TMP_DOCTORANT (SOURCE_CODE);
create index TMP_DOCTORANT_SOURCE_ID_IDX on TMP_DOCTORANT (SOURCE_ID);
CREATE UNIQUE INDEX TMP_DOCTORANT_UNIQ ON TMP_DOCTORANT(ID, ETABLISSEMENT_ID);

--DROP TABLE DOCTORANT cascade constraints;
create table DOCTORANT
(
  ID NUMBER constraint DOCTORANT_PK primary key,

  ETABLISSEMENT_ID NUMBER not null constraint DOCTORANT_ETAB_FK references ETABLISSEMENT on delete cascade,
  INDIVIDU_ID NUMBER not null constraint DOCTORANT_INDIV_FK references INDIVIDU on delete cascade,

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

create sequence DOCTORANT_ID_SEQ;


--------------------------- THESE -----------------------

--drop table TMP_THESE;
create table TMP_THESE
(
  insert_date date default sysdate,

  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,

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

create index TMP_THESE_SOURCE_CODE_INDEX on TMP_THESE (SOURCE_CODE);
create index TMP_THESE_SOURCE_ID_INDEX on TMP_THESE (SOURCE_ID);
CREATE UNIQUE INDEX TMP_THESE_UNIQ ON TMP_THESE(ID, ETABLISSEMENT_ID);

--DROP TABLE THESE cascade constraints;
create table THESE
(
  ID NUMBER not null constraint THESE_PK primary key,

  ETABLISSEMENT_ID NUMBER NOT NULL constraint THESE_ETAB_FK references ETABLISSEMENT on delete cascade,
  DOCTORANT_ID NUMBER NOT NULL constraint THESE_DOCTORANT_FK references DOCTORANT on delete cascade,
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

create sequence THESE_ID_SEQ;


--------------------------- ROLE -----------------------

--drop table TMP_ROLE;
create table TMP_ROLE
(
  insert_date date default sysdate,

  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,

  LIB_ROJ VARCHAR2(200 char),
  LIC_ROJ VARCHAR2(50 char)
);

create index TMP_ROLE_SOURCE_CODE_INDEX on TMP_ROLE (SOURCE_CODE);
create index TMP_ROLE_SOURCE_ID_INDEX on TMP_ROLE (SOURCE_ID);
CREATE UNIQUE INDEX TMP_ROLE_UNIQ ON TMP_ROLE(ID, ETABLISSEMENT_ID);

--DROP TABLE ROLE cascade constraints;
create table ROLE
(
  ID NUMBER NOT NULL CONSTRAINT ROLE_PK PRIMARY KEY,

  CODE VARCHAR2(50 char) not null,
  LIBELLE VARCHAR2(200 char) not null,

  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null constraint ROLE_SOURCE_FK references SOURCE on delete cascade,

  ROLE_ID VARCHAR2(64 CHAR) NOT NULL,
  IS_DEFAULT NUMBER DEFAULT 0,
  LDAP_FILTER VARCHAR2(255 CHAR),
  ATTRIB_AUTO NUMBER(1,0) DEFAULT 0 NOT NULL,
  STRUCTURE_DEP NUMBER(1,0) DEFAULT 0 NOT NULL ENABLE,
  THESE_DEP NUMBER(1,0) DEFAULT 0 NOT NULL ENABLE,

  HISTO_CREATEUR_ID NUMBER not null constraint ROLE_HC_FK references UTILISATEUR on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null constraint ROLE_HM_FK references UTILISATEUR on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER constraint ROLE_HD_FK references UTILISATEUR on delete cascade,
  HISTO_DESTRUCTION DATE
);

comment on table ROLE is 'Rôles au titre d''un établissement, ex: directeur de thèse (université de rouen).';

create unique index ROLE_SOURCE_CODE_UNIQ on ROLE (SOURCE_CODE);

create index ROLE_SOURCE_IDX on ROLE (SOURCE_ID);
create index ROLE_HC_IDX on ROLE (HISTO_CREATEUR_ID);
create index ROLE_HM_IDX on ROLE (HISTO_MODIFICATEUR_ID);
create index ROLE_HD_IDX on ROLE (HISTO_DESTRUCTEUR_ID);

--drop sequence ROLE_ID_SEQ;
create sequence ROLE_ID_SEQ;

ALTER TABLE ROLE ADD STRUCTURE_ID NUMBER NULL;
ALTER TABLE ROLE ADD CONSTRAINT ROLE_STRUCTURE_ID_fk FOREIGN KEY (STRUCTURE_ID) REFERENCES STRUCTURE (ID) ON DELETE CASCADE;

ALTER TABLE ROLE ADD TYPE_STRUCTURE_DEPENDANT_ID NUMBER NULL;
ALTER TABLE ROLE ADD CONSTRAINT ROLE_TYPE_STRUCT_ID_fk FOREIGN KEY (TYPE_STRUCTURE_DEPENDANT_ID) REFERENCES TYPE_STRUCTURE (ID) ON DELETE CASCADE;

create index ROLE_STRUCTURE_ID_IDX on ROLE (STRUCTURE_ID);
create index ROLE_TYPE_STRUCTURE_ID_IDX on ROLE (TYPE_STRUCTURE_DEPENDANT_ID);


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
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,

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

create index TMP_ACTEUR_SOURCE_CODE_INDEX on TMP_ACTEUR (SOURCE_CODE);
create index TMP_ACTEUR_SOURCE_ID_INDEX on TMP_ACTEUR (SOURCE_ID);
CREATE UNIQUE INDEX TMP_ACTEUR_UNIQ ON TMP_ACTEUR(ID, ETABLISSEMENT_ID);

--DROP TABLE ACTEUR cascade constraints;
create table ACTEUR
(
  ID NUMBER NOT NULL CONSTRAINT ACTEUR_PK PRIMARY KEY,

--   ETABLISSEMENT_ID NUMBER not null CONSTRAINT ACTEUR_ETAB_FK REFERENCES ETABLISSEMENT ON DELETE CASCADE,
  INDIVIDU_ID NUMBER not null constraint ACTEUR_INDIV_FK references INDIVIDU on delete cascade,
  THESE_ID NUMBER not null constraint ACTEUR_THESE_FK references THESE on delete cascade,
  ROLE_ID NUMBER not null constraint ACTEUR_ROLE_FK references ROLE on delete cascade,

  ETABLISSEMENT VARCHAR2(200 char),
  QUALITE VARCHAR2(200 char),
  LIB_ROLE_COMPL VARCHAR2(200 char),

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

create sequence ACTEUR_ID_SEQ;


--------------------------- VARIABLE -----------------------

--drop table TMP_VARIABLE;
create table TMP_VARIABLE
(
  insert_date date default sysdate,

  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,

  COD_VAP VARCHAR2(50 char),
  LIB_VAP VARCHAR2(300 char),
  PAR_VAP VARCHAR2(200 char),
  DATE_DEB_VALIDITE DATE not null,
  DATE_FIN_VALIDITE DATE not null
);

create index TMP_VARIABLE_SOURCE_CODE_INDEX on TMP_VARIABLE (SOURCE_CODE);
create index TMP_VARIABLE_SOURCE_ID_INDEX on TMP_VARIABLE (SOURCE_ID);
CREATE UNIQUE INDEX TMP_VARIABLE_UNIQ ON TMP_VARIABLE(ID, ETABLISSEMENT_ID);

--DROP TABLE VARIABLE cascade constraints;
create table VARIABLE
(
  ID NUMBER NOT NULL CONSTRAINT VARIABLE_PK PRIMARY KEY,

  ETABLISSEMENT_ID NUMBER not null CONSTRAINT VARIABLE_ETAB_FK REFERENCES ETABLISSEMENT ON DELETE CASCADE,

  CODE VARCHAR2(64) NOT NULL,
  DESCRIPTION VARCHAR2(300 char) not null,
  VALEUR VARCHAR2(200 char) not null,
  DATE_DEB_VALIDITE DATE default sysdate not null,
  DATE_FIN_VALIDITE DATE default to_date('9999-12-31', 'YYYY-MM-DD') not null,

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

create unique index VARIABLE_CODE_UNIQ on VARIABLE (CODE, ETABLISSEMENT_ID);
create unique index VARIABLE_SOURCE_CODE_UNIQ on VARIABLE (SOURCE_CODE);

create index VARIABLE_ETABLISSEMENT_IDX on VARIABLE (ETABLISSEMENT_ID);
create index VARIABLE_SOURCE_IDX on VARIABLE (SOURCE_ID);
create index VARIABLE_HC_IDX on VARIABLE (HISTO_CREATEUR_ID);
create index VARIABLE_HM_IDX on VARIABLE (HISTO_MODIFICATEUR_ID);
create index VARIABLE_HD_IDX on VARIABLE (HISTO_DESTRUCTEUR_ID);

create sequence VARIABLE_ID_SEQ;


--------------------------- INDIVIDU_ROLE -----------------------

create table INDIVIDU_ROLE
(
  ID NUMBER NOT NULL CONSTRAINT INDIVIDU_ROLE_PK PRIMARY KEY,

  INDIVIDU_ID NUMBER,
  ROLE_ID NUMBER
);

comment on table INDIVIDU_ROLE is 'Attributions à des individus de rôles sans lien avec une thèse en particulier, ex: bureau des doctorats.';

create index INDIVIDU_ROLE_INDIVIDU_IDX on INDIVIDU_ROLE (INDIVIDU_ID);
create index INDIVIDU_ROLE_ROLE_IDX on INDIVIDU_ROLE (ROLE_ID);

ALTER TABLE INDIVIDU_ROLE ADD CONSTRAINT INDIVIDU_ROLE_IND_ID_fk FOREIGN KEY (INDIVIDU_ID) REFERENCES INDIVIDU (ID) ON DELETE CASCADE;
ALTER TABLE INDIVIDU_ROLE ADD CONSTRAINT INDIVIDU_ROLE_ROLE_ID_fk FOREIGN KEY (ROLE_ID) REFERENCES ROLE (ID) ON DELETE CASCADE;

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


create sequence privilege_id_seq;

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
  EST_EXPURGE NUMBER(1) default 0  not null,
  EST_CONFORME NUMBER(1),
  EST_PARTIEL NUMBER(1) DEFAULT 0 NOT NULL,
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
  SOURCE_CODE VARCHAR2(64 char),
  CHEMIN_LOGO VARCHAR2(200) NOT NULL
);

create unique index ECOLE_DOCT_SOURCE_CODE_UN on ECOLE_DOCT (SOURCE_CODE);

create index ECOLE_DOCT_SOURCE_IDX on ECOLE_DOCT (SOURCE_ID);

create index ECOLE_DOCT_HC_IDX on ECOLE_DOCT (HISTO_CREATEUR_ID);
create index ECOLE_DOCT_HM_IDX on ECOLE_DOCT (HISTO_MODIFICATEUR_ID);
create index ECOLE_DOCT_HD_IDX on ECOLE_DOCT (HISTO_DESTRUCTEUR_ID);

create sequence ECOLE_DOCT_ID_SEQ;

alter table ECOLE_DOCT add structure_id NUMBER constraint ECOLE_DOCT_STRUCT_FK references STRUCTURE(id);
alter table ECOLE_DOCT MODIFY structure_id not null;
alter table ECOLE_DOCT drop COLUMN SIGLE;
alter table ECOLE_DOCT drop COLUMN LIBELLE;
alter table ECOLE_DOCT drop COLUMN CHEMIN_LOGO;

create index ECOLE_DOCT_STRUCT_ID_IDX on ECOLE_DOCT (structure_id);

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

create sequence ECOLE_DOCT_IND_ID_SEQ;

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

create sequence METADONNEE_THESE_ID_SEQ;

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

create sequence RDV_BU_ID_SEQ;

----

create table ROLE_PRIVILEGE
(
  ROLE_ID NUMBER not null,
  PRIVILEGE_ID NUMBER not null,
  constraint ROLE_PRIVILEGE_PK primary key (ROLE_ID, PRIVILEGE_ID)
);

create index ROLE_PRIVILEGE_ROLE_IDX on ROLE_PRIVILEGE (ROLE_ID);
create index ROLE_PRIVILEGE_PRIVILEGE_IDX on ROLE_PRIVILEGE (PRIVILEGE_ID);

ALTER TABLE ROLE_PRIVILEGE ADD CONSTRAINT ROLE_PRIVILEGE_ROLE_ID_fk FOREIGN KEY (ROLE_ID) REFERENCES ROLE (ID) ON DELETE CASCADE;
ALTER TABLE ROLE_PRIVILEGE ADD CONSTRAINT ROLE_PRIVILEGE_PRIV_ID_fk FOREIGN KEY (PRIVILEGE_ID) REFERENCES PRIVILEGE (ID) ON DELETE CASCADE;

------

create table ROLE_PRIVILEGE_MODELE
(
  ROLE_CODE VARCHAR2(100) not null,
  PRIVILEGE_ID NUMBER not null constraint ROLE_PRIV_MOD_PRIV_ID_FK references PRIVILEGE on delete cascade,
  constraint ROLE_PRIV_MOD_PK primary key (ROLE_CODE, PRIVILEGE_ID)
);

----

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

create sequence DOCTORANT_COMPL_ID_SEQ;

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
  CHEMIN_LOGO VARCHAR2(200) NOT NULL,

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

create sequence UNITE_RECH_ID_SEQ;

alter table UNITE_RECH add structure_id NUMBER constraint UNITE_RECH_STRUCT_FK references STRUCTURE(id);
alter table UNITE_RECH MODIFY structure_id not null;
alter table UNITE_RECH drop COLUMN SIGLE;
alter table UNITE_RECH drop COLUMN LIBELLE;
alter table UNITE_RECH drop COLUMN CHEMIN_LOGO;

create index UNITE_RECH_STRUCT_ID_IDX on UNITE_RECH (structure_id);

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

create sequence UNITE_RECH_IND_ID_SEQ;

-----

create table UTILISATEUR_ROLE
(
  UTILISATEUR_ID NUMBER constraint UTILISATEUR_ROLE_U_FK references UTILISATEUR on delete cascade,
  ROLE_ID NUMBER constraint UTILISATEUR_ROLE_R_FK references ROLE on delete cascade,
  constraint UTILISATEUR_ROLE_PK primary key (UTILISATEUR_ID, ROLE_ID)
);

create index UTILISATEUR_ROLE_USER_IDX on UTILISATEUR_ROLE (UTILISATEUR_ID);
create index UTILISATEUR_ROLE_ROLE_IDX on UTILISATEUR_ROLE (ROLE_ID);

ALTER TABLE UTILISATEUR_ROLE ADD CONSTRAINT UTILISATEUR_ROLE_IND_ID_fk FOREIGN KEY (UTILISATEUR_ID) REFERENCES UTILISATEUR (ID) ON DELETE CASCADE;
ALTER TABLE UTILISATEUR_ROLE ADD CONSTRAINT UTILISATEUR_ROLE_ROLE_ID_fk FOREIGN KEY (ROLE_ID) REFERENCES ROLE (ID) ON DELETE CASCADE;

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

create sequence VALIDATION_ID_SEQ;

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

create sequence VALIDITE_FICHIER_ID_SEQ;

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

create sequence NOTIF_RESULT_ID_SEQ;

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

create sequence IMPORT_OBSERV_RESULT_ID_SEQ;

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

create sequence IMPORT_OBS_RESULT_NOTIF_ID_SEQ;

create table IMPORT_NOTIF
(
  ID NUMBER not null constraint IMPORT_NOTIF_PK primary key,
  TABLE_NAME VARCHAR2(50 char) not null,
  COLUMN_NAME VARCHAR2(50 char) not null,
  OPERATION VARCHAR2(50 char) default 'UPDATE' not null,
  TO_VALUE VARCHAR2(1000 char),
  DESCRIPTION VARCHAR2(200 char),
  URL VARCHAR2(1000 char) not null,
  constraint IMPORT_NOTIF_UN unique (TABLE_NAME, COLUMN_NAME, OPERATION)
);


------------------------------ ENV ---------------------------

create table ENV
(
  ID NUMBER not null constraint ENV_PK primary key,
  ANNEE_ID NUMBER constraint ENV_ANNEE_UNIQ unique,
  LIB_ETAB VARCHAR2(200 char) default 'Université Formidable' not null,
  LIB_ETAB_A VARCHAR2(200 char) default 'à l''Université Formidable' not null,
  LIB_ETAB_LE VARCHAR2(200 char) default 'l''Université Formidable' not null,
  LIB_ETAB_DE VARCHAR2(200 char) default 'de l''Université Formidable' not null,
  LIB_PRESID_LE VARCHAR2(100 char) default 'le Chef' not null,
  LIB_PRESID_DE VARCHAR2(100 char) default 'du Chef' not null,
  NOM_PRESID VARCHAR2(100 char) default 'Patrick Patron' not null,
  EMAIL_ASSISTANCE VARCHAR2(100 char) default null not null,
  EMAIL_BU VARCHAR2(100 char) default null not null,
  EMAIL_BDD VARCHAR2(100 char) default null not null,
  LIB_COMUE VARCHAR2(200) default 'Normandie Université Formidable'
)
/

comment on column ENV.EMAIL_BU is 'Adresse de contact de la bibliothèque universitaire';
comment on column ENV.EMAIL_BDD is 'Adresse de contact du Bureau des doctorat';



------------------ Package UNICAEN_ORACLE ----------------------

create or replace PACKAGE UNICAEN_ORACLE AS

  FUNCTION implode(i_query VARCHAR2, i_seperator VARCHAR2 DEFAULT ',') RETURN VARCHAR2;

  FUNCTION STR_REDUCE( str CLOB ) RETURN CLOB;

  FUNCTION STR_FIND( haystack CLOB, needle VARCHAR2 ) RETURN NUMERIC;

  FUNCTION LIKED( haystack CLOB, needle CLOB ) RETURN NUMERIC;

  FUNCTION COMPRISE_ENTRE( date_debut DATE, date_fin DATE, date_obs DATE DEFAULT NULL, inclusif NUMERIC DEFAULT 0 ) RETURN NUMERIC;

END UNICAEN_ORACLE;
/

create or replace PACKAGE BODY UNICAEN_ORACLE AS

  FUNCTION implode(i_query VARCHAR2, i_seperator VARCHAR2 DEFAULT ',') RETURN VARCHAR2 AS
    l_return CLOB:='';
    l_temp CLOB;
    TYPE r_cursor is REF CURSOR;
    rc r_cursor;
    BEGIN
      OPEN rc FOR i_query;
      LOOP
        FETCH rc INTO L_TEMP;
        EXIT WHEN RC%NOTFOUND;
        l_return:=l_return||L_TEMP||i_seperator;
      END LOOP;
      RETURN RTRIM(l_return,i_seperator);
    END;

  FUNCTION STR_REDUCE( str CLOB ) RETURN CLOB IS
    BEGIN
      RETURN utl_raw.cast_to_varchar2((nlssort(str, 'nls_sort=binary_ai')));
    END;

  FUNCTION STR_FIND( haystack CLOB, needle VARCHAR2 ) RETURN NUMERIC IS
    BEGIN
      IF STR_REDUCE( haystack ) LIKE STR_REDUCE( '%' || needle || '%' ) THEN RETURN 1; END IF;
      RETURN 0;
    END;

  FUNCTION LIKED( haystack CLOB, needle CLOB ) RETURN NUMERIC IS
    BEGIN
      RETURN CASE WHEN STR_REDUCE(haystack) LIKE STR_REDUCE(needle) THEN 1 ELSE 0 END;
    END;

  FUNCTION COMPRISE_ENTRE( date_debut DATE, date_fin DATE, date_obs DATE DEFAULT NULL, inclusif NUMERIC DEFAULT 0 ) RETURN NUMERIC IS
    d_deb DATE;
    d_fin DATE;
    d_obs DATE;
    res NUMERIC;
    BEGIN
      IF inclusif = 1 THEN
        d_obs := TRUNC( COALESCE( d_obs     , SYSDATE ) );
        d_deb := TRUNC( COALESCE( date_debut, d_obs   ) );
        d_fin := TRUNC( COALESCE( date_fin  , d_obs   ) );
        IF d_obs BETWEEN d_deb AND d_fin THEN
          RETURN 1;
        ELSE
          RETURN 0;
        END IF;
      ELSE
        d_obs := TRUNC( COALESCE( d_obs, SYSDATE ) );
        d_deb := TRUNC( date_debut );
        d_fin := TRUNC( date_fin   );

        IF d_deb IS NOT NULL AND NOT d_deb <= d_obs THEN
          RETURN 0;
        END IF;
        IF d_fin IS NOT NULL AND NOT d_obs < d_fin THEN
          RETURN 0;
        END IF;
        RETURN 1;
      END IF;
    END;

END UNICAEN_ORACLE;
/


------------------ UNICAEN_IMPORT ----------------------

CREATE TABLE SYNC_LOG
(
    ID NUMBER(*, 0) NOT NULL
  , DATE_SYNC TIMESTAMP(6) NOT NULL
  , MESSAGE CLOB NOT NULL
  , TABLE_NAME VARCHAR2(30 CHAR)
  , SOURCE_CODE VARCHAR2(200 CHAR)
  , CONSTRAINT SYNC_LOG_PK PRIMARY KEY (ID)
  USING INDEX (CREATE UNIQUE INDEX SYNC_LOG_PK ON SYNC_LOG (ID ASC)) ENABLE
);

/

create sequence SYNC_LOG_ID_SEQ;

/

CREATE OR REPLACE VIEW "V_IMPORT_TAB_COLS" AS
  WITH importable_tables (table_name )AS (
    SELECT
      t.table_name
    FROM
      user_tab_cols c
      join user_tables t on t.table_name = c.table_name
    WHERE
      c.column_name = 'SOURCE_CODE'

    MINUS

    SELECT
      mview_name table_name
    FROM
      USER_MVIEWS
  ), c_values (table_name, column_name, c_table_name, c_column_name) AS (
      SELECT
        tc.table_name,
        tc.column_name,
        pcc.table_name c_table_name,
        pcc.column_name c_column_name
      FROM
        user_tab_cols tc
        JOIN USER_CONS_COLUMNS cc ON cc.table_name = tc.table_name AND cc.column_name = tc.column_name
        JOIN USER_CONSTRAINTS c ON c.constraint_name = cc.constraint_name
        JOIN USER_CONSTRAINTS pc ON pc.constraint_name = c.r_constraint_name
        JOIN USER_CONS_COLUMNS pcc ON pcc.constraint_name = pc.constraint_name
      WHERE
        c.constraint_type = 'R' AND pc.constraint_type = 'P'
  )
  SELECT
    tc.table_name,
    tc.column_name,
    tc.data_type,
    CASE WHEN tc.char_length = 0 THEN NULL ELSE tc.char_length END length,
    CASE WHEN tc.nullable = 'Y' THEN 1 ELSE 0 END nullable,
    CASE WHEN tc.data_default IS NOT NULL THEN 1 ELSE 0 END has_default,
    cv.c_table_name,
    cv.c_column_name,
    CASE WHEN stc.table_name IS NULL THEN 0 ELSE 1 END AS import_actif
  FROM
    user_tab_cols tc
    JOIN importable_tables t ON t.table_name = tc.table_name
    LEFT JOIN c_values cv ON cv.table_name = tc.table_name AND cv.column_name = tc.column_name
    LEFT JOIN user_tab_cols stc ON stc.table_name = 'SRC_' || tc.table_name AND stc.column_name = tc.column_name
  WHERE
    tc.column_name not like 'HISTO_%'
  ORDER BY
    tc.table_name, tc.column_id;

/

create or replace PACKAGE UNICAEN_IMPORT AS

  PROCEDURE set_current_user(p_current_user IN INTEGER);
  FUNCTION get_current_user return INTEGER;

  FUNCTION get_current_annee RETURN INTEGER;
  PROCEDURE set_current_annee (p_current_annee INTEGER);

  FUNCTION get_sql_criterion( table_name varchar2, sql_criterion VARCHAR2 ) RETURN CLOB;
  PROCEDURE SYNC_LOG( message CLOB, table_name VARCHAR2 DEFAULT NULL, source_code VARCHAR2 DEFAULT NULL );

  -- AUTOMATIC GENERATION --

  PROCEDURE MAJ_VARIABLE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_THESE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_ROLE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_INDIVIDU(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_DOCTORANT(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_ACTEUR(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');

  -- END OF AUTOMATIC GENERATION --
END UNICAEN_IMPORT;
/

create or replace PACKAGE BODY UNICAEN_IMPORT AS

  v_current_user INTEGER;
  v_current_annee INTEGER;



  FUNCTION get_current_user RETURN INTEGER IS
    BEGIN
      IF v_current_user IS NULL THEN
        v_current_user := 1; -- A remplacer par l'utilisateur (ID de la table USER) qui sera le créateur ou le modificateur des données
      END IF;
      RETURN v_current_user;
    END get_current_user;

  PROCEDURE set_current_user (p_current_user INTEGER) is
    BEGIN
      v_current_user := p_current_user;
    END set_current_user;



  FUNCTION get_current_annee RETURN INTEGER IS
    BEGIN
      IF v_current_annee IS NULL THEN
        v_current_annee := NULL; -- A remplacer par l'année d'import souhaitée (si vous avez de l'annualisation de prévue dans votre BDD)
      END IF;
      RETURN v_current_annee;
    END get_current_annee;

  PROCEDURE set_current_annee (p_current_annee INTEGER) IS
    BEGIN
      v_current_annee := p_current_annee;
    END set_current_annee;



  FUNCTION get_sql_criterion( table_name varchar2, sql_criterion VARCHAR2 ) RETURN CLOB IS
    BEGIN
      IF sql_criterion <> '' OR sql_criterion IS NOT NULL THEN
        RETURN sql_criterion;
      END IF;
      RETURN '';
      /* Exemple d'usage :

      RETURN CASE table_name
        WHEN 'INTERVENANT' THEN -- Met à jour toutes les données sauf le statut, qui sera traité à part
          'WHERE IMPORT_ACTION IN (''delete'',''update'',''undelete'')'

        WHEN 'AFFECTATION_RECHERCHE' THEN
          'WHERE INTERVENANT_ID IS NOT NULL'

        WHEN 'ADRESSE_INTERVENANT' THEN
          'WHERE INTERVENANT_ID IS NOT NULL'

        WHEN 'ELEMENT_TAUX_REGIMES' THEN
          'WHERE IMPORT_ACTION IN (''delete'',''insert'',''undelete'')'

        ELSE
          ''
      END;*/
    END;



  PROCEDURE SYNC_LOG( message CLOB, table_name VARCHAR2 DEFAULT NULL, source_code VARCHAR2 DEFAULT NULL ) IS
    BEGIN
      INSERT INTO SYNC_LOG("ID","DATE_SYNC","MESSAGE","TABLE_NAME","SOURCE_CODE") VALUES (SYNC_LOG_ID_SEQ.NEXTVAL, SYSDATE, message,table_name,source_code);
    END SYNC_LOG;



  FUNCTION IN_COLUMN_LIST( VALEUR VARCHAR2, CHAMPS CLOB ) RETURN NUMERIC IS
    BEGIN
      IF REGEXP_LIKE(CHAMPS, '(^|,)[ \t\r\n\v\f]*' || VALEUR || '[ \t\r\n\v\f]*(,|$)') THEN RETURN 1; END IF;
      RETURN 0;
    END;


  -- AUTOMATIC GENERATION --

  -- END OF AUTOMATIC GENERATION --
END UNICAEN_IMPORT;
/


------------------ Package APP_IMPORT ----------------------

create or replace PACKAGE "APP_IMPORT" IS

  PROCEDURE REFRESH_MV( mview_name VARCHAR2 );
  PROCEDURE SYNC_TABLES;
  PROCEDURE SYNCHRONISATION;

  PROCEDURE STORE_OBSERV_RESULTS;

END APP_IMPORT;
/

create or replace PACKAGE BODY "APP_IMPORT"
IS

  PROCEDURE REFRESH_MV( mview_name VARCHAR2 ) IS
    BEGIN
      DBMS_MVIEW.REFRESH(mview_name, 'C');
      EXCEPTION WHEN OTHERS THEN
      UNICAEN_IMPORT.SYNC_LOG( SQLERRM, mview_name );
    END;

  PROCEDURE SYNC_TABLES
  IS
    BEGIN
      -- mise à jour des tables à partir des vues sources
      -- NB: l'ordre importe !
      UNICAEN_IMPORT.MAJ_INDIVIDU();
      UNICAEN_IMPORT.MAJ_DOCTORANT();
      UNICAEN_IMPORT.MAJ_THESE();
      UNICAEN_IMPORT.MAJ_ROLE();
      UNICAEN_IMPORT.MAJ_ACTEUR();
      UNICAEN_IMPORT.MAJ_VARIABLE();
      REFRESH_MV('MV_RECHERCHE_THESE'); -- NB: à faire en dernier
    END;

  --
  -- Recherche des changements de type UPDATE concernant la colonne de table observée et
  -- enregistrement de ces changements dans une table.
  --
  PROCEDURE STORE_UPDATE_OBSERV_RESULT(observ IMPORT_OBSERV%ROWTYPE)
  IS
    u_col_name VARCHAR2(50) := 'U_' || observ.column_name;
    where_to_value CLOB := 'v.' || observ.column_name || case when observ.to_value is null then ' is null' else ' = ''' || observ.to_value || '''' end;
    i_query clob := 'select v.source_code, t.' || observ.column_name || ' || ''>'' || v.' || observ.column_name || ' detail ' ||
                    'from v_diff_' || observ.table_name || ' v join ' || observ.table_name || ' t on t.source_code = v.source_code where ' || u_col_name || ' = 1 and ' || where_to_value || ' order by v.source_code';
    TYPE r_cursor is REF CURSOR;
    rc r_cursor;
    l_id CLOB;
    l_detail CLOB;
    BEGIN
      OPEN rc FOR i_query;
      LOOP
        FETCH rc INTO l_id, l_detail;
        EXIT WHEN rc%NOTFOUND;
        --DBMS_OUTPUT.PUT_LINE(l_id); DBMS_OUTPUT.PUT_LINE(l_detail);
        insert into IMPORT_OBSERV_RESULT(ID, IMPORT_OBSERV_ID, DATE_CREATION, SOURCE_CODE, RESULTAT) values
          (IMPORT_OBSERV_RESULT_ID_SEQ.nextval, observ.id, sysdate, l_id, l_detail);
      END LOOP;
    END;


  PROCEDURE STORE_OBSERV_RESULTS
  IS
    BEGIN
      for observ in (select * from IMPORT_OBSERV where enabled = 1) loop
        if (observ.operation = 'UPDATE') then
          STORE_UPDATE_OBSERV_RESULT(observ);
        end if;
      end loop;
    END;


  PROCEDURE SYNCHRONISATION
  IS
    BEGIN
--       STORE_OBSERV_RESULTS;
      SYNC_TABLES;
    END;

END APP_IMPORT;
/


------------------------- Workflow ---------------------------

create or replace PACKAGE          "APP_WORKFLOW" AS

  function atteignable(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC;
  function atteignable2(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC;

END APP_WORKFLOW;
/

create or replace PACKAGE BODY          "APP_WORKFLOW"
AS

  function atteignable(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC AS
    v_ordre numeric;
    BEGIN
      select ordre into v_ordre from wf_etape where id = p_etape_id;
      --DBMS_OUTPUT.PUT_LINE('ordre ' || v_ordre);
      for row in (
      select code, ORDRE, franchie, resultat, objectif
      from V_WORKFLOW v
      where v.these_id = p_these_id and v.ordre < v_ordre
      order by v.ordre
      ) loop
        --DBMS_OUTPUT.PUT_LINE(rpad(row.ordre, 5) || ' ' || row.code || ' : ' || row.franchie);
        if row.franchie = 0 then
          return 0;
        end if;
      end loop;

      RETURN 1;
    END atteignable;



  function atteignable2(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC AS
    v_ordre numeric;
    BEGIN
      select ordre into v_ordre from wf_etape where id = p_etape_id;
      --DBMS_OUTPUT.PUT_LINE('ordre ' || v_ordre);
      for row in (
      select code, ORDRE, franchie, resultat, objectif
      from V_WORKFLOW v
      where v.these_id = p_these_id and v.ordre < v_ordre
      order by v.ordre
      ) loop
        --DBMS_OUTPUT.PUT_LINE(rpad(row.ordre, 5) || ' ' || row.code || ' : ' || row.franchie);
        if row.franchie = 0 then
          return 0;
        end if;
      end loop;

      RETURN 1;
    END atteignable2;

END APP_WORKFLOW;
  /

