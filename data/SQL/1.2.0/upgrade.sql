
--
-- Index manquants
--
create index FICHIER_NATURE_ID_index on FICHIER (NATURE_ID ASC)
/
create index ACTEUR_INDIVIDU_ID_idx on ACTEUR (INDIVIDU_ID ASC)
/
create index ACTEUR_THESE_ID_idx on ACTEUR (THESE_ID ASC)
/
create index ACTEUR_ROLE_ID_idx on ACTEUR (ROLE_ID ASC)
/
create index ACTEUR_SOURCE_ID_idx on ACTEUR (SOURCE_ID ASC)
/
create index ACTEUR_HISTO_MODIF_ID_idx on ACTEUR (HISTO_MODIFICATEUR_ID ASC)
/
create index ACTEUR_HISTO_DESTRUCT_ID_idx on ACTEUR (HISTO_DESTRUCTEUR_ID ASC)
/
create index ACTEUR_ACTEUR_ETAB_ID_idx on ACTEUR (ACTEUR_ETABLISSEMENT_ID ASC)
/
create index ACTEUR_ETABLISSEMENT_ID_idx on THESE (ETABLISSEMENT_ID ASC)
/
create index ACTEUR_DOCTORANT_ID_idx on THESE (DOCTORANT_ID ASC)
/
create index ACTEUR_ECOLE_DOCT_ID_idx on THESE (ECOLE_DOCT_ID ASC)
/
create index ACTEUR_UNITE_RECH_ID_idx on THESE (UNITE_RECH_ID ASC)
/

--
-- Contraintes manquantes
--
alter table ACTEUR add constraint ACTEUR_ROLE_ID_fk foreign key (ROLE_ID) references ROLE on delete cascade
/







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
-- Nouvelles entrées dans NATURE_FICHIER
--
--INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (?, 'XXX_XXX', 'Bla bla');




/*
drop table FICHIER;
drop table FICHIER_THESE;
drop table VALIDITE_FICHIER;

alter table FICHIER_SAV rename to FICHIER ;
alter table FICHIER_THESE_SAV rename to FICHIER_THESE ;
alter table VALIDITE_FICHIER_SAV rename to VALIDITE_FICHIER ;
*/




--
-- Modif FICHIER : ID varchar => ID number
--

--
-- Cleanup
--
drop index FICHIER_HCFK_IDX;
drop index FICHIER_HDFK_IDX;
drop index FICHIER_HMFK_IDX;
drop index FICHIER_VERSION_FK_IDX;
drop index FICHIER_NATURE_ID_INDEX;
drop index FICHIER_THESE_FICHIER_ID_index;
drop index FICHIER_THESE_THESE_ID_index;
drop index VALIDITE_FICHIER_FICHIER_IDX;
drop index VALIDITE_FICHIER_HCFK_IDX;
drop index VALIDITE_FICHIER_HDFK_IDX;
drop index VALIDITE_FICHIER_HMFK_IDX;


--
-- FICHIER
--
alter table FICHIER rename to FICHIER_SAV
/
alter table FICHIER drop constraint FICHIER_VERSION_FK
/
alter table FICHIER drop constraint FICHIER_HCFK
/
alter table FICHIER drop constraint FICHIER_HMFK
/
alter table FICHIER drop constraint FICHIER_HDFK
/
alter table FICHIER drop constraint FICHIER_NATURE_FIC_ID_FK
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






--
--
-- AMÉLIORATION DU "MOTEUR" DE WORKFLOW.
--
--

--
-- Amélioration de certaines vues V_SITU_* : supression de jointures inutiles.
--
create or replace view V_SITU_AUTORIS_DIFF_THESE as
SELECT
    d.these_id,
    d.id AS diffusion_id
FROM DIFFUSION d
where d.HISTO_DESTRUCTEUR_ID is null
/
create or replace view V_SITU_RDV_BU_VALIDATION_BU as
SELECT
    v.these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
FROM VALIDATION v
         JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'RDV_BU'
where v.HISTO_DESTRUCTEUR_ID is null
/

