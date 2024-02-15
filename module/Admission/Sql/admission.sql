-- Suppression des tables associés à Admission
DROP TABLE IF EXISTS admission_etat CASCADE;
DROP TABLE IF EXISTS admission_validation;
DROP TABLE IF EXISTS admission_avis;
DROP TABLE IF EXISTS admission_type_validation;
DROP TABLE IF EXISTS admission_verification;
DROP TABLE IF EXISTS admission_etudiant;
DROP TABLE IF EXISTS admission_inscription;

--------Import des composantes d'enseignement-----------------------
--Ajout de la table Composante d'enseignement
INSERT INTO source(id, code, libelle, etablissement_id, importable)
VALUES (6, 'UCN::octopus', 'Octopus UCN', 2, true)
ON CONFLICT DO NOTHING;
INSERT INTO type_structure(id, code, libelle)
VALUES (4, 'composante-enseignement', 'Composante d''enseignement')
ON CONFLICT DO NOTHING;;

--Suppression des données existantes concernant l'import des composantes d'enseignement
drop view if exists v_diff_structure;
drop view if exists src_structure;
drop view if exists v_diff_composante_ens;
drop view if exists src_composante_ens;
drop table if exists tmp_composante_ens;
drop table if exists composante_ens;
--Création de la table TEMP de Composante d'enseignement
create table public.tmp_composante_ens
(
    id                    bigserial,
    sigle                 varchar(64),
    libelle_long          varchar(200),
    insert_date           timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id             bigint                                                             not null,
    source_code           varchar(64)                                                        not null,
    source_insert_date    timestamp    default ('now'::text)::timestamp without time zone,
    histo_creation        timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_modification    timestamp(0),
    histo_destruction     timestamp(0),
    histo_createur_id     bigint                                                             not null,
    histo_modificateur_id bigint,
    histo_destructeur_id  bigint
);

create index tmp_composante_ens_source_code_index
    on public.tmp_composante_ens (source_code);

create index tmp_composante_ens_source_id_index
    on public.tmp_composante_ens (source_id);

create unique index tmp_composante_ens_unique_index
    on public.tmp_composante_ens (source_id, source_code);

--Création de la table Composante d'enseignement
create table public.composante_ens
(
    id                    bigserial                                                    not null
        primary key,
    structure_id          bigint references structure (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_createur_id     bigint                                                       not null
        constraint composante_ens_hcfk
            references public.utilisateur
            on delete cascade,
    histo_modification    timestamp,
    histo_modificateur_id bigint
        constraint composante_ens_hmfk
            references public.utilisateur
            on delete cascade,
    histo_destruction     timestamp,
    histo_destructeur_id  bigint
        constraint composante_ens_hdfk
            references public.utilisateur
            on delete cascade,
    source_id             bigint                                                       not null
        constraint composante_ens_source_fk
            references public.source
            on delete cascade,
    source_code           varchar(64)                                                  not null
);

create index composante_ens_hc_idx
    on public.composante_ens (histo_createur_id);

create index composante_ens_hd_idx
    on public.composante_ens (histo_destructeur_id);

create index composante_ens_hm_idx
    on public.composante_ens (histo_modificateur_id);

create unique index composante_ens_source_code_un
    on public.composante_ens (source_code);

create index composante_ens_source_idx
    on public.composante_ens (source_id);

--Création de la vue src_structure avec les infos de tmp_composante_ens
create or replace view src_structure(id, source_code, code, source_id, type_structure_id, sigle, libelle) as
SELECT NULL::bigint                                                                               AS id,
       tmp.source_code,
       ltrim(substr(tmp.source_code::text, strpos(tmp.source_code::text, '::'::text)), ':'::text) AS code,
       src.id                                                                                     AS source_id,
       ts.id                                                                                      AS type_structure_id,
       tmp.sigle,
       tmp.libelle
FROM tmp_structure tmp
         JOIN type_structure ts ON ts.code::text = tmp.type_structure_id::text
         JOIN source src ON src.id = tmp.source_id
union
select NULL::bigint                                                                               AS id,
       tmp.source_code,
       ltrim(substr(tmp.source_code::text, strpos(tmp.source_code::text, '::'::text)), ':'::text) AS code,
       src.id                                                                                     AS source_id,
       ts.id                                                                                      AS type_structure_id,
       tmp.sigle,
       tmp.libelle_long                                                                           as libelle
FROM tmp_composante_ens tmp
         JOIN type_structure ts ON ts.code = 'composante-enseignement'
         JOIN source src ON src.id = tmp.source_id
;

--Création de la vue src_composante_ens avec les infos de tmp_composante_ens
create or replace view src_composante_ens(id, source_code, source_id) as
select NULL::bigint AS id,
       tmp.source_code,
       src.id       AS source_id,
       s.id         as structure_id
FROM tmp_composante_ens tmp
         JOIN structure s ON s.source_code = tmp.source_code
         JOIN source src ON src.id = tmp.source_id
;
---------------------------------------------
DROP TABLE IF EXISTS admission_financement;
DROP TABLE IF EXISTS admission_document;
DROP TABLE IF EXISTS admission_convention_formation_doctorale CASCADE;
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
VALUES ('C', 'En cours de saisie', 'Dossier d''admission en cours de saisie', '', '', 1),
       ('E', 'En cours de validation', 'Dossier d''admission en cours de validation', '', '', 2),
       ('A', 'Abandonné', 'Dossier d''admission abandonné', '', '', 4),
       ('R', 'Rejeté', 'Dossier d''admission rejeté', '', '', 5),
       ('V', 'Validé', 'Dossier d''admission validé', '', '', 3)
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
    id                                        bigserial                                                    not null
        primary key,
    admission_id                              bigint REFERENCES admission_admission (id),
    discipline_doctorat                       varchar(60),
    specialite_doctorat                       varchar(255),
    composante_doctorat_id                    bigint REFERENCES composante_ens (id),
    ecole_doctorale_id                        bigint REFERENCES ecole_doct (id),
    unite_recherche_id                        bigint REFERENCES unite_rech (id),
    etablissement_inscription_id              bigint REFERENCES etablissement (id),
    directeur_these_id                        bigint REFERENCES individu (id),
    prenom_directeur_these                    varchar(60),
    nom_directeur_these                       varchar(60),
    mail_directeur_these                      varchar(255),
    fonction_directeur_these_id               bigint REFERENCES soutenance_qualite (id),
    codirecteur_these_id                      bigint REFERENCES individu (id),
    prenom_codirecteur_these                  varchar(60),
    nom_codirecteur_these                     varchar(60),
    mail_codirecteur_these                    varchar(255),
    fonction_codirecteur_these_id             bigint REFERENCES soutenance_qualite (id),
    unite_recherche_codirecteur_id            bigint REFERENCES unite_rech (id),
    etablissement_rattachement_codirecteur_id bigint REFERENCES etablissement (id),
    titre_these                               varchar(60),
    confidentialite                           boolean,
    date_confidentialite                      timestamp,
    co_tutelle                                boolean,
    pays_co_tutelle_id                        bigint REFERENCES pays (id),
    etablissement_co_tutelle_id               bigint REFERENCES etablissement (id),
    co_encadrement                            boolean,
    co_direction                              boolean,
    histo_createur_id                         bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation                            timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id                     bigint REFERENCES utilisateur (id),
    histo_modification                        timestamp,
    histo_destructeur_id                      bigint REFERENCES utilisateur (id),
    histo_destruction                         timestamp
);

UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 4;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 0;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 554;
UPDATE public.soutenance_qualite
SET Admission = 'N'
WHERE id = 12;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 16;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 174;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 11;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 574;
UPDATE public.soutenance_qualite
SET Admission = 'N'
WHERE id = 434;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 94;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 7;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 394;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 254;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 5;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 114;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 474;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 134;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 454;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 214;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 8;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 10;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 6;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 154;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 494;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 374;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 2;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 274;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 414;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 537;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 54;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 9;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 354;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 1;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 34;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 536;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 294;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 514;
UPDATE public.soutenance_qualite
SET admission = 'O'
WHERE id = 74;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 194;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 234;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 314;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 334;
UPDATE public.soutenance_qualite
SET admission = 'N'
WHERE id = 35;

create table IF NOT EXISTS admission_financement
(
    id                                  bigserial                                                    not null
        primary key,
    admission_id                        bigint REFERENCES admission_admission (id),
    contrat_doctoral                    boolean,
    financement_id                      bigint REFERENCES origine_financement (id),
    employeur_contrat                   varchar(60),
    detail_contrat_doctoral             varchar(1024),
    temps_travail                       integer,
    est_salarie                         boolean,
    etablissement_laboratoire_recherche varchar(100),
    statut_professionnel                varchar(200),
    histo_createur_id                   bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation                      timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id               bigint REFERENCES utilisateur (id),
    histo_modification                  timestamp,
    histo_destructeur_id                bigint REFERENCES utilisateur (id),
    histo_destruction                   timestamp
);

--------------------------- Validations du dossier d'admission ---------------------------
create table IF NOT EXISTS admission_type_validation
(
    id      bigserial   not null
        primary key,
    code    varchar(60) not null,
    libelle varchar(150)
);

INSERT INTO admission_type_validation (code, libelle)
VALUES ('ATTESTATION_HONNEUR_CHARTE_DOCTORALE',
        'Attestation sur l''honneur par l''étudiant de la bonne lecture de sa charte doctorale');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('ATTESTATION_HONNEUR', 'Attestation sur l''honneur de la part de l''étudiant');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('VALIDATION_GESTIONNAIRE', 'Validation effectuée par les gestionnaires');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('VALIDATION_CONVENTION_FORMATION_DOCT_DIR_THESE',
        'Validation de la bonne lecture de la convention de formation doctorale par la direction de thèse');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('VALIDATION_CONVENTION_FORMATION_DOCT_CODIR_THESE',
        'Validation de la bonne lecture de la convention de formation doctorale par la co-direction de thèse');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('VALIDATION_CONVENTION_FORMATION_DOCT_DIR_UR',
        'Validation de la bonne lecture de la convention de formation doctorale par la direction de l''unité de recherche');
INSERT INTO admission_type_validation (code, libelle)
VALUES ('VALIDATION_CONVENTION_FORMATION_DOCT_DIR_ED',
        'Validation de la bonne lecture de la convention de formation doctorale par la direction de l''école doctorale');
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

--------------------------- Avis du dossier d'admission ---------------------------

create table admission_avis
(
    id                    bigserial                                                    not null
        primary key,
    admission_id          bigint REFERENCES admission_admission (id),
    avis_id               bigint references unicaen_avis (id),
    histo_createur_id     bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

-- Ajouts des avis
insert into unicaen_avis_valeur(code, valeur, valeur_bool, tags)
values ('AVIS_ADMISSION_VALEUR_COMPLET', 'Dossier d''admission complet', true, 'icon-ok'),
       ('AVIS_ADMISSION_VALEUR_INCOMPLET', 'Dossier d''admission incomplet', false, 'icon-ko'),
       ('AVIS_ADMISSION_VALEUR_POSITIF', 'Avis positif', true, 'icon-ok'),
       ('AVIS_ADMISSION_VALEUR_NEGATIF', 'Avis réservé', false, 'icon-ko')
ON CONFLICT DO NOTHING;
--------------------------- Avis Direction These ---------------------------

insert into unicaen_avis_type (code, libelle, description, ordre)
values ('AVIS_ADMISSION_DIR_THESE', 'Avis de la direction de thèse', 'Point de vue de la direction de thèse', 30)
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur (avis_type_id, avis_valeur_id)
select t.id, v.id
from unicaen_avis_type t,
     unicaen_avis_valeur v
where t.code = 'AVIS_ADMISSION_DIR_THESE'
  and v.code in (
                 'AVIS_ADMISSION_VALEUR_INCOMPLET',
                 'AVIS_ADMISSION_VALEUR_POSITIF',
                 'AVIS_ADMISSION_VALEUR_NEGATIF'
    )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_INFOS',
                                                 false,
                                                 'information',
                                                 'Si le dossier d''admission est jugé incomplet, l''étudiant devra reprendre le circuit de signatures depuis le début...')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_DIR_THESE'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_INCOMPLET'
         )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_MOTIF', true, 'textarea', 'Motif')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_DIR_THESE'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_NEGATIF'
         )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_COMMENTAIRES', false, 'textarea', 'Commentaires')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_DIR_THESE'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
                                                                               'AVIS_ADMISSION_VALEUR_INCOMPLET',
                                                                               'AVIS_ADMISSION_VALEUR_POSITIF',
                                                                               'AVIS_ADMISSION_VALEUR_NEGATIF'
         )
ON CONFLICT DO NOTHING;

--------------------------- Avis CoDirection These ---------------------------

insert into unicaen_avis_type (code, libelle, description, ordre)
values ('AVIS_ADMISSION_CODIR_THESE', 'Avis de la codirection de thèse', 'Point de vue de la codirection de thèse', 30)
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur (avis_type_id, avis_valeur_id)
select t.id, v.id
from unicaen_avis_type t,
     unicaen_avis_valeur v
where t.code = 'AVIS_ADMISSION_CODIR_THESE'
  and v.code in (
                 'AVIS_ADMISSION_VALEUR_INCOMPLET',
                 'AVIS_ADMISSION_VALEUR_POSITIF',
                 'AVIS_ADMISSION_VALEUR_NEGATIF'
    )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_INFOS',
                                                 false,
                                                 'information',
                                                 'Si le dossier d''admission est jugé incomplet, l''étudiant devra reprendre le circuit de signatures depuis le début...')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_CODIR_THESE'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_INCOMPLET'
         )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_MOTIF', true, 'textarea', 'Motif')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_CODIR_THESE'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_NEGATIF'
         )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_COMMENTAIRES', false, 'textarea', 'Commentaires')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_CODIR_THESE'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_INCOMPLET'
         )
ON CONFLICT DO NOTHING;

--------------------------- Avis Direction UR ---------------------------

insert into unicaen_avis_type (code, libelle, description, ordre)
values ('AVIS_ADMISSION_DIR_UR', 'Avis de la direction de l''unité de recherche', 'Point de vue de la direction d''UR',
        30)
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur (avis_type_id, avis_valeur_id)
select t.id, v.id
from unicaen_avis_type t,
     unicaen_avis_valeur v
where t.code = 'AVIS_ADMISSION_DIR_UR'
  and v.code in (
                 'AVIS_ADMISSION_VALEUR_INCOMPLET',
                 'AVIS_ADMISSION_VALEUR_POSITIF',
                 'AVIS_ADMISSION_VALEUR_NEGATIF'
    )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_INFOS',
                                                 false,
                                                 'information',
                                                 'Si le dossier d''admission est jugé incomplet, l''étudiant devra reprendre le circuit de signatures depuis le début...')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_DIR_UR'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_INCOMPLET'
         )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_MOTIF', true, 'textarea', 'Motif')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_DIR_UR'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_NEGATIF'
         )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_COMMENTAIRES', false, 'textarea', 'Commentaires')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_DIR_UR'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
                                                                               'AVIS_ADMISSION_VALEUR_INCOMPLET',
                                                                               'AVIS_ADMISSION_VALEUR_POSITIF',
                                                                               'AVIS_ADMISSION_VALEUR_NEGATIF'
         )
