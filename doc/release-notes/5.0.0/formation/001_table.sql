
-- ETAT ----------------------------------------------------------------------------------------------------------------

create table formation_etat
(
    code varchar(1) not null constraint formation_etat_pk primary key,
    libelle varchar(1024),
    description text,
    icone varchar(1024),
    couleur varchar(1024),
    ordre bigint
);

-- DECLARATION DE L'OFFRE DE FORMATION ---------------------------------------------------------------------------------

create table formation_module
(
    id                       serial        not null        constraint formation_module_pkey primary key,
    libelle                  text          not null,
    description              text,
    lien                     text,
    histo_creation           timestamp     not null,
    histo_createur_id        integer       not null        constraint formation_module_utilisateur_id_fk_1 references utilisateur,
    histo_modification       timestamp,
    histo_modificateur_id    integer                       constraint formation_module_utilisateur_id_fk_2 references utilisateur,
    histo_destruction        timestamp,
    histo_destructeur_id     integer                       constraint formation_module_utilisateur_id_fk_3 references utilisateur
);
create unique index formation_module_id_uindex on formation_module (id);

create table formation_formation
(
    id                       serial        not null        constraint formation_formation_pkey primary key,
    module_id                integer                       constraint formation_formation_module_id_fk references formation_module on delete set null,
    libelle                  varchar(1024) not null,
    description              text,
    lien                     varchar(1024),
    site_id                  integer                       constraint formation_formation_etablissement_id_fk references etablissement on delete set null,
    responsable_id           integer                       constraint formation_formation_individu_id_fk references individu on delete set null,
    modalite                 varchar(1),
    type                     varchar(1),
    type_structure_id        integer                       constraint formation_formation_structure_id_fk references structure on delete set null,
    taille_liste_principale     integer,
    taille_liste_complementaire integer,
    histo_createur_id        integer       not null        constraint formation_formation_utilisateur_id_fk_1 references utilisateur,
    histo_creation           timestamp     not null,
    histo_modificateur_id    integer                       constraint formation_formation_utilisateur_id_fk_2 references utilisateur,
    histo_modification       timestamp,
    histo_destructeur_id     integer                       constraint formation_formation_utilisateur_id_fk_3 references utilisateur,
    histo_destruction        timestamp
);
create unique index formation_formation_id_uindex on formation_formation (id);

create table formation_session
(
    id                       serial        not null        constraint formation_session_pkey primary key,
    session_index            integer,
    formation_id             integer       not null        constraint formation_session_formation_id_fk references formation_formation on delete cascade,
    description              text,
    taille_liste_principale     integer,
    taille_liste_complementaire integer,
    site_id                  integer                       constraint formation_session_site_id_fk references etablissement on delete set null,
    responsable_id           integer                       constraint formation_session_responsable_id_fk references individu on delete set null,
    modalite                 varchar(1),
    type                     varchar(1),
    type_structure_id        integer                       constraint formation_session_type_structure_id_fk references structure on delete set null,
    etat_code                varchar(1)                    constraint session_etat_code_fk references formation_etat on delete set null,
    histo_createur_id        integer       not null        constraint formation_session_utilisateur_id_fk_1 references utilisateur,
    histo_creation           timestamp     not null,
    histo_modificateur_id    integer                       constraint formation_session_utilisateur_id_fk_2 references utilisateur,
    histo_modification       timestamp,
    histo_destructeur_id     integer                       constraint formation_session_utilisateur_id_fk_3 references utilisateur,
    histo_destruction        timestamp
);
create unique index formation_session_id_uindex on formation_session (id);

create table formation_seance
(
    id                       serial        not null        constraint formation_seance_pkey primary key,
    session_id               integer       not null        constraint formation_seance_session_fk references formation_session on delete cascade,
    debut                    timestamp     not null,
    fin                      timestamp     not null,
    lieu                     varchar(1024),
    description              text,
    histo_createur_id        integer       not null        constraint formation_seance_utilisateur_id_fk_1 references utilisateur,
    histo_creation           timestamp     not null,
    histo_modificateur_id    integer                       constraint formation_seance_utilisateur_id_fk_2 references utilisateur,
    histo_modification       timestamp,
    histo_destructeur_id     integer                       constraint formation_seance_utilisateur_id_fk_3 references utilisateur,
    histo_destruction        timestamp
);
create unique index formation_seance_id_uindex on formation_seance (id);

create table formation_session_structure_valide
(
    id                       serial        not null        constraint formation_session_structure_valide_pkey primary key,
    session_id               integer       not null        constraint formation_session_structure_valide_session_id_fk references formation_session on delete cascade,
    structure_id             integer       not null        constraint formation_session_structure_valide_structure_id_fk references structure on delete cascade,
    lieu                     varchar(1024),
    histo_createur_id        integer       not null        constraint formation_session_structure_valide_utilisateur_id_fk_1 references utilisateur,
    histo_creation           timestamp     not null,
    histo_modificateur_id    integer                       constraint formation_session_structure_valide_utilisateur_id_fk_2 references utilisateur,
    histo_modification       timestamp,
    histo_destructeur_id     integer                       constraint formation_session_structure_valide_utilisateur_id_fk_3 references utilisateur,
    histo_destruction        timestamp
);
create unique index formation_session_structure_valide_id_uindex on formation_session_structure_valide (id);

