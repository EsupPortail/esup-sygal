--
-- Saisie d'une thèse
-- Modifications à effectuer afin de pouvoir saisir une thèse dans SyGAL de A à Z.
--

--
-- Table ACTEUR
--

alter table acteur add if not exists acteur_ecoledoct_id bigint;
alter table acteur add constraint acteur_ecole_doct_id_fk foreign key (acteur_ecoledoct_id) references ecole_doct;
alter table acteur add if not exists principal boolean default false not null;
alter table acteur add if not exists exterieur boolean default false not null;
alter table acteur add if not exists ordre smallint default 1 not null;
-- Index manquant sur individu_rech
create index if not exists individu_rech_haystack_index on individu_rech (haystack asc);

-- Ajouter la nouvelle colonne qualite_id
ALTER TABLE acteur ADD COLUMN if not exists qualite_id bigint;

ALTER TABLE acteur
    ADD CONSTRAINT fk_qualite_id
        FOREIGN KEY (qualite_id) REFERENCES soutenance_qualite(id);

-- Mettre à jour les valeurs de qualite_id
UPDATE acteur a
SET qualite_id = sq.id
    FROM soutenance_qualite sq
WHERE sq.libelle = a.qualite;

-- Supprimer l'ancienne colonne qualite (attendre avant de faire)
-- ALTER TABLE acteur DROP COLUMN qualite;

-- Supprimer l'ancienne colonne lib_role_compl (toujours null) :

-- 1- Supprimer les vues utilisant le champ
DROP VIEW IF EXISTS v_diff_acteur;
DROP VIEW IF EXISTS src_acteur;

-- 2- Supprimer le champ
ALTER TABLE acteur DROP COLUMN if exists lib_role_compl;

-- 3- Recréer les vues
create or replace view src_acteur
            (id, source_id, source_code, these_id, role_id, individu_id, etablissement_id, qualite) as
WITH pre AS (SELECT NULL::bigint      AS id,
                    tmp.source_code,
                    src.id            AS source_id,
                    i.id              AS individu_id,
                    t.id              AS these_id,
                    r.id              AS role_id,
                    eact.id           AS etablissement_id,
                    tmp.lib_cps       AS qualite
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
                    eact.id                            AS etablissement_id,
                    tmp.lib_cps                        AS qualite
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
                    eact.id                 AS etablissement_id,
                    tmp.lib_cps             AS qualite
             FROM tmp_acteur tmp
                      JOIN source src ON src.id = tmp.source_id
                      JOIN individu i ON i.source_code::text = tmp.individu_id::text
                      JOIN these t ON t.source_code::text = tmp.these_id::text
                      JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text <> 'P'::text
                      LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text)
SELECT pre.id,
       pre.source_id,
       pre.source_code,
       pre.these_id,
       pre.role_id,
       COALESCE(isub.to_id, pre.individu_id)      AS individu_id,
       COALESCE(esub.to_id, pre.etablissement_id) AS etablissement_id,
       pre.qualite
FROM pre
         LEFT JOIN substit_individu isub ON isub.from_id = pre.individu_id
         LEFT JOIN substit_etablissement esub ON esub.from_id = pre.etablissement_id;

create view public.v_diff_acteur
            (source_code, source_id, operation, u_source_id, u_these_id, u_role_id, u_individu_id, u_etablissement_id,
             u_qualite, s_source_id, s_these_id, s_role_id, s_individu_id, s_etablissement_id,
             s_qualite, d_source_id, d_these_id, d_role_id, d_individu_id, d_etablissement_id,
             d_qualite)