create or replace view V_SITU_RDV_BU_SAISIE_DOCT as
SELECT
    r.these_id,
    CASE WHEN r.COORD_DOCTORANT IS NOT NULL AND r.DISPO_DOCTORANT IS NOT NULL
             THEN 1 ELSE 0 END ok
FROM RDV_BU r
/

create or replace view V_SITU_RDV_BU_SAISIE_BU as
SELECT
    r.these_id,
    CASE WHEN r.VERSION_ARCHIVABLE_FOURNIE = 1 and r.CONVENTION_MEL_SIGNEE = 1 and r.EXEMPL_PAPIER_FOURNI = 1
        and r.MOTS_CLES_RAMEAU is not null
             THEN 1 ELSE 0 END ok
FROM RDV_BU r
/

create or replace view V_SITU_SIGNALEMENT_THESE as
SELECT
    d.these_id,
    d.id AS description_id
FROM METADONNEE_THESE d
/

create or replace view V_SITU_ATTESTATIONS as
SELECT
    a.these_id,
    a.id AS attestation_id
FROM ATTESTATION a
where a.HISTO_DESTRUCTEUR_ID is null
/

create or replace view V_SITU_DEPOT_VC_VALID_DOCT as
SELECT
    v.these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
FROM VALIDATION v
         JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'DEPOT_THESE_CORRIGEE'
where v.HISTO_DESTRUCTEUR_ID is null
/

create or replace view V_SITU_DEPOT_VC_VALID_DIR as
    WITH validations_attendues AS (
        SELECT a.THESE_ID, a.INDIVIDU_ID, tv.ID as TYPE_VALIDATION_ID
        FROM ACTEUR a
                 JOIN ROLE r on a.ROLE_ID = r.ID and r.CODE = 'D' -- directeur de thèse
                 JOIN TYPE_VALIDATION tv on tv.code = 'CORRECTION_THESE'
        where a.HISTO_DESTRUCTION is null
    )
    SELECT
        ROWNUM as id,
        va.these_id,
        va.INDIVIDU_ID,
        CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
    FROM validations_attendues va
--              JOIN these t on va.THESE_ID = t.id
             LEFT JOIN VALIDATION v ON --v.THESE_ID = t.id and
                v.INDIVIDU_ID = va.INDIVIDU_ID and -- suppose que l'INDIVIDU_ID soit enregistré lors de la validation
                v.HISTO_DESTRUCTEUR_ID is null and
                v.TYPE_VALIDATION_ID = va.TYPE_VALIDATION_ID
/

create or replace view V_SITU_VERSION_PAPIER_CORRIGEE as
SELECT
    v.these_id,
    v.id as validation_id
FROM VALIDATION v
         JOIN TYPE_VALIDATION tv ON tv.ID = v.TYPE_VALIDATION_ID
WHERE tv.CODE='VERSION_PAPIER_CORRIGEE'
/

create or replace view V_SITU_VALIDATION_PAGE_COUV as
SELECT
    v.these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
FROM VALIDATION v
         JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'PAGE_DE_COUVERTURE'
where v.HISTO_DESTRUCTEUR_ID is null
/


