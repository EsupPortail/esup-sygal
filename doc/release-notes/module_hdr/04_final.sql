-------------------- validations --------------------------
-- Mise à jour de l'état des propositions n'ayant pas encore de validation
UPDATE soutenance_proposition sp
SET etat_id = (
    SELECT id FROM soutenance_etat
    where code = 'EN_COURS_SAISIE'
)
WHERE NOT EXISTS (
    SELECT 1
    FROM validation_these vt
    WHERE vt.these_id = sp.these_id
)
  and type = 'SOUTENANCE_THESE_PROPOSITION'
  and etat_id = (
    SELECT id FROM soutenance_etat
    where code = 'EN_COURS_EXAMEN'
);

create or replace view v_situ_depot_vc_valid_doct(these_id, valide) as
SELECT vt.these_id,
       CASE
           WHEN v.id IS NOT NULL THEN 1
           ELSE 0
           END AS valide
FROM validation_these vt
         join validation v on v.id = vt.validation_id
         JOIN type_validation tv ON v.type_validation_id = tv.id AND tv.code::text = 'DEPOT_THESE_CORRIGEE'::text
WHERE v.histo_destructeur_id IS NULL;

create or replace view v_situ_depot_vc_valid_pres(id, these_id, individu_id, valide) as
WITH validations_attendues AS (
    SELECT a.these_id,
           a.individu_id,
           tv.id AS type_validation_id
    FROM acteur_these a
             JOIN role r ON a.role_id = r.id AND r.code::text = 'P'::text
             JOIN type_validation tv ON tv.code::text = 'CORRECTION_THESE'::text
    WHERE a.histo_destruction IS NULL
), validations_dt_existantes AS (
    SELECT DISTINCT vt.these_id,
                    tv.id AS type_validation_id
    FROM validation_these vt
             join validation v on v.id = vt.validation_id
             JOIN type_validation tv ON v.type_validation_id = tv.id AND tv.code::text = 'CORRECTION_THESE'::text
             JOIN acteur_these a ON vt.these_id = a.these_id AND vt.individu_id = a.individu_id AND a.histo_destructeur_id IS NULL
             JOIN role r ON a.role_id = r.id AND r.code::text = 'D'::text
    WHERE vt.histo_destructeur_id IS NULL
)
SELECT (va.these_id || '_'::text) || va.individu_id AS id,
       va.these_id,
       va.individu_id,
       CASE
           WHEN v.id IS NOT NULL OR vdte.these_id IS NOT NULL THEN 1
           ELSE 0
           END AS valide
FROM validations_attendues va
         LEFT JOIN validation_these vt ON vt.these_id = va.these_id AND vt.individu_id = va.individu_id AND vt.histo_destructeur_id IS NULL
         left join validation v on v.id = vt.validation_id AND v.type_validation_id = va.type_validation_id
         LEFT JOIN validations_dt_existantes vdte ON vdte.these_id = va.these_id AND vdte.type_validation_id = va.type_validation_id;


create or replace view v_situ_depot_vc_valid_pres_new(id, these_id, individu_id, valide) as
WITH validations_attendues AS (
    SELECT a.these_id,
           a.individu_id,
           tv.id AS type_validation_id
    FROM acteur_these a
             JOIN role r ON a.role_id = r.id AND r.code::text = 'P'::text
             JOIN type_validation tv ON tv.code::text = 'CORRECTION_THESE'::text
    WHERE a.histo_destruction IS NULL AND NOT (EXISTS (
        SELECT vt.id,
               v.type_validation_id,
               vt.these_id,
               vt.individu_id,
               vt.histo_creation,
               vt.histo_createur_id,
               vt.histo_modification,
               vt.histo_modificateur_id,
               vt.histo_destruction,
               vt.histo_destructeur_id,
               tv.id,
               tv.code,
               tv.libelle
        FROM validation_these vt
                 join validation v on v.id = vt.validation_id
                 JOIN type_validation tv ON v.type_validation_id = tv.id AND tv.code::text = 'CORRECTION_THESE'::text
        WHERE vt.these_id = a.these_id AND v.type_validation_id = tv.id AND vt.histo_destruction IS NULL))
)
SELECT (va.these_id || '_'::text) || va.individu_id AS id,
       va.these_id,
       va.individu_id,
       CASE
           WHEN v.id IS NOT NULL THEN 1
           ELSE 0
           END AS valide
FROM validations_attendues va
         LEFT JOIN validation_these vt ON vt.these_id = va.these_id AND vt.individu_id = va.individu_id AND vt.histo_destructeur_id IS NULL
         left join validation v on v.id = vt.validation_id AND v.type_validation_id = va.type_validation_id;

create or replace view v_situ_rdv_bu_validation_bu(these_id, valide) as
SELECT vt.these_id,
       CASE
           WHEN v.id IS NOT NULL THEN 1
           ELSE 0
           END AS valide
FROM validation_these vt
         join validation v on v.id = vt.validation_id
         JOIN type_validation tv ON v.type_validation_id = tv.id AND tv.code::text = 'RDV_BU'::text
WHERE v.histo_destructeur_id IS NULL;

create or replace view v_situ_validation_page_couv(these_id, valide) as
SELECT vt.these_id,
       CASE
           WHEN v.id IS NOT NULL THEN 1
           ELSE 0
           END AS valide
FROM validation_these vt
         join validation v on v.id = vt.validation_id
         JOIN type_validation tv ON v.type_validation_id = tv.id AND tv.code::text = 'PAGE_DE_COUVERTURE'::text
