create table FORMATION_ETAT
(
    CODE VARCHAR2(1) not null constraint FORMATION_ETAT_PK primary key,
    LIBELLE VARCHAR2(1024),
    DESCRIPTION CLOB,
    ICONE VARCHAR2(1024),
    COULEUR VARCHAR2(1024),
    ORDRE NUMBER
);

create table FORMATION_MODULE
(
    ID NUMBER not null constraint FORMATION_PK primary key,
    LIBELLE VARCHAR2(1024) not null,
    DESCRIPTION CLOB,
    LIEN VARCHAR2(1024),
    SITE_ID NUMBER constraint FORMATION_ETABLISSEMENT_ID_FK references ETABLISSEMENT on delete set null,
    RESPONSABLE_ID NUMBER constraint FORMATION_INDIVIDU_ID_FK references INDIVIDU on delete set null,
    MODALITE VARCHAR2(1),
    TYPE VARCHAR2(1),
    TYPE_STRUCTURE_ID NUMBER constraint FORMATION_STRUCTURE_ID_FK references STRUCTURE on delete set null,
    TAILLE_LISTE_PRINCIPALE NUMBER,
    TAILLE_LISTE_COMPLEMENTAIRE NUMBER,
    HISTO_CREATEUR_ID NUMBER not null constraint FORMATION_CREATEUR_FK references UTILISATEUR,
    HISTO_CREATION TIMESTAMP(6) not null,
    HISTO_MODIFICATEUR_ID NUMBER constraint FORMATION_MODIFICATEUR_FK  references UTILISATEUR,
    HISTO_MODIFICATION TIMESTAMP(6),
    HISTO_DESTRUCTEUR_ID NUMBER  constraint FORMATION_DESTRUCTEUR_FK   references UTILISATEUR,
    HISTO_DESTRUCTION TIMESTAMP(6)
);

create table FORMATION_SESSION
(
    ID NUMBER not null constraint FORMATION_INSTANCE_PK primary key,
    MODULE_ID NUMBER not null constraint SESSION_ID_FK references FORMATION_MODULE on delete cascade,
    DESCRIPTION CLOB,
    TAILLE_LISTE_PRINCIPALE NUMBER,
    TAILLE_LISTE_COMPLEMENTAIRE NUMBER,
    TYPE_STRUCTURE_ID NUMBER,
    SITE_ID NUMBER constraint SESSION_SITE_ID_FK references ETABLISSEMENT on delete set null,
    RESPONSABLE_ID NUMBER constraint SESSION_RESPONSABLE_ID_FK references INDIVIDU on delete set null,
    MODALITE VARCHAR2(1),
    TYPE VARCHAR2(1),
    ETAT_CODE VARCHAR2(1) constraint SESSION_ETAT_CODE_FK references FORMATION_ETAT on delete set null,
    SESSION_INDEX NUMBER,
    HISTO_CREATION TIMESTAMP(6) not null,
    HISTO_CREATEUR_ID NUMBER not null constraint SESSION_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION TIMESTAMP(6),
    HISTO_MODIFICATEUR_ID NUMBER constraint SESSION_MODIFICATEUR_FK  references UTILISATEUR,
    HISTO_DESTRUCTION TIMESTAMP(6),
    HISTO_DESTRUCTEUR_ID NUMBER constraint SESSION_DESTRUCTEUR_FK    references UTILISATEUR
);

create table FORMATION_SEANCE
(
    ID NUMBER not null constraint FORMATION_SEANCE_PK primary key,
    SESSION_ID NUMBER not null constraint FORMATION_SEANCE_SESSION_FK references FORMATION_SESSION on delete cascade,
    DEBUT TIMESTAMP(6) not null,
    FIN TIMESTAMP(6) not null,
    LIEU VARCHAR2(1024),
    DESCRIPTION CLOB,
    HISTO_CREATION TIMESTAMP(6) not null,
    HISTO_CREATEUR_ID NUMBER not null constraint SEANCE_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION TIMESTAMP(6),
    HISTO_MODIFICATEUR_ID NUMBER constraint SEANCE_MODIFICATEUR_FK  references UTILISATEUR,
    HISTO_DESTRUCTION TIMESTAMP(6),
    HISTO_DESTRUCTEUR_ID NUMBER constraint SEANCE_DESTRUCTEUR_FK    references UTILISATEUR
);

