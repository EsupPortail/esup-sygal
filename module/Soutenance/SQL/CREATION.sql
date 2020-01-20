-------------------------------------------------------------------------------
--- QUALITE
-------------------------------------------------------------------------------

create table SOUTENANCE_QUALITE
(
    ID NUMBER not null primary key,
    LIBELLE VARCHAR2(128) not null,
    RANG VARCHAR2(1) not null,
    HDR VARCHAR2(1) not null,
    EMERITAT VARCHAR2(1) not null
);

create table SOUTENANCE_QUALITE_SUP
(
    ID NUMBER not null primary key,
    QUALITE_ID NUMBER not null constraint SQS_QUALITE_FK references SOUTENANCE_QUALITE on delete cascade,
    LIBELLE VARCHAR2(255) not null,
    HISTO_CREATION DATE not null,
    HISTO_CREATEUR_ID NUMBER not null constraint SQS_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION DATE not null,
    HISTO_MODIFICATEUR_ID NUMBER not null constraint SQS_MODIFICATEUR_FK references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER constraint SQS_DESTRUCTEUR_FK references UTILISATEUR
);

create sequence SOUTENANCE_QUALITE_ID_SEQ;
create sequence SOUTENANCE_QUALITE_SUP_ID_SEQ;

-------------------------------------------------------------------------------
--- ETAT
-------------------------------------------------------------------------------

create table SOUTENANCE_ETAT
(
    ID NUMBER not null primary key,
    CODE VARCHAR2(63) not null,
    LIBELLE VARCHAR2(255) not null
);

create unique index SOUTENANCE_ETAT_ID_UINDEX on SOUTENANCE_ETAT (ID);

-------------------------------------------------------------------------------
--- CONFIGURATION
-------------------------------------------------------------------------------

