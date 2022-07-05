create sequence formation_module_id_seq;
create sequence formation_session_id_seq;
create sequence formation_seance_id_seq;
create sequence formation_formateur_id_seq;
create sequence formation_inscription_id_seq;
create sequence formation_presence_id_seq;
create sequence formation_enquete_question_id_seq;
create sequence formation_enquete_reponse_id_seq;
create sequence formation_module_id_seq1;
create sequence formation_enquete_categorie_id_seq;
create sequence formation_formation_id_seq;


create table formation_etat
(
    code varchar(1) not null
        constraint formation_etat_pk
            primary key,
    libelle varchar(1024),
    description text,
    icone varchar(1024),
    couleur varchar(1024),
    ordre bigint
);

create table formation_module
(
    histo_destructeur_id integer
        constraint formation_module_utilisateur_id_fk_3
            references utilisateur,
    histo_destruction timestamp,
    histo_modificateur_id integer
        constraint formation_module_utilisateur_id_fk_2
            references utilisateur,
    histo_modification timestamp,
    histo_createur_id integer not null
        constraint formation_module_utilisateur_id_fk
            references utilisateur,
    histo_creation timestamp not null,
    description text,
    libelle text not null,
    id integer default nextval('formation_module_id_seq1'::regclass) not null
        constraint formation_module_pk
            primary key,
    lien text
);

create table formation_formation
(
    id bigint not null
        constraint formation_pk
            primary key,
    libelle varchar(1024) not null,
    description text,
    lien varchar(1024),
    site_id bigint
        constraint formation_etablissement_id_fk
            references etablissement
            on delete set null,
    responsable_id bigint
        constraint formation_individu_id_fk
            references individu
            on delete set null,
    modalite varchar(1),
    type varchar(1),
    type_structure_id bigint
        constraint formation_structure_id_fk
            references structure
            on delete set null,
    taille_liste_principale bigint,
    taille_liste_complementaire bigint,
    histo_createur_id bigint not null
        constraint formation_createur_fk
            references utilisateur,
    histo_creation timestamp(6) not null,
    histo_modificateur_id bigint
        constraint formation_modificateur_fk
            references utilisateur,
    histo_modification timestamp(6),
    histo_destructeur_id bigint
        constraint formation_destructeur_fk
            references utilisateur,
    histo_destruction timestamp(6),
    module_id integer
        constraint formation_module_id_fk
            references formation_module
            on delete set null
);

create table formation_session
(
    id bigint not null
        constraint formation_instance_pk
            primary key,
    formation_id bigint not null
        constraint session_id_fk
            references formation_formation
            on delete cascade,
    description text,
    taille_liste_principale bigint,
    taille_liste_complementaire bigint,
    type_structure_id bigint,
    site_id bigint
        constraint session_site_id_fk
            references etablissement
            on delete set null,
    responsable_id bigint
        constraint session_responsable_id_fk
            references individu
            on delete set null,
    modalite varchar(1),
    type varchar(1),
    etat_code varchar(1)
        constraint session_etat_code_fk
            references formation_etat
            on delete set null,
    session_index bigint,
    histo_creation timestamp(6) not null,
    histo_createur_id bigint not null
        constraint session_createur_fk
            references utilisateur,
    histo_modification timestamp(6),
    histo_modificateur_id bigint
        constraint session_modificateur_fk
            references utilisateur,
    histo_destruction timestamp(6),
    histo_destructeur_id bigint
        constraint session_destructeur_fk
            references utilisateur
);

create table formation_seance
(
    id bigint not null
        constraint formation_seance_pk
            primary key,
    session_id bigint not null
        constraint formation_seance_session_fk
            references formation_session
            on delete cascade,
    debut timestamp(6) not null,
    fin timestamp(6) not null,
    lieu varchar(1024),
    description text,
    histo_creation timestamp(6) not null,
    histo_createur_id bigint not null
        constraint seance_createur_fk
            references utilisateur,
    histo_modification timestamp(6),
    histo_modificateur_id bigint
        constraint seance_modificateur_fk
            references utilisateur,
    histo_destruction timestamp(6),
    histo_destructeur_id bigint
        constraint seance_destructeur_fk
            references utilisateur
);

create table formation_formateur
(
    id bigint not null
        constraint formation_formateur_pk
            primary key,
    individu_id bigint not null
        constraint formateur_individu_id_fk
            references individu
            on delete cascade,
    session_id bigint not null
        constraint formateur_session_id_fk
            references formation_session
            on delete cascade,
    description text,
    histo_creation timestamp(6) not null,
    histo_createur_id bigint not null
        constraint formateur_createur_fk
            references utilisateur,
    histo_modification timestamp(6),
    histo_modificateur_id bigint
        constraint formateur_modificateur_fk
            references utilisateur,
    histo_destruction timestamp(6),
    histo_destructeur_id bigint
        constraint formateur_destructeur_fk
            references utilisateur
);

