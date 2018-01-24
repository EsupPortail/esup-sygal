--
-- Base de données SYGAL.
--
-- Schéma.
--


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

INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (1, 'APOGEE-UCN',  'Apogée - Université de Caen Normandie', 1);
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (2, 'SYGAL',       'SYGAL',                                 0);


---------------------- ETABLISSEMENT ---------------------

CREATE TABLE ETABLISSEMENT
(
  ID NUMBER constraint ETAB_PK primary key,
  CODE VARCHAR2(32 char) not null constraint ETAB_CODE_UN unique,
  LIBELLE VARCHAR2(128) NOT NULL constraint ETAB_LIBELLE_UN unique
);

INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (1, 'COMUE', 'Normandie Université');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (2, 'UCN', 'Université de Caen Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (3, 'URN', 'Université de Rouen Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (4, 'ULHN', 'Université Le Havre Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (5, 'INSA', 'INSA de Rouen');


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

create or replace view SRC_INDIVIDU as
select
  null id,
  to_char(i.id)    as SOURCE_CODE,
  src.id           as SOURCE_ID,
  TYPE,
  civ              as CIVILITE,
  lib_nom_usu_ind  as NOM_USUEL,
  lib_nom_pat_ind  as NOM_PATRONYMIQUE,
  lib_pr1_ind      as PRENOM1,
  lib_pr2_ind      as PRENOM2,
  lib_pr3_ind      as PRENOM3,
  EMAIL,
  dat_nai_per      as DATE_NAISSANCE,
  lib_nat          as NATIONALITE
from TMP_INDIVIDU i
  join SOURCE src on src.CODE = i.SOURCE_ID;

--drop table INDIVIDU;
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

  HISTO_CREATEUR_ID NUMBER not null,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER,
  HISTO_DESTRUCTION DATE,
  HISTO_MODIFICATEUR_ID NUMBER not null,
  HISTO_MODIFICATION DATE default SYSDATE not null
);

create index INDIVIDU_SRC_ID_INDEX on INDIVIDU (SOURCE_ID);

create index INDIVIDU_HCFK_IDX on INDIVIDU (HISTO_CREATEUR_ID);
create index INDIVIDU_HMFK_IDX on INDIVIDU (HISTO_MODIFICATEUR_ID);
create index INDIVIDU_HDFK_IDX on INDIVIDU (HISTO_DESTRUCTEUR_ID);


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

create or replace view SRC_DOCTORANT AS
  select
    null id,
    to_char(d.id)    as SOURCE_CODE,
    src.id           as source_id,
    i.id             as individu_id,
    etab.id          as etablissement_id
  from TMP_DOCTORANT d
    join SOURCE src on src.CODE = d.SOURCE_ID
    join INDIVIDU i on i.id = d.INDIVIDU_ID
    join ETABLISSEMENT etab on etab.code = d.ETABLISSEMENT_ID;

--DROP TABLE DOCTORANT;
create table DOCTORANT
(
  ID NUMBER constraint DOCTORANT_PK primary key,

  ETABLISSEMENT_ID NUMBER constraint DOCTORANT_ETAB_FK references ETABLISSEMENT on delete cascade,
  INDIVIDU_ID NUMBER constraint DOCTORANT_INDIV_FK references INDIVIDU on delete cascade,

  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null constraint DOCTORANT_SOURCE_FK references SOURCE on delete cascade,

  HISTO_CREATEUR_ID NUMBER not null,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER,
  HISTO_DESTRUCTION DATE,
  HISTO_MODIFICATEUR_ID NUMBER not null,
  HISTO_MODIFICATION DATE default SYSDATE not null
);

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

create or replace view SRC_THESE as
  select
    null id,
    to_char(t.id)            as SOURCE_CODE,
    src.ID                   as source_id,
    etab.id                  as etablissement_id,
    d.id                     as doctorant_id,
    null                     as ecole_doct_id,
    null                     as unite_rech_id,
    t.lib_ths                as titre,
    t.eta_ths                as etat_these,
    to_number(t.cod_neg_tre) as resultat,
    t.lib_int1_dis           as lib_disc,
    t.dat_deb_ths            as date_prem_insc,
    t.dat_prev_sou           as date_prev_soutenance,
    t.dat_sou_ths            as date_soutenance,
    t.dat_fin_cfd_ths        as date_fin_confid,
    t.lib_etab_cotut         as lib_etab_cotut,
    t.lib_pays_cotut         as lib_pays_cotut,
    t.correction_possible    as CORREC_AUTORISEE,
    tem_sou_aut_ths          as soutenance_autoris,
    dat_aut_sou_ths          as date_autoris_soutenance,
    tem_avenant_cotut        as tem_avenant_cotut
  from TMP_THESE t
    join SOURCE src on src.CODE = t.SOURCE_ID
    join ETABLISSEMENT etab on etab.code = t.ETABLISSEMENT_ID
    join DOCTORANT d ON d.ID = t.DOCTORANT_ID
;

--drop table THESE;
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

  HISTO_CREATEUR_ID NUMBER not null,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER,
  HISTO_DESTRUCTION DATE,
  HISTO_MODIFICATEUR_ID NUMBER not null,
  HISTO_MODIFICATION DATE default SYSDATE not null
);

-- create index THESE_ED_ID_INDEX on THESE (ECOLE_DOCT_ID);
-- create index THESE_UR_ID_INDEX on THESE (UNITE_RECH_ID);

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

create or replace view SRC_ROLE as
  select
    null id,
    to_char(r.id)   as SOURCE_CODE,
    src.ID          as source_id,
    etab.id         as etablissement_id,
    r.LIB_ROJ       as libelle_long,
    r.LIC_ROJ       as libelle_court
  from TMP_ROLE r
    join SOURCE src on src.CODE = r.SOURCE_ID
    join ETABLISSEMENT etab on etab.code = r.ETABLISSEMENT_ID