WHERE v.histo_destructeur_id IS NULL;

create or replace view v_situ_version_papier_corrigee(these_id, validation_id) as
SELECT vt.these_id,
       v.id AS validation_id
FROM validation_these vt
         join validation v on v.id = vt.validation_id
         JOIN type_validation tv ON tv.id = v.type_validation_id
WHERE tv.code::text = 'VERSION_PAPIER_CORRIGEE'::text;

create or replace view v_situ_validation_rapport_activite_doctorant(rapport_id, these_id, validation_id) as
SELECT rav.rapport_id,
       ra.these_id,
       rav.id AS validation_id
FROM rapport_activite_validation rav
         JOIN type_validation tv ON tv.id = rav.type_validation_id AND tv.code::text = 'RAPPORT_ACTIVITE_DOCTORANT'::text
         JOIN rapport_activite ra ON rav.rapport_id = ra.id AND ra.histo_destruction IS NULL
WHERE rav.histo_destruction IS NULL;

create or replace view v_situ_validation_rapport_activite_auto(rapport_id, these_id, validation_id) as
SELECT rav.rapport_id,
       ra.these_id,
       rav.id AS validation_id
FROM rapport_activite_validation rav
         JOIN type_validation tv ON tv.id = rav.type_validation_id AND tv.code::text = 'RAPPORT_ACTIVITE_AUTO'::text
         JOIN rapport_activite ra ON rav.rapport_id = ra.id AND ra.histo_destruction IS NULL
WHERE rav.histo_destruction IS NULL;



----------------------------- acteur --------------------------------


create or replace view these_rech as
WITH acteurs AS (
    SELECT a.these_id,
           string_agg(COALESCE(ia.nom_usuel::text, ''::text), ' '::text) AS agg
    FROM acteur_these a
             JOIN these t on a.these_id = t.id and t.histo_destruction is null
             JOIN role r ON a.role_id = r.id AND r.code in ('D', 'K') -- dir, codir de thèse
             JOIN individu ia ON ia.id = a.individu_id and ia.histo_destruction is null
    WHERE a.histo_destruction IS NULL
    GROUP BY 1
)
SELECT 'now'::text::timestamp without time zone AS date_creation,
       t.source_code AS code_these,
       d.source_code AS code_doctorant,
       ed.source_code AS code_ecole_doct,
       these_rech_compute_haystack(eds.code,
                                   urs.code,
                                   t.titre,
                                   d.source_code,
                                   id.nom_patronymique,
                                   id.nom_usuel,
                                   id.prenom1,
                                   a.agg::character varying) AS haystack
FROM these t
    JOIN doctorant d ON d.id = t.doctorant_id
    JOIN individu id ON id.id = d.individu_id
    LEFT JOIN ecole_doct ed ON t.ecole_doct_id = ed.id
    LEFT JOIN structure eds ON ed.structure_id = eds.id
    LEFT JOIN unite_rech ur ON t.unite_rech_id = ur.id
    LEFT JOIN structure urs ON ur.structure_id = urs.id
    LEFT JOIN acteurs a ON a.these_id = t.id;



create or replace view v_situ_depot_vc_valid_pres(id, these_id, individu_id, valide) as
WITH validations_attendues AS (
    SELECT a.these_id,
           a.individu_id,
           tv.id AS type_validation_id
    FROM acteur_these a
             JOIN role r ON a.role_id = r.id AND r.code::text = 'P'::text
             JOIN type_validation tv ON tv.code::text = 'CORRECTION_THESE'::text
    WHERE a.histo_destruction IS NULL
), validations_dt_existantes AS (
    SELECT DISTINCT vt.these_id,
                    tv.id AS type_validation_id
    FROM validation v_1
             JOIN validation_these vt on v_1.id = vt.validation_id and vt.histo_destruction is null
             JOIN type_validation tv ON v_1.type_validation_id = tv.id AND tv.code::text = 'CORRECTION_THESE'::text
             JOIN acteur_these a ON vt.these_id = a.these_id AND vt.individu_id = a.individu_id AND a.histo_destructeur_id IS NULL
             JOIN role r ON a.role_id = r.id AND r.code::text = 'D'::text
    WHERE v_1.histo_destructeur_id IS NULL
)
SELECT (va.these_id || '_'::text) || va.individu_id AS id,
       va.these_id,
       va.individu_id,
       CASE
           WHEN v.id IS NOT NULL OR vdte.these_id IS NOT NULL THEN 1
           ELSE 0
           END AS valide
FROM validations_attendues va
         LEFT JOIN validation_these vt ON vt.these_id = va.these_id AND vt.individu_id = va.individu_id AND vt.histo_destructeur_id IS NULL
         LEFT JOIN validation v ON v.id = vt.validation_id AND v.histo_destructeur_id IS NULL AND v.type_validation_id = va.type_validation_id
         LEFT JOIN validations_dt_existantes vdte ON vdte.these_id = va.these_id AND vdte.type_validation_id = va.type_validation_id;


