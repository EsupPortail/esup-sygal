--
-- 8.6.0
--

-- Changement de la taille de la colonne
ALTER TABLE admission_etudiant
    ALTER COLUMN adresse_cp_ville_etrangere TYPE VARCHAR(38);

-- Renommage de la validation
UPDATE admission_type_validation
SET libelle = 'Validation par les gestionnaires'
WHERE code = 'VALIDATION_GESTIONNAIRE';

-- Macro Renderer
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('Url#AccueilAdmission', '<p>Permet de récupérer l''url de l''accueil du module admission</p>', 'Url',
        'getAccueilAdmission');

-- ajout de la colonne pour le financement complémentaire
alter table admission_financement
    ADD column if not exists financement_compl_id bigint REFERENCES origine_financement (id);

-- déplacement du numero_candidature de admission_etudiant vers admission_admission
ALTER TABLE admission_admission
    ADD COLUMN numero_candidature VARCHAR(10);

UPDATE admission_admission aa
SET numero_candidature = ae.numero_candidat
    FROM admission_etudiant ae
WHERE aa.id = ae.admission_id;

ALTER TABLE admission_etudiant
DROP COLUMN numero_candidat;

-- Nouveau nature de fichier pour la charte du doctorat signée
INSERT INTO nature_fichier (id, code, libelle)
VALUES (220, 'ADMISSION_CHARTE_DOCTORAT_SIGNEE', 'Charte du doctorat signée')
ON CONFLICT DO NOTHING;

--Création d'une nouvelle table utile pour l'export vers pégase
create table if not exists admission_transmission
(
    id                                  bigserial
        primary key,
    admission_id                        bigint
        references admission_admission,
    code_voeu                           varchar(25),
    code_periode                        varchar(25),
    histo_createur_id                   bigint                                                       not null
        references utilisateur,
    histo_creation                      timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id               bigint
        references utilisateur,
    histo_modification                  timestamp,
    histo_destructeur_id                bigint
        references utilisateur,
    histo_destruction                   timestamp
);

--Ajout d'un nouveau privilège concernant l'ajout de données nécessaires pour l'export vers Pégase (par exemple)

insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (select 30,
                                    'admission-ajouter-donnees-export',
                                    'Ajouter des données nécessaires à l''export vers Pégase (par exemple)')
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d
         join CATEGORIE_PRIVILEGE cp on cp.CODE = 'admission'
WHERE NOT EXISTS (SELECT 1
                  FROM PRIVILEGE p
                  WHERE p.CODE = d.code);

-- ajout des privilèges au profil GEST_ED et ADMIN_TECH
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-ajouter-donnees-export')
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