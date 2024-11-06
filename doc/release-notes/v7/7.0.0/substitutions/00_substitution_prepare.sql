--
-- patch : modif pas encore faite en prod
--

create sequence if not exists source_id_seq;
select setval('source_id_seq', coalesce(max(id),1)) from source;
alter table source alter column id set default nextval('source_id_seq');

alter table tmp_doctorant add column code_apprenant_in_source varchar(128);
alter table doctorant add column code_apprenant_in_source varchar(128);



create or replace view v_extract_theses
            (date_extraction, id, civilite, nom_usuel, nom_patronymique, prenom1, date_naissance, nationalite,
             email_pro, email_contact, ine, num_etudiant, num_these, titre, code_sise_disc, lib_disc, dirs, codirs,
             coencs, etab_lib, ed_code, ed_lib, ur_code, ur_lib, lib_etab_cotut, lib_pays_cotut, libelle_titre_acces,
             libelle_etb_titre_acces, financ_origs_visibles, financ_annees, financ_origs, financ_compls, financ_types,
             domaines, date_prem_insc, date_abandon, date_transfert, date_prev_soutenance, date_soutenance,
             date_fin_confid, duree_these_mois, date_depot_vo, date_depot_voc, etat_these, soutenance_autoris,
             confidentielle, resultat, correc_autorisee, depot_pdf, depot_annexe, autoris_mel, autoris_embargo_duree,
             autoris_motif, dernier_rapport_activite, dernier_rapport_csi)
as
WITH mails_contacts AS (
    SELECT DISTINCT mail_confirmation.individu_id,
                    first_value(mail_confirmation.email) OVER (PARTITION BY mail_confirmation.individu_id ORDER BY mail_confirmation.id DESC) AS email
    FROM mail_confirmation
    WHERE mail_confirmation.etat::text = 'C'::text
), directeurs AS (
    SELECT a.these_id,
           string_agg(concat(i.nom_usuel, ' ', i.prenom1), ' ; '::text) AS identites
    FROM acteur a
             JOIN role r ON a.role_id = r.id AND r.code::text = 'D'::text
             JOIN individu i ON a.individu_id = i.id
    WHERE a.histo_destruction IS NULL
    GROUP BY a.these_id
), codirecteurs AS (
    SELECT a.these_id,
           string_agg(concat(i.nom_usuel, ' ', i.prenom1), ' ; '::text) AS identites
    FROM acteur a
             JOIN role r ON a.role_id = r.id AND (r.code::text = ANY (ARRAY['C'::character varying::text, 'K'::character varying::text]))
             JOIN individu i ON a.individu_id = i.id
    WHERE a.histo_destruction IS NULL
    GROUP BY a.these_id
), coencadrants AS (
    SELECT a.these_id,
           string_agg(concat(i.nom_usuel, ' ', i.prenom1), ' ; '::text) AS identites
    FROM acteur a
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
       th.lib_disc,
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
       to_char(th.date_prev_soutenance, 'DD/MM/YYYY'::text) AS date_prev_soutenance,
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