create or replace view v_situ_depot_vc_valid_pres_new(id, these_id, individu_id, valide) as
WITH validations_attendues AS (
    SELECT a.these_id,
           a.individu_id,
           tv.id AS type_validation_id
    FROM acteur_these a
             JOIN role r ON a.role_id = r.id AND r.code::text = 'P'::text
             JOIN type_validation tv ON tv.code::text = 'CORRECTION_THESE'::text
    WHERE a.histo_destruction IS NULL AND NOT (EXISTS (
        SELECT vt.id,
               v_1.type_validation_id,
               vt.these_id,
               vt.individu_id,
               vt.histo_creation,
               vt.histo_createur_id,
               vt.histo_modification,
               vt.histo_modificateur_id,
               vt.histo_destruction,
               vt.histo_destructeur_id,
               tv_1.id,
               tv_1.code,
               tv_1.libelle
        FROM validation_these vt
                 JOIN validation v_1 ON v_1.id = vt.validation_id
                 JOIN type_validation tv_1 ON v_1.type_validation_id = tv_1.id AND tv_1.code::text = 'CORRECTION_THESE'::text
        WHERE vt.these_id = a.these_id AND v_1.type_validation_id = tv_1.id AND vt.histo_destruction IS NULL))
)
SELECT (va.these_id || '_'::text) || va.individu_id AS id,
       va.these_id,
       va.individu_id,
       CASE
           WHEN v.id IS NOT NULL THEN 1
           ELSE 0
           END AS valide
FROM validations_attendues va
         LEFT JOIN validation_these vt ON vt.these_id = va.these_id AND vt.individu_id = va.individu_id AND vt.histo_destructeur_id IS NULL
         LEFT JOIN validation v ON v.id = vt.validation_id AND v.type_validation_id = va.type_validation_id;

-- create or replace view v_situ_avis_rapport_activite_codir_these(rapport_id, these_id, individu_id, avis_id) as
-- WITH codir AS (
--     SELECT a_1.these_id,
--            a_1.individu_id,
--            dense_rank() OVER (PARTITION BY a_1.these_id ORDER BY a_1.est_principal DESC) AS rank
--     FROM acteur_these a_1
--              JOIN role r ON a_1.role_id = r.id AND r.code::text = 'K'::text
--     WHERE a_1.histo_destruction IS NULL
-- )
-- SELECT ra.id AS rapport_id,
--        ra.these_id,
--        codir.individu_id,
--        raa.id AS avis_id
-- FROM rapport_activite ra
--          JOIN codir ON codir.these_id = ra.these_id
--          LEFT JOIN rapport_activite_avis raa ON raa.rapport_id = ra.id AND raa.individu_id = codir.individu_id AND raa.histo_destruction IS NULL
--          LEFT JOIN unicaen_avis a ON raa.avis_id = a.id
--          LEFT JOIN unicaen_avis_type ta ON ta.id = a.avis_type_id AND ta.code::text = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE'::text
-- WHERE ra.histo_destruction IS NULL AND codir.rank = 1;

-- create or replace view v_situ_avis_rapport_activite_codir_these_2(rapport_id, these_id, individu_id, avis_id) as
-- WITH codir AS (
--     SELECT a_1.these_id,
--            a_1.individu_id,
--            dense_rank() OVER (PARTITION BY a_1.these_id ORDER BY a_1.est_principal DESC) AS rank
--     FROM acteur_these a_1
--              JOIN role r ON a_1.role_id = r.id AND r.code::text = 'K'::text
--     WHERE a_1.histo_destruction IS NULL
-- )
-- SELECT ra.id AS rapport_id,
--        ra.these_id,
--        codir.individu_id,
--        raa.id AS avis_id
-- FROM rapport_activite ra
--          JOIN codir ON codir.these_id = ra.these_id
--          LEFT JOIN rapport_activite_avis raa ON raa.rapport_id = ra.id AND raa.individu_id = codir.individu_id AND raa.histo_destruction IS NULL
--          LEFT JOIN unicaen_avis a ON raa.avis_id = a.id
--          LEFT JOIN unicaen_avis_type ta ON ta.id = a.avis_type_id AND ta.code::text = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE'::text
-- WHERE ra.histo_destruction IS NULL AND codir.rank = 2;

