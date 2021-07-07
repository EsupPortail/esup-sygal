--
-- ora2pg ne peut pas traduire cette fonction.
--
CREATE OR REPLACE FUNCTION str_reduce ( str text )
    RETURNS text
    LANGUAGE PLPGSQL
AS $body$
BEGIN
--     RETURN utl_raw.cast_to_varchar2(str COLLATE "binary_ai");
--     return unaccent_string(str);
    return lower(unaccent(str));
END;
$body$;



--
-- ora2pg ne traduit pas les trunc(DATE).
--
CREATE OR REPLACE FUNCTION comprise_entre(date_debut timestamp, date_fin timestamp, date_obs timestamp DEFAULT NULL,
                                          inclusif NUMERIC DEFAULT 0) RETURNS NUMERIC AS
$body$
DECLARE
    d_deb timestamp;
    d_fin timestamp;
    d_obs timestamp;
    res   NUMERIC;
BEGIN
    IF inclusif = 1 THEN
        d_obs := date_trunc('day', COALESCE(date_obs, current_timestamp));
        d_deb := date_trunc('day', COALESCE(date_debut, d_obs));
        d_fin := date_trunc('day', COALESCE(date_fin, d_obs));
        IF d_obs BETWEEN d_deb AND d_fin THEN
            RETURN 1;
        ELSE
            RETURN 0;
        END IF;
    ELSE
        d_obs := date_trunc('day', COALESCE(date_obs, current_timestamp));
        d_deb := date_trunc('day', date_debut);
        d_fin := date_trunc('day', date_fin);

        IF d_deb IS NOT NULL AND NOT d_deb <= d_obs THEN
            RETURN 0;
        END IF;
        IF d_fin IS NOT NULL AND NOT d_obs < d_fin THEN
            RETURN 0;
        END IF;
        RETURN 1;
    END IF;
END;
$body$
    LANGUAGE PLPGSQL
    SECURITY DEFINER
    STABLE;


--
-- Création à la main des VM mal traduites par ora2pg.
--

CREATE MATERIALIZED VIEW mv_recherche_these AS
with acteurs as (
    select a.these_id, i.nom_usuel, INDIVIDU_ID
    FROM individu i
             join acteur a on i.id = a.individu_id
             join these t on t.id = a.these_id
             join role r on a.role_id = r.id and r.CODE in ('D', 'K') -- (co)directeur de thèse
)
select
            LOCALTIMESTAMP as date_creation,
            t.source_code code_these,
            d.source_code code_doctorant,
            ed.source_code code_ecole_doct,
            trim(both str_reduce(
                        'code-ed{' || eds.code || '} ' ||
                        'code-ur{' || urs.code || '} ' ||
                        'titre{' || t.TITRE || '} ' ||
                        'doctorant-numero{' || substr(d.SOURCE_CODE, position('::' in d.SOURCE_CODE)+2) || '} ' ||
                        'doctorant-nom{' || id.NOM_PATRONYMIQUE || ' ' || id.NOM_USUEL || '} ' ||
                        'doctorant-prenom{' || id.PRENOM1 || '} ' ||
                        'directeur-nom{' || a.nom_usuel || '} '
                )) as haystack
from these t
         join doctorant d on d.id = t.doctorant_id
         join individu id on id.id = d.INDIVIDU_ID
         join these th on th.source_code = t.source_code
         left join ecole_doct ed on t.ecole_doct_id = ed.id
         left join structure eds on ed.STRUCTURE_ID = eds.id
         left join UNITE_RECH ur on t.UNITE_RECH_ID = ur.id
         left join structure urs on ur.STRUCTURE_ID = urs.id
         left join acteurs a on a.these_id = t.id
         left join individu ia on ia.id = a.INDIVIDU_ID;


CREATE MATERIALIZED VIEW mv_indicateur_141 AS
select distinct *
from individu i
where exists (
        select id
        from INDIVIDU_ROLE ir
        where i.ID = ir.INDIVIDU_ID
    )
  and i.HISTO_DESTRUCTION is not null;

