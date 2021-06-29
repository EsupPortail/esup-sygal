-- FORMATION -----------------------------------------------------------------------------------------------------------

create table FORMATION_MODULE
(
    ID NUMBER not null constraint FORMATION_PK primary key,
    LIBELLE VARCHAR2(1024) not null,
    DESCRIPTION CLOB,
    LIEN VARCHAR2(1024),

    -- VALEURS PAR DEFAUT DES SESSIONS DECOULANT DU MODULE
    SITE_ID NUMBER constraint FORMATION_ETABLISSEMENT_ID_FK references ETABLISSEMENT on delete set null,
    RESPONSABLE_ID NUMBER constraint FORMATION_INDIVIDU_ID_FK references INDIVIDU on delete set null,
    MODALITE VARCHAR2(1),
    TYPE VARCHAR2(1),
    TYPE_STRUCTURE_ID NUMBER constraint FORMATION_STRUCTURE_ID_FK references STRUCTURE on delete set null,
    TAILLE_LISTE_PRINCIPALE NUMBER,
    TAILLE_LISTE_COMPLEMENTAIRE NUMBER,

    HISTO_CREATEUR_ID NUMBER not null constraint FORMATION_CREATEUR_FK references UTILISATEUR,
    HISTO_CREATION TIMESTAMP(6) not null,
    HISTO_MODIFICATEUR_ID NUMBER constraint FORMATION_MODIFICATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION TIMESTAMP(6),
    HISTO_DESTRUCTEUR_ID NUMBER constraint FORMATION_DESTRUCTEUR_FK references UTILISATEUR,
    HISTO_DESTRUCTION TIMESTAMP(6)

);
create sequence FORMATION_MODULE_ID_SEQ;

-- SESSION -------------------------------------------------------------------------------------------------------------
create table FORMATION_SESSION
(
    ID NUMBER not null constraint FORMATION_INSTANCE_PK primary key,
    SESSION_INDEX NUMBER,
    MODULE_ID NUMBER not null constraint SESSION_ID_FK references FORMATION_MODULE on delete cascade,
    DESCRIPTION CLOB,
    TAILLE_LISTE_PRINCIPALE NUMBER,
    TAILLE_LISTE_COMPLEMENTAIRE NUMBER,
    TYPE_STRUCTURE_ID NUMBER,
    SITE_ID NUMBER constraint SESSION_SITE_ID_FK references ETABLISSEMENT on delete set null,
    RESPONSABLE_ID NUMBER constraint SESSION_RESPONSABLE_ID_FK references INDIVIDU on delete set null,
    MODALITE VARCHAR2(1),
    TYPE VARCHAR2(1),
    ETAT VARCHAR2(1),

    HISTO_CREATION TIMESTAMP(6) not null,
    HISTO_CREATEUR_ID NUMBER not null constraint SESSION_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION TIMESTAMP(6),
    HISTO_MODIFICATEUR_ID NUMBER constraint SESSION_MODIFICATEUR_FK references UTILISATEUR,
    HISTO_DESTRUCTION TIMESTAMP(6),
    HISTO_DESTRUCTEUR_ID NUMBER constraint SESSION_DESTRUCTEUR_FK references UTILISATEUR
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

-- INSCRIPTION ---------------------------------------------------------------------------------------------------------

create table formation_inscription
(
    id number not null constraint formation_inscription_pk primary key,
    session_id integer not null constraint formation_session_fk references formation_session on delete cascade,
    individu_id integer not null constraint formation_individu_fk references individu on delete cascade,
    liste varchar2(1),
    description clob,
    histo_creation timestamp not null,
    histo_createur_id integer not null constraint inscription_createur_fk references utilisateur,
    histo_modification timestamp,
    histo_modificateur_id integer constraint inscription_modificateur_fk references utilisateur,
    histo_destruction timestamp,
    histo_destructeur_id integer constraint inscription_destructeur_fk references utilisateur
);
create sequence FORMATION_INSCRIPTION_ID_SEQ;