-- create or replace view v_wf_rapport_activite_etape_pertin(rapport_activite_id, these_id, etape_id, code, ordre, id) as
-- SELECT t.rapport_activite_id,
--        t.these_id,
--        t.etape_id,
--        t.code,
--        t.ordre,
--        row_number() OVER (ORDER BY 1::integer, 2::integer, 3::integer, 4::integer) AS id
-- FROM ( SELECT ra.id AS rapport_activite_id,
--               th.id AS these_id,
--               e.id AS etape_id,
--               e.code,
--               e.ordre
--        FROM rapport_activite ra
--                 JOIN these th ON th.id = ra.these_id AND th.histo_destruction IS NULL
--                 JOIN rapport_activite_wf_etape e ON e.code::text = 'VALIDATION_RAPPORT_ACTIVITE_DOCTORANT'::text
--        WHERE ra.fichier_id IS NULL
--        UNION ALL
--        SELECT ra.id AS rapport_activite_id,
--               th.id AS these_id,
--               e.id AS etape_id,
--               e.code,
--               e.ordre
--        FROM rapport_activite ra
--                 JOIN these th ON th.id = ra.these_id AND th.histo_destruction IS NULL
--                 JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_GEST'::text
--        WHERE ra.fichier_id IS NOT NULL
--        UNION ALL
--        SELECT ra.id AS rapport_activite_id,
--               th.id AS these_id,
--               e.id AS etape_id,
--               e.code,
--               e.ordre
--        FROM rapport_activite ra
--                 JOIN these th ON th.id = ra.these_id AND th.histo_destruction IS NULL
--                 JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_DIR_THESE'::text
--        WHERE ra.fichier_id IS NULL AND ra.par_directeur_these = false
--        UNION ALL
--        SELECT ra.id AS rapport_activite_id,
--               th.id AS these_id,
--               e.id AS etape_id,
--               e.code,
--               e.ordre
--        FROM rapport_activite ra
--                 JOIN these th ON th.id = ra.these_id AND th.histo_destruction IS NULL
--                 JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE'::text
--        WHERE ra.fichier_id IS NULL AND (EXISTS ( SELECT
--                                                  FROM acteur_these a
--                                                           JOIN role r ON r.id = a.role_id AND r.code::text = 'K'::text
--                                                  WHERE a.these_id = ra.these_id AND a.histo_destruction IS NULL))
--        UNION ALL
--        SELECT ra.id AS rapport_activite_id,
--               th.id AS these_id,
--               e.id AS etape_id,
--               e.code,
--               e.ordre
--        FROM rapport_activite ra
--                 JOIN these th ON th.id = ra.these_id AND th.histo_destruction IS NULL
--                 JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE_2'::text
--        WHERE ra.fichier_id IS NULL AND 1 < (( SELECT count(*) AS count
--                                               FROM acteur_these a
--                                                        JOIN role r ON r.id = a.role_id AND r.code::text = 'K'::text
--                                               WHERE a.these_id = ra.these_id AND a.histo_destruction IS NULL))
--        UNION ALL
--        SELECT ra.id AS rapport_activite_id,
--               th.id AS these_id,
--               e.id AS etape_id,
--               e.code,
--               e.ordre
--        FROM rapport_activite ra
--                 JOIN these th ON th.id = ra.these_id AND th.histo_destruction IS NULL
--                 JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_DIR_UR'::text
--        WHERE ra.fichier_id IS NULL
--        UNION ALL
--        SELECT ra.id AS rapport_activite_id,
--               th.id AS these_id,
--               e.id AS etape_id,
--               e.code,
--               e.ordre
--        FROM rapport_activite ra
--                 JOIN these th ON th.id = ra.these_id AND th.histo_destruction IS NULL
--                 JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_DIR_ED'::text
--        UNION ALL
--        SELECT ra.id AS rapport_activite_id,
--               th.id AS these_id,
--               e.id AS etape_id,
--               e.code,
--               e.ordre
--        FROM rapport_activite ra
--                 JOIN these th ON th.id = ra.these_id AND th.histo_destruction IS NULL
--                 JOIN rapport_activite_wf_etape e ON e.code::text = 'VALIDATION_RAPPORT_ACTIVITE_AUTO'::text
--        WHERE ra.fichier_id IS NOT NULL) t;

-- create or replace view v_workflow_rapport_activite
--             (rapport_id, these_id, etape_id, code, ordre, franchie, resultat, objectif, from_resultat_requis,
--              atteignable, courante, id)
-- as
-- WITH situ AS (
--     SELECT ra.id AS rapport_id,
--            ra.these_id,
--            e.id AS etape_id,
--            e.code,
--            e.ordre,
--            CASE
--                WHEN v_1.validation_id IS NULL THEN 0
--                ELSE 1
--                END AS franchie,
--            CASE
--                WHEN v_1.validation_id IS NULL THEN 0
--                ELSE 1
--                END AS resultat,
--            1 AS objectif
--     FROM rapport_activite ra
--              JOIN rapport_activite_wf_etape e ON e.code::text = 'VALIDATION_RAPPORT_ACTIVITE_DOCTORANT'::text
--              LEFT JOIN v_situ_validation_rapport_activite_doctorant v_1 ON v_1.rapport_id = ra.id
--     UNION ALL
--     SELECT ra.id AS rapport_id,
--            ra.these_id,
--            e.id AS etape_id,
--            e.code,
--            e.ordre,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS franchie,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS resultat,
--            1 AS objectif
--     FROM rapport_activite ra
--              JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_GEST'::text
--              LEFT JOIN v_situ_avis_rapport_activite_gest v_1 ON v_1.rapport_id = ra.id
--     UNION ALL
--     SELECT ra.id AS rapport_id,
--            ra.these_id,
--            e.id AS etape_id,
--            e.code,
--            e.ordre,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS franchie,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS resultat,
--            1 AS objectif
--     FROM rapport_activite ra
--              JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_DIR_THESE'::text
--              LEFT JOIN v_situ_avis_rapport_activite_dir_these v_1 ON v_1.rapport_id = ra.id
--     UNION ALL
--     SELECT ra.id AS rapport_id,
--            ra.these_id,
--            e.id AS etape_id,
--            e.code,
--            e.ordre,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS franchie,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS resultat,
--            1 AS objectif
--     FROM rapport_activite ra
--              JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE'::text
--              LEFT JOIN v_situ_avis_rapport_activite_codir_these v_1 ON v_1.rapport_id = ra.id
--     UNION ALL
--     SELECT ra.id AS rapport_id,
--            ra.these_id,
--            e.id AS etape_id,
--            e.code,
--            e.ordre,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS franchie,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS resultat,
--            1 AS objectif
--     FROM rapport_activite ra
--              JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE_2'::text
--              LEFT JOIN v_situ_avis_rapport_activite_codir_these_2 v_1 ON v_1.rapport_id = ra.id
--     UNION ALL
--     SELECT ra.id AS rapport_id,
--            ra.these_id,
--            e.id AS etape_id,
--            e.code,
--            e.ordre,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS franchie,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS resultat,
--            1 AS objectif
--     FROM rapport_activite ra
--              JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_DIR_UR'::text
--              LEFT JOIN v_situ_avis_rapport_activite_dir_ur v_1 ON v_1.rapport_id = ra.id
--     UNION ALL
--     SELECT ra.id AS rapport_id,
--            ra.these_id,
--            e.id AS etape_id,
--            e.code,
--            e.ordre,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS franchie,
--            CASE
--                WHEN v_1.avis_id IS NULL THEN 0
--                ELSE 1
--                END AS resultat,
--            1 AS objectif
--     FROM rapport_activite ra
--              JOIN rapport_activite_wf_etape e ON e.code::text = 'AVIS_RAPPORT_ACTIVITE_DIR_ED'::text
--              LEFT JOIN v_situ_avis_rapport_activite_dir_ed v_1 ON v_1.rapport_id = ra.id
--     UNION ALL
--     SELECT ra.id AS rapport_id,
--            ra.these_id,
--            e.id AS etape_id,
--            e.code,
--            e.ordre,
--            CASE
--                WHEN v_1.validation_id IS NULL THEN 0
--                ELSE 1
--                END AS franchie,
--            CASE
--                WHEN v_1.validation_id IS NULL THEN 0
--                ELSE 1
--                END AS resultat,
--            1 AS objectif
--     FROM rapport_activite ra
--              JOIN rapport_activite_wf_etape e ON e.code::text = 'VALIDATION_RAPPORT_ACTIVITE_AUTO'::text
--              LEFT JOIN v_situ_validation_rapport_activite_auto v_1 ON v_1.rapport_id = ra.id
-- )
-- SELECT s.rapport_id,
--        s.these_id,
--        s.etape_id,
--        s.code,
--        e2e.ordre,
--        s.franchie,
--        s.resultat,
--        s.objectif,
--        e2e.from_resultat_requis,
--        CASE
--            WHEN s.franchie = 1 OR dense_rank() OVER (PARTITION BY s.rapport_id, s.franchie ORDER BY s.ordre) = 1 THEN true
--            ELSE false
--            END AS atteignable,
--        CASE
--            WHEN dense_rank() OVER (PARTITION BY s.rapport_id, s.franchie ORDER BY s.ordre) = 1 AND s.franchie = 0 THEN true
--            ELSE false
--            END AS courante,
--        row_number() OVER (ORDER BY s.rapport_id, s.franchie) AS id
-- FROM situ s
--          JOIN v_wf_rapport_activite_etape_pertin v ON s.rapport_id = v.rapport_activite_id AND s.etape_id = v.etape_id
--          JOIN mv_wf_rapport_activite_etape_to_etape e2e ON e2e.to_etape_id = s.etape_id;

