-----------------------------------------------------------------
--                      IMPORT DES STRUCTURES                  --
-----------------------------------------------------------------


---------------------------------- STRUCTURE ---------------------------------

--
-- Table destination import par ws : TMP_STRUCTURE.
--
--drop table TMP_STRUCTURE;
create table TMP_STRUCTURE
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,
  TYPE_STRUCTURE_ID VARCHAR2(64) not null,
  SIGLE VARCHAR2(64),
  LIBELLE VARCHAR2(200) not null,
  CODE_PAYS VARCHAR2(64),
  LIBELLE_PAYS VARCHAR2(200)
)
/
create index TMP_STRUCTURE_SOURCE_CODE_IDX on TMP_STRUCTURE (SOURCE_CODE)
/
create index TMP_STRUCTURE_SOURCE_ID_IDX on TMP_STRUCTURE (SOURCE_ID)
/
create index TMP_STRUCTURE_TYPE_ID_IDX on TMP_STRUCTURE (TYPE_STRUCTURE_ID)
/

--
-- Vue source import insa-rouen : SRC_STRUCTURE.
--
create or replace view SRC_STRUCTURE as
  SELECT
    NULL              AS id,
    tmp.SOURCE_CODE   as SOURCE_CODE,
    src.id            AS SOURCE_ID,
    ts.id             as TYPE_STRUCTURE_ID,
    tmp.SIGLE,
    tmp.LIBELLE,
    tmp.CODE_PAYS,
    tmp.LIBELLE_PAYS
  FROM TMP_STRUCTURE tmp
    JOIN TYPE_STRUCTURE ts on ts.CODE = tmp.TYPE_STRUCTURE_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

--
-- AJout histo, source à la table STRUCTURE.
--
alter TABLE STRUCTURE ADD HISTO_CREATION        DATE DEFAULT SYSDATE not null;
alter TABLE STRUCTURE ADD HISTO_CREATEUR_ID     NUMBER(38, 0) /*not null*/;
alter TABLE STRUCTURE ADD HISTO_MODIFICATION    DATE DEFAULT SYSDATE  not null;
alter TABLE STRUCTURE ADD HISTO_MODIFICATEUR_ID NUMBER(38, 0) /*not null*/;
alter TABLE STRUCTURE ADD HISTO_DESTRUCTION     DATE;
alter TABLE STRUCTURE ADD HISTO_DESTRUCTEUR_ID  NUMBER(38, 0);
ALTER TABLE STRUCTURE ADD CONSTRAINT STRUCTURE_HCFK FOREIGN KEY (HISTO_CREATEUR_ID) REFERENCES UTILISATEUR (ID);
ALTER TABLE STRUCTURE ADD CONSTRAINT STRUCTURE_HDFK FOREIGN KEY (HISTO_DESTRUCTEUR_ID) REFERENCES UTILISATEUR (ID);
ALTER TABLE STRUCTURE ADD CONSTRAINT STRUCTURE_HMFK FOREIGN KEY (HISTO_MODIFICATEUR_ID) REFERENCES UTILISATEUR (ID);

alter TABLE STRUCTURE ADD SOURCE_ID   NUMBER(38, 0) /*not null*/;
alter TABLE STRUCTURE ADD SOURCE_CODE VARCHAR2(64 CHAR) /*not null*/;
ALTER TABLE STRUCTURE ADD CONSTRAINT STRUCTURE_SOURCE_FK FOREIGN KEY (SOURCE_ID) REFERENCES SOURCE (ID);

update structure s set SOURCE_ID = (
  select id from source where code = 'UCN::apogee'
), SOURCE_CODE = (
    select SOURCE_CODE from ETABLISSEMENT where STRUCTURE_ID = s.id union
    select SOURCE_CODE from ECOLE_DOCT    where STRUCTURE_ID = s.id union
    select SOURCE_CODE from UNITE_RECH    where STRUCTURE_ID = s.id
)
;
update structure set HISTO_CREATEUR_ID = (
  select id from UTILISATEUR where USERNAME = 'sygal-app'
), HISTO_MODIFICATEUR_ID = (
  select id from UTILISATEUR where USERNAME = 'sygal-app'
)
;

update structure s0 set TYPE_STRUCTURE_ID = (
  select ts1.id from TYPE_STRUCTURE ts1
)
where s0.id = (select id from ETABLISSEMENT e where e.STRUCTURE_ID = s0.id)
;


alter TABLE STRUCTURE modify HISTO_CREATEUR_ID     not null;
alter TABLE STRUCTURE modify HISTO_MODIFICATEUR_ID not null;
alter TABLE STRUCTURE modify SOURCE_ID   not null;
alter TABLE STRUCTURE modify SOURCE_CODE not null;



---------------------------------- ECOLE_DOCT ---------------------------------

