--
-- Nouvelle table FICHIER_THESE
--
create table FICHIER_THESE
(
    ID NUMBER not null constraint FICHIER_THESE_PK primary key,
    FICHIER_ID VARCHAR2(40 char) not null constraint FICHIER_THESE_FICHIER_FK references FICHIER on delete cascade,
    THESE_ID NUMBER not null constraint FICHIER_THESE_THESE_FK references THESE on delete cascade,
    EST_ANNEXE NUMBER(1) default 0 not null,
    EST_EXPURGE NUMBER(1) default 0 not null,
    EST_CONFORME NUMBER(1),
    RETRAITEMENT VARCHAR2(50 char),
    EST_PARTIEL NUMBER(1) default 0 not null
)
/
create index FICHIER_THESE_FICHIER_ID_index on FICHIER_THESE (FICHIER_ID)
/
create index FICHIER_THESE_THESE_ID_index on FICHIER_THESE (THESE_ID)
/
create sequence FICHIER_THESE_ID_SEQ
/
insert into FICHIER_THESE(ID, FICHIER_ID, THESE_ID, EST_ANNEXE, EST_EXPURGE, EST_CONFORME, RETRAITEMENT, EST_PARTIEL)
SELECT FICHIER_THESE_ID_SEQ.nextval,
       ID,
       THESE_ID,
       EST_ANNEXE,
       EST_EXPURGE,
       EST_CONFORME,
       RETRAITEMENT,
       EST_PARTIEL
from FICHIER
/

--
-- Màj table FICHIER
--
alter table FICHIER drop column THESE_ID;
/
alter table FICHIER drop column EST_ANNEXE;
/
alter table FICHIER drop column EST_EXPURGE;
/
alter table FICHIER drop column EST_CONFORME;
/
alter table FICHIER drop column RETRAITEMENT;
/
alter table FICHIER drop column EST_PARTIEL;
/

--
-- Corrections des vues.
--
create or replace view V_SITU_ARCHIVAB_VA as
SELECT
    ft.these_id,
    ft.RETRAITEMENT,
    vf.EST_VALIDE
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
         JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
where EST_ANNEXE = 0 AND EST_EXPURGE = 0
/

create or replace view V_SITU_ARCHIVAB_VAC as
SELECT
    ft.these_id,
    ft.RETRAITEMENT,
    vf.EST_VALIDE
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
         JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
where EST_ANNEXE = 0 AND EST_EXPURGE = 0
/

create or replace view V_SITU_ARCHIVAB_VO as
SELECT
    ft.these_id,
    vf.EST_VALIDE
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VO'
         JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
where EST_ANNEXE = 0 AND EST_EXPURGE = 0
/

create or replace view V_SITU_ARCHIVAB_VOC as
SELECT
    ft.these_id,
    vf.EST_VALIDE
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
         JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
where EST_ANNEXE = 0 AND EST_EXPURGE = 0
/

create or replace view V_SITU_ATTESTATIONS_VOC as
SELECT
    a.these_id,
    a.id AS attestation_id
FROM ATTESTATION a
         -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
         JOIN FICHIER_THESE ft ON ft.THESE_ID = a.THESE_ID AND EST_ANNEXE = 0 AND EST_EXPURGE = 0
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
where a.HISTO_DESTRUCTEUR_ID is null
/

create or replace view V_SITU_AUTORIS_DIFF_THESE_VOC as
SELECT
    d.these_id,
    d.id AS diffusion_id
FROM DIFFUSION d
         -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
         JOIN FICHIER_THESE ft ON ft.THESE_ID = d.THESE_ID AND EST_ANNEXE = 0 AND EST_EXPURGE = 0
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
where d.HISTO_DESTRUCTEUR_ID is null
/

create or replace view V_SITU_DEPOT_PV_SOUT as
SELECT
    ft.these_id,
    f.id AS fichier_id
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN NATURE_FICHIER nf on f.NATURE_ID = nf.id and nf.CODE = 'PV_SOUTENANCE'
/

create or replace view V_SITU_DEPOT_RAPPORT_SOUT as
SELECT
    ft.these_id,
    f.id AS fichier_id
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN NATURE_FICHIER nf on f.NATURE_ID = nf.id and nf.CODE = 'RAPPORT_SOUTENANCE'
/

create or replace view V_SITU_DEPOT_VA as
SELECT
    ft.these_id,
    f.id AS fichier_id
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
where EST_ANNEXE = 0 AND EST_EXPURGE = 0
/

create or replace view V_SITU_DEPOT_VAC as
SELECT
    ft.these_id,
    f.id AS fichier_id
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
where EST_ANNEXE = 0 AND EST_EXPURGE = 0
/