as
WITH diff AS (SELECT COALESCE(s.source_code, d.source_code) AS source_code,
                     COALESCE(s.source_id, d.source_id)     AS source_id,
                     CASE
                         WHEN src.synchro_insert_enabled = true AND s.source_code IS NOT NULL AND d.source_code IS NULL
                             THEN 'insert'::text
                         WHEN src.synchro_undelete_enabled = true AND s.source_code IS NOT NULL AND
                              d.source_code IS NOT NULL AND d.histo_destruction IS NOT NULL AND
                              d.histo_destruction <= LOCALTIMESTAMP(0) THEN 'undelete'::text
                         WHEN src.synchro_update_enabled = true AND s.source_code IS NOT NULL AND
                              d.source_code IS NOT NULL AND
                              (d.histo_destruction IS NULL OR d.histo_destruction > LOCALTIMESTAMP(0)) THEN 'update'::text
                         WHEN src.synchro_delete_enabled = true AND s.source_code IS NULL AND
                              d.source_code IS NOT NULL AND
                              (d.histo_destruction IS NULL OR d.histo_destruction > LOCALTIMESTAMP(0)) THEN 'delete'::text
                         ELSE NULL::text
                         END                                AS operation,
                     CASE
                         WHEN d.source_id <> s.source_id OR d.source_id IS NULL AND s.source_id IS NOT NULL OR
                              d.source_id IS NOT NULL AND s.source_id IS NULL THEN 1
                         ELSE 0
                         END                                AS u_source_id,
                     CASE
                         WHEN d.these_id <> s.these_id OR d.these_id IS NULL AND s.these_id IS NOT NULL OR
                              d.these_id IS NOT NULL AND s.these_id IS NULL THEN 1
                         ELSE 0
                         END                                AS u_these_id,
                     CASE
                         WHEN d.role_id <> s.role_id OR d.role_id IS NULL AND s.role_id IS NOT NULL OR
                              d.role_id IS NOT NULL AND s.role_id IS NULL THEN 1
                         ELSE 0
                         END                                AS u_role_id,
                     CASE
                         WHEN d.individu_id <> s.individu_id OR d.individu_id IS NULL AND s.individu_id IS NOT NULL OR
                              d.individu_id IS NOT NULL AND s.individu_id IS NULL THEN 1
                         ELSE 0
                         END                                AS u_individu_id,
                     CASE
                         WHEN d.etablissement_id <> s.etablissement_id OR
                              d.etablissement_id IS NULL AND s.etablissement_id IS NOT NULL OR
                              d.etablissement_id IS NOT NULL AND s.etablissement_id IS NULL THEN 1
                         ELSE 0
                         END                                AS u_etablissement_id,
                     CASE
                         WHEN d.qualite::text <> s.qualite::text OR d.qualite IS NULL AND s.qualite IS NOT NULL OR
                     d.qualite IS NOT NULL AND s.qualite IS NULL THEN 1
        ELSE 0
END                                AS u_qualite,
                     s.source_id                            AS s_source_id,
                     s.these_id                             AS s_these_id,
                     s.role_id                              AS s_role_id,
                     s.individu_id                          AS s_individu_id,
                     s.etablissement_id                     AS s_etablissement_id,
                     s.qualite                              AS s_qualite,
                     d.source_id                            AS d_source_id,
                     d.these_id                             AS d_these_id,
                     d.role_id                              AS d_role_id,
                     d.individu_id                          AS d_individu_id,
                     d.etablissement_id                     AS d_etablissement_id,
                     d.qualite                              AS d_qualite
              FROM acteur d
                       FULL JOIN src_acteur s ON s.source_id = d.source_id AND s.source_code::text = d.source_code::text
                       JOIN source src ON src.id = COALESCE(s.source_id, d.source_id) AND src.importable = true)
SELECT diff.source_code,
       diff.source_id,
       diff.operation,
       diff.u_source_id,
       diff.u_these_id,
       diff.u_role_id,
       diff.u_individu_id,
       diff.u_etablissement_id,
       diff.u_qualite,
       diff.s_source_id,
       diff.s_these_id,
       diff.s_role_id,
       diff.s_individu_id,
       diff.s_etablissement_id,
       diff.s_qualite,
       diff.d_source_id,
       diff.d_these_id,
       diff.d_role_id,
       diff.d_individu_id,
       diff.d_etablissement_id,
       diff.d_qualite
FROM diff
WHERE diff.operation IS NOT NULL
  AND (diff.operation = 'undelete'::text OR 0 <
                                            (diff.u_source_id + diff.u_these_id + diff.u_role_id + diff.u_individu_id +
                                             diff.u_etablissement_id + diff.u_qualite));

--
-- Table THESE
--

-- Ajout de la nouvelle colone discipline_sise_code
ALTER TABLE these ADD COLUMN if not exists discipline_sise_code varchar(255);

UPDATE these t
SET discipline_sise_code = ds.code
    FROM discipline_sise ds
WHERE ds.code = t.code_sise_disc;

UPDATE these t
SET discipline_sise_code = ds.code
    FROM discipline_sise ds
WHERE ds.libelle = t.lib_disc;

-- Supprimer les anciennes colonnes code_sise_disc, lib_disc (attendre avant de faire)
-- ALTER TABLE these DROP COLUMN code_sise_disc;
-- ALTER TABLE these DROP COLUMN lib_disc;