ON CONFLICT DO NOTHING;

--------------------------- Avis Direction ED ---------------------------

insert into unicaen_avis_type (code, libelle, description, ordre)
values ('AVIS_ADMISSION_DIR_ED', 'Avis de la direction de l''école doctorale', 'Point de vue de la direction d''ED',
        30)
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur (avis_type_id, avis_valeur_id)
select t.id, v.id
from unicaen_avis_type t,
     unicaen_avis_valeur v
where t.code = 'AVIS_ADMISSION_DIR_ED'
  and v.code in (
                 'AVIS_ADMISSION_VALEUR_INCOMPLET',
                 'AVIS_ADMISSION_VALEUR_POSITIF',
                 'AVIS_ADMISSION_VALEUR_NEGATIF'
    )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_INFOS',
                                                 false,
                                                 'information',
                                                 'Si le dossier d''admission est jugé incomplet, l''étudiant devra reprendre le circuit de signatures depuis le début...')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_DIR_ED'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_INCOMPLET'
         )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_MOTIF', true, 'textarea', 'Motif')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_DIR_ED'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_NEGATIF'
         )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_COMMENTAIRES', false, 'textarea', 'Commentaires')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_DIR_ED'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
                                                                               'AVIS_ADMISSION_VALEUR_INCOMPLET',
                                                                               'AVIS_ADMISSION_VALEUR_POSITIF',
                                                                               'AVIS_ADMISSION_VALEUR_NEGATIF'
         )
ON CONFLICT DO NOTHING;


--------------------------- Avis Présidence de l'établissement d'inscription ---------------------------

insert into unicaen_avis_type (code, libelle, description, ordre)
values ('AVIS_ADMISSION_PRESIDENCE', 'Signature du dossier par la direction de l''établissement',
        'Point de vue de la présidence de l''établissement d''inscription',
        30)
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur (avis_type_id, avis_valeur_id)
select t.id, v.id
from unicaen_avis_type t,
     unicaen_avis_valeur v
where t.code = 'AVIS_ADMISSION_PRESIDENCE'
  and v.code in (
                 'AVIS_ADMISSION_VALEUR_INCOMPLET',
                 'AVIS_ADMISSION_VALEUR_POSITIF',
                 'AVIS_ADMISSION_VALEUR_NEGATIF'
    )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_INFOS',
                                                 false,
                                                 'information',
                                                 'Si le dossier d''admission est jugé incomplet, l''étudiant devra reprendre le circuit de signatures depuis le début...')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_PRESIDENCE'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_INCOMPLET'
         )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_MOTIF', true, 'textarea', 'Motif')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_PRESIDENCE'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
         'AVIS_ADMISSION_VALEUR_NEGATIF'
         )
ON CONFLICT DO NOTHING;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (select 'PB_COMMENTAIRES', false, 'textarea', 'Commentaires')
select tv.id, concat(t.code, '__', v.code, '__', tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp,
     unicaen_avis_type_valeur tv
         join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_ADMISSION_PRESIDENCE'
         join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
                                                                               'AVIS_ADMISSION_VALEUR_INCOMPLET',
                                                                               'AVIS_ADMISSION_VALEUR_POSITIF',
                                                                               'AVIS_ADMISSION_VALEUR_NEGATIF'
         )
ON CONFLICT DO NOTHING;

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

create table IF NOT EXISTS admission_convention_formation_doctorale
(
    id                                  bigserial                                                    not null
        primary key,
    admission_id                        bigint REFERENCES admission_admission (id),
    calendrier_projet_recherche         text,
    modalites_encadr_suivi_avancmt_rech text,
    conditions_realisation_proj_rech    text,
    modalites_integration_ur            text,
    partenariats_proj_these             text,
    motivation_demande_confidentialite  text,
    projet_pro_doctorant                text,
    histo_createur_id                   bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation                      timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id               bigint REFERENCES utilisateur (id),
    histo_modification                  timestamp,
    histo_destructeur_id                bigint REFERENCES utilisateur (id),
    histo_destruction                   timestamp
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

-- GESTION DES RôLES
--Suppression des privilèges existants aux profils, rôles
DELETE
from profil_privilege
where privilege_id in (select id from privilege where code LIKE 'admission-%');
DELETE
from role_privilege
where privilege_id in (select id from privilege where code LIKE 'admission-%');
DELETE
from privilege
where code LIKE 'admission-%';
DELETE
from role
where code LIKE 'ADMISSION_%';

insert into role (id, code, libelle, source_code, source_id, role_id, these_dep, histo_createur_id,
                  histo_modificateur_id)
values (4000, 'ADMISSION_CANDIDAT', 'Candidat', 'ADMISSION_CANDIDAT', 1, 'Candidat', false, 1, 1)
;
insert into role (id, code, libelle, source_code, source_id, role_id, these_dep, histo_createur_id,
                  histo_modificateur_id)
values (4001, 'ADMISSION_DIRECTEUR_THESE', 'Potentiel directeur de thèse', 'ADMISSION_DIRECTEUR_THESE', 1,
        'Potentiel directeur de thèse', false, 1, 1)
;
insert into role (id, code, libelle, source_code, source_id, role_id, these_dep, histo_createur_id,
                  histo_modificateur_id)
values (4002, 'ADMISSION_CODIRECTEUR_THESE', 'Potentiel co-directeur de thèse', 'ADMISSION_CODIRECTEUR_THESE', 1,
        'Potentiel co-directeur de thèse', false, 1, 1)
;

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
                                    'admission-lister-mes-dossiers-admission',
                                    'Lister mes dossiers d''admission en cours'
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
                             select 21, 'admission-ajouter-avis-tout', 'Ajouter un avis à un dossier d''admission'
                             union
                             select 22, 'admission-ajouter-avis-sien', 'Ajouter un avis à son dossier d''admission'
                             union
                             select 23, 'admission-modifier-avis-tout', 'Modifier un avis d''un dossier d''admission'
                             union
                             select 24, 'admission-modifier-avis-sien', 'Modifier un avis de son dossier d''admission'
                             union
                             select 25, 'admission-supprimer-avis-tout', 'Supprimer un avis d''un dossier d''admission'
                             union
                             select 26, 'admission-supprimer-avis-sien', 'Supprimer un avis de son dossier d''admission'
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
                             select 19,
                                    'admission-commentaires-ajoutes',
                                    'Notifier l''étudiant des commentaires ajoutés sur son dossier d''admission'
                             union
                             select 21,
                                    'admission-notifier-dossier-incomplet',
                                    'Notifier l''étudiant que son dossier d''admission est incomplet'
                             union
                             select 20,
                                    'admission-generer-recapitulatif',
                                    'Générer le récapitulatif du dossier d''admission'
                             union
                             select 7,
                                    'admission-acceder-commentaires',
                                    'Accéder à la saisie/vue des commentaires du dossier d''admission'
                             union
                             select 22,
                                    'admission-convention-formation-modifier',
                                    'Modifier la convention de formation doctorale du dossier d''admission'
                             union
                             select 23,
                                    'admission-convention-formation-visualiser',
                                    'Visualiser la convention de formation doctorale du dossier d''admission'
                             union
                             select 24,
                                    'admission-convention-formation-generer',
                                    'Générer la convention de formation doctorale du dossier d''admission'
                             union
                             select 25, 'admission-rechercher-dossiers-admission', 'Rechercher un dossier d''admission'
                             union
                             select 26,
                                    'admission-afficher-son-dossier-admission-dans-liste',
                                    'Afficher son dossier d''admission dans la liste des dossiers d''admission en cours'
                             union
                             select 27,
                                    'admission-initialiser-son-dossier-admission',
                                    'Initialiser son dossier d''admission')
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
with data(categ, priv) as (select 'admission', 'admission-lister-mes-dossiers-admission'
                           union
                           select 'admission', 'admission-rechercher-dossiers-admission'
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
                           select 'admission', 'admission-acceder-commentaires'
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
                           select 'admission', 'admission-commentaires-ajoutes'
                           union
                           select 'admission', 'admission-notifier-dossier-incomplet'
                           union
                           select 'admission', 'admission-ajouter-avis-tout'
                           union
                           select 'admission', 'admission-ajouter-avis-sien'
                           union
                           select 'admission', 'admission-modifier-avis-tout'
                           union
                           select 'admission', 'admission-modifier-avis-sien'
                           union
                           select 'admission', 'admission-supprimer-avis-tout'
                           union
                           select 'admission', 'admission-supprimer-avis-sien'
                           union
                           select 'admission', 'admission-generer-recapitulatif'
                           union
                           select 'admission', 'admission-convention-formation-modifier'
                           union
                           select 'admission', 'admission-convention-formation-visualiser'
                           union
                           select 'admission', 'admission-convention-formation-generer'
                           union
                           select 'admission', 'admission-afficher-son-dossier-admission-dans-liste')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'ADMIN_TECH'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

