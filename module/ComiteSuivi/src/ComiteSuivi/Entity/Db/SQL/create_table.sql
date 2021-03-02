create table COMITE_SUIVI
(
    ID NUMBER not null
        constraint COMITE_SUIVI_PK
            primary key,
    THESE_ID NUMBER not null
        constraint COMITESUIVI_THESE_ID_FK
            references THESE
                on delete cascade,
    DATE_COMITE DATE not null,
    ANNEE_THESE VARCHAR2(64) not null,
    ANNEE_SCOLAIRE VARCHAR2(64) not null,
    VALIDATION_ID NUMBER
        constraint CS_VALIDATION_ID_FK_2
            references VALIDATION
                on delete set null,
    FINALISATION_ID NUMBER
        constraint COMITE_SUIVI_VALIDATION_ID_FK
            references VALIDATION
                on delete set null,
    HISTO_CREATION DATE not null,
    HISTO_CREATEUR_ID NUMBER not null
        constraint COMITESUIVI_CREATEUR_FK
            references UTILISATEUR,
    HISTO_MODIFICATION DATE not null,
    HISTO_MODIFICATEUR_ID NUMBER not null
        constraint COMITESUIVI_MODIFICATEUR_FK
            references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER not null
        constraint COMITESUIVI_DESTRUCTEUR_FK
            references UTILISATEUR
)
/

create table COMITE_MEMBRE
(
    ID NUMBER not null
        constraint COMITE_MEMBRE_PK
            primary key,
    COMITE_ID NUMBER not null
        constraint COMITEMEMBRE_INSTANCE_FK
            references COMITE_SUIVI
            on delete cascade,
    INDIVIDU_ID NUMBER
        constraint COMITEMEMBRE_INDIVIDU_FK
            references INDIVIDU
            on delete cascade,
    ROLE_ID NUMBER not null
        constraint COMITEMEMBRE_ROLE_FK
            references ROLE
            on delete cascade,
    PRENOM VARCHAR2(1024),
    NOM VARCHAR2(1024),
    ETABLISSEMENT VARCHAR2(1024),
    EMAIL VARCHAR2(1024),
    HISTO_CREATION DATE not null,
    HISTO_CREATEUR_ID NUMBER not null
        constraint COMITEMEMBRE_CREATEUR_FK
            references UTILISATEUR,
    HISTO_MODIFICATION DATE not null,
    HISTO_MODIFICATEUR_ID NUMBER not null
        constraint COMITEMEMBRE_MODIFICATEUR_FK
            references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER not null
        constraint COMITEMEMBRE_DESTRUCTEUR_FK
            references UTILISATEUR
)
;

create table COMITE_RAPPORT
(
    ID NUMBER not null
        constraint COMITE_RAPPORT_PK
            primary key,
    COMITE_ID NUMBER not null
        constraint COMITERAPPORT_COMITE_FK
            references COMITE_SUIVI
                on delete cascade,
    MEMBRE_ID NUMBER not null
        constraint COMITERAPPORT_MEMBRE_FK
            references COMITE_MEMBRE
                on delete cascade,
    FICHIER_ID NUMBER
        constraint COMITE_RAPPORT_FICHIER_ID_FK
            references FICHIER
                on delete set null,
    HISTO_CREATION DATE not null,
    HISTO_CREATEUR_ID NUMBER not null
        constraint COMITERAPPORT_CREATEUR_FK
            references UTILISATEUR,
    HISTO_MODIFICATION DATE not null,
    HISTO_MODIFICATEUR_ID NUMBER not null
        constraint COMITERAPPORT_MODIFICATEUR_FK
            references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER
        constraint COMITERAPPORT_DESTRUCTEUR_FK
            references UTILISATEUR,
    FINALISER DATE
)
;

create sequence COMITE_SUIVI_ID_seq;
create sequence COMITE_MEMBRE_ID_seq;
create sequence COMITE_RAPPORT_ID_seq;