drop view if exists v_extract_theses;

create or replace view v_extract_theses
as
WITH mails_contacts AS (
    SELECT DISTINCT mail_confirmation.individu_id,
                    first_value(mail_confirmation.email) OVER (PARTITION BY mail_confirmation.individu_id ORDER BY mail_confirmation.id DESC) AS email
    FROM mail_confirmation
    WHERE mail_confirmation.etat::text = 'C'::text
), directeurs AS (
    SELECT a.these_id,
           string_agg(concat(i.nom_usuel, ' ', i.prenom1), ' ; '::text) AS identites
    FROM acteur_these a
             JOIN role r ON a.role_id = r.id AND r.code::text = 'D'::text
             JOIN individu i ON a.individu_id = i.id
    WHERE a.histo_destruction IS NULL
    GROUP BY a.these_id
), codirecteurs AS (
    SELECT a.these_id,
           string_agg(concat(i.nom_usuel, ' ', i.prenom1), ' ; '::text) AS identites
    FROM acteur_these a
             JOIN role r ON a.role_id = r.id AND (r.code::text = ANY (ARRAY['C'::character varying::text, 'K'::character varying::text]))
             JOIN individu i ON a.individu_id = i.id
    WHERE a.histo_destruction IS NULL
    GROUP BY a.these_id
), coencadrants AS (
    SELECT a.these_id,
           string_agg(concat(i.nom_usuel, ' ', i.prenom1), ' ; '::text) AS identites
    FROM acteur_these a
             JOIN role r ON a.role_id = r.id AND (r.code::text = ANY (ARRAY['B'::character varying::text, 'N'::character varying::text]))
             JOIN individu i ON a.individu_id = i.id
    WHERE a.histo_destruction IS NULL
    GROUP BY a.these_id
), financements AS (
    SELECT f_1.these_id,
           string_agg(
                   CASE o.visible
                       WHEN true THEN 'O'::text
                       ELSE 'N'::text
                       END, ' ; '::text) AS financ_origs_visibles,
           string_agg(f_1.annee::character varying::text, ' ; '::text) AS financ_annees,
           string_agg(o.libelle_long::text, ' ; '::text) AS financ_origs,
           string_agg(f_1.complement_financement::text, ' ; '::text) AS financ_compls,
           string_agg(f_1.libelle_type_financement::text, ' ; '::text) AS financ_types
    FROM financement f_1
             JOIN origine_financement o ON f_1.origine_financement_id = o.id
    WHERE f_1.histo_destruction IS NULL
    GROUP BY f_1.these_id
), domaines AS (
    SELECT udl.unite_id,
           string_agg(d_1.libelle::text, ' ; '::text) AS libelles
    FROM unite_domaine_linker udl
             JOIN domaine_scientifique d_1 ON d_1.id = udl.domaine_id
    GROUP BY udl.unite_id
), depots_vo_pdf AS (
    SELECT DISTINCT ft.these_id,
                    first_value(vf.code) OVER (PARTITION BY ft.these_id ORDER BY ft.id DESC) AS version_code,
                    first_value(f_1.histo_creation) OVER (PARTITION BY ft.these_id ORDER BY ft.id DESC) AS histo_creation
    FROM fichier_these ft
             JOIN fichier f_1 ON ft.fichier_id = f_1.id AND f_1.histo_destruction IS NULL
             JOIN nature_fichier nf ON f_1.nature_id = nf.id AND nf.code::text = 'THESE_PDF'::text
             JOIN version_fichier vf ON f_1.version_fichier_id = vf.id AND vf.code::text = 'VO'::text
), depots_voc_pdf AS (
    SELECT DISTINCT ft.these_id,
                    first_value(vf.code) OVER (PARTITION BY ft.these_id ORDER BY ft.id DESC) AS version_code,
                    first_value(f_1.histo_creation) OVER (PARTITION BY ft.these_id ORDER BY ft.id DESC) AS histo_creation
    FROM fichier_these ft
             JOIN fichier f_1 ON ft.fichier_id = f_1.id AND f_1.histo_destruction IS NULL
             JOIN nature_fichier nf ON f_1.nature_id = nf.id AND nf.code::text = 'THESE_PDF'::text
             JOIN version_fichier vf ON f_1.version_fichier_id = vf.id AND vf.code::text = 'VOC'::text
), depots_non_pdf AS (
    SELECT DISTINCT ft.these_id,
                    first_value(vf.code) OVER (PARTITION BY ft.these_id ORDER BY ft.id DESC) AS version_code,
                    first_value(f_1.histo_creation) OVER (PARTITION BY ft.these_id ORDER BY ft.id DESC) AS histo_creation
    FROM fichier_these ft
             JOIN fichier f_1 ON ft.fichier_id = f_1.id AND f_1.histo_destruction IS NULL
             JOIN nature_fichier nf ON f_1.nature_id = nf.id AND nf.code::text = 'FICHIER_NON_PDF'::text
             JOIN version_fichier vf ON f_1.version_fichier_id = vf.id AND (vf.code::text = ANY (ARRAY['VO'::character varying::text, 'VOC'::character varying::text]))
), diffusion AS (
    SELECT DISTINCT d_1.these_id,
                    first_value(d_1.autoris_mel) OVER (PARTITION BY d_1.these_id ORDER BY d_1.version_corrigee DESC, d_1.id DESC) AS autoris_mel,
                    first_value(d_1.autoris_embargo_duree) OVER (PARTITION BY d_1.these_id ORDER BY d_1.version_corrigee DESC, d_1.id DESC) AS autoris_embargo_duree,
                    first_value(d_1.autoris_motif) OVER (PARTITION BY d_1.these_id ORDER BY d_1.version_corrigee DESC, d_1.id DESC) AS autoris_motif
    FROM diffusion d_1
    WHERE d_1.histo_destruction IS NULL
), dernier_rapport_activite AS (
    SELECT DISTINCT ra.these_id,
                    first_value(ra.annee_univ) OVER (PARTITION BY ra.these_id ORDER BY ra.annee_univ DESC) AS annee
    FROM rapport_activite ra
    WHERE ra.histo_destruction IS NULL
), dernier_rapport_csi AS (
    SELECT DISTINCT r.these_id,
                    first_value(r.annee_univ) OVER (PARTITION BY r.these_id ORDER BY r.annee_univ DESC) AS annee
    FROM rapport r
             JOIN type_rapport tr ON r.type_rapport_id = tr.id AND tr.code::text = 'RAPPORT_CSI'::text
    WHERE r.histo_destruction IS NULL
)
SELECT to_char(now(), 'DD/MM/YYYY HH24:MI:SS'::text) AS date_extraction,
       th.id,
       di.civilite,
       di.nom_usuel,
       di.nom_patronymique,
       di.prenom1,
       to_char(di.date_naissance, 'DD/MM/YYYY'::text) AS date_naissance,
       di.nationalite,
       COALESCE(dic.email, di.email) AS email_pro,
       mc.email AS email_contact,
       d.ine,
       substr(d.source_code::text, strpos(d.source_code::text, '::'::text) + 2) AS num_etudiant,
       th.source_code AS num_these,
       th.titre,
       th.code_sise_disc,
       dirs.identites AS dirs,
       codirs.identites AS codirs,
       coencs.identites AS coencs,
       se.libelle AS etab_lib,
       sed.code AS ed_code,
       sed.libelle AS ed_lib,
       sur.code AS ur_code,
       sur.libelle AS ur_lib,
       th.lib_etab_cotut,
       th.lib_pays_cotut,
       ta.libelle_titre_acces,
       ta.libelle_etb_titre_acces,
       f.financ_origs_visibles,
       f.financ_annees,
       f.financ_origs,
       f.financ_compls,
       f.financ_types,
       dom.libelles AS domaines,
       to_char(th.date_prem_insc, 'DD/MM/YYYY'::text) AS date_prem_insc,
       to_char(th.date_abandon, 'DD/MM/YYYY'::text) AS date_abandon,
       to_char(th.date_transfert, 'DD/MM/YYYY'::text) AS date_transfert,
       to_char(th.date_soutenance, 'DD/MM/YYYY'::text) AS date_soutenance,
       to_char(th.date_fin_confid, 'DD/MM/YYYY'::text) AS date_fin_confid,
       round((th.date_soutenance::date - th.date_prem_insc::date)::numeric / 30.5, 2) AS duree_these_mois,
       to_char(depots_vo_pdf.histo_creation, 'DD/MM/YYYY'::text) AS date_depot_vo,
       to_char(depots_voc_pdf.histo_creation, 'DD/MM/YYYY'::text) AS date_depot_voc,
       CASE th.etat_these
           WHEN 'E'::text THEN 'En cours'::text
           WHEN 'A'::text THEN 'Abandonnée'::text
           WHEN 'S'::text THEN 'Soutenue'::text
           WHEN 'U'::text THEN 'Transférée'::text
           ELSE NULL::text
           END AS etat_these,
       th.soutenance_autoris,
       CASE
           WHEN th.date_fin_confid IS NULL OR th.date_fin_confid < now() THEN 'N'::text
           ELSE 'O'::text
           END AS confidentielle,
       th.resultat,
       CASE
           WHEN th.correc_autorisee_forcee::text = 'aucune'::text THEN 'N'::character varying
           ELSE COALESCE(th.correc_autorisee_forcee, th.correc_autorisee)