CREATE MATERIALIZED VIEW mv_indicateur_2 AS
SELECT t.*
FROM THESE T
         LEFT JOIN VALIDATION V ON T.ID = V.THESE_ID
         LEFT JOIN TYPE_VALIDATION N on V.TYPE_VALIDATION_ID = N.ID
WHERE T.DATE_SOUTENANCE > LOCALTIMESTAMP - interval '2 month'
  AND T.ETAT_THESE = 'E'
  AND N.CODE = 'PAGE_DE_COUVERTURE'
  AND V.ID IS NULL;

CREATE MATERIALIZED VIEW mv_indicateur_3 AS
SELECT t.*
FROM THESE T
         LEFT JOIN fichier_these F on T.ID = F.THESE_ID
         LEFT JOIN fichier Fi on Fi.ID = F.fichier_id
         LEFT JOIN NATURE_FICHIER N on Fi.NATURE_ID = N.ID
WHERE T.DATE_SOUTENANCE > LOCALTIMESTAMP - interval '1 month'
  AND T.ETAT_THESE = 'E'
  AND N.CODE = 'THESE_PDF'
  AND F.ID IS NULL;

CREATE MATERIALIZED VIEW mv_indicateur_1 AS
SELECT THESE.ID ID,THESE.ETABLISSEMENT_ID ETABLISSEMENT_ID,THESE.DOCTORANT_ID DOCTORANT_ID,THESE.ECOLE_DOCT_ID ECOLE_DOCT_ID,
       THESE.UNITE_RECH_ID UNITE_RECH_ID,THESE.BESOIN_EXPURGE BESOIN_EXPURGE,THESE.COD_UNIT_RECH COD_UNIT_RECH,
       THESE.CORREC_AUTORISEE CORREC_AUTORISEE,THESE.DATE_AUTORIS_SOUTENANCE DATE_AUTORIS_SOUTENANCE,
       THESE.DATE_FIN_CONFID DATE_FIN_CONFID,THESE.DATE_PREM_INSC DATE_PREM_INSC,THESE.DATE_PREV_SOUTENANCE DATE_PREV_SOUTENANCE,
       THESE.DATE_SOUTENANCE DATE_SOUTENANCE,THESE.ETAT_THESE ETAT_THESE,THESE.LIB_DISC LIB_DISC,THESE.LIB_ETAB_COTUT LIB_ETAB_COTUT,
       THESE.LIB_PAYS_COTUT LIB_PAYS_COTUT,THESE.LIB_UNIT_RECH LIB_UNIT_RECH,THESE.RESULTAT RESULTAT,
       THESE.SOUTENANCE_AUTORIS SOUTENANCE_AUTORIS,THESE.TEM_AVENANT_COTUT TEM_AVENANT_COTUT,
       THESE.TITRE TITRE,THESE.SOURCE_CODE SOURCE_CODE,THESE.SOURCE_ID SOURCE_ID,
       THESE.HISTO_CREATEUR_ID HISTO_CREATEUR_ID,THESE.HISTO_CREATION HISTO_CREATION,
       THESE.HISTO_MODIFICATEUR_ID HISTO_MODIFICATEUR_ID,THESE.HISTO_MODIFICATION HISTO_MODIFICATION,
       THESE.HISTO_DESTRUCTEUR_ID HISTO_DESTRUCTEUR_ID,THESE.HISTO_DESTRUCTION HISTO_DESTRUCTION
FROM THESE THESE;

CREATE MATERIALIZED VIEW mv_indicateur_4 AS
SELECT *
FROM THESE t
WHERE t.ETAT_THESE = 'E'
  AND t.DATE_SOUTENANCE < LOCALTIMESTAMP;

CREATE MATERIALIZED VIEW mv_indicateur_5 AS
SELECT *
FROM THESE t
WHERE t.ETAT_THESE = 'E'
  AND t.DATE_SOUTENANCE > LOCALTIMESTAMP;