-- Colonnes devenues inutiles
-- Attention certaines colonnes sont utilisées dans des vues (Utiliser le script 03_matviews.sql présent ici : doc/release-notes/8.4.0)
ALTER TABLE these DROP COLUMN if exists besoin_expurge;
ALTER TABLE these DROP COLUMN if exists cod_unit_rech;
ALTER TABLE these DROP COLUMN if exists lib_unit_rech;
ALTER TABLE these DROP COLUMN if exists source_code_sav;
-- Supprimer la vue utilisant le champ date_autoris_soutenance
DROP VIEW if exists v_diff_these;
ALTER TABLE these DROP COLUMN if exists date_autoris_soutenance;
ALTER TABLE these DROP COLUMN if exists date_prop;

-- Ajout de la nouvelle colone etab_cotut_id
ALTER TABLE these ADD COLUMN if not exists etab_cotut_id bigint;

ALTER TABLE these
    ADD CONSTRAINT fk_etab_cotut_id
        FOREIGN KEY (etab_cotut_id) REFERENCES etablissement(id);

-- Ajout de la nouvelle colone pays_cotut_id
ALTER TABLE these ADD COLUMN if not exists pays_cotut_id bigint;

ALTER TABLE these
    ADD CONSTRAINT fk_pays_cotut_id
        FOREIGN KEY (pays_cotut_id) REFERENCES pays(id);

WITH updated_these AS (
UPDATE these t
SET pays_cotut_id = p.id
    FROM pays p
WHERE unaccent(lower(t.lib_pays_cotut)) = unaccent(lower(p.libelle))
   OR
    unaccent(lower(t.lib_pays_cotut)) = unaccent(lower(p.libelle_iso))
   OR (
    unaccent(lower(t.lib_pays_cotut)) = 'republique tcheque' AND unaccent(lower(p.libelle)) = 'tchequie'
    )
   OR (
    unaccent(lower(t.lib_pays_cotut)) = 'cote d ivoire' AND unaccent(lower(p.libelle)) = 'cote d''ivoire'
    )
   OR (
    unaccent(lower(t.lib_pays_cotut)) = replace(lower(p.libelle), '-', ' ')
    )
   OR (
    unaccent(lower(t.lib_pays_cotut)) = replace(lower(p.libelle_iso), '-', ' ')
    )
    RETURNING t.*
    )
SELECT * FROM updated_these;

-- Recréer les vues utilisant les champs supprimés
DROP VIEW IF EXISTS src_these;
create view src_these
            (id, source_code, source_id, doctorant_id, etablissement_id, ecole_doct_id, unite_rech_id, titre,
             etat_these, resultat, code_sise_disc, lib_disc, date_prem_insc, date_soutenance,
             date_fin_confid, lib_etab_cotut, lib_pays_cotut, correc_autorisee, correc_effectuee, soutenance_autoris, tem_avenant_cotut, date_abandon, date_transfert)
as
WITH pre AS (WITH version_corrigee_deposee AS (SELECT t.source_code,
                                                      f.id AS fichier_id
                                               FROM fichier_these ft
                                                        JOIN these t ON ft.these_id = t.id
                                                        JOIN fichier f ON ft.fichier_id = f.id AND f.histo_destruction IS NULL
                                                        JOIN nature_fichier nf
                                                             ON f.nature_id = nf.id AND nf.code::text = 'THESE_PDF'::text
        JOIN version_fichier vf
        ON f.version_fichier_id = vf.id AND vf.code::text = 'VOC'::text
        WHERE ft.est_annexe = false
        AND ft.est_expurge = false
        AND ft.retraitement IS NULL)
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
                    tmp.tem_avenant_cotut,
                    tmp.dat_abandon                AS date_abandon,
                    tmp.dat_transfert_dep          AS date_transfert
             FROM tmp_these tmp
                      JOIN source src ON src.id = tmp.source_id
                      JOIN etablissement e ON e.id = src.etablissement_id
                      JOIN doctorant d ON d.source_code::text = tmp.doctorant_id::text
                      LEFT JOIN ecole_doct ed ON ed.source_code::text = tmp.ecole_doct_id::text
                      LEFT JOIN unite_rech ur ON ur.source_code::text = tmp.unite_rech_id::text
                      LEFT JOIN version_corrigee_deposee vcd ON vcd.source_code::text = tmp.source_code::text)
SELECT pre.id,
       pre.source_code,
       pre.source_id,
       COALESCE(dsub.to_id, pre.doctorant_id)     AS doctorant_id,
       COALESCE(esub.to_id, pre.etablissement_id) AS etablissement_id,
       COALESCE(edsub.to_id, pre.ecole_doct_id)   AS ecole_doct_id,
       COALESCE(ursub.to_id, pre.unite_rech_id)   AS unite_rech_id,
       pre.titre,
       pre.etat_these,
       pre.resultat,
       pre.code_sise_disc,
       pre.lib_disc,
       pre.date_prem_insc,
       pre.date_soutenance,
       pre.date_fin_confid,
       pre.lib_etab_cotut,
       pre.lib_pays_cotut,
       pre.correc_autorisee,
       pre.correc_effectuee,
       pre.soutenance_autoris,
       pre.tem_avenant_cotut,
       pre.date_abandon,
       pre.date_transfert