--
-- Table destination import par ws : TMP_ECOLE_DOCT.
--
--drop table TMP_ECOLE_DOCT;
create table TMP_ECOLE_DOCT
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64) not null,
  STRUCTURE_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null
)
/
create index TMP_ECOLE_DOCT_SOURCE_CODE_IDX on TMP_ECOLE_DOCT (SOURCE_CODE)
/
create index TMP_ECOLE_DOCT_SOURCE_ID_IDX on TMP_ECOLE_DOCT (SOURCE_ID)
/
create index TMP_ECOLE_DOCT_STRUCT_ID_IDX on TMP_ECOLE_DOCT (STRUCTURE_ID)
/
create unique index TMP_ECOLE_DOCT_UNIQ on TMP_ECOLE_DOCT (ID, STRUCTURE_ID)
/

--
-- Vue source import insa-rouen : SRC_ECOLE_DOCT.
--
create or replace view SRC_ECOLE_DOCT as
  SELECT
    NULL              AS id,
    tmp.SOURCE_CODE   as SOURCE_CODE,
    src.id            AS SOURCE_ID,
    s.ID              as STRUCTURE_ID
  FROM TMP_ECOLE_DOCT tmp
    JOIN STRUCTURE s on s.SOURCE_CODE = tmp.STRUCTURE_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

--
-- AJout source à la table ECOLE_DOCT.
--
update ECOLE_DOCT set SOURCE_ID = (
  select id from source where code = 'UCN::apogee'
), SOURCE_CODE = 'UCN::' || SOURCE_CODE
;



---------------------------------- UNITE_RECH ---------------------------------

--
-- Table destination import par ws : TMP_UNITE_RECH.
--
--drop table TMP_UNITE_RECH;
create table TMP_UNITE_RECH
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64) not null,
  STRUCTURE_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null
)
/
create index TMP_UNITE_RECH_SOURCE_CODE_IDX on TMP_UNITE_RECH (SOURCE_CODE)
/
create index TMP_UNITE_RECH_SOURCE_ID_IDX on TMP_UNITE_RECH (SOURCE_ID)
/
create index TMP_UNITE_RECH_STRUCT_ID_IDX on TMP_UNITE_RECH (STRUCTURE_ID)
/
create unique index TMP_UNITE_RECH_UNIQ on TMP_UNITE_RECH (ID, STRUCTURE_ID)
/

--
-- Vue source import insa-rouen : SRC_UNITE_RECH.
--
create or replace view SRC_UNITE_RECH as
  SELECT
    NULL              AS id,
    tmp.SOURCE_CODE   as SOURCE_CODE,
    src.id            AS SOURCE_ID,
    s.ID              as STRUCTURE_ID
  FROM TMP_UNITE_RECH tmp
    JOIN STRUCTURE s on s.SOURCE_CODE = tmp.STRUCTURE_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

--
-- AJout source à la table UNITE_RECH.
--
update UNITE_RECH set SOURCE_ID = (
  select id from source where code = 'UCN::apogee'
), SOURCE_CODE = 'UCN::' || SOURCE_CODE
;


---------------------------------- ETAB ---------------------------------

--
-- Table destination import par ws : TMP_ETABLISSEMENT.
--
--drop table TMP_ETABLISSEMENT;
create table TMP_ETABLISSEMENT
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64) not null,
  STRUCTURE_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null
)
/
create index TMP_ETAB_SOURCE_CODE_IDX on TMP_ETABLISSEMENT (SOURCE_CODE)
/
create index TMP_ETAB_SOURCE_ID_IDX on TMP_ETABLISSEMENT (SOURCE_ID)
/
create index TMP_ETAB_STRUCT_ID_IDX on TMP_ETABLISSEMENT (STRUCTURE_ID)
/
create unique index TMP_ETAB_UNIQ on TMP_ETABLISSEMENT (ID, STRUCTURE_ID)
/

--
-- Vue source import insa-rouen : SRC_ETABLISSEMENT.
--
--drop view SRC_ETABLISSEMENT;
create or replace view SRC_ETABLISSEMENT as
  SELECT
    NULL              AS id,
    tmp.SOURCE_CODE   as SOURCE_CODE,
    tmp.SOURCE_CODE   as CODE,
    src.id            AS SOURCE_ID,
    s.ID              as STRUCTURE_ID
  FROM TMP_ETABLISSEMENT tmp
    JOIN STRUCTURE s on s.SOURCE_CODE = tmp.STRUCTURE_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

--
-- AJout source à la table ETABLISSEMENT.
--
alter TABLE ETABLISSEMENT ADD SOURCE_ID   NUMBER(38, 0) /*not null*/;
alter TABLE ETABLISSEMENT ADD SOURCE_CODE VARCHAR2(64 CHAR);
ALTER TABLE ETABLISSEMENT ADD CONSTRAINT ETABLISSEMENT_SOURCE_FK FOREIGN KEY (SOURCE_ID) REFERENCES SOURCE (ID);

update ETABLISSEMENT set SOURCE_ID = (
  select id from source where code = 'UCN::apogee'
), SOURCE_CODE = 'UCN::' || ID
;
update ETABLISSEMENT set HISTO_CREATEUR_ID = (
  select id from UTILISATEUR where USERNAME = 'sygal-app'
), HISTO_MODIFICATEUR_ID = (
  select id from UTILISATEUR where USERNAME = 'sygal-app'
)
;

