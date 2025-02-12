--
-- 9.3.0
--

drop materialized view if exists mv_recherche_these;

create materialized view mv_recherche_these as
WITH acteurs AS (
    SELECT a_1.these_id,
           i.nom_usuel,
           a_1.individu_id
    FROM individu i
             JOIN acteur_these a_1 ON i.id = a_1.individu_id
             JOIN these t_1 ON t_1.id = a_1.these_id
             JOIN role r ON a_1.role_id = r.id AND (r.code::text = ANY (ARRAY['D'::character varying::text, 'K'::character varying::text]))
)
SELECT 'now'::text::timestamp without time zone AS date_creation,
       t.source_code AS code_these,
       d.source_code AS code_doctorant,
       ed.source_code AS code_ecole_doct,
       btrim(str_reduce(((((((((((((((((((((('code-ed{'::text || COALESCE(eds.code::text, ''::text)) || '} '::text) || 'code-ur{'::text) || COALESCE(urs.code::text, ''::text)) || '} '::text) || 'titre{'::text) || t.titre::text) || '} '::text) || 'doctorant-numero{'::text) || substr(d.source_code::text, "position"(d.source_code::text, '::'::text) + 2)) || '} '::text) || 'doctorant-nom{'::text) || id.nom_patronymique::text) || ' '::text) || id.nom_usuel::text) || '} '::text) || 'doctorant-prenom{'::text) || id.prenom1::text) || '} '::text) || 'directeur-nom{'::text) || COALESCE(ia.nom_usuel::text, ''::text)) || '} '::text)) AS haystack
FROM these t
         JOIN doctorant d ON d.id = t.doctorant_id
         JOIN individu id ON id.id = d.individu_id
         LEFT JOIN ecole_doct ed ON t.ecole_doct_id = ed.id
         LEFT JOIN structure eds ON ed.structure_id = eds.id
         LEFT JOIN unite_rech ur ON t.unite_rech_id = ur.id
         LEFT JOIN structure urs ON ur.structure_id = urs.id
         LEFT JOIN acteurs a ON a.these_id = t.id
         LEFT JOIN individu ia ON ia.id = a.individu_id;