FROM pre
         LEFT JOIN substit_doctorant dsub ON dsub.from_id = pre.doctorant_id
         LEFT JOIN substit_etablissement esub ON esub.from_id = pre.etablissement_id
         LEFT JOIN substit_ecole_doct edsub ON edsub.from_id = pre.ecole_doct_id
         LEFT JOIN substit_unite_rech ursub ON ursub.from_id = pre.unite_rech_id;

--
-- Table TITREACCES
--

-- Ajout de la nouvelle colone etab_cotut_id
ALTER TABLE titre_acces ADD COLUMN if not exists etab_id bigint;

ALTER TABLE titre_acces
    ADD CONSTRAINT fk_etab_id
        FOREIGN KEY (etab_id) REFERENCES etablissement(id);

-- Ajout de la nouvelle colonne pays_id
ALTER TABLE titre_acces ADD COLUMN if not exists pays_id bigint;

ALTER TABLE titre_acces
    ADD CONSTRAINT fk_pays_id
        FOREIGN KEY (pays_id) REFERENCES pays(id);

UPDATE titre_acces t
SET pays_id = (
    SELECT p.id
    FROM pays p
    WHERE p.id = CAST(t.code_pays_titre_acces AS BIGINT)
)
WHERE t.code_pays_titre_acces IS NOT NULL;

--
-- Table UNITERECHERCHE
--

-- Colonnes devenues inutiles
-- Attention certaines colonnes sont utilisées dans des vues (Utiliser le script 03_matviews.sql présent ici : doc/release-notes/8.4.0)
ALTER TABLE unite_rech DROP COLUMN if exists etab_support;
ALTER TABLE unite_rech DROP COLUMN if exists autres_etab;

--
-- Table INDIVIDU
--

UPDATE individu i
SET pays_id_nationalite = (SELECT p.id
                           FROM pays p
                           WHERE INITCAP(SUBSTRING(p.libelle_nationalite, 1, 1)) ||
                                 lower(SUBSTRING(unaccent(p.libelle_nationalite), 2)) =
                                 INITCAP(SUBSTRING(i.nationalite, 1, 1)) ||
                                 lower(SUBSTRING(unaccent(i.nationalite), 2)))
WHERE i.nationalite IS NOT NULL
  and i.pays_id_nationalite is null;

-- Ajout de la nouvelle colonne pays_naissance_id
alter table individu add if not exists pays_naissance_id bigint REFERENCES pays (id);


DROP VIEW v_extract_theses;
ALTER TABLE these DROP COLUMN if exists date_prev_soutenance;

-- Script de création récupéré de 7.0.0/substitutions/00_substitution_prepare.sql
create or replace view v_extract_theses
            (date_extraction, id, civilite, nom_usuel, nom_patronymique, prenom1, date_naissance, nationalite,
             email_pro, email_contact, ine, num_etudiant, num_these, titre, code_sise_disc, lib_disc, dirs, codirs,
             coencs, etab_lib, ed_code, ed_lib, ur_code, ur_lib, lib_etab_cotut, lib_pays_cotut, libelle_titre_acces,
             libelle_etb_titre_acces, financ_origs_visibles, financ_annees, financ_origs, financ_compls, financ_types,
             domaines, date_prem_insc, date_abandon, date_transfert, date_soutenance,
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


-- Gestion des privilèges
-- Suppression du privilège pour le Doctorant pour modifier sa thèse, qui n'était pas utilisé
DELETE
from role_privilege
where privilege_id in (select id from privilege where code LIKE 'modification-de-ses-theses')
  and role_id in (select id from role where code LIKE 'DOCTORANT');

delete from profil_privilege pp1
where exists (
    select *
    from profil_privilege pp
             join profil on pp.profil_id = profil.id and role_id in ('DOCTORANT')
             join privilege p on pp.privilege_id = p.id
    where p.code in ('modification-de-ses-theses')
      and pp.profil_id = pp1.profil_id and pp.privilege_id = pp1.privilege_id
);

-- Suppression du privilège pour le Gestionnaire d'ED pour modifier leur thèse
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'these', 'modification-de-ses-theses')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'GEST_ED'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
;