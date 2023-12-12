DROP TABLE IF EXISTS admission_etat;
DROP TABLE IF EXISTS admission_validation;
DROP TABLE IF EXISTS admission_type_validation;
DROP TABLE IF EXISTS admission_verification;
DROP TABLE IF EXISTS admission_etudiant;
DROP TABLE IF EXISTS admission_inscription;
DROP TABLE IF EXISTS admission_financement;
DROP TABLE IF EXISTS admission_document;
DROP TABLE IF EXISTS admission_admission;

create table IF NOT EXISTS admission_etat
(
    code        varchar(1) not null primary key,
    libelle     varchar(1024),
    description text,
    icone       varchar(1024),
    couleur     varchar(1024),
    ordre       bigint
);

INSERT INTO admission_etat (code, libelle, description, icone, couleur, ordre)
VALUES ('C', 'En cours', 'Dossier d''admission en cours de saisie', '', '', 1),
       ('A', 'Abandonné', 'Dossier d''admission abandonné', '', '', 3),
       ('V', 'Validé', 'Dossier d''admission validé', '', '', 2)
ON CONFLICT DO NOTHING;

create table IF NOT EXISTS admission_admission
(
    id                    bigserial                                                    not null
        primary key,
    individu_id           bigint REFERENCES individu (id),
    etat_code             varchar(1) REFERENCES admission_etat (code),
    histo_createur_id     bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

create table IF NOT EXISTS admission_etudiant
(
    id                                        bigserial                                                    not null
        primary key,
    admission_id                              bigint REFERENCES admission_admission (id),
    civilite                                  varchar(5),
    nom_usuel                                 varchar(60),
    nom_famille                               varchar(60),
    prenom                                    varchar(60),
    prenom2                                   varchar(60),
    prenom3                                   varchar(60),
    date_naissance                            timestamp,
    ville_naissance                           varchar(60),
    nationalite_id                            bigint REFERENCES pays (id),
    pays_naissance_id                         bigint REFERENCES pays (id),
    code_nationalite                          varchar(5),
    ine                                       varchar(11),
    adresse_code_pays                         varchar(5),
    adresse_ligne1_etage                      varchar(45),
    adresse_ligne2_etage                      varchar(45),
    adresse_ligne3_batiment                   varchar(45),
    adresse_ligne3_bvoie                      varchar(45),
    adresse_ligne4_complement                 varchar(45),
    adresse_code_postal                       bigint,
    adresse_code_commune                      varchar(10),
    adresse_cp_ville_etrangere                varchar(10),
    numero_telephone1                         varchar(20),
    numero_telephone2                         varchar(20),
    courriel                                  varchar(255),
    situation_handicap                        boolean,
    niveau_etude                              integer,
    intitule_du_diplome_national              varchar(128),
    annee_dobtention_diplome_national         integer,
    etablissement_dobtention_diplome_national varchar(128),
    type_diplome_autre                        integer,
    intitule_du_diplome_autre                 varchar(128),
    annee_dobtention_diplome_autre            integer,
    etablissement_dobtention_diplome_autre    varchar(128),
    histo_createur_id                         bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation                            timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id                     bigint REFERENCES utilisateur (id),
    histo_modification                        timestamp,
    histo_destructeur_id                      bigint REFERENCES utilisateur (id),
    histo_destruction                         timestamp
);

create table IF NOT EXISTS admission_inscription
(
    id                       bigserial                                                    not null
        primary key,
    admission_id             bigint REFERENCES admission_admission (id),
    discipline_doctorat      varchar(60),
    specialite_doctorat      varchar(60),
    composante_doctorat_id   bigint REFERENCES etablissement (id),
    ecole_doctorale_id       bigint REFERENCES ecole_doct (id),
    unite_recherche_id       bigint REFERENCES unite_rech (id),
    directeur_these_id       bigint REFERENCES individu (id),
    prenom_directeur_these   varchar(60),
    nom_directeur_these      varchar(60),
    mail_directeur_these     varchar(255),
    codirecteur_these_id     bigint REFERENCES individu (id),
    prenom_codirecteur_these varchar(60),
    nom_codirecteur_these    varchar(60),
    mail_codirecteur_these   varchar(255),
    titre_these              varchar(60),
    confidentialite          boolean,
    date_confidentialite     timestamp,
    co_tutelle               boolean,
    pays_co_tutelle_id       bigint REFERENCES pays (id),
    co_encadrement           boolean,
    co_direction             boolean,
    histo_createur_id        bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation           timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id    bigint REFERENCES utilisateur (id),
    histo_modification       timestamp,
    histo_destructeur_id     bigint REFERENCES utilisateur (id),
    histo_destruction        timestamp
);

create table IF NOT EXISTS admission_financement
(
    id                      bigserial                                                    not null
        primary key,
    admission_id            bigint REFERENCES admission_admission (id),
    contrat_doctoral        boolean,
    employeur_contrat       varchar(60),
    detail_contrat_doctoral varchar(1024),
    histo_createur_id       bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation          timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id   bigint REFERENCES utilisateur (id),
    histo_modification      timestamp,
    histo_destructeur_id    bigint REFERENCES utilisateur (id),
    histo_destruction       timestamp
);

create table IF NOT EXISTS admission_type_validation
(
    id      bigserial   not null
        primary key,
    code    varchar(50) not null,
    libelle varchar(100)
);

INSERT INTO admission_type_validation (code, libelle)
VALUES ('ATTESTATION_HONNEUR', 'Attestation sur l''honneur de la part de l''étudiant');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('VALIDATION_GESTIONNAIRE', 'Validation effectuée par les gestionnaires');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('VALIDATION_DIRECTION_THESE', 'Validation par la direction de thèse');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('VALIDATION_CO_DIRECTION_THESE', 'Validation par la codirection de thèse');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('VALIDATION_UR', 'Validation par l''unité de recherche');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('VALIDATION_ED', 'Validation par l''école doctorale');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('SIGNATURE_PRESIDENT', 'Signature de la présidence de l''établissement d''inscription');

create table IF NOT EXISTS admission_validation
(
    id                    bigserial                                                    not null
        primary key,
    admission_id          bigint REFERENCES admission_admission (id),
    type_validation_id    bigint                                                       not null REFERENCES admission_type_validation (id),
    individu_id           bigint                                                       not null REFERENCES individu (id),
    histo_createur_id     bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

create table IF NOT EXISTS admission_document
(
    id                    bigserial                                                    not null
        primary key,
    admission_id          bigint REFERENCES admission_admission (id),
    nature_id             bigint REFERENCES nature_fichier (id),
    fichier_id            bigint REFERENCES fichier (id),
    histo_createur_id     bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

create table IF NOT EXISTS admission_verification
(
    id                       bigserial                                                    not null
        primary key,
    admission_etudiant_id    bigint REFERENCES admission_etudiant (id),
    admission_inscription_id bigint REFERENCES admission_inscription (id),
    admission_financement_id bigint REFERENCES admission_financement (id),
    admission_document_id    bigint REFERENCES admission_document (id),
    est_complet              boolean,
    individu_id              bigint REFERENCES individu (id),
    commentaire              text,
    histo_createur_id        bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation           timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id    bigint REFERENCES utilisateur (id),
    histo_modification       timestamp,
    histo_destructeur_id     bigint REFERENCES utilisateur (id),
    histo_destruction        timestamp
);

INSERT INTO nature_fichier (id, code, libelle)
VALUES (207, 'ADMISSION_DIPLOME_BAC', 'Diplôme de Bac + 5 permettant l''accès au doctorat'),
       (208, 'ADMISSION_CURRICULUM_VITAE', 'Curriculum Vitae'),
       (209, 'ADMISSION_FINANCEMENT', 'Justificatif du financement (contrat, attestation de l''employeur)'),
       (210, 'ADMISSION_PROJET_THESE', 'Le projet de thèse et son titre'),
       (211, 'ADMISSION_CONVENTION', 'Convention de formation doctorale'),
       (212, 'ADMISSION_CHARTE_DOCTORAT', 'Charte du doctorat'),
       (213, 'ADMISSION_DIPLOMES_RELEVES_TRADUITS',
        'Diplômes et relevés de notes traduits en français avec tampons originaux'),
       (214, 'ADMISSION_ACTE_NAISSANCE', 'Extrait d''acte de naissance'),
       (215, 'ADMISSION_PASSEPORT',
        'Photocopie du passeport (ou de la carte d''identité pour les ressortissants européens)'),
       (216, 'ADMISSION_DIPLOMES_TRAVAUX_EXPERIENCE_PRO', 'Diplômes, travaux et expérience professionnelle détaillés'),
       (217, 'ADMISSION_DEMANDE_COTUTELLE', 'Formulaire de demande de cotutelle'),
       (218, 'ADMISSION_DEMANDE_COENCADREMENT', 'Formulaire de demande de co-encadrement')
ON CONFLICT DO NOTHING;

-- GESTION DES PRIVILÈGES
--
-- Nouvelle catégorie de privilèges : Admission.
--
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE)
SELECT nextval('categorie_privilege_id_seq'), 'admission', 'Admission', 11000
WHERE NOT EXISTS (SELECT 1
                  FROM CATEGORIE_PRIVILEGE
                  WHERE CODE = 'admission');

--
-- Nouveaux privilèges.
--
insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (select 1,
                                    'admission-lister-tous-dossiers-admission',
                                    'Lister les dossiers d''admission en cours'
                             union
                             select 1,
                                    'admission-lister-son-dossier-admission',
                                    'Lister son dossier d''admission en cours'
                             union
                             select 2, 'admission-afficher-tous-dossiers-admission', 'Consulter un dossier d''admission'
                             union
                             select 2, 'admission-afficher-son-dossier-admission', 'Consulter son dossier d''admission'
                             union
                             select 4, 'admission-modifier-tous-dossiers-admission', 'Modifier un dossier d''admission'
                             union
                             select 4,
                                    'admission-modifier-son-dossier-admission',
                                    'Modifier son dossier d''admission'
                             union
                             select 5, 'admission-historiser', 'Historiser un dossier d''admission'
                             union
                             select 6,
                                    'admission-supprimer-tous-dossiers-admission',
                                    'Supprimer un dossier d''admission'
                             union
                             select 6, 'admission-supprimer-son-dossier-admission', 'Supprimer son dossier d''admission'
                             union
                             select 7, 'admission-verifier', 'Ajouter des commentaires au dossier d''admission'
                             union
                             select 8, 'admission-valider-tout', 'Valider un dossier d''admission'
                             union
                             select 9, 'admission-valider-sien', 'Valider son dossier d''admission'
                             union
                             select 10, 'admission-devalider-tout', 'Dévalider son dossier d''admission'
                             union
                             select 11, 'admission-devalider-sien', 'Dévalider un dossier d''admission'
                             union
                             select 12,
                                    'admission-televerser-tout-document',
                                    'Téléverser un document dans un dossier d''admission'
                             union
                             select 13,
                                    'admission-televerser-son-document',
                                    'Téléverser un document dans son d''admission'
                             union
                             select 14,
                                    'admission-supprimer-tout-document',
                                    'Supprimer un document dans un dossier d''admission'
                             union
                             select 15,
                                    'admission-supprimer-son-document',
                                    'Supprimer un document dans son dossier d''admission'
                             union
                             select 16,
                                    'admission-telecharger-tout-document',
                                    'Télécharger un document dans un dossier d''admission'
                             union
                             select 17,
                                    'admission-telecharger-son-document',
                                    'Télécharger un document dans son dossier d''admission'
                             union
                             select 18,
                                    'admission-notifier-gestionnaires',
                                    'Notifier ses gestionnaires de la fin de saisie de son dossier d''admission'
                             union
                             select 19,
                                    'admission-commentaires-ajoutes',
                                    'Notifier l''étudiant des commentaires ajoutés sur son dossier d''admission')
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d
         join CATEGORIE_PRIVILEGE cp on cp.CODE = 'admission'
WHERE NOT EXISTS (SELECT 1
                  FROM PRIVILEGE p
                  WHERE p.CODE = d.code);

--
-- Accord de privilèges à des profils.
--
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-lister-tous-dossiers-admission'
                           union
                           select 'admission', 'admission-lister-son-dossier-admission'
                           union
                           select 'admission', 'admission-afficher-tous-dossiers-admission'
                           union
                           select 'admission', 'admission-afficher-son-dossier-admission'
                           union
                           select 'admission', 'admission-modifier-tous-dossiers-admission'
                           union
                           select 'admission', 'admission-modifier-son-dossier-admission'
                           union
                           select 'admission', 'admission-historiser'
                           union
                           select 'admission', 'admission-supprimer-tous-dossiers-admission'
                           union
                           select 'admission', 'admission-supprimer-son-dossier-admission'
                           union
                           select 'admission', 'admission-valider-tout'
                           union
                           select 'admission', 'admission-valider-sien'
                           union
                           select 'admission', 'admission-devalider-tout'
                           union
                           select 'admission', 'admission-devalider-sien'
                           union
                           select 'admission', 'admission-verifier'
                           union
                           select 'admission', 'admission-televerser-tout-document'
                           union
                           select 'admission', 'admission-televerser-son-document'
                           union
                           select 'admission', 'admission-supprimer-tout-document'
                           union
                           select 'admission', 'admission-supprimer-son-document'
                           union
                           select 'admission', 'admission-telecharger-tout-document'
                           union
                           select 'admission', 'admission-telecharger-son-document'
                           union
                           select 'admission', 'admission-notifier-gestionnaires'
                           union
                           select 'admission', 'admission-commentaires-ajoutes')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'ADMIN_TECH'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-lister-son-dossier-admission'
                           union
                           select 'admission', 'admission-afficher-son-dossier-admission'
                           union
                           select 'admission', 'admission-modifier-son-dossier-admission'
                           union
                           select 'admission', 'admission-supprimer-son-dossier-admission'
                           union
                           select 'admission', 'admission-valider-sien'
                           union
                           select 'admission', 'admission-devalider-sien'
                           union
                           select 'admission', 'admission-televerser-son-document'
                           union
                           select 'admission', 'admission-supprimer-son-document'
                           union
                           select 'admission', 'admission-telecharger-son-document')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'DOCTORANT',
                                           'GEST_ED',
                                           'GEST_UR',
                                           'RESP_UR',
                                           'RESP_ED',
                                           'D',
                                           'K'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-notifier-gestionnaires')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'DOCTORANT')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-commentaires-ajoutes')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'GEST_ED',
                                           'GEST_UR')
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