create table FORMATION_FORMATEUR
(
    ID NUMBER not null constraint FORMATION_FORMATEUR_PK primary key,
    INDIVIDU_ID NUMBER not null constraint FORMATEUR_INDIVIDU_ID_FK references INDIVIDU on delete cascade,
    SESSION_ID NUMBER not null  constraint FORMATEUR_SESSION_ID_FK  references FORMATION_SESSION on delete cascade,
    DESCRIPTION CLOB,
    HISTO_CREATION TIMESTAMP(6) not null,
    HISTO_CREATEUR_ID NUMBER not null constraint FORMATEUR_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION TIMESTAMP(6),
    HISTO_MODIFICATEUR_ID NUMBER constraint FORMATEUR_MODIFICATEUR_FK  references UTILISATEUR,
    HISTO_DESTRUCTION TIMESTAMP(6),
    HISTO_DESTRUCTEUR_ID NUMBER constraint FORMATEUR_DESTRUCTEUR_FK    references UTILISATEUR
);

create table FORMATION_INSCRIPTION
(
    ID NUMBER not null constraint FORMATION_INSCRIPTION_PK primary key,
    SESSION_ID NUMBER not null constraint FORMATION_SESSION_FK references FORMATION_SESSION on delete cascade,
    DOCTORANT_ID NUMBER not null constraint INSCRIPTION_DOCTORANT_ID_FK references DOCTORANT on delete cascade,
    LISTE VARCHAR2(1),
    DESCRIPTION CLOB,
    HISTO_CREATION TIMESTAMP(6) not null,
    HISTO_CREATEUR_ID NUMBER not null constraint INSCRIPTION_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION TIMESTAMP(6),
    HISTO_MODIFICATEUR_ID NUMBER constraint INSCRIPTION_MODIFICATEUR_FK  references UTILISATEUR,
    HISTO_DESTRUCTION TIMESTAMP(6),
    HISTO_DESTRUCTEUR_ID NUMBER constraint INSCRIPTION_DESTRUCTEUR_FK    references UTILISATEUR
);

create table FORMATION_PRESENCE
(
    ID NUMBER not null constraint FORMATION_PRESENCE_PK primary key,
    INSCRIPTION_ID NUMBER not null constraint PRESENCE_INSCRIPTION_ID_FK references FORMATION_INSCRIPTION on delete cascade,
    SEANCE_ID NUMBER not null constraint PRESENCE_SEANCE_ID_FK references FORMATION_SEANCE on delete cascade,
    TEMOIN VARCHAR2(1),
    DESCRIPTION CLOB,
    HISTO_CREATEUR_ID NUMBER not null constraint PRESENCE_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION TIMESTAMP(6),
    HISTO_MODIFICATEUR_ID NUMBER constraint PRESENCE_MODIFICATEUR_FK  references UTILISATEUR,
    HISTO_DESTRUCTION TIMESTAMP(6),
    HISTO_DESTRUCTEUR_ID NUMBER constraint PRESENCE_DESTRUCTEUR_FK    references UTILISATEUR
);

create table FORMATION_ENQUETE_QUESTION
(
    ID NUMBER not null constraint FORMATION_ENQUETE_QUESTION_PK primary key,
    LIBELLE VARCHAR2(1024) not null,
    DESCRIPTION CLOB,
    ORDRE NUMBER not null,
    HISTO_CREATEUR_ID NUMBER not null constraint QUESTION_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION TIMESTAMP(6),
    HISTO_MODIFICATEUR_ID NUMBER constraint QUESTION_MODIFICATEUR_FK  references UTILISATEUR,
    HISTO_DESTRUCTION TIMESTAMP(6),
    HISTO_DESTRUCTEUR_ID NUMBER constraint QUESTION_DESTRUCTEUR_FK    references UTILISATEUR
);

create table FORMATION_ENQUETE_REPONSE
(
    ID NUMBER not null constraint FORMATION_ENQUETE_REPONSE_PK primary key,
    INSCRIPTION_ID NUMBER not null constraint REPONSE_INSCRIPTION_ID_FK references FORMATION_INSCRIPTION on delete cascade,
    QUESTION_ID NUMBER not null constraint REPONSE_QUESTION_ID_FK references FORMATION_ENQUETE_QUESTION on delete cascade,
    NIVEAU NUMBER not null,
    DESCRIPTION CLOB,
    HISTO_CREATEUR_ID NUMBER not null constraint REPONSE_CREATEUR_FK references UTILISATEUR,
    HISTO_MODIFICATION TIMESTAMP(6),
    HISTO_MODIFICATEUR_ID NUMBER constraint REPONSE_MODIFICATEUR_FK  references UTILISATEUR,
    HISTO_DESTRUCTION TIMESTAMP(6),
    HISTO_DESTRUCTEUR_ID NUMBER constraint REPONSE_DESTRUCTEUR_FK    references UTILISATEUR
);