CREATE MATERIALIZED VIEW mv_indicateur_6 AS
SELECT *
FROM THESE t
WHERE t.ETAT_THESE = 'E'
  AND t.DATE_PREM_INSC < LOCALTIMESTAMP - interval '72 month';

CREATE MATERIALIZED VIEW mv_indicateur_7 AS
SELECT i.*
FROM THESE t
         JOIN DOCTORANT d ON d.ID = t.DOCTORANT_ID
         JOIN INDIVIDU I on d.INDIVIDU_ID = I.ID
WHERE i.TYPE='doctorant'
  AND t.ETAT_THESE = 'E'
  AND i.EMAIL is NULL;

CREATE MATERIALIZED VIEW mv_indicateur_21 AS
SELECT * FROM THESE WHERE ECOLE_DOCT_ID=(SELECT ID FROM ECOLE_DOCT WHERE SOURCE_CODE='UCN::497') AND ETAT_THESE='E';

CREATE MATERIALIZED VIEW mv_indicateur_61 AS
SELECT *
FROM STRUCTURE
WHERE ID IN (
    SELECT UNITE_RECH.STRUCTURE_ID
    FROM THESE
             LEFT JOIN UNITE_RECH ON THESE.UNITE_RECH_ID = UNITE_RECH.ID
    WHERE THESE.ETAT_THESE='E'
      AND UNITE_RECH.STRUCTURE_ID IS NOT NULL
    GROUP BY UNITE_RECH.STRUCTURE_ID)
  AND CHEMIN_LOGO IS NULL;

CREATE MATERIALIZED VIEW mv_indicateur_62 AS
SELECT *
FROM STRUCTURE
WHERE ID IN (
    SELECT ECOLE_DOCT.STRUCTURE_ID
    FROM THESE
             LEFT JOIN ECOLE_DOCT ON THESE.ECOLE_DOCT_ID = ECOLE_DOCT.ID
    WHERE THESE.ETAT_THESE='E'
      AND ECOLE_DOCT.STRUCTURE_ID IS NOT NULL
    GROUP BY ECOLE_DOCT.STRUCTURE_ID)
  AND CHEMIN_LOGO IS NULL;

CREATE MATERIALIZED VIEW mv_indicateur_81 AS
SELECT *
FROM THESE t
WHERE t.DATE_PREM_INSC <= LOCALTIMESTAMP - interval '60 month'
  AND t.ETAT_THESE = 'E'
  AND ID IN (
    SELECT THESE_ID
    FROM THESE_ANNEE_UNIV
             JOIN THESE T on THESE_ANNEE_UNIV.THESE_ID = t.ID
    WHERE t.ETAT_THESE = 'E'
    GROUP BY(THESE_ID)
    HAVING MAX(ANNEE_UNIV) <= extract(YEAR from (LOCALTIMESTAMP - interval '12 month'))
);

CREATE MATERIALIZED VIEW mv_indicateur_101 AS
select i.*
FROM DOCTORANT d
         join INDIVIDU i ON d.INDIVIDU_ID = i.ID
         left join THESE t ON d.ID = t.DOCTORANT_ID
where t.ETAT_THESE = 'E'
  and t.DATE_PREM_INSC < LOCALTIMESTAMP + interval '60 month'
  and t.ID IN (
    SELECT THESE_ID
    FROM THESE_ANNEE_UNIV
             JOIN THESE T on THESE_ANNEE_UNIV.THESE_ID = T.ID
    WHERE T.ETAT_THESE = 'E'
    GROUP BY(THESE_ID)
    HAVING MAX(ANNEE_UNIV) < extract(YEAR from (LOCALTIMESTAMP - interval '12 month'))
);

-- CREATE MATERIALIZED VIEW mv_indicateur_121 AS
---- Pas possible à ce stade donc déplacé
---- dans migration/docker/pg_after_import.sql (lancé manuellement après l'import)

