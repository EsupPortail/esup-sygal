--
-- Substitutions
--

--=============================== Autres ================================-

drop view if exists v_diff_acteur;
drop view if exists src_acteur;
create or replace view src_acteur as
with pre as (
    SELECT NULL::bigint      AS id,
           tmp.source_code,
           src.id            AS source_id,
           i.id              AS individu_id,
           t.id              AS these_id,
           r.id              AS role_id,
           eact.id           AS acteur_etablissement_id,
           tmp.lib_cps       AS qualite,
           tmp.lib_roj_compl AS lib_role_compl
    FROM tmp_acteur tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN individu i ON i.source_code::text = tmp.individu_id::text
             JOIN these t ON t.source_code::text = tmp.these_id::text
             JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text = 'P'::text
             LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
    UNION ALL
    SELECT NULL::bigint                       AS id,
           tmp.source_code::text || 'P'::text AS source_code,
           src.id                             AS source_id,
           i.id                               AS individu_id,
           t.id                               AS these_id,
           r_pj.id                            AS role_id,
           eact.id                            AS acteur_etablissement_id,
           tmp.lib_cps                        AS qualite,
           NULL::character varying            AS lib_role_compl
    FROM tmp_acteur tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN individu i ON i.source_code::text = tmp.individu_id::text
             JOIN these t ON t.source_code::text = tmp.these_id::text
             JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text = 'M'::text
             JOIN role r_pj ON r_pj.code::text = 'P'::text AND r_pj.structure_id = r.structure_id
             LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
    WHERE tmp.lib_roj_compl::text = 'Président du jury'::text
    UNION ALL
    SELECT NULL::bigint            AS id,
           tmp.source_code,
           src.id                  AS source_id,
           i.id                    AS individu_id,
           t.id                    AS these_id,
           r.id                    AS role_id,
           eact.id                 AS acteur_etablissement_id,
           tmp.lib_cps             AS qualite,
           NULL::character varying AS lib_role_compl
    FROM tmp_acteur tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN individu i ON i.source_code::text = tmp.individu_id::text
             JOIN these t ON t.source_code::text = tmp.these_id::text
             JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text <> 'P'::text
             LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
)
select pre.id,
       pre.source_id,
       pre.source_code,
       pre.these_id,
       pre.role_id,
       coalesce(isub.to_id, pre.individu_id) as individu_id,
       coalesce(esub.id, pre.acteur_etablissement_id) as acteur_etablissement_id,
--        pre.individu_id as individu_id_orig,                         -- pour debug
--        pre.acteur_etablissement_id as acteur_etablissement_id_orig, -- pour debug
       pre.qualite,
       pre.lib_role_compl
from pre
    left join individu_substit isub on isub.from_id = pre.individu_id and isub.histo_destruction is null
    left join etablissement e on e.id = pre.acteur_etablissement_id
    left join structure_substit ssub on ssub.from_id = e.structure_id and ssub.histo_destruction is null
    left join etablissement esub on esub.structure_id = ssub.to_id
;


drop view if exists v_diff_role;
drop view if exists src_role;
create or replace view src_role
as
with pre as (
    SELECT NULL::bigint                                                                               AS id,
           tmp.source_code,
           src.id                                                                                     AS source_id,
           tmp.lib_roj                                                                                AS libelle,
           ltrim(substr(tmp.source_code::text, strpos(tmp.source_code::text, '::'::text)), ':'::text) AS code,
           (tmp.lib_roj::text || ' '::text) || coalesce(s.sigle, s.code)::text                        AS role_id,
           true                                                                                       AS these_dep,
           s.id                                                                                       AS structure_id,
           NULL::bigint                                                                               AS type_structure_dependant_id
    FROM tmp_role tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN etablissement e ON e.id = src.etablissement_id
             JOIN structure s ON s.id = e.structure_id
)
select pre.id,
       pre.source_code,
       pre.source_id,
       pre.libelle,
       pre.code,
       pre.role_id,
       pre.these_dep,
       coalesce(ssub.to_id, pre.structure_id) as structure_id,
--        pre.structure_id as structure_id_orig, -- pour debug
       pre.type_structure_dependant_id
from pre
    left join structure_substit ssub on ssub.from_id = pre.structure_id and ssub.histo_destruction is null;