END AS correc_autorisee,
       CASE
           WHEN depots_vo_pdf.these_id IS NULL AND depots_voc_pdf.these_id IS NULL THEN 'N'::text
           ELSE 'O'::text
END AS depot_pdf,
       CASE
           WHEN depots_non_pdf.these_id IS NULL THEN 'N'::text
           ELSE 'O'::text
END AS depot_annexe,
       CASE diff.autoris_mel
           WHEN 0 THEN 'Non'::text
           WHEN 1 THEN 'Oui, avec embargo'::text
           WHEN 2 THEN 'Oui, immédiatement'::text
           ELSE NULL::text
END AS autoris_mel,
       diff.autoris_embargo_duree,
       diff.autoris_motif,
       CASE
           WHEN ract.annee IS NOT NULL THEN concat(ract.annee, '/', ract.annee + 1)
           ELSE NULL::text
END AS dernier_rapport_activite,
       CASE
           WHEN rcsi.annee IS NOT NULL THEN concat(rcsi.annee, '/', rcsi.annee + 1)
           ELSE NULL::text
END AS dernier_rapport_csi
FROM these th
         JOIN doctorant d ON th.doctorant_id = d.id
         JOIN individu di ON d.individu_id = di.id
         LEFT JOIN individu_compl dic ON di.id = dic.individu_id AND dic.histo_destruction IS NULL
         LEFT JOIN mails_contacts mc ON mc.individu_id = di.id
         JOIN etablissement e ON d.etablissement_id = e.id
         JOIN structure se ON e.structure_id = se.id
         LEFT JOIN ecole_doct ed ON th.ecole_doct_id = ed.id
         LEFT JOIN structure sed ON ed.structure_id = sed.id
         LEFT JOIN unite_rech ur ON th.unite_rech_id = ur.id
         LEFT JOIN structure sur ON ur.structure_id = sur.id
         LEFT JOIN domaines dom ON dom.unite_id = ur.id
         LEFT JOIN titre_acces ta ON th.id = ta.these_id AND ta.histo_destruction IS NULL
         LEFT JOIN financements f ON th.id = f.these_id
         LEFT JOIN directeurs dirs ON dirs.these_id = th.id
         LEFT JOIN codirecteurs codirs ON codirs.these_id = th.id
         LEFT JOIN coencadrants coencs ON coencs.these_id = th.id
         LEFT JOIN depots_vo_pdf ON depots_vo_pdf.these_id = th.id
         LEFT JOIN depots_voc_pdf ON depots_voc_pdf.these_id = th.id
         LEFT JOIN depots_non_pdf ON depots_non_pdf.these_id = th.id
         LEFT JOIN diffusion diff ON diff.these_id = th.id
         LEFT JOIN dernier_rapport_activite ract ON ract.these_id = th.id
         LEFT JOIN dernier_rapport_csi rcsi ON rcsi.these_id = th.id