;

--drop table ROLE;
create table ROLE
(
  ID NUMBER NOT NULL CONSTRAINT ROLE_PK PRIMARY KEY,

  ETABLISSEMENT_ID NUMBER CONSTRAINT ROLE_ETAB_FK REFERENCES ETABLISSEMENT ON DELETE CASCADE,

  LIBELLE_LONG VARCHAR2(200 char) not null,
  LIBELLE_COURT VARCHAR2(50 char) not null,

  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null constraint ROLE_SOURCE_FK references SOURCE on delete cascade,

  HISTO_CREATEUR_ID NUMBER not null,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER,
  HISTO_DESTRUCTION DATE,
  HISTO_MODIFICATEUR_ID NUMBER not null,
  HISTO_MODIFICATION DATE default SYSDATE not null
);

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

--drop sequence UTILISATEUR_ID_SEQ;
create sequence UTILISATEUR_ID_SEQ;

--truncate table UTILISATEUR;
insert into UTILISATEUR(ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD)
    with ds (USERNAME, EMAIL, DISPLAY_NAME) as (
      select 'sygal-app', 'contact.sygal@unicaen.fr',          'SYGAL' from dual union all
      select 'bernardb',  'bruno.bernard@unicaen.fr',          'BB'    from dual union all
      select 'gauthierb', 'bertrand.gauthier@unicaen.fr',      'BG'    from dual union all
      select 'metivier',  'jean-philippe.metivier@unicaen.fr', 'JPM'   from dual
    )
  select UTILISATEUR_ID_SEQ.nextval, ds.USERNAME,  ds.EMAIL, ds.DISPLAY_NAME, 'ldap' from ds;


--------------------------- ROLE -----------------------

--delete from ROLE;
insert into ROLE(ID, ETABLISSEMENT_ID, LIBELLE_LONG, LIBELLE_COURT, SOURCE_CODE, SOURCE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
  with ds (LIBELLE_LONG, LIBELLE_COURT) as (
    SELECT 'Administrateur technique',   'Admin tech' from dual union all
    SELECT 'Administrateur',             'Admin'      from dual union all
    SELECT 'Bureau des doctorats',       'BdD'        from dual union all
    SELECT 'Bibliothèque universitaire', 'BU'         from dual
  )
    SELECT ROLE_ID_SEQ.nextval, etab.id, ds.LIBELLE_LONG, ds.LIBELLE_COURT, ds.LIBELLE_LONG, src.id, u.id, u.id
    FROM ds
      join ETABLISSEMENT etab on etab.CODE <> 'COMUE'
      join SOURCE src on src.CODE = 'SYGAL'
      join UTILISATEUR u on u.USERNAME = 'sygal-app'
;


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

create or replace view SRC_ACTEUR as
  select
    null id,
    to_char(a.id)       as SOURCE_CODE,
    src.ID              as SOURCE_ID,
    i.id                as INDIVIDU_ID,
    t.id                as THESE_ID,
    r.id                as ROLE_ID,
    a.LIB_CPS           as QUALITE,
    a.LIB_ETB           as ETABLISSEMENT,
    a.LIB_ROJ_COMPL     as LIB_ROLE_COMPL
  from TMP_ACTEUR a
    join SOURCE src on src.CODE = a.SOURCE_ID
    join INDIVIDU i on i.id = a.INDIVIDU_ID
    join THESE t on t.id = a.THESE_ID
    join ROLE r on r.id = a.ROLE_ID
;

--drop table ACTEUR;
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

  HISTO_CREATEUR_ID NUMBER not null,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER,
  HISTO_DESTRUCTION DATE,
  HISTO_MODIFICATEUR_ID NUMBER not null,
  HISTO_MODIFICATION DATE default SYSDATE not null
);


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

create or replace view SRC_VARIABLE as
  select
    null                  as id,
    to_char(v.id)         as SOURCE_CODE,
    src.ID                as SOURCE_ID,
    etab.id               as ETABLISSEMENT_ID,
    v.cod_vap,
    v.lib_vap,
    v.par_vap
  from TMP_VARIABLE v
    join SOURCE src on src.CODE = v.SOURCE_ID
    join ETABLISSEMENT etab on etab.code = v.ETABLISSEMENT_ID
;

--drop table VARIABLE;
create table VARIABLE
(
  ID NUMBER NOT NULL CONSTRAINT VARIABLE_PK PRIMARY KEY,

  ETABLISSEMENT_ID NUMBER CONSTRAINT VARIABLE_ETAB_FK REFERENCES ETABLISSEMENT ON DELETE CASCADE,

  DESCRIPTION VARCHAR2(300 char) not null,
  VALEUR VARCHAR2(200 char) not null,

  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null constraint VARIABLE_SOURCE_FK references SOURCE on delete cascade,

  HISTO_CREATEUR_ID NUMBER not null,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER,
  HISTO_DESTRUCTION DATE,
  HISTO_MODIFICATEUR_ID NUMBER not null,
  HISTO_MODIFICATION DATE default SYSDATE not null
);


--------------------------- INDIVIDU_ROLE -----------------------

create table INDIVIDU_ROLE
(
  ID NUMBER NOT NULL CONSTRAINT INDIVIDU_ROLE_PK PRIMARY KEY,

  INDIVIDU_ID NUMBER constraint INDIVIDU_ROLE_INDIV_FK references INDIVIDU on delete cascade,
  ROLE_ID NUMBER constraint INDIVIDU_ROLE_ROLE_FK references ROLE on delete cascade
);