alter TABLE ETABLISSEMENT modify HISTO_CREATEUR_ID     not null;
alter TABLE ETABLISSEMENT modify HISTO_MODIFICATEUR_ID not null;
alter TABLE ETABLISSEMENT modify SOURCE_ID   not null;
alter TABLE ETABLISSEMENT modify SOURCE_CODE not null;




---------------------------------- STRUCTURE_SUBSTIT ---------------------------------

create table STRUCTURE_SUBSTIT (
  ID NUMBER not null constraint STR_SUBSTIT_PK primary key,
  FROM_STRUCTURE_ID NUMBER not null constraint STR_SUBSTIT_STR_FROM_FK references STRUCTURE,
  TO_STRUCTURE_ID   NUMBER not null constraint STR_SUBSTIT_STR_TO_FK   references STRUCTURE,
  HISTO_CREATION     DATE default SYSDATE not null,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTION  DATE,
  HISTO_CREATEUR_ID     NUMBER constraint STR_SUBSTIT_CREATEUR_FK     references UTILISATEUR,
  HISTO_MODIFICATEUR_ID NUMBER constraint STR_SUBSTIT_MODIFICATEUR_FK references UTILISATEUR,
  HISTO_DESTRUCTEUR_ID  NUMBER constraint STR_SUBSTIT_DESTRUCTEUR_FK  references UTILISATEUR
)
/
create index STR_SUBSTIT_STR_FROM_IDX on STRUCTURE_SUBSTIT (FROM_STRUCTURE_ID)
/
create index STR_SUBSTIT_STR_TO_IDX on STRUCTURE_SUBSTIT (TO_STRUCTURE_ID)
/
create unique index STR_SUBSTIT_UNIQUE on STRUCTURE_SUBSTIT (FROM_STRUCTURE_ID, TO_STRUCTURE_ID)
/

create sequence STRUCTURE_SUBSTIT_ID_SEQ;




--
-- SRC_THESE : ajout jointure avec STRUCTURE_SUBSTIT.
--
create or replace view SRC_THESE as
  SELECT
    NULL                            AS id,
    tmp.SOURCE_CODE                 AS SOURCE_CODE,
    src.ID                          AS source_id,
    e.id                            AS etablissement_id,
    d.id                            AS doctorant_id,
    coalesce(ed_substit.id, ed.id)  AS ecole_doct_id,
    coalesce(ur_substit.id, ur.id)  AS unite_rech_id,
--     ed.id  AS ecole_doct_id,
--     ur.id  AS unite_rech_id,
    ed.id                           AS ecole_doct_id_orig,
    ur.id                           AS unite_rech_id_orig,
    tmp.lib_ths                     AS titre,
    tmp.eta_ths                     AS etat_these,
    to_number(tmp.cod_neg_tre)      AS resultat,
    tmp.lib_int1_dis                AS lib_disc,
    tmp.dat_deb_ths                 AS date_prem_insc,
    tmp.dat_prev_sou                AS date_prev_soutenance,
    tmp.dat_sou_ths                 AS date_soutenance,
    tmp.dat_fin_cfd_ths             AS date_fin_confid,
    tmp.lib_etab_cotut              AS lib_etab_cotut,
    tmp.lib_pays_cotut              AS lib_pays_cotut,
    tmp.correction_possible         AS CORREC_AUTORISEE,
    tem_sou_aut_ths                 AS soutenance_autoris,
    dat_aut_sou_ths                 AS date_autoris_soutenance,
    tem_avenant_cotut               AS tem_avenant_cotut
  FROM TMP_THESE tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
    JOIN DOCTORANT d ON d.SOURCE_CODE = tmp.DOCTORANT_ID

    LEFT JOIN ECOLE_DOCT ed ON ed.SOURCE_CODE = tmp.ECOLE_DOCT_ID
    LEFT JOIN UNITE_RECH ur ON ur.SOURCE_CODE = tmp.UNITE_RECH_ID

    LEFT JOIN STRUCTURE_SUBSTIT ss_ed on ss_ed.FROM_STRUCTURE_ID = ed.STRUCTURE_ID
    LEFT JOIN ECOLE_DOCT ed_substit on ed_substit.STRUCTURE_ID = ss_ed.TO_STRUCTURE_ID

    LEFT JOIN STRUCTURE_SUBSTIT ss_ur on ss_ur.FROM_STRUCTURE_ID = ur.STRUCTURE_ID
    LEFT JOIN UNITE_RECH ur_substit on ur_substit.STRUCTURE_ID = ss_ur.TO_STRUCTURE_ID
/

-- 'UCN::UMR6554' remplacée par 'UCN::EA9999'
truncate table STRUCTURE_SUBSTIT;
insert into STRUCTURE_SUBSTIT(ID, FROM_STRUCTURE_ID, TO_STRUCTURE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
  select STRUCTURE_SUBSTIT_ID_SEQ.nextval, ur_from.structure_id, ur_to.structure_id, 1, 1
  from UNITE_RECH ur_from, UNITE_RECH ur_to
  where ur_from.SOURCE_CODE = 'UCN::UMR6554' and
        ur_to.SOURCE_CODE   = 'UCN::EA9999'
;