drop view if exists v_diff_these;
drop view if exists src_these;
create or replace view src_these
as
with pre as (
    WITH version_corrigee_deposee AS (
      SELECT t.source_code,
             f.id AS fichier_id
      FROM fichier_these ft
               JOIN these t ON ft.these_id = t.id
               JOIN fichier f ON ft.fichier_id = f.id AND f.histo_destruction IS NULL
               JOIN nature_fichier nf ON f.nature_id = nf.id AND nf.code::text = 'THESE_PDF'::text
               JOIN version_fichier vf
                    ON f.version_fichier_id = vf.id AND vf.code::text = 'VOC'::text
      WHERE ft.est_annexe = false
        AND ft.est_expurge = false
        AND ft.retraitement IS NULL
    )
    SELECT NULL::bigint                   AS id,
           tmp.source_code,
           src.id                         AS source_id,
           e.id                           AS etablissement_id,
           d.id                           AS doctorant_id,
           ed.id                          AS ecole_doct_id,
           ur.id                          AS unite_rech_id,
           tmp.lib_ths                    AS titre,
           tmp.eta_ths                    AS etat_these,
           tmp.cod_neg_tre::numeric       AS resultat,
           tmp.code_sise_disc,
           tmp.lib_int1_dis               AS lib_disc,
           tmp.dat_deb_ths                AS date_prem_insc,
           tmp.dat_prev_sou               AS date_prev_soutenance,
           tmp.dat_sou_ths                AS date_soutenance,
           tmp.dat_fin_cfd_ths            AS date_fin_confid,
           tmp.lib_etab_cotut,
           tmp.lib_pays_cotut,
           tmp.correction_possible        AS correc_autorisee,
           CASE
               WHEN vcd.source_code IS NOT NULL THEN 'O'::character varying
               ELSE tmp.correction_effectuee
               END::character varying(30) AS correc_effectuee,
           tmp.tem_sou_aut_ths            AS soutenance_autoris,
           tmp.dat_aut_sou_ths            AS date_autoris_soutenance,
           tmp.tem_avenant_cotut,
           tmp.dat_abandon                AS date_abandon,
           tmp.dat_transfert_dep          AS date_transfert
    FROM tmp_these tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.id = src.etablissement_id
         JOIN doctorant d ON d.source_code::text = tmp.doctorant_id::text
         LEFT JOIN ecole_doct ed ON ed.source_code::text = tmp.ecole_doct_id::text
         LEFT JOIN unite_rech ur ON ur.source_code::text = tmp.unite_rech_id::text
         LEFT JOIN version_corrigee_deposee vcd ON vcd.source_code::text = tmp.source_code::text
)
select pre.id,
       pre.source_code,
       pre.source_id,
       coalesce(dsub.to_id, pre.doctorant_id) as doctorant_id,
       coalesce(esub.id, pre.etablissement_id) as etablissement_id,
       coalesce(edsub.id, pre.ecole_doct_id) as ecole_doct_id,
       coalesce(ursub.id, pre.unite_rech_id) as unite_rech_id,
--        pre.doctorant_id as doctorant_id_orig,         -- pour debug
--        pre.etablissement_id as etablissement_id_orig, -- pour debug
--        pre.ecole_doct_id as ecole_doct_id_orig,       -- pour debug
--        pre.unite_rech_id as unite_rech_id_orig,       -- pour debug
       pre.titre,
       pre.etat_these,
       pre.resultat,
       pre.code_sise_disc,
       pre.lib_disc,
       pre.date_prem_insc,
       pre.date_prev_soutenance,
       pre.date_soutenance,
       pre.date_fin_confid,
       pre.lib_etab_cotut,
       pre.lib_pays_cotut,
       pre.correc_autorisee,
       pre.correc_effectuee,
       pre.soutenance_autoris,
       pre.date_autoris_soutenance,
       pre.tem_avenant_cotut,
       pre.date_abandon,
       pre.date_transfert
from pre
    left join doctorant_substit dsub on dsub.from_id = pre.doctorant_id and dsub.histo_destruction is null

    left join etablissement e on e.id = pre.etablissement_id
    left join structure_substit sse on sse.from_id = e.structure_id and sse.histo_destruction is null
    left join etablissement esub on esub.structure_id = sse.to_id

    left join ecole_doct ed on ed.id = pre.ecole_doct_id
    left join structure_substit ssed on ssed.from_id = ed.structure_id and ssed.histo_destruction is null
    left join ecole_doct edsub on edsub.structure_id = ssed.to_id

    left join unite_rech ur on ur.id = pre.unite_rech_id
    left join structure_substit ssur on ssur.from_id = ur.structure_id and ssur.histo_destruction is null
    left join unite_rech ursub on ursub.structure_id = ssur.to_id
;


drop view if exists v_diff_variable;
drop view if exists src_variable;
create or replace view src_variable
as
with pre as (
    SELECT NULL::bigint AS id,
           tmp.source_code,
           src.id       AS source_id,
           e.id         AS etablissement_id,
           tmp.cod_vap  AS code,
           tmp.lib_vap  AS description,
           tmp.par_vap  AS valeur,
           tmp.date_deb_validite,
           tmp.date_fin_validite
    FROM tmp_variable tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.structure_id = src.etablissement_id
)
select pre.id,
       pre.source_code,
       pre.source_id,
       coalesce(esub.id, pre.etablissement_id) as etablissement_id,
--        pre.etablissement_id as etablissement_id_orig, -- pour debug
       pre.code,
       pre.description,
       pre.valeur,
       pre.date_deb_validite,
       pre.date_fin_validite
from pre
    left join etablissement e on e.id = pre.etablissement_id
    left join structure_substit sse on sse.from_id = e.structure_id and sse.histo_destruction is null
    left join etablissement esub on esub.structure_id = sse.to_id
;


/*
select sub.npd, ps.id, ps.code, ps.source_code, s.id, s.code, s.source_code
from unite_rech_substit sub
join pre_unite_rech pur on pur.id = sub.from_id and pur.histo_destruction is null
join unite_rech ur on ur.id = sub.to_id and ur.histo_destruction is null
join pre_structure ps on ps.id = pur.structure_id and ps.histo_destruction is null
join structure s on s.id = ur.structure_id and s.histo_destruction is null
where sub.histo_destruction is null
order by npd;
*/
