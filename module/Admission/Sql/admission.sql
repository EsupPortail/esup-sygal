create table admission_admission
(
    id                    bigserial                                                    not null
        primary key,
    individu_id           bigint REFERENCES individu (id),
    etat_id               bigint,
    histo_createur_id     bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

create table admission_etudiant
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
    type_diplome_autre                        boolean,
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

create table admission_inscription
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

create table admission_financement
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

create table admission_type_validation
(
    id                    bigserial                                                    not null
        primary key,
    code                  varchar(50)                                                  not null,
    libelle               varchar(100),
    histo_createur_id     bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

create table admission_validation
(
    id                    bigserial                                                    not null
        primary key,
    admission_id          bigint REFERENCES admission_admission (id),
    type_validation_id    bigint                                                       not null REFERENCES admission_type_validation (id),
    individu_id           bigint                                                       not null REFERENCES utilisateur (id),
    histo_createur_id     bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

create table admission_document
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

create table admission_verification
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

--
-- Nouvelle catégorie de privilèges : Admission.
--
insert into CATEGORIE_PRIVILEGE(ID, CODE, LIBELLE, ORDRE)
select nextval('categorie_privilege_id_seq'), 'admission', 'Admission', 11000;

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
                                    'admission-modifier-modifier-son-dossier-admission',
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
                             select 7, 'admission-verifier', 'Ajouter des commentaires au dossier d''admission')
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d
         join CATEGORIE_PRIVILEGE cp on cp.CODE = 'admission'
;

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
                           select 'admission', 'admission-modifier-modifier-son-dossier-admission'
                           union
                           select 'admission', 'admission-historiser'
                           union
                           select 'admission', 'admission-supprimer-tous-dossiers-admission'
                           union
                           select 'admission', 'admission-supprimer-son-dossier-admission'
                           union
                           select 'admission', 'admission-verifier')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH',
                                           'GEST_ED'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-lister-son-dossier-admission'
                           union
                           select 'admission', 'admission-afficher-son-dossier-admission'
                           union
                           select 'admission', 'admission-modifier-modifier-son-dossier-admission'
                           union
                           select 'admission', 'admission-supprimer-son-dossier-admission')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'DOCTORANT'
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