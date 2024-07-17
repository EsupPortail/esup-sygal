--
-- Saisie d'une thèse
-- Modifications à effectuer afin de pouvoir saisir une thèse dans SyGAL de A à Z.
--

--
-- Table ACTEUR
--

-- Ajouter la nouvelle colonne qualite_id
ALTER TABLE acteur ADD COLUMN qualite_id bigint;

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

-- 1- Supprimer la vue utilisant le champ
DROP VIEW v_diff_acteur;

-- 2- Supprimer le champ
ALTER TABLE acteur DROP COLUMN lib_role_compl;

-- 3- Recréer la vue
create view public.v_diff_acteur
            (source_code, source_id, operation, u_source_id, u_these_id, u_role_id, u_individu_id, u_etablissement_id,
             u_qualite, s_source_id, s_these_id, s_role_id, s_individu_id, s_etablissement_id,
             s_qualite, s_lib_role_compl, d_source_id, d_these_id, d_role_id, d_individu_id, d_etablissement_id,
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
                     s.lib_role_compl                       AS s_lib_role_compl,
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
       diff.s_lib_role_compl,
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
ALTER TABLE these ADD COLUMN discipline_sise_code varchar(255);

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
ALTER TABLE these DROP COLUMN besoin_expurge;
ALTER TABLE these DROP COLUMN cod_unit_rech;
ALTER TABLE these DROP COLUMN lib_unit_rech;
ALTER TABLE these DROP COLUMN source_code_sav;

-- Ajout de la nouvelle colone etab_cotut_id
ALTER TABLE these ADD COLUMN etab_cotut_id bigint;

ALTER TABLE these
    ADD CONSTRAINT fk_etab_cotut_id
        FOREIGN KEY (etab_cotut_id) REFERENCES etablissement(id);

-- Ajout de la nouvelle colone pays_cotut_id
ALTER TABLE these ADD COLUMN pays_cotut_id bigint;

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
    RETURNING t.*
    )
SELECT * FROM updated_these;

--
-- Table TITREACCES
--

-- Ajout de la nouvelle colone etab_cotut_id
ALTER TABLE titre_acces ADD COLUMN etab_id bigint;

ALTER TABLE titre_acces
    ADD CONSTRAINT fk_etab_id
        FOREIGN KEY (etab_id) REFERENCES etablissement(id);

-- Ajout de la nouvelle colone pays_id
ALTER TABLE titre_acces ADD COLUMN pays_id bigint;

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
ALTER TABLE unite_rech DROP COLUMN etab_support;
ALTER TABLE unite_rech DROP COLUMN autres_etab;

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