WHERE th.histo_destruction IS NULL;



drop view if exists v_diff_acteur;

alter view src_acteur rename to src_acteur_these;

create or replace view src_acteur_these (id, source_id, source_code, these_id, role_id, individu_id, etablissement_id, qualite) as
WITH pre AS (
    SELECT NULL::bigint AS id,
           tmp.source_code,
           src.id AS source_id,
           i.id AS individu_id,
           t.id AS these_id,
           r.id AS role_id,
           eact.id AS etablissement_id,
           tmp.lib_cps AS qualite
    FROM tmp_acteur tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN individu i ON i.source_code::text = tmp.individu_id::text
             JOIN these t ON t.source_code::text = tmp.these_id::text
             JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text = 'P'::text
             LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
    UNION ALL
    SELECT NULL::bigint AS id,
           tmp.source_code::text || 'P'::text AS source_code,
           src.id AS source_id,
           i.id AS individu_id,
           t.id AS these_id,
           r_pj.id AS role_id,
           eact.id AS etablissement_id,
           tmp.lib_cps AS qualite
    FROM tmp_acteur tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN individu i ON i.source_code::text = tmp.individu_id::text
             JOIN these t ON t.source_code::text = tmp.these_id::text
             JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text = 'M'::text
             JOIN role r_pj ON r_pj.code::text = 'P'::text AND r_pj.structure_id = r.structure_id
             LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
    WHERE tmp.lib_roj_compl::text = 'Président du jury'::text
    UNION ALL
    SELECT NULL::bigint AS id,
           tmp.source_code,
           src.id AS source_id,
           i.id AS individu_id,
           t.id AS these_id,
           r.id AS role_id,
           eact.id AS etablissement_id,
           tmp.lib_cps AS qualite
    FROM tmp_acteur tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN individu i ON i.source_code::text = tmp.individu_id::text
             JOIN these t ON t.source_code::text = tmp.these_id::text
             JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text <> 'P'::text
             LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
)
SELECT pre.id,
       pre.source_id,
       pre.source_code,
       pre.these_id,
       pre.role_id,
       COALESCE(isub.to_id, pre.individu_id) AS individu_id,
       COALESCE(esub.to_id, pre.etablissement_id) AS etablissement_id,
       pre.qualite
