create table SOUTENANCE_QUALITE
(
    ID NUMBER not null
        primary key,
    LIBELLE VARCHAR2(128) not null,
    RANG VARCHAR2(1) not null,
    HDR VARCHAR2(1) not null,
    EMERITAT VARCHAR2(1) not null
);

create table SOUTENANCE_QUALITE_SUP
(
    ID NUMBER not null,
    QUALITE_ID NUMBER not null
        constraint SQS_QUALITE_FK
            references SOUTENANCE_QUALITE
            on delete cascade,
    LIBELLE VARCHAR2(255) not null,
    HISTO_CREATION DATE not null,
    HISTO_CREATEUR_ID NUMBER not null
        constraint SQS_CREATEUR_FK
            references UTILISATEUR,
    HISTO_MODIFICATION DATE not null,
    HISTO_MODIFICATEUR_ID NUMBER not null
        constraint SQS_MODIFICATEUR_FK
            references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER
        constraint SQS_DESTRUCTEUR_FK
            references UTILISATEUR
)
    /

create unique index SQS_ID_UINDEX
    on SOUTENANCE_QUALITE_SUP (ID)
    /

alter table SOUTENANCE_QUALITE_SUP
    add constraint SOUTENANCE_QUALITE_SUP_PK
        primary key (ID)
    /

INSERT INTO SYGAL_TEST.SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (0, 'Qualité inconnue', 'B', 'N', 'N');
INSERT INTO SYGAL_TEST.SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (1, 'Professeur des universités', 'A', 'O', 'N');
INSERT INTO SYGAL_TEST.SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (2, 'Directeur de recherche', 'A', 'O', 'N');
INSERT INTO SYGAL_TEST.SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (3, 'Maître de conférences', 'B', 'N', 'N');
INSERT INTO SYGAL_TEST.SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (4, 'Chargé de recherche', 'B', 'N', 'N');
INSERT INTO SYGAL_TEST.SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (5, 'Maître de conférences (HDR)', 'B', 'O', 'N');
INSERT INTO SYGAL_TEST.SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (6, 'Professeur émerite', 'A', 'O', 'O');