create table formation_formateur
(
    id                       serial        not null        constraint formation_formateur_pkey primary key,
    individu_id              integer       not null        constraint formation_formateur_individu_id_fk references individu on delete cascade,
    session_id               integer       not null        constraint formation_formateur_session_id_fk references formation_session on delete cascade,
    description              text,
    histo_createur_id        integer       not null        constraint formation_formateur_utilisateur_id_fk_1 references utilisateur,
    histo_creation           timestamp     not null,
    histo_modificateur_id    integer                       constraint formation_formateur_utilisateur_id_fk_2 references utilisateur,
    histo_modification       timestamp,
    histo_destructeur_id     integer                       constraint formation_formateur_utilisateur_id_fk_3 references utilisateur,
    histo_destruction        timestamp
);
create unique index formation_formateur_id_uindex on formation_formateur (id);


-- INSCRIPTION ---------------------------------------------------------------------------------------------------------

create table formation_inscription
(
    id                       serial        not null        constraint formation_inscription_pkey primary key,
    session_id               integer       not null        constraint formation_inscription_session_fk references formation_session on delete cascade,
    doctorant_id             integer       not null        constraint formation_inscription_doctorant_id_fk references doctorant on delete cascade,
    liste                    varchar(1),
    description              text,
    validation_enquete       timestamp,
    histo_createur_id        integer       not null        constraint formation_inscription_utilisateur_id_fk_1 references utilisateur,
    histo_creation           timestamp     not null,
    histo_modificateur_id    integer                       constraint formation_inscription_utilisateur_id_fk_2 references utilisateur,
    histo_modification       timestamp,
    histo_destructeur_id     integer                       constraint formation_inscription_utilisateur_id_fk_3 references utilisateur,
    histo_destruction        timestamp

);
create unique index formation_inscription_id_uindex on formation_inscription (id);

create table formation_presence
(
    id                       serial        not null        constraint formation_presence_pkey primary key,
    inscription_id           integer       not null        constraint formation_presence_inscription_id_fk references formation_inscription on delete cascade,
    seance_id                integer       not null        constraint foramtion_presence_seance_id_fk references formation_seance on delete cascade,
    temoin                   varchar(1),
    description              text,
    histo_createur_id        integer       not null        constraint formation_presence_utilisateur_id_fk_1 references utilisateur,
    histo_creation           timestamp     not null,
    histo_modificateur_id    integer                       constraint formation_presence_utilisateur_id_fk_2 references utilisateur,
    histo_modification       timestamp,
    histo_destructeur_id     integer                       constraint formation_presence_utilisateur_id_fk_3 references utilisateur,
    histo_destruction        timestamp
);
create unique index formation_presence_id_uindex on formation_presence (id);


-- ENQUETE -------------------------------------------------------------------------------------------------------------

create table formation_enquete_categorie
(
    id                       serial        not null        constraint formation_enquete_categorie_pkey primary key,
    libelle                  varchar(1024) not null,
    description              text,
    ordre                    integer       not null,
    histo_createur_id        integer       not null        constraint formation_enquete_categorie_utilisateur_id_fk_1 references utilisateur,
    histo_creation           timestamp     not null,
    histo_modificateur_id    integer                       constraint formation_enquete_categorie_utilisateur_id_fk_2 references utilisateur,
    histo_modification       timestamp,
    histo_destructeur_id     integer                       constraint formation_enquete_categorie_utilisateur_id_fk_3 references utilisateur,
    histo_destruction        timestamp
);
create unique index formation_enquete_categorie_id_uindex on formation_enquete_categorie (id);

create table formation_enquete_question
(
    id                       serial        not null        constraint formation_enquete_question_pkey primary key,
    categorie_id             integer                       constraint formation_enquete_question_categorie_id_fk references formation_enquete_categorie,
    libelle                  varchar(1024) not null,
    description              text,
    ordre                    integer       not null,
    histo_createur_id        integer       not null        constraint formation_enquete_question_utilisateur_id_fk_1 references utilisateur,
    histo_creation           timestamp     not null,
    histo_modificateur_id    integer                       constraint formation_enquete_question_utilisateur_id_fk_2 references utilisateur,
    histo_modification       timestamp,
    histo_destructeur_id     integer                       constraint formation_enquete_question_utilisateur_id_fk_3 references utilisateur,
    histo_destruction        timestamp
);
create unique index formation_enquete_question_id_uindex on formation_enquete_question (id);

create table formation_enquete_reponse
(
    id                       serial        not null        constraint formation_enquete_reponse_pkey primary key,
    inscription_id           integer       not null        constraint formation_enquete_reponse_inscription_id_fk references formation_inscription on delete cascade,
    question_id              integer       not null        constraint formation_enquete_reponse_question_id_fk references formation_enquete_question on delete cascade,
    niveau                   integer       not null,
    description              text,
    histo_createur_id        integer       not null        constraint formation_enquete_reponse_utilisateur_id_fk_1 references utilisateur,
    histo_creation           timestamp     not null,
    histo_modificateur_id    integer                       constraint formation_enquete_reponse_utilisateur_id_fk_2 references utilisateur,
    histo_modification       timestamp,
    histo_destructeur_id     integer                       constraint formation_enquete_reponse_utilisateur_id_fk_3 references utilisateur,
    histo_destruction        timestamp
);
create unique index formation_enquete_reponse_id_uindex on formation_enquete_reponse (id);