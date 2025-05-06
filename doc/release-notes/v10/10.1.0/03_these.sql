--
-- v10.1.0
--

-- Correction de la vue v_situ_depot_vc_valid_pres : calcul erroné de l'existence d'une validation.

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
                    vt.individu_id,
                    tv.id AS type_validation_id
    FROM validation_these vt
             join validation v on v.id = vt.validation_id and v.histo_destruction is null
             JOIN type_validation tv ON v.type_validation_id = tv.id AND tv.code::text = 'CORRECTION_THESE'::text
             JOIN acteur_these a ON vt.these_id = a.these_id AND vt.individu_id = a.individu_id AND a.histo_destruction IS NULL
             JOIN role r ON a.role_id = r.id AND r.code::text = 'P'::text
    WHERE vt.histo_destructeur_id IS NULL
)
SELECT (va.these_id || '_'::text) || va.individu_id AS id,
       va.these_id,
       va.individu_id,
       exists(select from validations_dt_existantes vdte
              where vdte.these_id = va.these_id
                and vdte.type_validation_id = va.type_validation_id
                and vdte.individu_id = va.individu_id)::integer as valide
FROM validations_attendues va
;