-- ajout des privilèges communs au profil GEST_ED, RESP_UR, RESP_ED, D, K
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-rechercher-dossiers-admission'
                           union
                           select 'admission', 'admission-afficher-son-dossier-admission'
                           union
                           select 'admission', 'admission-lister-mes-dossiers-admission'
                           union
                           select 'admission', 'admission-valider-sien'
                           union
                           select 'admission', 'admission-devalider-sien'
                           union
                           select 'admission', 'admission-ajouter-avis-sien'
                           union
                           select 'admission', 'admission-modifier-avis-sien'
                           union
                           select 'admission', 'admission-supprimer-avis-sien'
                           union
                           select 'admission', 'admission-telecharger-son-document'
                           union
                           select 'admission', 'admission-acceder-commentaires'
                           union
                           select 'admission', 'admission-convention-formation-visualiser'
                           union
                           select 'admission', 'admission-convention-formation-generer'
                           union
                           select 'admission', 'admission-afficher-son-dossier-admission-dans-liste')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'GEST_ED',
                                           'RESP_UR',
                                           'RESP_ED',
                                           'D',
                                           'K'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

-- ajout des privilèges au profil GEST_ED
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-commentaires-ajoutes'
                           union
                           select 'admission', 'admission-supprimer-son-dossier-admission'
                           union
                           select 'admission', 'admission-notifier-dossier-incomplet'
                           union
                           select 'admission', 'admission-verifier'
                           union
                           select 'admission', 'admission-generer-recapitulatif')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'GEST_ED')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

-- ajout des privilèges au profil D (Directeur de thèse)
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-modifier-son-dossier-admission')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('D')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

-- ajout des privilèges communs au rôle ADMISSION_CANDIDAT, ADMISSION_DIRECTEUR_THESE et ADMISSION_CODIRECTEUR_THESE
INSERT INTO ROLE_PRIVILEGE (PRIVILEGE_ID, ROLE_ID)
with data(categ, priv) as (select 'admission', 'admission-afficher-son-dossier-admission'
                           union
                           select 'admission', 'admission-acceder-commentaires'
                           union
                           select 'admission', 'admission-valider-sien'
                           union
                           select 'admission', 'admission-devalider-sien'
                           union
                           select 'admission', 'admission-ajouter-avis-sien'
                           union
                           select 'admission', 'admission-modifier-avis-sien'
                           union
                           select 'admission', 'admission-supprimer-avis-sien'
                           union
                           select 'admission', 'admission-telecharger-son-document'
                           union
                           select 'admission', 'admission-convention-formation-visualiser'
                           union
                           select 'admission', 'admission-convention-formation-generer')
select p.id as PRIVILEGE_ID, role.id as ROLE_ID
from data
         join ROLE on role.ROLE_ID in (
                                       'Potentiel directeur de thèse',
                                       'Potentiel co-directeur de thèse',
                                       'Candidat'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from ROLE_PRIVILEGE where PRIVILEGE_ID = p.id and ROLE_ID = role.id);

-- ajout des privilèges communs au rôle ADMISSION_DIRECTEUR_THESE et ADMISSION_CODIRECTEUR_THESE
INSERT INTO ROLE_PRIVILEGE (PRIVILEGE_ID, ROLE_ID)
with data(categ, priv) as (select 'admission', 'admission-lister-mes-dossiers-admission'
                           union
                           select 'admission', 'admission-rechercher-dossiers-admission'
                           union
                           select 'admission', 'admission-afficher-son-dossier-admission-dans-liste')
select p.id as PRIVILEGE_ID, role.id as ROLE_ID
from data
         join ROLE on role.ROLE_ID in (
                                       'Potentiel directeur de thèse',
                                       'Potentiel co-directeur de thèse'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from ROLE_PRIVILEGE where PRIVILEGE_ID = p.id and ROLE_ID = role.id);

-- ajout des privilèges communs au rôle ADMISSION_CANDIDAT et ADMISSION_DIRECTEUR_THESE
INSERT INTO ROLE_PRIVILEGE (PRIVILEGE_ID, ROLE_ID)
with data(categ, priv) as (select 'admission', 'admission-supprimer-son-dossier-admission'
                           union
                           select 'admission', 'admission-televerser-son-document'
                           union
                           select 'admission', 'admission-supprimer-son-document'
                           union
                           select 'admission', 'admission-modifier-son-dossier-admission'
                           union
                           select 'admission', 'admission-convention-formation-modifier')
select p.id as PRIVILEGE_ID, role.id as ROLE_ID
from data
         join ROLE on role.ROLE_ID in ('Candidat',
                                       'Potentiel directeur de thèse')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from ROLE_PRIVILEGE where PRIVILEGE_ID = p.id and ROLE_ID = role.id);

-- ajout des privilèges au rôle user

INSERT INTO ROLE_PRIVILEGE (PRIVILEGE_ID, ROLE_ID)
with data(categ, priv) as (select 'admission', 'admission-initialiser-son-dossier-admission')
select p.id as PRIVILEGE_ID, role.id as ROLE_ID
from data
         join ROLE on role.ROLE_ID in ('user')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from ROLE_PRIVILEGE where PRIVILEGE_ID = p.id and ROLE_ID = role.id);

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
;

-------------------- Macros et Templates alimentant UnicaenRenderer ---------------------------

-------------------- Macros alimentant UnicaenRenderer ---------------------------
-- Suppression des données concernant les macros de Renderer
DELETE
FROM unicaen_renderer_macro
WHERE code LIKE 'Admission%'
   OR code IN ('Individu#Denomination', 'Url#Admission', 'TypeValidation#Libelle');

INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('Admission#Date', '<p>Retourne la date de création d''un dossier d''admission</p>', 'admission',
        'getDateToString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('Admission#Libelle', '', 'admission',
        '__toString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionOperationAttenduNotification#Anomalies',
        '<p>Retourne les possibles anomalies rencontrées lors de la création d''une notification Operation Attendue</p>',
        'anomalies',
        'getAnomalies');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionValidation#Auteur', '<p>Retourne l''auteur de la validation concernant un dossier d''admission</p>',
        'admissionValidation',
        'getAuteurToString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionValidation#Date', '<p>Retourne la date de la validation concernant un dossier d''admission</p>',
        'admissionValidation',
        'getDateToString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionValidation#Destructeur',
        '<p>Retourne le nom/prénom du destructeur de la validation concernant un dossier d''admission</p>',
        'admissionValidation',
        'getDestructeurToString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionAvis#Auteur', '<p>Retourne l''auteur de l''avis concernant un dossier d''admission</p>',
        'admissionAvis',
        'getAuteurToString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionAvis#Date', '<p>Retourne la date de l''avis concernant un dossier d''admission</p>',
        'admissionAvis',
        'getDateToString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionAvis#Destructeur',
        '<p>Retourne le nom/prénom du destructeur de l''avis concernant un dossier d''admission</p>',
        'admissionAvis',
        'getDestructeurToString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionAvis#Modificateur',
        '<p>Retourne le nom/prénom du modificateur de l''avis concernant un dossier d''admission</p>',
        'admissionAvis',
        'getModificateurToString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('Individu#Denomination', '', 'individu',
        'getNomComplet');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('Url#Admission', '<p>Permet de récupérer l''url du dossier d''admission d''un étudiant</p>', 'Url',
        'getAdmission');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('TypeValidation#Libelle', '', 'typeValidation',
        '__toString');

INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#INE', '<p>Retourne le numéro INE de l''étudiant</p>', 'admissionEtudiant',
        'getINE');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#DenominationEtudiant', '<p>Retourne la dénomination de l''étudiant</p>', 'admissionEtudiant',
        'getDenominationEtudiant');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#DateNaissance', '<p>Retourne la date de naissance formatée de l''étudiant</p>',
        'admissionEtudiant',
        'getDateNaissanceFormat');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#VilleNaissance', '<p>Retourne la ville de naissance de l''étudiant</p>', 'admissionEtudiant',
        'getVilleNaissance');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#PaysNaissance', '<p>Retourne le pays de naissance de l''étudiant</p>', 'admissionEtudiant',
        'getPaysNaissanceLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#Nationalite', '<p>Retourne la nationalité de l''étudiant</p>', 'admissionEtudiant',
        'getNationaliteLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#Adresse', '<p>Retourne l''adresse de l''étudiant</p>', 'admissionEtudiant',
        'getAdresseLigne3Bvoie');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#CodePostal', '<p>Retourne le code postal de l''adresse de l''étudiant</p>',
        'admissionEtudiant',
        'getAdresseCodePostal');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#VilleEtudiant', '<p>Retourne la ville de l''étudiant</p>', 'admissionEtudiant',
        'getAdresseCodeCommune');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#NumeroTelephone', '<p>Retourne le numéro de téléphone de l''étudiant</p>',
        'admissionEtudiant',
        'getNumeroTelephone1');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#MailEtudiant', '<p>Retourne le mail de l''étudiant</p>', 'admissionEtudiant', 'getCourriel');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#SituationHandicap', '<p>Retourne la situation de l''handicap de l''étudiant</p>',
        'admissionEtudiant', 'getSituationHandicapLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionEtudiant#NiveauEtude', '<p>Retourne le niveau d''étude de l''étudiant</p>', 'admissionEtudiant',
        'getNiveauEtudeInformations');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionRecapitulatif#InfosDiplome',
        '<p>Retourne les informations concernant le diplôme de l''étudiant</p>',
        'admissionRecapitulatif', 'getDiplomeIntituleInformationstoHtmlArray');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#SpecialiteDoctorat', '<p>Retourne la spécialité du doctorat choisie par l''étudiant</p>',
        'admissionInscription', 'getSpecialiteDoctoratLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#ComposanteRattachement',
        '<p>Retourne la composante choisie pour le doctorat de l''étudiant</p>',
        'admissionInscription', 'getComposanteRattachementLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#EcoleDoctorale',
        '<p>Retourne l''école doctorale choisie pour le doctorat de l''étudiant</p>',
        'admissionInscription', 'getEcoleDoctoraleLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#UniteRecherche',
        '<p>Retourne l''unité de recherche choisie pour le doctorat de l''étudiant</p>',
        'admissionInscription', 'getUniteRechercheLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#EtablissementInscription',
        '<p>Retourne l''établissement d''inscription choisie pour le doctorat de l''étudiant</p>',
        'admissionInscription', 'getEtablissementInscriptionLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#DenominationDirecteurThese',
        '<p>Retourne la dénomination du directeur de thèse de l''étudiant</p>',
        'admissionInscription', 'getDenominationDirecteurThese');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#MailDirecteurThese', '<p>Retourne le mail du directeur de thèse</p>',
        'admissionInscription', 'getEmailDirecteurThese');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#FonctionDirecteurThese', '<p>Retourne la fonction du directeur de thèse</p>',
        'admissionInscription', 'getFonctionDirecteurLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#TitreThese', '<p>Retourne le titre provisoire de la thèse de l''étudiant</p>',
        'admissionInscription', 'getTitreThese');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#ConfidentialiteSouhaitee',
        '<p>Retourne la confidentialité voulue pour la thèse de l''étudiant</p>',
        'admissionInscription', 'getConfidentialiteSouhaiteeLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#CotutelleEnvisagee',
        '<p>Retourne si une cotutelle est envisagée pour la thèse de l''étudiant</p>',
        'admissionInscription', 'getCotutelleEnvisageeLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#CoDirectionDemandee',
        '<p>Retourne si une codirection est demandée pour la thèse de l''étudiant</p>',
        'admissionInscription', 'getCoDirectionDemandeeLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#UniteRechercheCoDirection',
        '<p>Retourne l''unité de recherche du co-directeur de thèse pour le doctorat de l''étudiant</p>',
        'admissionInscription', 'getUniteRechercheCoDirecteurLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#EtablissementRattachementCoDirection',
        '<p>Retourne l''établissement d''inscription du co-directeur de thèse pour le doctorat de l''étudiant</p>',
        'admissionInscription', 'getEtablissementRattachementCoDirecteurLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#FonctionCoDirecteurThese', '<p>Retourne la fonction du co-directeur de thèse</p>',
        'admissionInscription', 'getFonctionCoDirecteurLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionInscription#CoEncadrementEnvisage',
        '<p>Retourne si un co-encadrement est envisagé pour la thèse de l''étudiant</p>', 'admissionInscription',
        'getCoEncadrementLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionFinancement#ContratDoctoral',
        '<p>Retourne si un contrat doctoral est prévu pour la thèse de l''étudiant, et si oui l''employeur est retourné</p>',
        'admissionFinancement', 'getContratDoctoralLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionFinancement#DetailContratDoctoral',
        '<p>Retourne si les détails du contrat doctoral pour la thèse de l''étudiant</p>', 'admissionFinancement',
        'getDetailContratDoctoral');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionFinancement#TempsTravail',
        '<p>Retourne si les détails du temps de travail pour la thèse de l''étudiant</p>', 'admissionFinancement',
        'getTempsTravailInformations');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionFinancement#EstSalarie',
        '<p>Retourne si le doctorant sera salarié</p>', 'admissionFinancement',
        'getEstSalarieInfos');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionFinancement#StatutProfessionnel',
        '<p>Retourne si les détails du statut professionnel pour la thèse de l''étudiant</p>', 'admissionFinancement',
        'getStatutProfessionnel');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionFinancement#EtablissementLaboratoireRecherche',
        '<p>Retourne si l''établissement hébergeant le laboratoire de recherche</p>', 'admissionFinancement',
        'getEtablissementLaboratoireRecherche');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionRecapitulatif#Operations',
        '<p>Retourne les opérations accordées au dossier d''admission de l''étudiant</p>',
        'admissionRecapitulatif', 'getOperationstoHtmlArray');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#InfosCoDirecteur',
        '<p>Retourne les informations concernant le co-directeur du dossier d''admission de l''étudiant</p>',
        'admissionConventionFormationDoctoraleData', 'getCoDirectionInformationstoHtml');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#InfosCoTutelle',
        '<p>Retourne les informations concernant la co-tutelle du dossier d''admission de l''étudiant</p>',
        'admissionConventionFormationDoctoraleData', 'getCoTutelleInformationstoHtml');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#InfosConventionCollaboration',
        '<p>Retourne les informations concernant la convention de collaboration du dossier d''admission de l''étudiant</p>',
        'admissionConventionFormationDoctoraleData', 'getConventionCollaborationInformationstoHtml');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#ResponsablesURDirecteurThese',
        '<p>Retourne les responsables de l''UR du directeur de thèse du dossier d''admission de l''étudiant</p>',
        'admissionConventionFormationDoctoraleData', 'getResponsablesURDirecteurtoHtml');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#ResponsablesURCoDirecteurThese',
        '<p>Retourne les responsables de l''UR du co-directeur de thèse du dossier d''admission de l''étudiant</p>',
        'admissionConventionFormationDoctoraleData', 'getResponsablesURCoDirecteurtoHtml');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#CalendrierPrevisionnel',
        '<p>Retourne les informations concernant le calendrier prévisionnel de la convention de formation doctorale du dossier d''admission de l''étudiant</p>',
        'conventionFormationDoctorale', 'getCalendrierProjetRecherche');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#ModalitesEncadrement',
        '<p>Retourne les informations concernant les modalités d''encadrement, de suivi de la formation et d''avancement des recherches de la convention de formation doctorale du dossier d''admission de l''étudiant</p>',
        'conventionFormationDoctorale', 'getModalitesEncadrSuiviAvancmtRech');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#ConditionsProjRech',
        '<p>Retourne les informations concernant les conditions matérielles de réalisation du projet de recherche et conditions de sécurité spécifiques de la convention de formation doctorale du dossier d''admission de l''étudiant</p>',
        'conventionFormationDoctorale', 'getConditionsRealisationProjRech');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#ModalitesIntegrationUR',
        '<p>Retourne les informations concernant les modalités d''intégration dans l''unité ou l’équipe de recherche de la convention de formation doctorale du dossier d''admission de l''étudiant</p>',
        'conventionFormationDoctorale', 'getModalitesIntegrationUr');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#PartenariatsProjThese',
        '<p>Retourne les informations concernant les Partenariats impliqués par le projet de thèse de la convention de formation doctorale du dossier d''admission de l''étudiant</p>',
        'conventionFormationDoctorale', 'getPartenariatsProjThese');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#MotivationDemandeConfidentialite',
        '<p>Retourne les informations concernant le calendrier prévisionnel de la convention de collaboration du dossier d''admission de l''étudiant</p>',
        'conventionFormationDoctorale', 'getMotivationDemandeConfidentialite');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#ProjetProDoctorant',
        '<p>Retourne les informations concernant le projet professionnel du doctorant de la convention de collaboration du dossier d''admission de l''étudiant</p>',
        'conventionFormationDoctorale', 'getProjetProDoctorant');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#Operations',
        '<p>Retourne les opérations liées  à la convention de formations doctorale accordées au dossier d''admission de l''étudiant</p>',
        'admissionConventionFormationDoctoraleData', 'getOperationstoHtmlArray');