create or replace view V_SITU_DEPOT_VO as
SELECT
    ft.these_id,
    f.id AS fichier_id
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
         JOIN VERSION_FICHIER vf ON f.VERSION_FICHIER_ID = vf.ID AND vf.CODE = 'VO'
where EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND RETRAITEMENT IS NULL
/

create or replace view V_SITU_DEPOT_VOC as
SELECT
    ft.these_id,
    f.id AS fichier_id
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
         JOIN VERSION_FICHIER vf ON f.VERSION_FICHIER_ID = vf.ID AND vf.CODE = 'VOC'
where EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND RETRAITEMENT IS NULL
/

create or replace view V_SITU_VERIF_VA as
SELECT
    ft.these_id,
    ft.EST_CONFORME
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
where EST_ANNEXE = 0 AND EST_EXPURGE = 0
/

create or replace view V_SITU_VERIF_VAC as
SELECT
    ft.these_id,
    ft.EST_CONFORME
FROM FICHIER_THESE ft
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
where EST_ANNEXE = 0 AND EST_EXPURGE = 0
/


--
-- Modif FICHIER : ID varchar => ID number
--

--
-- Cleanup
--
drop index FICHIER_HCFK_IDX
/
drop index FICHIER_HDFK_IDX
/
drop index FICHIER_HMFK_IDX
/
drop index FICHIER_VERSION_FK_IDX
/
drop index FICHIER_NATURE_ID_INDEX
/
drop index FICHIER_THESE_FICHIER_ID_index
/
drop index FICHIER_THESE_THESE_ID_index
/
drop index VALIDITE_FICHIER_FICHIER_IDX
/
drop index VALIDITE_FICHIER_HCFK_IDX
/
drop index VALIDITE_FICHIER_HDFK_IDX
/
drop index VALIDITE_FICHIER_HMFK_IDX
/

--
-- FICHIER
--
alter table FICHIER rename to FICHIER_SAV
/
alter table FICHIER_THESE drop constraint FICHIER_THESE_PK
/
alter table FICHIER_THESE drop constraint FICHIER_THESE_FICHIER_FK
/
alter table FICHIER_THESE drop constraint FICHIER_THESE_THESE_FK
/
alter table VALIDITE_FICHIER drop constraint CONFORMITE_FICHIER_PK
/
alter table VALIDITE_FICHIER drop constraint CONFORMITE_FICHIER_FFK
/
alter table VALIDITE_FICHIER drop constraint VALIDITE_FICHIER_HCFK
/
alter table VALIDITE_FICHIER drop constraint VALIDITE_FICHIER_HMFK
/
alter table VALIDITE_FICHIER drop constraint VALIDITE_FICHIER_HDFK
/
--drop table FICHIER
create table FICHIER
(
    ID NUMBER not null constraint FICHIER_PK_NEW primary key,
    UUID varchar2(40) not null,
    NATURE_ID NUMBER default 1 not null constraint FICHIER_NATURE_FIC_ID_FK references NATURE_FICHIER,
    NOM VARCHAR2(255 char) not null,
    NOM_ORIGINAL VARCHAR2(255 char) default NULL not null,
    TYPE_MIME VARCHAR2(128 char) not null,
    TAILLE NUMBER not null,
    DESCRIPTION VARCHAR2(256 char),
    VERSION_FICHIER_ID NUMBER not null constraint FICHIER_VERSION_FK references VERSION_FICHIER on delete cascade,
    HISTO_CREATION DATE default SYSDATE not null,
    HISTO_CREATEUR_ID NUMBER not null constraint FICHIER_HCFK references UTILISATEUR,
    HISTO_MODIFICATION DATE default SYSDATE not null,
    HISTO_MODIFICATEUR_ID NUMBER not null constraint FICHIER_HMFK references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER constraint FICHIER_HDFK references UTILISATEUR
)
/
create unique index FICHIER_UUID_un on FICHIER (UUID)
/
create index FICHIER_HCFK_IDX on FICHIER (HISTO_CREATEUR_ID)
/
create index FICHIER_HDFK_IDX on FICHIER (HISTO_DESTRUCTEUR_ID)
/
create index FICHIER_HMFK_IDX on FICHIER (HISTO_MODIFICATEUR_ID)
/
create index FICHIER_VERSION_FK_IDX on FICHIER (VERSION_FICHIER_ID)
/
create index FICHIER_NATURE_ID_INDEX on FICHIER (NATURE_ID)
/
create sequence FICHIER_id_seq
/
insert into FICHIER (
    ID,
    UUID,
    NOM ,
    TYPE_MIME ,
    TAILLE ,
    DESCRIPTION,
    VERSION_FICHIER_ID,
    HISTO_CREATION ,
    HISTO_CREATEUR_ID,
    HISTO_MODIFICATION,
    HISTO_MODIFICATEUR_ID,
    HISTO_DESTRUCTION ,
    HISTO_DESTRUCTEUR_ID,
    NOM_ORIGINAL ,
    NATURE_ID
)
select
    FICHIER_id_seq.nextval as ID,
    ID as UUID,
    NOM ,
    TYPE_MIME ,
    TAILLE ,
    DESCRIPTION,
    VERSION_FICHIER_ID,
    HISTO_CREATION ,
    HISTO_CREATEUR_ID,
    HISTO_MODIFICATION,
    HISTO_MODIFICATEUR_ID,
    HISTO_DESTRUCTION ,
    HISTO_DESTRUCTEUR_ID,
    NOM_ORIGINAL ,
    NATURE_ID