--
-- Amélioration V_WORKFLOW : il devient inutile d'utiliser APP_WORKFLOW.ATTEIGNABLE() :-)
--
create or replace view V_WORKFLOW as
    SELECT
        ROWNUM as id,
        t.THESE_ID,
        t.ETAPE_ID,
        t.CODE,
        t.ORDRE,
        t.FRANCHIE,
        t.RESULTAT,
        t.OBJECTIF,
        -- NB: dans les 3 lignes suivantes, c'est la même expression 'dense_rank() over(...)' qui est répétée :
        (dense_rank() over(partition by t.THESE_ID, t.FRANCHIE order by t.ORDRE)) dense_rank,
        case when t.FRANCHIE = 1 or (dense_rank() over(partition by t.THESE_ID, t.FRANCHIE order by t.ORDRE)) = 1 then 1 else 0 end atteignable,
        case when (dense_rank() over(partition by t.THESE_ID, t.FRANCHIE order by t.ORDRE)) = 1 and t.FRANCHIE = 0 then 1 else 0 end courante
    FROM (

         --
         -- VALIDATION_PAGE_DE_COUVERTURE : franchie si version page de couverture validée
         --
         SELECT
             t.id AS    these_id,
             e.id AS    etape_id,
             e.code,
             e.ORDRE,
             CASE WHEN v.valide IS NULL THEN 0 ELSE 1 END franchie,
             CASE WHEN v.valide IS NULL THEN 0 ELSE 1 END resultat,
             1 objectif
         FROM these t
                  JOIN WF_ETAPE e ON e.code = 'VALIDATION_PAGE_DE_COUVERTURE'
                  LEFT JOIN V_SITU_VALIDATION_PAGE_COUV v ON v.these_id = t.id

         UNION ALL

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
             -- CASE WHEN v.THESE_ID IS NULL THEN
             --   0 -- test d'archivabilité inexistant
             -- ELSE
             --   CASE WHEN v.EST_VALIDE IS NULL THEN
             --     1 -- test d'archivabilité existant mais résultat indéterminé (plantage)
             --   ELSE
             --     CASE WHEN v.EST_VALIDE = 1 THEN
             --       1 -- test d'archivabilité réussi
             --     ELSE
             --       0 -- test d'archivabilité échoué
             --     END
             --   END
             -- END franchie,
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

         --    --
         --    -- RDV_BU_SAISIE_BU : franchie si données BU saisies
         --    --
         --    SELECT
         --      t.id AS                                                                          these_id,
         --      e.id AS                                                                          etape_id,
         --      e.code,
         --      e.ORDRE,
         --      coalesce(v.ok, 0)                                                                franchie,
         --      CASE WHEN rdv.MOTS_CLES_RAMEAU IS NULL THEN 0 ELSE 1 END +
         --      coalesce(rdv.VERSION_ARCHIVABLE_FOURNIE, 0) +
         --      coalesce(rdv.EXEMPL_PAPIER_FOURNI, 0) +
         --      coalesce(rdv.CONVENTION_MEL_SIGNEE, 0)                                           resultat,
         --      4                                                                                objectif
         --    FROM these t
         --      JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_BU'
         --      LEFT JOIN V_SITU_RDV_BU_SAISIE_BU v ON v.these_id = t.id
         --      LEFT JOIN RDV_BU rdv ON rdv.THESE_ID = t.id
         --
         -- UNION ALL

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
                   case when coalesce(v.resultat, 0) = v.objectif then 1 else 0 end franchie,
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

     ) t
     JOIN V_WF_ETAPE_PERTIN v ON t.these_id = v.these_id AND t.etape_id = v.etape_id
/

--
-- APP_WORKFLOW.ATTEIGNABLE() devient inutile, on le garde quand même un moment en souvenir.
--
create or replace PACKAGE APP_WORKFLOW AS

    /**
     * @deprecated depuis l'utilisation de 'dense_rank' pour calculer les témoins 'attegnable' et 'courant'
     * dans la vue V_WORKFLOW.
     */
    function atteignable(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC;

END APP_WORKFLOW;
/
create or replace PACKAGE BODY APP_WORKFLOW
AS

    /**
     * @deprecated depuis l'utilisation de 'dense_rank' pour calculer les témoins 'attegnable' et 'courant'
     * dans la vue V_WORKFLOW.
     */
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

END APP_WORKFLOW;
/




/*
select *
from V_WORKFLOW v2_
INNER JOIN THESE t1_ ON v2_.THESE_ID = t1_.ID
INNER JOIN WF_ETAPE w0_ ON v2_.ETAPE_ID = w0_.ID
where THESE_ID = 38307
order by THESE_ID, v2_.ORDRE
/


select *
from V_WORKFLOW v
JOIN THESE t ON v.THESE_ID = t.ID
join WF_ETAPE e on v.ETAPE_ID = e.id
where e.CODE = 'DEPOT_VERSION_ORIGINALE'
  and courante = 1
order by THESE_ID, v.ORDRE
/
*/
