insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (select 28,
                                    'admission-generer-export-admissions',
                                    'Générer l''export des dossiers d''admissions validés')
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d
         join CATEGORIE_PRIVILEGE cp on cp.CODE = 'admission'
WHERE NOT EXISTS (SELECT 1
                  FROM PRIVILEGE p
                  WHERE p.CODE = d.code);

-- ajout des privilèges au profil GEST_ED
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-generer-export-admissions')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'GEST_ED',
                                           'ADMIN_TECH')
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

--
-- Modification des champs de la table Admission pour l'export vers Pégase
--

-- Renommer la colonne civilite en sexe
ALTER TABLE admission_etudiant RENAME COLUMN civilite TO sexe;
ALTER TABLE admission_etudiant RENAME COLUMN adresse_ligne2_etage TO adresse_ligne2_batiment;
ALTER TABLE admission_etudiant RENAME COLUMN adresse_ligne3_bvoie TO adresse_ligne3_voie;

-- Modifier les types et longueurs des colonnes selon la structure souhaitée
ALTER TABLE admission_etudiant
ALTER COLUMN prenom TYPE VARCHAR(40),
    ALTER COLUMN prenom2 TYPE VARCHAR(40),
    ALTER COLUMN prenom3 TYPE VARCHAR(40),
    ALTER COLUMN adresse_ligne1_etage TYPE VARCHAR(38),
    ALTER COLUMN adresse_ligne2_batiment TYPE VARCHAR(38),
    ALTER COLUMN adresse_ligne3_voie TYPE VARCHAR(38),
    ALTER COLUMN adresse_ligne4_complement TYPE VARCHAR(38),
    ALTER COLUMN adresse_code_postal TYPE BIGINT,
    ALTER COLUMN adresse_code_commune TYPE VARCHAR(5),
    ALTER COLUMN adresse_cp_ville_etrangere TYPE VARCHAR(10),
    ALTER COLUMN courriel TYPE VARCHAR(254);

-- Ajouter les nouvelles colonnes manquantes
ALTER TABLE admission_etudiant
    ADD COLUMN numero_candidat VARCHAR(10),
    ADD COLUMN code_commune_naissance VARCHAR(5),
    ADD COLUMN libelle_commune_naissance VARCHAR(50);