FROM pre
         LEFT JOIN substit_individu isub ON isub.from_id = pre.individu_id
         LEFT JOIN substit_etablissement esub ON esub.from_id = pre.etablissement_id;




drop materialized view if exists mv_demo_these;
create materialized view mv_demo_these as
WITH doctorant_usurpable(id) AS (SELECT d.id
                                 FROM doctorant d
                                          JOIN utilisateur u ON u.individu_id = d.individu_id
                                 WHERE d.histo_destruction IS NULL)
    (SELECT t.id,
            'Thèse soutenue, pas de demande de correction'::text AS description
     FROM these t
              JOIN doctorant_usurpable d ON t.doctorant_id = d.id
     WHERE t.histo_destruction IS NULL
       AND t.etat_these::text = 'S'::text
       AND NOT (EXISTS (SELECT
                        FROM validation_these vt
                                 join validation v on vt.validation_id = v.id
                                 JOIN type_validation tv ON v.type_validation_id = tv.id
                        WHERE tv.code::text = 'CORRECTION_THESE'::text
                          AND v.histo_destruction IS NULL
                          AND vt.these_id = t.id))
     ORDER BY t.date_soutenance DESC
     LIMIT 5)
UNION ALL
(SELECT t.id,
        'Thèse soutenue, corrections demandées'::text AS description
 FROM these t
          JOIN doctorant_usurpable d ON t.doctorant_id = d.id
 WHERE t.histo_destruction IS NULL
   AND t.etat_these::text = 'S'::text
   AND (EXISTS (SELECT
                FROM validation_these vt
                         join validation v on vt.validation_id = v.id
                         JOIN type_validation tv ON v.type_validation_id = tv.id
                WHERE tv.code::text = 'CORRECTION_THESE'::text
                  AND v.histo_destruction IS NULL
                  AND vt.these_id = t.id))
 ORDER BY t.date_soutenance DESC
 LIMIT 5)
UNION ALL
(SELECT t.id,
        'Thèse en cours, proposition de soutenance non validée'::text AS description
 FROM these t
          JOIN doctorant_usurpable d ON t.doctorant_id = d.id
 WHERE t.histo_destruction IS NULL
   AND t.etat_these::text = 'E'::text
   AND EXTRACT(year FROM t.date_prem_insc) = (EXTRACT(year FROM CURRENT_TIMESTAMP) - 3::numeric)
   AND NOT (EXISTS (SELECT
                    FROM validation_these vt
                             join validation v on vt.validation_id = v.id
                             JOIN type_validation tv ON v.type_validation_id = tv.id
                    WHERE tv.code::text = 'PROPOSITION_SOUTENANCE'::text
                      AND v.histo_destruction IS NULL
                      AND vt.these_id = t.id))
 ORDER BY t.date_prem_insc
 LIMIT 5)
UNION ALL
(SELECT t.id,
        'Thèse soutenue, proposition de soutenance validée, page de couverture validée, Rdv BU non validé'::text AS description
 FROM these t
          JOIN doctorant_usurpable d ON t.doctorant_id = d.id
 WHERE t.histo_destruction IS NULL
   AND t.etat_these::text = 'E'::text
   AND EXTRACT(year FROM t.date_prem_insc) = (EXTRACT(year FROM CURRENT_TIMESTAMP) - 3::numeric)
   AND (EXISTS (SELECT
                FROM validation_these vt
                         join validation v on vt.validation_id = v.id
                         JOIN type_validation tv ON v.type_validation_id = tv.id
                WHERE tv.code::text = 'PROPOSITION_SOUTENANCE'::text
                  AND v.histo_destruction IS NULL
                  AND vt.these_id = t.id))
   AND (EXISTS (SELECT
                FROM validation_these vt
                         join validation v on vt.validation_id = v.id
                         JOIN type_validation tv ON v.type_validation_id = tv.id
                WHERE tv.code::text = 'PAGE_DE_COUVERTURE'::text
                  AND v.histo_destruction IS NULL
                  AND vt.these_id = t.id))
   AND NOT (EXISTS (SELECT
                    FROM validation_these vt
                             join validation v on vt.validation_id = v.id
                             JOIN type_validation tv ON v.type_validation_id = tv.id
                    WHERE tv.code::text = 'RDV_BU'::text
                      AND v.histo_destruction IS NULL
                      AND vt.these_id = t.id))
 ORDER BY t.date_prem_insc
 LIMIT 5);

alter table validation_these drop type_validation_id;

call unicaen_indicateur_recreate_matviews();