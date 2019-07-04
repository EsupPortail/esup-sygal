
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
-- Amélioration d'autres vues.
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