create table formation_inscription
(
    id bigint not null
        constraint formation_inscription_pk
            primary key,
    session_id bigint not null
        constraint formation_session_fk
            references formation_session
            on delete cascade,
    doctorant_id bigint not null
        constraint inscription_doctorant_id_fk
            references doctorant
            on delete cascade,
    liste varchar(1),
    description text,
    histo_creation timestamp(6) not null,
    histo_createur_id bigint not null
        constraint inscription_createur_fk
            references utilisateur,
    histo_modification timestamp(6),
    histo_modificateur_id bigint
        constraint inscription_modificateur_fk
            references utilisateur,
    histo_destruction timestamp(6),
    histo_destructeur_id bigint
        constraint inscription_destructeur_fk
            references utilisateur
);

create table formation_presence
(
    id bigint not null
        constraint formation_presence_pk
            primary key,
    inscription_id bigint not null
        constraint presence_inscription_id_fk
            references formation_inscription
            on delete cascade,
    seance_id bigint not null
        constraint presence_seance_id_fk
            references formation_seance
            on delete cascade,
    temoin varchar(1),
    description text,
    histo_creation timestamp(6) not null,
    histo_createur_id bigint not null
        constraint presence_createur_fk
            references utilisateur,
    histo_modification timestamp(6),
    histo_modificateur_id bigint
        constraint presence_modificateur_fk
            references utilisateur,
    histo_destruction timestamp(6),
    histo_destructeur_id bigint
        constraint presence_destructeur_fk
            references utilisateur
);

create unique index formation_module_id_uindex
    on formation_module (id);

create table formation_enquete_categorie
(
    histo_destructeur_id integer
        constraint formation_enquete_categorie_utilisateur_id_fk_3
            references utilisateur,
    histo_destruction timestamp,
    histo_modificateur_id integer
        constraint formation_enquete_categorie_utilisateur_id_fk_2
            references utilisateur,
    histo_modification timestamp,
    histo_createur_id integer not null
        constraint formation_enquete_categorie_utilisateur_id_fk
            references utilisateur,
    histo_creation timestamp not null,
    ordre integer not null,
    description text,
    libelle varchar(1024) not null,
    id serial
        constraint formation_enquete_categorie_pk
            primary key
);

create table formation_enquete_question
(
    id bigint not null
        constraint formation_enquete_question_pk
            primary key,
    libelle varchar(1024) not null,
    description text,
    ordre bigint not null,
    histo_creation timestamp(6) not null,
    histo_createur_id bigint not null
        constraint question_createur_fk
            references utilisateur,
    histo_modification timestamp(6),
    histo_modificateur_id bigint
        constraint question_modificateur_fk
            references utilisateur,
    histo_destruction timestamp(6),
    histo_destructeur_id bigint
        constraint question_destructeur_fk
            references utilisateur,
    categorie_id integer
        constraint formation_enquete_question_formation_enquete_categorie_id_fk
            references formation_enquete_categorie
            on delete set null
);

create table formation_enquete_reponse
(
    id bigint not null
        constraint formation_enquete_reponse_pk
            primary key,
    inscription_id bigint not null
        constraint reponse_inscription_id_fk
            references formation_inscription
            on delete cascade,
    question_id bigint not null
        constraint reponse_question_id_fk
            references formation_enquete_question
            on delete cascade,
    niveau bigint not null,
    description text,
    histo_creation timestamp(6) not null,
    histo_createur_id bigint not null
        constraint reponse_createur_fk
            references utilisateur,
    histo_modification timestamp(6),
    histo_modificateur_id bigint
        constraint reponse_modificateur_fk
            references utilisateur,
    histo_destruction timestamp(6),
    histo_destructeur_id bigint
        constraint reponse_destructeur_fk
            references utilisateur
);

create unique index formation_enquete_categorie_id_uindex
    on formation_enquete_categorie (id);

create table formation_session_structure_complementaire
(
    id                    integer default nextval('formation_session_site_complementaire_id_seq'::regclass) not null
        constraint formation_session_site_complementaire_pk
            primary key,
    session_id            integer                                                                           not null
        constraint formation_session_site_complementaire_formation_session_id_fk
            references formation_session
            on delete cascade,
    structure_id          integer                                                                           not null
        constraint formation_session_site_complementaire_structure_id_fk
            references structure
            on delete cascade,
    lieu                  varchar(1024),
    histo_creation        timestamp                                                                         not null,
    histo_createur_id     integer                                                                           not null
        constraint formation_session_site_complementaire_utilisateur_id_fk_1
            references utilisateur,
    histo_modification    timestamp,
    histo_modificateur_id integer
        constraint formation_session_site_complementaire_utilisateur_id_fk_2
            references utilisateur,
    histo_destruction     timestamp,
    histo_destructeur_id  integer
        constraint formation_session_site_complementaire_utilisateur_id_fk_3
            references utilisateur
);

create unique index formation_session_site_complementaire_id_uindex
    on formation_session_structure_complementaire (id);