-------------------- Templates alimentant UnicaenRenderer ---------------------------
-- Suppression des données concernant les templates de Renderer
DELETE
FROM unicaen_renderer_template
WHERE code LIKE 'ADMISSION%';

INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_OPERATION_ATTENDUE',
        '<p>Mail pour notifier les acteurs de la prochaine opération attendue du dossier d''admission</p>', 'mail',
        'Dossier d''admission de VAR[Individu#Denomination]', e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL. </p>
<p><em>VAR[AdmissionOperationAttenduNotification#Anomalies]</em></p>
<p>Le <strong>dossier d\'admission</strong> de <strong>VAR[Individu#Denomination]</strong> est en attente de l\'opération suivante de votre part : <strong>VAR[TypeValidation#Libelle]</strong>.</p>
<p>Merci de vous connecter sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission]</p>
<p><strong>Note importante : une fois connecté, pensez à vérifier le rôle que vous endossez (en cliquant sur votre nom en haut à droite des pages de l\'application) et le cas échéant à sélectionner celui permettant de réaliser l\'opération attendue.</strong></p>',
        null, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_VALIDATION_AJOUTEE',
        '<p>Mail pour notifier qu''une validation a été ajoutée au dossier d''admission</p>', 'mail',
        'Dossier d''admission de VAR[Individu#Denomination] ', e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.</p>
<p>Le <strong>dossier d\'admission</strong> de <strong>VAR[Individu#Denomination]</strong> a été <strong>validé</strong> par VAR[AdmissionValidation#Auteur], le VAR[AdmissionValidation#Date]</p>',
        null, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_NOTIFICATION_GESTIONNAIRE',
        '<p>Envoi d''un mail à l''initiative de l''étudiant, afin de notifier le(s) gestionnaire(s) que le dossier est prêt à être vérifié</p>',
        'mail', 'La saisie du dossier d''admission de VAR[Individu#Denomination] est terminée', e'<p>Bonjour,</p>
<p>VAR[Individu#Denomination] a terminé la saisie de son dossier d\'admission.</p>
<p>Merci de prendre connaissance des informations saisies en vous connectant sur la plateforme ESUP-SyGAL via le lien : VAR[Url#Admission].</p>',
        null, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_VALIDATION_SUPPRIMEE',
        '<p>Mail pour notifier qu''une validation a été supprimée du dossier d''admission</p>', 'mail',
        'Dossier d''admission de VAR[Individu#Denomination] dévalidé', e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.</p>
<p>La <strong>VAR[TypeValidation#Libelle]</strong> du <strong>dossier d\'admission</strong> de <strong>VAR[Individu#Denomination]</strong> datant du VAR[AdmissionValidation#Date] a été <strong>annulée </strong>VAR[AdmissionValidation#Destructeur]</p>',
        null, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_CONVENTION_FORMATION_DOCTORALE',
        '<p>Convention de formation doctorale appartenant à un dossier d''admission</p>', 'pdf',
        'Convention de formation doctorale de VAR[Individu#Denomination]', e'<h1 style="text-align: center;">Convention de formation doctorale</h1>
<h1 style="text-align: center;">de VAR[Individu#Denomination]</h1>
<p> </p>
<p><strong>Entre</strong> VAR[AdmissionEtudiant#DenominationEtudiant]<br /><strong>né le</strong> VAR[AdmissionEtudiant#DateNaissance]<br /><strong>à</strong> VAR[AdmissionEtudiant#VilleNaissance], VAR[AdmissionEtudiant#PaysNaissance] (VAR[AdmissionEtudiant#Nationalite])<br /><strong>ayant pour adresse</strong> VAR[AdmissionEtudiant#Adresse] VAR[AdmissionEtudiant#CodePostal], VAR[AdmissionEtudiant#VilleEtudiant]<br /><strong>Mail</strong> : VAR[AdmissionEtudiant#MailEtudiant]</p>
<p><strong>ci-après dénommé le Doctorant</strong></p>
<p><strong>Et</strong> VAR[AdmissionInscription#DenominationDirecteurThese], directeur(-trice) de thèse<br /><strong>Fonction</strong> : VAR[AdmissionInscription#FonctionDirecteurThese]<br /><strong>Unité de recherche </strong>: VAR[AdmissionInscription#UniteRecherche]<br /><strong>Établissement de rattachement</strong> : VAR[AdmissionInscription#EtablissementInscription]<br /><strong>Mail</strong> : VAR[AdmissionInscription#MailDirecteurThese]</p>
<p>VAR[AdmissionConventionFormationDoctorale#InfosCoDirecteur]</p>
<p>- Vu l’article L612-7 du Code de l’éducation, Vu les articles L412-1 et L412-2 du Code de la recherche,</p>
<p>- Vu l’arrêté du 25 mai 2016 fixant le cadre national de la formation et les modalités conduisant à la délivrance du diplôme national de doctorat, modifié par l’arrêté du 26 août 2022,</p>
<p>- Vu la charte du doctorat dans le cadre de la délivrance conjointe du doctorat entre la ComUE Normandie Université et les établissements d’inscription co-accrédités en date du 1er septembre 2023, </p>
<p>VAR[AdmissionConventionFormationDoctorale#InfosCoTutelle]</p>
<p>VAR[AdmissionConventionFormationDoctorale#InfosConventionCollaboration]</p>
<p>Il est établi la convention de formation doctorale suivante. Cette convention peut être modifiée par avenant autant de fois que nécessaire pendant le doctorat.</p>
<h3><strong>Article 1 - Objet de la convention de formation doctorale</strong></h3>
<p>La présente convention de formation, signée à la première inscription par le Doctorant, par le (ou les) (co)Directeur(s) de Thèse et, le cas échéant, par le(s) co-encadrant(s) de thèse, fixe les conditions de suivi et d''encadrement de la thèse, sous la responsabilité de l’VAR[AdmissionInscription#EtablissementInscription] (établissement d’inscription), établissement de préparation du doctorat. En cas de besoin, la convention de formation doctorale est révisable annuellement par un avenant à la convention initiale.</p>
<p>Les règles générales en matière de signature des travaux issus de la thèse, de confidentialité et de propriété intellectuelle sont précisées dans la Charte du doctorat adoptée par Normandie Université et l’VAR[AdmissionInscription#EtablissementInscription] (établissement d’inscription), également signée à la première inscription, par le Doctorant et le (ou les) Directeur(s) de Thèse.</p>
<p><strong>Titre de la thèse </strong></p>
<p>VAR[AdmissionInscription#TitreThese]</p>
<p><strong>Spécialité du diplôme</strong></p>
<p>VAR[AdmissionInscription#SpecialiteDoctorat]</p>
<p><strong>Financement</strong></p>
<p>VAR[AdmissionFinancement#ContratDoctoral]</p>
<p><strong>Établissement d''inscription du doctorant</strong></p>
<p>VAR[AdmissionInscription#EtablissementInscription]</p>
<p><strong>École doctorale</strong></p>
<p>VAR[AdmissionInscription#EcoleDoctorale]</p>
<p><strong>Temps de travail du Doctorat mené à</strong> : VAR[AdmissionFinancement#TempsTravail]</p>
<p><strong>Statut professionnel </strong>: VAR[AdmissionFinancement#StatutProfessionnel]</p>
<h3><strong>Article 2 – Laboratoire d’accueil</strong></h3>
<p>Le doctorant réalise sa thèse au sein de :</p>
<ul>
<li style="font-weight: 400; text-align: start;">Unité d''accueil (UMR, U INSERM, UR, Laboratoire privé...) : VAR[AdmissionInscription#UniteRecherche]</li>
<li style="font-weight: 400; text-align: start;">Directeur de l''unité (Nom, Prénom, coordonnées téléphoniques et courriel) : VAR[AdmissionConventionFormationDoctorale#ResponsablesURDirecteurThese]</li>
</ul>
<p>Et (en cas de co-tutelle ou co-direction de thèse) :</p>
<ul>
<li style="font-weight: 400; text-align: start;">Unité d''accueil (EA, UMR, U INSERM, UR, Laboratoire privé …) : VAR[AdmissionInscription#UniteRechercheCoDirection]</li>
<li style="font-weight: 400; text-align: start;">Directeur de l''unité (Nom, Prénom, coordonnées téléphoniques et courriel) : VAR[AdmissionConventionFormationDoctorale#ResponsablesURCoDirecteurThese]</li>
</ul>
<h3><strong>Article 3 - Méthodes et Moyens</strong></h3>
<p><strong>3-1 Calendrier prévisionnel du projet de recherche</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#CalendrierPrevisionnel]</p>
<p><strong>3-2 Modalités d''encadrement, de suivi de la formation et d''avancement des recherches du doctorant</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ModalitesEncadrement]</p>
<p><strong>3-3 Conditions matérielles de réalisation du projet de recherche et conditions de sécurité spécifiques si nécessaire</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ConditionsProjRech]</p>
<p><strong>3-4 Modalités d''intégration dans l''unité ou l’équipe de recherche</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ModalitesIntegrationUR]</p>
<p><strong>3-5 Partenariats impliqués par le projet de thèse</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#PartenariatsProjThese]</p>
<h3><strong>Article 4 - Confidentialité des travaux de recherche</strong></h3>
<p>Caractère confidentiel des travaux :</p>
<p>VAR[AdmissionInscription#ConfidentialiteSouhaitee]</p>
<p>Si oui, motivation de la demande de confidentialité par le doctorant et la direction de thèse:</p>
<p>VAR[AdmissionConventionFormationDoctorale#MotivationDemandeConfidentialite]</p>
<h3><strong>Article 5 - Projet professionnel du doctorant</strong></h3>
<p>VAR[AdmissionConventionFormationDoctorale#ProjetProDoctorant]</p>
<p> </p>
<h2>Validations accordées à la convention de formation doctorale</h2>
<p>VAR[AdmissionConventionFormationDoctorale#Operations]</p>',
        'table { border-collapse: collapse;  width: 100%;  } th, td { border: 1px solid #000; padding: 8px;  }body{font-size: 9pt;}.pas_valeur_avis_renseigne { background-color: #dddddd;} p{padding:unset}',
        'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_RECAPITULATIF', null, 'pdf',
        'Récapitulatif du dossier d''admission de VAR[Individu#Denomination]', e'<h1 style="text-align: center;">Récapitulatif du dossier d''admission</h1>
<h1 style="text-align: center;">de VAR[Individu#Denomination]</h1>
<h2>Étudiant</h2>
<h3>Informations concernant l''étudiant</h3>
<table style="height: 125px; width: 929px;">
<tbody>
<tr>
<td style="width: 163.5px;"><strong>Numéro I.N.E :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#INE]</td>
<td style="width: 145px;"><strong> </strong></td>
<td style="width: 303px;"> </td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Étudiant :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#DenominationEtudiant]</td>
<td style="width: 145px;"><strong>Date de naissance :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#DateNaissance]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Ville de naissance :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#VilleNaissance]</td>
<td style="width: 145px;"><strong>Pays de naissance :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#PaysNaissance]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Nationalité :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#Nationalite]</td>
<td style="width: 145px;"><strong>Adresse :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#Adresse]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Code postal :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#CodePostal]</td>
<td style="width: 145px;"><strong>Ville :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#VilleEtudiant]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Numéro de téléphone :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#NumeroTelephone]</td>
<td style="width: 145px;"><strong>Mail :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#MailEtudiant]</td>
</tr>
</tbody>
</table>
<p>Etes-vous en situation de handicap ? VAR[AdmissionEtudiant#SituationHandicap]</p>
<h3>Niveau permettant l''accès au doctorat</h3>
<p>VAR[AdmissionEtudiant#NiveauEtude] </p>
<p>VAR[AdmissionRecapitulatif#InfosDiplome]</p>
<h2>Inscription </h2>
<h3>Informations concernant son inscription demandée</h3>
<ul>
<li><strong>Spécialité d''inscription :</strong> VAR[AdmissionInscription#SpecialiteDoctorat]</li>
<li><strong>Composante de rattachement : </strong>VAR[AdmissionInscription#ComposanteRattachement]</li>
<li><strong>École Doctorale :</strong> VAR[AdmissionInscription#EcoleDoctorale]</li>
<li><strong>Unité de recherche :</strong> VAR[AdmissionInscription#UniteRecherche]</li>
<li><strong>Établissement d''inscription</strong> : VAR[AdmissionInscription#EtablissementInscription] </li>
<li><strong>Directeur(-trice) de thèse :</strong> VAR[AdmissionInscription#DenominationDirecteurThese]
<ul>
<li><strong>Fonction du directeur(-trice) de thèse :</strong> VAR[AdmissionInscription#FonctionDirecteurThese]</li>
</ul>
</li>
<li><strong>Titre provisoire de la thèse :</strong> VAR[AdmissionInscription#TitreThese]</li>
</ul>
<h3>Spéciﬁcités envisagées concernant son inscription</h3>
<ul>
<li><strong>Conﬁdentialité souhaitée :</strong> VAR[AdmissionInscription#ConfidentialiteSouhaitee]</li>
<li><strong>Cotutelle envisagée :</strong> VAR[AdmissionInscription#CotutelleEnvisagee]</li>
<li><strong>Codirection demandée :</strong> VAR[AdmissionInscription#CoDirectionDemandee]
<ul>
<li><strong>Si oui, fonction du codirecteur(-trice) de thèse :</strong> VAR[AdmissionInscription#FonctionCoDirecteurThese]</li>
</ul>
</li>
<li><strong>Co-encadrement envisagé :</strong> VAR[AdmissionInscription#CoEncadrementEnvisage]</li>
</ul>
<h2>Financement</h2>
<p><strong>Avez-vous un contrat doctoral</strong> <strong>?</strong> VAR[AdmissionFinancement#ContratDoctoral]</p>
<p><strong>Si oui, détails du contrat doctoral</strong> : VAR[AdmissionFinancement#DetailContratDoctoral]</p>
<p><strong>Temps de travail du Doctorat mené à</strong> : VAR[AdmissionFinancement#TempsTravail]</p>
<p><strong>Êtes-vous salarié ?</strong> VAR[AdmissionFinancement#EstSalarie]</p>
<ul>
<li><strong>Si oui, Statut professionnel </strong>: VAR[AdmissionFinancement#StatutProfessionnel]</li>
<li><strong>Si oui, Établissement hébergeant le laboratoire de recherche</strong> :  VAR[AdmissionFinancement#EtablissementLaboratoireRecherche]</li>
</ul>
<p> </p>
<h2>Validations et Avis accordés au dossier d''admission</h2>
<p>VAR[AdmissionRecapitulatif#Operations]</p>
<h2>Validation par la direction de l''établissement</h2>
<ul>
<li>Favorable</li>
<li>Défavorable
<ul>
<li>Motif du refus :</li>
</ul>
</li>
</ul>
<p> </p>
<p> </p>
<p>Fait à ____________________, le ________________,</p>
<p>Signature de VAR[String#ToString]</p>',
        'table { border-collapse: collapse;  width: 100%;  } th, td { border: 1px solid #000; padding: 8px;  }body{font-size: 9pt;}.pas_valeur_avis_renseigne { background-color: #dddddd;}',
        'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_COMMENTAIRES_AJOUTES',
        '<p>Notification lorsque le gestionnaire a ajouté des commentaires au dossier d''admission</p>', 'mail',
        'Commentaires ajoutés à votre dossier d''admission', e'<p>Bonjour, </p>
<p style="font-family: \'Segoe UI\', \'Lucida Sans\', sans-serif; font-size: 14.16px; background-color: #fdfcfa;">Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.</p>
<p><strong>Des commentaires ont été ajoutés</strong> par vos gestionnaires à votre dossier d\'admission. </p>
<p><span style="background-color: #fdfcfa; font-family: \'Segoe UI\', \'Lucida Sans\', sans-serif; font-size: 14.16px;">Merci de prendre connaissance des commentaires en vous connectant sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission]</span><span style="background-color: #fdfcfa; font-family: \'Segoe UI\', \'Lucida Sans\', sans-serif; font-size: 14.16px;"> </span></p>',
        null, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_NOTIFICATION_DOSSIER_COMPLET',
        '<p>Mail pour notifier l''étudiant que son dossier est complet</p>', 'mail',
        'Votre dossier d''admission est complet', e'<p>Bonjour, </p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.</p>
<p>Votre dossier d\'admission est noté comme <strong>complet</strong> par votre gestionnaire.</p>
<p>Vous pouvez dès à présent faire votre attestation sur l\'honneur en vous connectant sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission] </p>',
        null, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_NOTIFICATION_DOSSIER_INCOMPLET',
        '<p>Mail pour notifier l''étudiant que son dossier est incomplet</p>', 'mail',
        'Votre dossier d''admission est incomplet', e'<p>Bonjour, </p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.</p>
<p>Votre dossier d\'admission est noté comme <strong>incomplet</strong> par votre gestionnaire.</p>
<p>Veuillez prendre connaissance des commentaires ajoutés à votre dossier, en vous connectant sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission] </p>',
        null, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_AVIS_AJOUTE', '<p>Mail pour notifier qu''un avis a été ajouté au dossier d''admission</p>',
        'mail', 'Dossier d''admission de VAR[Individu#Denomination] ', e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.</p>
<p>Un avis a été ajouté au <strong>dossier d\'admission</strong> de <strong>VAR[Individu#Denomination]</strong> par VAR[AdmissionAvis#Auteur], le VAR[AdmissionAvis#Date]</p>',
        null, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_AVIS_MODIFIE', '<p>Mail pour notifier qu''un avis a été modifié du dossier d''admission</p>',
        'mail', 'Dossier d''admission de VAR[Individu#Denomination]', e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.</p>
<p>L\'<strong>VAR[TypeValidation#Libelle]</strong> du <strong>dossier d\'admission</strong> de <strong>VAR[Individu#Denomination]</strong> datant du VAR[AdmissionAvis#Date] a été modifié<strong> </strong>VAR[AdmissionAvis#Modificateur]</p>',
        null, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_AVIS_SUPPRIME', '<p>Mail pour notifier qu''un avis a été supprimé du dossier d''admission</p>',
        'mail', 'Dossier d''admission de VAR[Individu#Denomination]', e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.</p>
<p>L\'<strong>VAR[TypeValidation#Libelle]</strong> du <strong>dossier d\'admission</strong> de <strong>VAR[Individu#Denomination]</strong> datant du VAR[AdmissionAvis#Date] a été <strong>supprimé </strong>VAR[AdmissionAvis#Destructeur]</p>',
        null, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('ADMISSION_DERNIERE_VALIDATION_AJOUTEE',
        '<p>Mail pour notifier que la dernière validation a été ajoutée au dossier d''admission</p>', 'mail',
        'Dossier d''admission de VAR[Individu#Denomination] ', e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.</p>
<p>Le <strong>dossier d\'admission</strong> de <strong>VAR[Individu#Denomination]</strong> a été <strong>validé</strong> par VAR[AdmissionAvis#Auteur], le VAR[AdmissionAvis#Date]</p>
<p>Le circuit de signature de votre dossier est maintenant terminé. </p>', null, 'Admission\Provider\Template');