from FICHIER_SAV
/
--
-- FICHIER_THESE
--
alter table FICHIER_THESE rename to FICHIER_THESE_SAV
/
--drop table FICHIER_THESE;
create table FICHIER_THESE
(
    ID NUMBER not null constraint FICHIER_THESE_PK_NEW primary key,
    FICHIER_ID NUMBER not null constraint FICHIER_THESE_FICHIER_FK references FICHIER on delete cascade,
    THESE_ID NUMBER not null constraint FICHIER_THESE_THESE_FK references THESE on delete cascade,
    EST_ANNEXE NUMBER(1) default 0 not null,
    EST_EXPURGE NUMBER(1) default 0 not null,
    EST_CONFORME NUMBER(1),
    RETRAITEMENT VARCHAR2(50 char),
    EST_PARTIEL NUMBER(1) default 0 not null
)
/
create index FICHIER_THESE_FICH_ID_idx on FICHIER_THESE (FICHIER_ID)
/
create index FICHIER_THESE_THESE_ID_idx on FICHIER_THESE (THESE_ID)
/
insert into FICHIER_THESE(
    ID,
    FICHIER_ID,
    THESE_ID,
    EST_ANNEXE,
    EST_EXPURGE,
    EST_CONFORME,
    RETRAITEMENT,
    EST_PARTIEL
)
select fts.ID,
       f.ID,
       THESE_ID,
       EST_ANNEXE,
       EST_EXPURGE,
       EST_CONFORME,
       RETRAITEMENT,
       EST_PARTIEL
from FICHIER_THESE_SAV fts
join fichier f on f.UUID = fts.FICHIER_ID
/

--
-- VALIDITE_FICHIER
--
alter table VALIDITE_FICHIER rename to VALIDITE_FICHIER_SAV
/
--drop table VALIDITE_FICHIER;
create table VALIDITE_FICHIER
(
    ID NUMBER not null constraint VALIDITE_FICHIER_PK primary key,
    FICHIER_ID NUMBER not null constraint VALIDITE_FICHIER_FFK references FICHIER on delete cascade,
    EST_VALIDE VARCHAR2(1 char) default NULL,
    MESSAGE CLOB default NULL,
    LOG CLOB,
    HISTO_CREATEUR_ID NUMBER not null constraint VALIDITE_FICHIER_HCFK references UTILISATEUR,
    HISTO_MODIFICATEUR_ID NUMBER not null constraint VALIDITE_FICHIER_HMFK references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER constraint VALIDITE_FICHIER_HDFK references UTILISATEUR,
    HISTO_CREATION DATE default SYSDATE not null,
    HISTO_MODIFICATION DATE default SYSDATE not null
)
/
create index VALIDITE_FICHIER_FICHIER_IDX on VALIDITE_FICHIER (FICHIER_ID)
/
create index VALIDITE_FICHIER_HCFK_IDX on VALIDITE_FICHIER (HISTO_CREATEUR_ID)
/
create index VALIDITE_FICHIER_HDFK_IDX on VALIDITE_FICHIER (HISTO_DESTRUCTEUR_ID)
/
create index VALIDITE_FICHIER_HMFK_IDX on VALIDITE_FICHIER (HISTO_MODIFICATEUR_ID)
/
insert into VALIDITE_FICHIER (
    ID,
    FICHIER_ID,
    EST_VALIDE,
    MESSAGE,
    LOG,
    HISTO_CREATEUR_ID,
    HISTO_MODIFICATEUR_ID,
    HISTO_DESTRUCTION,
    HISTO_DESTRUCTEUR_ID,
    HISTO_CREATION,
    HISTO_MODIFICATION
)
select
    vfs.ID,
    f.ID,
    EST_VALIDE,
    MESSAGE,
    LOG,
    vfs.HISTO_CREATEUR_ID,
    vfs.HISTO_MODIFICATEUR_ID,
    vfs.HISTO_DESTRUCTION,
    vfs.HISTO_DESTRUCTEUR_ID,
    vfs.HISTO_CREATION,
    vfs.HISTO_MODIFICATION
from VALIDITE_FICHIER_SAV vfs
join FICHIER f on vfs.FICHIER_ID = f.UUID
/
