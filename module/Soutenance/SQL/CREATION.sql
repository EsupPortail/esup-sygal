-------------------------------------------------------------------------------
--- QUALITE [0/8 => 2/8]
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
--- ETAT [2/8 => 3/8]
-------------------------------------------------------------------------------

create table SOUTENANCE_ETAT
(
    ID NUMBER not null primary key,
    CODE VARCHAR2(63) not null,
    LIBELLE VARCHAR2(255) not null
);

create unique index SOUTENANCE_ETAT_ID_UINDEX on SOUTENANCE_ETAT (ID);

create sequence SOUTENANCE_ETAT_ID_SEQ;

-------------------------------------------------------------------------------
--- CONFIGURATION [3/8 => 4/8]
-------------------------------------------------------------------------------

create table SOUTENANCE_CONFIGURATION
(
    ID NUMBER not null primary key,
    CODE VARCHAR2(64) not null,
    LIBELLE VARCHAR2(256),
    VALEUR VARCHAR2(128)
);

create unique index CONFIGURATION_CODE_UINDEX on SOUTENANCE_CONFIGURATION (CODE);

create sequence SOUTENANCE_CONFIGURATION_ID_SEQ;

-------------------------------------------------------------------------------
--- PROPOSITION [4/8 => 5/8]
-------------------------------------------------------------------------------

create table SOUTENANCE_PROPOSITION
(
    ID NUMBER not null primary key,
    THESE_ID NUMBER not null constraint PROPOSITION_THESE_FK references THESE on delete cascade,
    DATEPREV DATE,
    LIEU VARCHAR2(256),
    RENDU_RAPPORT DATE,
    CONFIDENTIALITE DATE,
    LABEL_EUROPEEN NUMBER default 0 not null,
    MANUSCRIT_ANGLAIS NUMBER default 0 not null,
    SOUTENANCE_ANGLAIS NUMBER default 0 not null,
    HUIT_CLOS NUMBER default 0 not null,
    EXTERIEUR NUMBER default 0 not null,
    NOUVEAU_TITRE VARCHAR2(2048),
    ETAT_ID NUMBER not null constraint SOUTENANCE_ETAT_ID_FK references SOUTENANCE_ETAT on delete set null,
    SURSIS VARCHAR2(1),
    ADRESSE_EXACTE VARCHAR2(2048),
    HISTO_CREATION DATE not null,
    HISTO_CREATEUR_ID NUMBER not null constraint PROPOSITION_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION DATE not null,
    HISTO_MODIFICATEUR_ID NUMBER not null constraint PROPOSITION_MODIFICATEUR_FK references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER constraint PROPOSITION_DESTRUCTEUR_FK references UTILISATEUR
);

create sequence SOUTENANCE_PROPOSITION_ID_SEQ;

-------------------------------------------------------------------------------
--- MEMBRE [5/8 => 6/8]
-------------------------------------------------------------------------------

create table SOUTENANCE_MEMBRE
(
    ID NUMBER not null primary key,
    PROPOSITION_ID NUMBER not null constraint MEMBRE_PROPOSITION_FK references SOUTENANCE_PROPOSITION on delete cascade,
    GENRE VARCHAR2(1) not null,
    QUALITE NUMBER default NULL not null constraint MEMBRE_QUALITE_FK references SOUTENANCE_QUALITE on delete set null,
    ETABLISSEMENT VARCHAR2(128) not null,
    ROLE_ID VARCHAR2(64) not null,
    EXTERIEUR VARCHAR2(3),
    EMAIL VARCHAR2(256) default NULL not null,
    ACTEUR_ID NUMBER constraint SOUTEMEMBRE_ACTEUR_FK references ACTEUR on delete cascade,
    VISIO NUMBER default 0 not null,
    NOM VARCHAR2(256),
    PRENOM VARCHAR2(256),
    HISTO_CREATION DATE not null,
    HISTO_CREATEUR_ID NUMBER not null constraint MEMBRE_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION DATE not null,
    HISTO_MODIFICATEUR_ID NUMBER not null constraint MEMBRE_MODIFICATEUR_FK references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER constraint MEMBRE_DESTRUCTEUR_FK references UTILISATEUR
);

create sequence SOUTENANCE_MEMBRE_ID_SEQ;

-------------------------------------------------------------------------------
--- JUSTIFICATIF [6/8 => 7/8]
-------------------------------------------------------------------------------

create table SOUTENANCE_JUSTIFICATIF
(
    ID NUMBER not null constraint SOUTENANCE_JUSTIFICATIF_PK primary key,
    PROPOSITION_ID NUMBER not null constraint JUSTIFICATIF_PROPOSITION_FK references SOUTENANCE_PROPOSITION on delete cascade,
    FICHIER_ID NUMBER not null constraint JUSTIFICATIF_FICHIER_FK references FICHIER_THESE on delete cascade,
    MEMBRE_ID NUMBER constraint JUSTIFICATIF_MEMBRE_FK references SOUTENANCE_MEMBRE on delete set null,
    HISTO_CREATION DATE not null,
    HISTO_CREATEUR_ID NUMBER not null constraint JUSTIFICATIF_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION DATE not null,
    HISTO_MODIFICATEUR_ID NUMBER not null constraint JUSTIFICATIF_MODIFICATEUR_FK references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER constraint JUSTIFICATIF_DESTRUCTEUR_FK references UTILISATEUR
);

create sequence SOUTENANCE_JUSTIFICATIF_ID_SEQ;

-------------------------------------------------------------------------------
--- AVIS [7/8 => 8/8]
-------------------------------------------------------------------------------

create table SOUTENANCE_AVIS
(
    ID NUMBER not null,
    PROPOSITION_ID NUMBER not null constraint AVIS_PROPOSITION_ID references SOUTENANCE_PROPOSITION on delete cascade,
    MEMBRE_ID NUMBER not null constraint AVIS_MEMBRE_FK references SOUTENANCE_MEMBRE on delete cascade,
    AVIS VARCHAR2(64),
    MOTIF VARCHAR2(1024),
    VALIDATION_ID NUMBER constraint AVIS_VALIDATION_FK references VALIDATION,
    FICHIER_ID NUMBER default NULL constraint AVIS_FICHIER_ID_FK references FICHIER,
    HISTO_CREATION DATE not null,
    HISTO_CREATEUR_ID NUMBER not null,
    HISTO_MODIFICATION DATE not null,
    HISTO_MODIFICATEUR_ID NUMBER not null constraint AVIS_MODIFICATEUR_FK references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER constraint AVIS_DESTRUCTEUR_ID references UTILISATEUR
);

create sequence SOUTENANCE_AVIS_ID_SEQ;
