-- FORMATION -----------------------------------------------------------------------------------------------------------

create table formation
(
    id number not null constraint formation_pk primary key,
    libelle varchar2(1024) not null,
    description clob,
    lien varchar2(1024),
    histo_createur_id integer not null constraint formation_createur_fk references utilisateur,
    histo_creation timestamp not null,
    histo_modificateur_id integer constraint formation_modificateur_fk references utilisateur,
    histo_modification timestamp,
    histo_destructeur_id integer constraint formation_destructeur_fk references utilisateur,
    histo_destruction timestamp
);
create sequence FORMATION_ID_SEQ;

-- SESSION -------------------------------------------------------------------------------------------------------------

create table FORMATION_SESSION
(
    id number not null constraint formation_instance_pk primary key,
    formation_id integer not null constraint session_id_fk references formation on delete cascade,
    description clob,
    histo_creation timestamp not null,
    histo_createur_id integer not null constraint session_createur_fk references utilisateur,
    histo_modification timestamp,
    histo_modificateur_id integer constraint session_modificateur_fk references utilisateur,
    histo_destruction timestamp,
    histo_destructeur_id integer constraint session_destructeur_fk references utilisateur
);
create sequence FORMATION_SESSION_ID_SEQ;

-- SEANCE --------------------------------------------------------------------------------------------------------------

create table formation_seance
(
    id number not null constraint formation_seance_pk primary key,
    session_id integer not null constraint formation_seance_session_fk references formation_session on delete cascade,
    debut timestamp not null,
    fin timestamp not null,
    lieu varchar2(1024),
    description clob,
    histo_creation timestamp not null,
    histo_createur_id integer not null constraint seance_createur_fk references utilisateur,
    histo_modification timestamp,
    histo_modificateur_id integer constraint seance_modificateur_fk references utilisateur,
    histo_destruction timestamp,
    histo_destructeur_id integer constraint seance_destructeur_fk references utilisateur
);
create sequence FORMATION_SEANCE_ID_SEQ;




