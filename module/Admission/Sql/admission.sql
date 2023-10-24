create table admission_admission
(
    id                    bigserial not null
        primary key,
    individu_id           bigint REFERENCES individu (id),
    etat_id               bigint,
    histo_createur_id     bigint    not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text):: timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

create table admission_individu
(
    id                                        bigserial not null
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
    histo_createur_id                         bigint    not null REFERENCES utilisateur (id),
    histo_creation                            timestamp default ('now'::text):: timestamp without time zone not null,
    histo_modificateur_id                     bigint REFERENCES utilisateur (id),
    histo_modification                        timestamp,
    histo_destructeur_id                      bigint REFERENCES utilisateur (id),
    histo_destruction                         timestamp
);

create table admission_inscription
(
    id                       bigserial not null
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
    histo_createur_id        bigint    not null REFERENCES utilisateur (id),
    histo_creation           timestamp default ('now'::text):: timestamp without time zone not null,
    histo_modificateur_id    bigint REFERENCES utilisateur (id),
    histo_modification       timestamp,
    histo_destructeur_id     bigint REFERENCES utilisateur (id),
    histo_destruction        timestamp
);

create table admission_financement
(
    id                      bigserial not null
        primary key,
    admission_id            bigint REFERENCES admission_admission (id),
    contrat_doctoral        boolean,
    employeur_contrat       varchar(60),
    detail_contrat_doctoral varchar(1024),
    histo_createur_id       bigint    not null REFERENCES utilisateur (id),
    histo_creation          timestamp default ('now'::text):: timestamp without time zone not null,
    histo_modificateur_id   bigint REFERENCES utilisateur (id),
    histo_modification      timestamp,
    histo_destructeur_id    bigint REFERENCES utilisateur (id),
    histo_destruction       timestamp
);

create table admission_type_validation
(
    id                    bigserial   not null
        primary key,
    code                  varchar(50) not null,
    libelle               varchar(100),
    histo_createur_id     bigint      not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text):: timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

create table admission_validation
(
    id                    bigserial not null
        primary key,
    admission_id          bigint REFERENCES admission_admission (id),
    type_validation_id    bigint    not null REFERENCES admission_type_validation (id),
    individu_id           bigint    not null REFERENCES utilisateur (id),
    histo_createur_id     bigint    not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text):: timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

create table admission_document
(
    id                    bigserial not null
        primary key,
    admission_id          bigint REFERENCES admission_admission (id),
    nature_id             bigint REFERENCES nature_fichier (id),
    fichier_id            bigint REFERENCES fichier (id),
    histo_createur_id     bigint    not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text):: timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);
