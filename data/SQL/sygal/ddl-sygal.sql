create sequence IMPORT_NOTIF_ID_SEQ
/

create sequence IMPORT_OBSERV_ID_SEQ
/

create sequence API_LOG_ID_SEQ
/

create sequence UTILISATEUR_ID_SEQ
/

create sequence ROLE_ID_SEQ
/

create sequence ATTESTATION_ID_SEQ
/

create sequence FICHIER_ID_SEQ
/

create sequence CONTENU_FICHIER_ID_SEQ
/

create sequence DIFFUSION_ID_SEQ
/

create sequence FAQ_ID_SEQ
/

create sequence INDIVIDU_ID_SEQ
/

create sequence USER_ROLE_ID_SEQ
/

create sequence "USER_id_seq"
/

create sequence SYNC_LOG_ID_SEQ
/

create sequence VARIABLE_ID_SEQ
/

create sequence DOCTORANT_ID_SEQ
/

create sequence THESE_ID_SEQ
/

create sequence ACTEUR_ID_SEQ
/

create sequence IMPORT_OBSERV_RESULT_ID_SEQ
/

create sequence DOCTORANT_COMPL_ID_SEQ
/

create sequence UNITE_RECH_ID_SEQ
/

create sequence RDV_BU_ID_SEQ
/

create sequence ECOLE_DOCT_ID_SEQ
/

create sequence ECOLE_DOCT_IND_ID_SEQ
/

create sequence METADONNEE_THESE_ID_SEQ
/

create sequence UNITE_RECH_IND_ID_SEQ
/

create sequence VALIDATION_ID_SEQ
/

create sequence VALIDITE_FICHIER_ID_SEQ
/

create sequence NOTIF_RESULT_ID_SEQ
/

create sequence ETABLISSEMENT_ID_SEQ
/

create sequence PRIVILEGE_ID_SEQ
/

create sequence STRUCTURE_ID_SEQ
/

create sequence INDIVIDU_ROLE_ID_SEQ
/

create sequence RECAP_BU_ID_SEQ
/

create sequence MAILCONFIRMATION_ID_SEQ
/

create sequence MAIL_CONFIRMATION_ID_SEQ
/

create sequence STRUCTURE_SUBSTIT_ID_SEQ
/

create table SOURCE
(
  ID NUMBER not null
    constraint SOURCE_PK
    primary key,
  CODE VARCHAR2(64 char) not null
    constraint SOURCE_CODE_UN
    unique,
  LIBELLE VARCHAR2(128 char) not null,
  IMPORTABLE NUMBER(1) not null
)
/

comment on table SOURCE is 'Sources de données, importables ou non, ex: Apogée, Physalis.'
/

create table API_LOG
(
  ID NUMBER not null
    constraint API_LOG_PK
    primary key,
  REQ_URI VARCHAR2(2000 char) not null,
  REQ_START_DATE DATE not null,
  REQ_END_DATE DATE,
  REQ_STATUS VARCHAR2(32 char),
  REQ_RESPONSE CLOB
)
/

comment on table API_LOG is 'Logs des appels aux API des établissements.'
/

create table UTILISATEUR
(
  ID NUMBER not null
    constraint UTILISATEUR_PK
    primary key,
  USERNAME VARCHAR2(255 char)
    constraint UTILISATEUR_USERNAME_UN
    unique,
  EMAIL VARCHAR2(255 char),
  DISPLAY_NAME VARCHAR2(100 char),
  PASSWORD VARCHAR2(128 char) not null,
  STATE NUMBER default 1 not null,
  LAST_ROLE_ID NUMBER,
  INDIVIDU_ID NUMBER
)
/

comment on table UTILISATEUR is 'Comptes utilisateurs s''étant déjà connecté à l''application + comptes avec mot de passe local.'
/

create table CATEGORIE_PRIVILEGE
(
  ID NUMBER not null
    primary key,
  CODE VARCHAR2(150 char) not null,
  LIBELLE VARCHAR2(200 char) not null,
  ORDRE NUMBER
)
/

create unique index CATEGORIE_PRIVILEGE_UNIQUE
  on CATEGORIE_PRIVILEGE (CODE)
/

create table NATURE_FICHIER
(
  ID NUMBER not null
    primary key,
  CODE VARCHAR2(50 char) default NULL not null,
  LIBELLE VARCHAR2(100 char) default NULL

)
/

create table VERSION_FICHIER
(
  ID NUMBER not null
    constraint VERSION_FICHIER_PK
    primary key,
  CODE VARCHAR2(16 char) not null,
  LIBELLE VARCHAR2(128 char) not null
)
/

create unique index VERSION_FICHIER_UNIQ_CODE
  on VERSION_FICHIER (CODE)
/

create table FAQ
(
  ID NUMBER not null
    constraint FAQ_PK
    primary key,
  QUESTION VARCHAR2(2000 char) not null,
  REPONSE VARCHAR2(2000 char) not null,
  ORDRE NUMBER
)
/

create table PRIVILEGE
(
  ID NUMBER not null
    primary key,
  CATEGORIE_ID NUMBER not null
    references CATEGORIE_PRIVILEGE
    on delete cascade,
  CODE VARCHAR2(150 char) not null,
  LIBELLE VARCHAR2(200 char) not null,
  ORDRE NUMBER
)
/

create index PRIVILEGE_CATEG_IDX
  on PRIVILEGE (CATEGORIE_ID)
/

create unique index PRIVILEGE_UNIQUE
  on PRIVILEGE (CATEGORIE_ID, CODE)
/

create table INDIVIDU
(
  ID NUMBER not null
    constraint INDIVIDU_PK
    primary key,
  TYPE VARCHAR2(32),
  CIVILITE VARCHAR2(5 char) default NULL,
  NOM_USUEL VARCHAR2(60 char) not null,
  NOM_PATRONYMIQUE VARCHAR2(60 char) default NULL,
  PRENOM1 VARCHAR2(60 char) not null,
  PRENOM2 VARCHAR2(60 char),
  PRENOM3 VARCHAR2(60 char),
  EMAIL VARCHAR2(255 char),
  DATE_NAISSANCE DATE default NULL,
  NATIONALITE VARCHAR2(128 char),
  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null
    constraint INDIVIDU_SOURCE_FK
    references SOURCE
    on delete cascade,
  HISTO_CREATEUR_ID NUMBER not null
    constraint INDIVIDU_HC_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint INDIVIDU_HM_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint INDIVIDU_HD_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_DESTRUCTION DATE
)
/

create unique index INDIVIDU_SOURCE_CODE_UNIQ
  on INDIVIDU (SOURCE_CODE)
/

create index INDIVIDU_SRC_ID_INDEX
  on INDIVIDU (SOURCE_ID)
/

create index INDIVIDU_HCFK_IDX
  on INDIVIDU (HISTO_CREATEUR_ID)
/

create index INDIVIDU_HMFK_IDX
  on INDIVIDU (HISTO_MODIFICATEUR_ID)
/

create index INDIVIDU_HDFK_IDX
  on INDIVIDU (HISTO_DESTRUCTEUR_ID)
/

create table TYPE_VALIDATION
(
  ID NUMBER not null
    constraint TYPE_VALIDATION_PK
    primary key,
  CODE VARCHAR2(50 char) not null,
  LIBELLE VARCHAR2(100 char)
)
/

create unique index TYPE_VALIDATION_UN
  on TYPE_VALIDATION (CODE)
/

create table WF_ETAPE
(
  ID NUMBER not null
    constraint WF_ETAPE_PK
    primary key,
  CODE VARCHAR2(128 char) not null
    constraint WF_ETAPE_CODE_UN
    unique,
  ORDRE NUMBER default 1 not null
    constraint WF_ETAPE_ORDRE_UN
    unique,
  CHEMIN NUMBER default 1 not null,
  OBLIGATOIRE NUMBER(1) default 1 not null,
  ROUTE VARCHAR2(200 char) not null,
  LIBELLE_ACTEUR VARCHAR2(150 char) not null,
  LIBELLE_AUTRES VARCHAR2(150 char) not null,
  DESC_NON_FRANCHIE VARCHAR2(250 char) not null,
  DESC_SANS_OBJECTIF VARCHAR2(250 char)
)
/

create table NOTIF
(
  ID NUMBER not null
    constraint NOTIF_PK
    primary key,
  CODE VARCHAR2(100) not null
    constraint NOTIF_UNIQ
    unique,
  DESCRIPTION VARCHAR2(255) not null,
  DESTINATAIRES VARCHAR2(500) not null,
  TEMPLATE CLOB not null,
  ENABLED NUMBER default 1 not null
)
/

create table NOTIF_RESULT
(
  ID NUMBER not null
    constraint NOTIF_RESULT_PK
    primary key,
  NOTIF_ID NUMBER not null
    constraint NOTIF_RESULT__NOTIF_FK
    references NOTIF
    on delete cascade,
  SUJET VARCHAR2(255) not null,
  CORPS CLOB not null,
  DATE_ENVOI DATE not null,
  ERREUR CLOB
)
/

create index NOTIF_RESULT_NOTIF_IDX
  on NOTIF_RESULT (NOTIF_ID)
/

create table IMPORT_OBSERV
(
  ID NUMBER not null
    constraint IMPORT_OBSERV_PK
    primary key,
  CODE VARCHAR2(50 char) not null
    constraint IMPORT_OBSERV_CODE_UN
    unique,
  TABLE_NAME VARCHAR2(50 char) not null,
  COLUMN_NAME VARCHAR2(50 char) not null,
  OPERATION VARCHAR2(50 char) default 'UPDATE' not null,
  TO_VALUE VARCHAR2(1000 char),
  DESCRIPTION VARCHAR2(200 char),
  ENABLED NUMBER(1) default 0 not null,
  constraint IMPORT_OBSERV_UN
  unique (TABLE_NAME, COLUMN_NAME, OPERATION, TO_VALUE)
)
/

create table IMPORT_OBSERV_RESULT
(
  ID NUMBER not null
    constraint IMPORT_OBSERV_RESULT_PK
    primary key,
  IMPORT_OBSERV_ID NUMBER not null
    constraint IMPORT_OBSERV_RESULT__IO_FK
    references IMPORT_OBSERV
    on delete cascade,
  DATE_CREATION DATE default SYSDATE not null,
  SOURCE_CODE VARCHAR2(64 char) not null,
  RESULTAT CLOB not null,
  DATE_NOTIF DATE
)
/

create index IMPORT_OBSERV_RESULT_IO_IDX
  on IMPORT_OBSERV_RESULT (IMPORT_OBSERV_ID)
/

create table IMPORT_OBS_NOTIF
(
  ID NUMBER not null
    constraint IOND_PK
    primary key,
  IMPORT_OBSERV_ID NUMBER not null
    constraint IOND__IO_FK
    references IMPORT_OBSERV
    on delete cascade,
  NOTIF_ID NUMBER not null
    constraint IOND__N_FK
    references NOTIF
    on delete cascade
)
/

create index IMPORT_OBS_NOTIF_N_IDX
  on IMPORT_OBS_NOTIF (NOTIF_ID)
/

create index IMPORT_OBS_NOTIF_IO_IDX
  on IMPORT_OBS_NOTIF (IMPORT_OBSERV_ID)
/

create table IMPORT_OBS_RESULT_NOTIF
(
  ID NUMBER not null
    constraint IORNR_PK
    primary key,
  IMPORT_OBSERV_RESULT_ID NUMBER not null
    constraint IORNR__IOR_FK
    references IMPORT_OBSERV_RESULT
    on delete cascade,
  NOTIF_RESULT_ID NUMBER not null
    constraint IORNR__NR_FK
    references NOTIF_RESULT
    on delete cascade
)
/

create index IMPORT_OBS_NOTIF_IOR_IDX
  on IMPORT_OBS_RESULT_NOTIF (IMPORT_OBSERV_RESULT_ID)
/

create index IMPORT_OBS_NOTIF_NR_IDX
  on IMPORT_OBS_RESULT_NOTIF (NOTIF_RESULT_ID)
/

create table SYNC_LOG
(
  ID NUMBER not null
    constraint SYNC_LOG_PK
    primary key,
  DATE_SYNC TIMESTAMP(6) not null,
  MESSAGE CLOB not null,
  TABLE_NAME VARCHAR2(30 char),
  SOURCE_CODE VARCHAR2(200 char)
)
/

create table TMP_VARIABLE
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,
  COD_VAP VARCHAR2(50 char),
  LIB_VAP VARCHAR2(300 char),
  PAR_VAP VARCHAR2(200 char),
  DATE_DEB_VALIDITE DATE default NULL not null,
  DATE_FIN_VALIDITE DATE default NULL not null
)
/

create index TMP_VARIABLE_SOURCE_CODE_INDEX
  on TMP_VARIABLE (SOURCE_CODE)
/

create index TMP_VARIABLE_SOURCE_ID_INDEX
  on TMP_VARIABLE (SOURCE_ID)
/

create unique index TMP_VARIABLE_UNIQ
  on TMP_VARIABLE (ID, ETABLISSEMENT_ID)
/

create table TMP_ACTEUR
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,
  INDIVIDU_ID VARCHAR2(64 char) not null,
  THESE_ID VARCHAR2(64 char) not null,
  ROLE_ID VARCHAR2(64 char) not null,
  LIB_CPS VARCHAR2(200 char),
  LIB_ETB VARCHAR2(200 char),
  COD_CPS VARCHAR2(50 char),
  COD_ETB VARCHAR2(50 char),
  COD_ROJ_COMPL VARCHAR2(50 char),
  LIB_ROJ_COMPL VARCHAR2(200 char),
  TEM_HAB_RCH_PER VARCHAR2(1 char),
  TEM_RAP_RECU VARCHAR2(1 char)
)
/

create index TMP_ACTEUR_SOURCE_CODE_INDEX
  on TMP_ACTEUR (SOURCE_CODE)
/

create index TMP_ACTEUR_SOURCE_ID_INDEX
  on TMP_ACTEUR (SOURCE_ID)
/

create unique index TMP_ACTEUR_UNIQ
  on TMP_ACTEUR (ID, ETABLISSEMENT_ID)
/

create table TMP_ROLE
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,
  LIB_ROJ VARCHAR2(200 char),
  LIC_ROJ VARCHAR2(50 char)
)
/

create index TMP_ROLE_SOURCE_CODE_INDEX
  on TMP_ROLE (SOURCE_CODE)
/

create index TMP_ROLE_SOURCE_ID_INDEX
  on TMP_ROLE (SOURCE_ID)
/

create unique index TMP_ROLE_UNIQ
  on TMP_ROLE (ID, ETABLISSEMENT_ID)
/

create table TMP_THESE
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,
  DOCTORANT_ID VARCHAR2(64 char) not null,
  ECOLE_DOCT_ID VARCHAR2(64 char),
  UNITE_RECH_ID VARCHAR2(64 char),
  CORRECTION_POSSIBLE VARCHAR2(30 char),
  DAT_AUT_SOU_THS DATE,
  DAT_FIN_CFD_THS DATE,
  DAT_DEB_THS DATE,
  DAT_PREV_SOU DATE,
  DAT_SOU_THS DATE,
  ETA_THS VARCHAR2(20 char),
  LIB_INT1_DIS VARCHAR2(200 char),
  LIB_ETAB_COTUT VARCHAR2(60 char),
  LIB_PAYS_COTUT VARCHAR2(40 char),
  COD_NEG_TRE VARCHAR2(1 char),
  TEM_SOU_AUT_THS VARCHAR2(1 char),
  TEM_AVENANT_COTUT VARCHAR2(1 char),
  LIB_THS VARCHAR2(2048 char)
)
/

create index TMP_THESE_SOURCE_CODE_INDEX
  on TMP_THESE (SOURCE_CODE)
/

create index TMP_THESE_SOURCE_ID_INDEX
  on TMP_THESE (SOURCE_ID)
/

create unique index TMP_THESE_UNIQ
  on TMP_THESE (ID, ETABLISSEMENT_ID)
/

create table TMP_DOCTORANT
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,
  INDIVIDU_ID VARCHAR2(64 char) not null
)
/

create index TMP_DOCTORANT_SOURCE_CODE_IDX
  on TMP_DOCTORANT (SOURCE_CODE)
/

create index TMP_DOCTORANT_SOURCE_ID_IDX
  on TMP_DOCTORANT (SOURCE_ID)
/

create unique index TMP_DOCTORANT_UNIQ
  on TMP_DOCTORANT (ID, ETABLISSEMENT_ID)
/

create table TMP_INDIVIDU
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,
  TYPE VARCHAR2(32),
  CIV VARCHAR2(5),
  LIB_NOM_USU_IND VARCHAR2(60 char) not null,
  LIB_NOM_PAT_IND VARCHAR2(60 char) not null,
  LIB_PR1_IND VARCHAR2(60 char) not null,
  LIB_PR2_IND VARCHAR2(60 char),
  LIB_PR3_IND VARCHAR2(60 char),
  EMAIL VARCHAR2(255 char),
  DAT_NAI_PER DATE,
  LIB_NAT VARCHAR2(128 char)
)
/

create index TMP_INDIVIDU_SOURCE_CODE_IDX
  on TMP_INDIVIDU (SOURCE_CODE)
/

create index TMP_INDIVIDU_SOURCE_ID_IDX
  on TMP_INDIVIDU (SOURCE_ID)
/

create unique index TMP_INDIVIDU_UNIQ
  on TMP_INDIVIDU (ID, ETABLISSEMENT_ID)
/

create table IMPORT_NOTIF
(
  ID NUMBER not null
    constraint IMPORT_NOTIF_PK
    primary key,
  TABLE_NAME VARCHAR2(50 char) not null,
  COLUMN_NAME VARCHAR2(50 char) not null,
  OPERATION VARCHAR2(50 char) default 'UPDATE' not null,
  TO_VALUE VARCHAR2(1000 char),
  DESCRIPTION VARCHAR2(200 char),
  URL VARCHAR2(1000 char) not null,
  constraint IMPORT_NOTIF_UN
  unique (TABLE_NAME, COLUMN_NAME, OPERATION)
)
/

create table TYPE_STRUCTURE
(
  ID NUMBER not null
    constraint TYPE_STRUCTURE_PK
    primary key,
  CODE VARCHAR2(50 char) not null,
  LIBELLE VARCHAR2(100 char)
)
/

create table STRUCTURE
(
  ID NUMBER not null
    constraint STRUCTURE_PK
    primary key,
  SIGLE VARCHAR2(40 char),
  LIBELLE VARCHAR2(300 char) default NULL not null,
  CHEMIN_LOGO VARCHAR2(200 char),
  TYPE_STRUCTURE_ID NUMBER
    constraint STRUCTURE_TYPE_STRUCTURE_ID_FK
    references TYPE_STRUCTURE
    on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null
    constraint STRUCTURE_HCFK
    references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint STRUCTURE_HMFK
    references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint STRUCTURE_HDFK
    references UTILISATEUR,
  SOURCE_ID NUMBER not null
    constraint STRUCTURE_SOURCE_FK
    references SOURCE,
  SOURCE_CODE VARCHAR2(64 char)
)
/

create table ETABLISSEMENT
(
  ID NUMBER not null
    constraint ETAB_PK
    primary key,
  CODE VARCHAR2(32 char) not null
    constraint ETAB_CODE_UN
    unique,
  STRUCTURE_ID NUMBER not null
    constraint ETAB_STRUCT_FK
    references STRUCTURE,
  HISTO_CREATION DATE default sysdate not null,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTION DATE,
  HISTO_CREATEUR_ID NUMBER not null
    constraint ETAB_UTIL_CREATEUR_FK
    references UTILISATEUR,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint ETAB_UTIL_MODIFICATEUR_FK
    references UTILISATEUR,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint ETAB_UTIL_DESTRUCTEUR_FK
    references UTILISATEUR,
  DOMAINE VARCHAR2(50),
  SOURCE_ID NUMBER not null
    constraint ETABLISSEMENT_SOURCE_FK
    references SOURCE,
  SOURCE_CODE VARCHAR2(64 char) not null
)
/

comment on column ETABLISSEMENT.DOMAINE is 'Domaine DNS de l''établissement tel que présent dans l''EPPN Shibboleth, ex: unicaen.fr.'
/

create index ETABLISSEMENT_STRUCT_ID_IDX
  on ETABLISSEMENT (STRUCTURE_ID)
/

create unique index ETABLISSEMENT_DOMAINE_UINDEX
  on ETABLISSEMENT (DOMAINE)
/

create table DOCTORANT
(
  ID NUMBER not null
    constraint DOCTORANT_PK
    primary key,
  ETABLISSEMENT_ID NUMBER default NULL not null
    constraint DOCTORANT_ETAB_FK
    references ETABLISSEMENT
    on delete cascade,
  INDIVIDU_ID NUMBER default NULL not null
    constraint DOCTORANT_INDIV_FK
    references INDIVIDU
    on delete cascade,
  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null
    constraint DOCTORANT_SOURCE_FK
    references SOURCE
    on delete cascade,
  HISTO_CREATEUR_ID NUMBER not null
    constraint DOCTORANT_HC_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint DOCTORANT_HM_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint DOCTORANT_HD_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_DESTRUCTION DATE
)
/

comment on table DOCTORANT is 'Doctorant par établissement.'
/

create unique index DOCTORANT_SOURCE_CODE_UNIQ
  on DOCTORANT (SOURCE_CODE)
/

create index DOCTORANT_ETABLISSEMENT_IDX
  on DOCTORANT (ETABLISSEMENT_ID)
/

create index DOCTORANT_INDIVIDU_IDX
  on DOCTORANT (INDIVIDU_ID)
/

create index DOCTORANT_SRC_ID_INDEX
  on DOCTORANT (SOURCE_ID)
/

create index DOCTORANT_HCFK_IDX
  on DOCTORANT (HISTO_CREATEUR_ID)
/

create index DOCTORANT_HMFK_IDX
  on DOCTORANT (HISTO_MODIFICATEUR_ID)
/

create index DOCTORANT_HDFK_IDX
  on DOCTORANT (HISTO_DESTRUCTEUR_ID)
/

create table THESE
(
  ID NUMBER not null
    constraint THESE_PK
    primary key,
  ETABLISSEMENT_ID NUMBER default 2
    constraint THESE_ETAB_FK
    references ETABLISSEMENT
    on delete cascade,
  DOCTORANT_ID NUMBER default NULL not null
    constraint THESE_DOCTORANT_FK
    references DOCTORANT
    on delete cascade,
  ECOLE_DOCT_ID NUMBER,
  UNITE_RECH_ID NUMBER,
  BESOIN_EXPURGE NUMBER(1) default 0 not null,
  COD_UNIT_RECH VARCHAR2(50 char),
  CORREC_AUTORISEE VARCHAR2(30 char) default NULL,
  DATE_AUTORIS_SOUTENANCE DATE,
  DATE_FIN_CONFID DATE,
  DATE_PREM_INSC DATE,
  DATE_PREV_SOUTENANCE DATE,
  DATE_SOUTENANCE DATE,
  ETAT_THESE VARCHAR2(20 char),
  LIB_DISC VARCHAR2(200 char),
  LIB_ETAB_COTUT VARCHAR2(60 char),
  LIB_PAYS_COTUT VARCHAR2(40 char),
  LIB_UNIT_RECH VARCHAR2(200 char),
  RESULTAT NUMBER(1),
  SOUTENANCE_AUTORIS VARCHAR2(1 char),
  TEM_AVENANT_COTUT VARCHAR2(1 char),
  TITRE VARCHAR2(2048 char),
  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null
    constraint THESE_SOURCE_FK
    references SOURCE
    on delete cascade,
  HISTO_CREATEUR_ID NUMBER not null
    constraint THESE_HC_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint THESE_HM_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint THESE_HD_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_DESTRUCTION DATE
)
/

comment on table THESE is 'Thèses par établissement.'
/

create table METADONNEE_THESE
(
  ID NUMBER not null
    constraint METADONNEE_THESE_PK
    primary key,
  THESE_ID NUMBER not null
    constraint METADONNEE_THESE_THESE_ID_FK
    references THESE
    on delete cascade,
  TITRE VARCHAR2(2048 char) not null,
  LANGUE VARCHAR2(40 char) not null,
  RESUME CLOB default NULL not null,
  RESUME_ANGLAIS CLOB default NULL not null,
  MOTS_CLES_LIBRES_FR VARCHAR2(1024 char) not null,
  MOTS_CLES_RAMEAU VARCHAR2(1024 char),
  TITRE_AUTRE_LANGUE VARCHAR2(2048 char) not null,
  MOTS_CLES_LIBRES_ANG VARCHAR2(1024 char)
)
/

create unique index METADONNEE_THESE_UNIQ
  on METADONNEE_THESE (THESE_ID)
/

create unique index THESE_SOURCE_CODE_UNIQ
  on THESE (SOURCE_CODE)
/

create index THESE_TITRE_INDEX
  on THESE (TITRE)
/

create index THESE_ETAT_INDEX
  on THESE (ETAT_THESE)
/

create index THESE_SRC_ID_INDEX
  on THESE (SOURCE_ID)
/

create index THESE_HCFK_IDX
  on THESE (HISTO_CREATEUR_ID)
/

create index THESE_HMFK_IDX
  on THESE (HISTO_MODIFICATEUR_ID)
/

create index THESE_HDFK_IDX
  on THESE (HISTO_DESTRUCTEUR_ID)
/

create table VARIABLE
(
  ID NUMBER not null
    constraint VARIABLE_PK
    primary key,
  ETABLISSEMENT_ID NUMBER default NULL not null
    constraint VARIABLE_ETAB_FK
    references ETABLISSEMENT
    on delete cascade,
  DESCRIPTION VARCHAR2(300 char) not null,
  VALEUR VARCHAR2(200 char) not null,
  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null
    constraint VARIABLE_SOURCE_FK
    references SOURCE
    on delete cascade,
  HISTO_CREATEUR_ID NUMBER not null
    constraint VARIABLE_HC_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint VARIABLE_HM_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_DESTRUCTION DATE,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint VARIABLE_HD_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  DATE_DEB_VALIDITE DATE default sysdate not null,
  DATE_FIN_VALIDITE DATE default to_date('9999-12-31', 'YYYY-MM-DD') not null,
  CODE VARCHAR2(64)
)
/

comment on table VARIABLE is 'Variables d''environnement concernant un établissement, ex: nom de l''établissement, nom du président, etc.'
/

create unique index VARIABLE_SOURCE_CODE_UNIQ
  on VARIABLE (SOURCE_CODE)
/

create index VARIABLE_ETABLISSEMENT_IDX
  on VARIABLE (ETABLISSEMENT_ID)
/

create index VARIABLE_SOURCE_IDX
  on VARIABLE (SOURCE_ID)
/

create index VARIABLE_HC_IDX
  on VARIABLE (HISTO_CREATEUR_ID)
/

create index VARIABLE_HM_IDX
  on VARIABLE (HISTO_MODIFICATEUR_ID)
/

create index VARIABLE_HD_IDX
  on VARIABLE (HISTO_DESTRUCTEUR_ID)
/

create unique index VARIABLE_CODE_UNIQ
  on VARIABLE (CODE, ETABLISSEMENT_ID)
/

create table ATTESTATION
(
  ID NUMBER not null
    constraint ATTESTATION_PK
    primary key,
  THESE_ID NUMBER not null
    constraint ATTESTATION_THESE_FK
    references THESE
    on delete cascade,
  VER_DEPO_EST_VER_REF NUMBER(1) default 0 not null,
  EX_IMPR_CONFORM_VER_DEPO NUMBER(1) default 0 not null,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null
    constraint ATTESTATION_HC_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint ATTESTATION_HM_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint ATTESTATION_HD_FK
    references UTILISATEUR
    on delete cascade
)
/

create index ATTESTATION_THESE_IDX
  on ATTESTATION (THESE_ID)
/

create index ATTESTATION_HC_IDX
  on ATTESTATION (HISTO_CREATEUR_ID)
/

create index ATTESTATION_HM_IDX
  on ATTESTATION (HISTO_MODIFICATEUR_ID)
/

create index ATTESTATION_HD_IDX
  on ATTESTATION (HISTO_DESTRUCTEUR_ID)
/

create table FICHIER
(
  ID VARCHAR2(40 char) not null
    constraint FICHIER_PK
    primary key,
  NOM VARCHAR2(255 char) not null,
  TYPE_MIME VARCHAR2(128 char) not null,
  TAILLE NUMBER not null,
  DESCRIPTION VARCHAR2(256 char),
  THESE_ID NUMBER not null
    constraint FICHIER_THESE_FK
    references THESE
    on delete cascade,
  VERSION_FICHIER_ID NUMBER not null
    constraint FICHIER_VERSION_FK
    references VERSION_FICHIER
    on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null
    constraint FICHIER_HCFK
    references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint FICHIER_HMFK
    references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint FICHIER_HDFK
    references UTILISATEUR,
  EST_ANNEXE NUMBER(1) default 0 not null,
  NOM_ORIGINAL VARCHAR2(255 char) default NULL not null,
  EST_EXPURGE NUMBER(1) default 0 not null,
  EST_CONFORME NUMBER(1),
  RETRAITEMENT VARCHAR2(50 char),
  NATURE_ID NUMBER default 1 not null
    constraint FICHIER_NATURE_FICHIER_ID_FK
    references NATURE_FICHIER,
  EST_PARTIEL NUMBER(1) default 0 not null
)
/

create index FICHIER_THESE_FK_IDX
  on FICHIER (THESE_ID)
/

create index FICHIER_VERSION_FK_IDX
  on FICHIER (VERSION_FICHIER_ID)
/

create index FICHIER_HCFK_IDX
  on FICHIER (HISTO_CREATEUR_ID)
/

create index FICHIER_HMFK_IDX
  on FICHIER (HISTO_MODIFICATEUR_ID)
/

create index FICHIER_HDFK_IDX
  on FICHIER (HISTO_DESTRUCTEUR_ID)
/

create table DIFFUSION
(
  ID NUMBER not null
    constraint MISE_EN_LIGNE_PK
    primary key,
  THESE_ID NUMBER not null
    constraint MISE_EN_LIGNE_THESE_FK
    references THESE
    on delete cascade,
  DROIT_AUTEUR_OK NUMBER(1) default 0 not null,
  AUTORIS_MEL NUMBER(1) default 0 not null,
  AUTORIS_EMBARGO_DUREE VARCHAR2(20 char),
  AUTORIS_MOTIF VARCHAR2(2000 char),
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null
    constraint DIFFUSION_HC_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint DIFFUSION_HM_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint DIFFUSION_HD_FK
    references UTILISATEUR
    on delete cascade,
  CERTIF_CHARTE_DIFF NUMBER(1) default 0 not null,
  CONFIDENT NUMBER(1) default 0 not null,
  CONFIDENT_DATE_FIN DATE,
  ID_HAL VARCHAR2(200 char),
  NNT VARCHAR2(30)
)
/

comment on column DIFFUSION.DROIT_AUTEUR_OK is 'Je garantis que tous les documents de la version mise en ligne sont libres de droits ou que j''ai acquis les droits afférents pour la reproduction et la représentation sur tous supports'
/

comment on column DIFFUSION.AUTORIS_MEL is 'J''autorise la mise en ligne de la version de diffusion de la thèse sur Internet'
/

comment on column DIFFUSION.AUTORIS_EMBARGO_DUREE is 'Durée de l''embargo éventuel'
/

comment on column DIFFUSION.CERTIF_CHARTE_DIFF is 'En cochant cette case, je certifie avoir pris connaissance de la charte de diffusion des thèses en vigueur à la date de signature de la convention de mise en ligne'
/

comment on column DIFFUSION.CONFIDENT is 'La thèse est-elle confidentielle ?'
/

create index DIFFUSION_THESE_IDX
  on DIFFUSION (THESE_ID)
/

create index DIFFUSION_HC_IDX
  on DIFFUSION (HISTO_CREATEUR_ID)
/

create index DIFFUSION_HM_IDX
  on DIFFUSION (HISTO_MODIFICATEUR_ID)
/

create index DIFFUSION_HD_IDX
  on DIFFUSION (HISTO_DESTRUCTEUR_ID)
/

create table ECOLE_DOCT
(
  ID NUMBER not null
    constraint ECOLE_DOCT_PK
    primary key,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null
    constraint ECOLE_DOCT_HCFK
    references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint ECOLE_DOCT_HMFK
    references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint ECOLE_DOCT_HDFK
    references UTILISATEUR,
  SOURCE_ID NUMBER not null
    constraint ECOLE_DOCT_SOURCE_FK
    references SOURCE,
  SOURCE_CODE VARCHAR2(64 char),
  STRUCTURE_ID NUMBER not null
    constraint ECOLE_DOCT_STRUCT_FK
    references STRUCTURE
)
/

create index ECOLE_DOCT_HC_IDX
  on ECOLE_DOCT (HISTO_CREATEUR_ID)
/

create index ECOLE_DOCT_HM_IDX
  on ECOLE_DOCT (HISTO_MODIFICATEUR_ID)
/

create index ECOLE_DOCT_HD_IDX
  on ECOLE_DOCT (HISTO_DESTRUCTEUR_ID)
/

create index ECOLE_DOCT_SOURCE_IDX
  on ECOLE_DOCT (SOURCE_ID)
/

create unique index ECOLE_DOCT_SOURCE_CODE_UN
  on ECOLE_DOCT (SOURCE_CODE)
/

create index ECOLE_DOCT_STRUCT_ID_IDX
  on ECOLE_DOCT (STRUCTURE_ID)
/

create table DOCTORANT_COMPL
(
  ID NUMBER not null
    constraint THESARD_COMPL_PK
    primary key,
  DOCTORANT_ID NUMBER not null
    constraint DOCTORANT_COMPL_DOCTORANT_FK
    references DOCTORANT
    on delete cascade,
  PERSOPASS VARCHAR2(50 char),
  EMAIL_PRO VARCHAR2(100 char),
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null
    constraint THESARD_COMPL_HCFK
    references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint THESARD_COMPL_HMFK
    references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint THESARD_COMPL_HDFK
    references UTILISATEUR
)
/

create index DOCTORANT_COMPL_DOCTORANT_IDX
  on DOCTORANT_COMPL (DOCTORANT_ID)
/

create unique index DOCTORANT_COMPL_UN
  on DOCTORANT_COMPL (PERSOPASS, HISTO_DESTRUCTION)
/

create index DOCTORANT_COMPL_HC_IDX
  on DOCTORANT_COMPL (HISTO_CREATEUR_ID)
/

create index DOCTORANT_COMPL_HM_IDX
  on DOCTORANT_COMPL (HISTO_MODIFICATEUR_ID)
/

create index DOCTORANT_COMPL_HD_IDX
  on DOCTORANT_COMPL (HISTO_DESTRUCTEUR_ID)
/

create table UNITE_RECH
(
  ID NUMBER not null
    constraint UNITE_RECH_PK
    primary key,
  ETAB_SUPPORT VARCHAR2(500 char),
  AUTRES_ETAB VARCHAR2(500 char),
  SOURCE_ID NUMBER not null
    constraint UNITE_RECH_SOURCE_FK
    references SOURCE,
  SOURCE_CODE VARCHAR2(64 char),
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null
    constraint UNITE_RECH_COMPL_HCFK
    references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint UNITE_RECH_COMPL_HMFK
    references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint UNITE_RECH_COMPL_HDFK
    references UTILISATEUR,
  STRUCTURE_ID NUMBER not null
    constraint UNITE_RECH_STRUCT_FK
    references STRUCTURE
)
/

create index UNITE_RECH_SOURCE_IDX
  on UNITE_RECH (SOURCE_ID)
/

create index UNITE_RECH_HC_IDX
  on UNITE_RECH (HISTO_CREATEUR_ID)
/

create index UNITE_RECH_HM_IDX
  on UNITE_RECH (HISTO_MODIFICATEUR_ID)
/

create index UNITE_RECH_HD_IDX
  on UNITE_RECH (HISTO_DESTRUCTEUR_ID)
/

create unique index UNITE_RECH_SOURCE_CODE_UN
  on UNITE_RECH (SOURCE_CODE)
/

create index UNITE_RECH_STRUCT_ID_IDX
  on UNITE_RECH (STRUCTURE_ID)
/

create table VALIDATION
(
  ID NUMBER not null
    constraint VALIDATION_PK
    primary key,
  TYPE_VALIDATION_ID NUMBER not null
    constraint VALIDATION_TYPE_VALIDATION_FK
    references TYPE_VALIDATION
    on delete cascade,
  THESE_ID NUMBER not null
    constraint VALIDATION_THESE_FK
    references THESE
    on delete cascade,
  INDIVIDU_ID NUMBER default NULL
    constraint VALIDATION_INDIVIDU_ID_FK
    references INDIVIDU
    on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER default 1 not null
    constraint VALIDATION_HCFK
    references UTILISATEUR,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER default 1 not null
    constraint VALIDATION_HMFK
    references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint VALIDATION_HDFK
    references UTILISATEUR
)
/

create index VALIDATION_TYPE_IDX
  on VALIDATION (TYPE_VALIDATION_ID)
/

create index VALIDATION_THESE_IDX
  on VALIDATION (THESE_ID)
/

create index VALIDATION_INDIVIDU_IDX
  on VALIDATION (INDIVIDU_ID)
/

create unique index VALIDATION_UN
  on VALIDATION (TYPE_VALIDATION_ID, THESE_ID, HISTO_DESTRUCTION, INDIVIDU_ID)
/

create index VALIDATION_HCFK_IDX
  on VALIDATION (HISTO_CREATEUR_ID)
/

create index VALIDATION_HMFK_IDX
  on VALIDATION (HISTO_MODIFICATEUR_ID)
/

create index VALIDATION_HDFK_IDX
  on VALIDATION (HISTO_DESTRUCTEUR_ID)
/

create table VALIDITE_FICHIER
(
  ID NUMBER not null
    constraint CONFORMITE_FICHIER_PK
    primary key,
  FICHIER_ID VARCHAR2(38 char) not null
    constraint CONFORMITE_FICHIER_FFK
    references FICHIER
    on delete cascade,
  EST_VALIDE VARCHAR2(1 char) default NULL,
  MESSAGE CLOB default NULL,
  LOG CLOB,
  HISTO_CREATEUR_ID NUMBER not null
    constraint VALIDITE_FICHIER_HCFK
    references UTILISATEUR,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint VALIDITE_FICHIER_HMFK
    references UTILISATEUR,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint VALIDITE_FICHIER_HDFK
    references UTILISATEUR,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATION DATE default SYSDATE not null
)
/

create index VALIDITE_FICHIER_FICHIER_IDX
  on VALIDITE_FICHIER (FICHIER_ID)
/

create index VALIDITE_FICHIER_HCFK_IDX
  on VALIDITE_FICHIER (HISTO_CREATEUR_ID)
/

create index VALIDITE_FICHIER_HMFK_IDX
  on VALIDITE_FICHIER (HISTO_MODIFICATEUR_ID)
/

create index VALIDITE_FICHIER_HDFK_IDX
  on VALIDITE_FICHIER (HISTO_DESTRUCTEUR_ID)
/

create table RDV_BU
(
  ID NUMBER not null
    constraint RDV_BU_PK
    primary key,
  THESE_ID NUMBER not null
    constraint RDV_BU_FK
    references THESE
    on delete cascade,
  COORD_DOCTORANT VARCHAR2(2000 char),
  DISPO_DOCTORANT VARCHAR2(2000 char),
  MOTS_CLES_RAMEAU VARCHAR2(1024 char),
  CONVENTION_MEL_SIGNEE NUMBER(1) default 0 not null,
  EXEMPL_PAPIER_FOURNI NUMBER(1) default 0 not null,
  VERSION_ARCHIVABLE_FOURNIE NUMBER(1) default 0 not null,
  PAGE_TITRE_CONFORME NUMBER(1) default 0 not null,
  DIVERS CLOB,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_CREATEUR_ID NUMBER not null
    constraint RDV_BU_HC_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint RDV_BU_HM_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_DESTRUCTION DATE,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint RDV_BU_HD_FK
    references UTILISATEUR
    on delete cascade
)
/

comment on column RDV_BU.CONVENTION_MEL_SIGNEE is 'Convention de mise en ligne signée ?'
/

comment on column RDV_BU.EXEMPL_PAPIER_FOURNI is 'Exemplaire papier remis ?'
/

comment on column RDV_BU.VERSION_ARCHIVABLE_FOURNIE is 'Témoin indiquant si une version archivable de la thèse existe'
/

create index RDV_BU_THESE_IDX
  on RDV_BU (THESE_ID)
/

create index RDV_BU_HC_IDX
  on RDV_BU (HISTO_CREATEUR_ID)
/

create index RDV_BU_HM_IDX
  on RDV_BU (HISTO_MODIFICATEUR_ID)
/

create index RDV_BU_HD_IDX
  on RDV_BU (HISTO_DESTRUCTEUR_ID)
/

create table ACTEUR
(
  ID NUMBER not null
    constraint ACTEUR_PK
    primary key,
  INDIVIDU_ID NUMBER default NULL not null
    constraint ACTEUR_INDIV_FK
    references INDIVIDU
    on delete cascade,
  THESE_ID NUMBER default NULL not null
    constraint ACTEUR_THESE_FK
    references THESE
    on delete cascade,
  ROLE_ID NUMBER default NULL not null,
  ETABLISSEMENT VARCHAR2(200 char),
  QUALITE VARCHAR2(200 char),
  LIB_ROLE_COMPL VARCHAR2(200 char),
  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null
    constraint ACTEUR_SOURCE_FK
    references SOURCE
    on delete cascade,
  HISTO_CREATEUR_ID NUMBER not null
    constraint ACTEUR_HC_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint ACTEUR_HM_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint ACTEUR_HD_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_DESTRUCTION DATE
)
/

create table ROLE
(
  ID NUMBER not null
    constraint ROLE_PK
    primary key,
  CODE VARCHAR2(50 char) not null,
  LIBELLE VARCHAR2(200 char) not null,
  SOURCE_CODE VARCHAR2(64 char) not null,
  SOURCE_ID NUMBER not null
    constraint ROLE_SOURCE_FK
    references SOURCE
    on delete cascade,
  ROLE_ID VARCHAR2(64 char) not null,
  IS_DEFAULT NUMBER default 0,
  LDAP_FILTER VARCHAR2(255 char),
  ATTRIB_AUTO NUMBER(1) default 0 not null,
  THESE_DEP NUMBER(1) default 0 not null,
  HISTO_CREATEUR_ID NUMBER not null
    constraint ROLE_HC_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATEUR_ID NUMBER not null
    constraint ROLE_HM_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint ROLE_HD_FK
    references UTILISATEUR
    on delete cascade,
  HISTO_DESTRUCTION DATE,
  STRUCTURE_ID NUMBER
    constraint ROLE_STRUCTURE_ID_FK
    references STRUCTURE
    on delete cascade,
  TYPE_STRUCTURE_DEPENDANT_ID NUMBER
    constraint ROLE_TYPE_STRUCT_ID_FK
    references TYPE_STRUCTURE
    on delete cascade
)
/

create table INDIVIDU_ROLE
(
  ID NUMBER not null
    constraint INDIVIDU_ROLE_PK
    primary key,
  INDIVIDU_ID NUMBER
    constraint INDIVIDU_ROLE_IND_ID_FK
    references INDIVIDU
    on delete cascade,
  ROLE_ID NUMBER
    constraint INDIVIDU_ROLE_ROLE_ID_FK
    references ROLE
    on delete cascade
)
/

comment on table INDIVIDU_ROLE is 'Attributions à des individus de rôles sans lien avec une thèse en particulier, ex: bureau des doctorats.'
/

create index INDIVIDU_ROLE_INDIVIDU_IDX
  on INDIVIDU_ROLE (INDIVIDU_ID)
/

create index INDIVIDU_ROLE_ROLE_IDX
  on INDIVIDU_ROLE (ROLE_ID)
/

create unique index INDIVIDU_ROLE_UNIQUE
  on INDIVIDU_ROLE (INDIVIDU_ID, ROLE_ID)
/

create table ROLE_PRIVILEGE
(
  ROLE_ID NUMBER not null
    constraint ROLE_PRIVILEGE_ROLE_ID_FK
    references ROLE
    on delete cascade,
  PRIVILEGE_ID NUMBER not null
    constraint ROLE_PRIVILEGE_PRIV_ID_FK
    references PRIVILEGE
    on delete cascade,
  constraint ROLE_PRIVILEGE_PK
  primary key (ROLE_ID, PRIVILEGE_ID)
)
/

create index ROLE_PRIVILEGE_ROLE_IDX
  on ROLE_PRIVILEGE (ROLE_ID)
/

create index ROLE_PRIVILEGE_PRIVILEGE_IDX
  on ROLE_PRIVILEGE (PRIVILEGE_ID)
/

create index ROLE_STRUCTURE_ID_IDX
  on ROLE (STRUCTURE_ID)
/

create index ROLE_TYPE_STRUCTURE_ID_IDX
  on ROLE (TYPE_STRUCTURE_DEPENDANT_ID)
/

create unique index STRUCTURE_UNIQUE
  on STRUCTURE (TYPE_STRUCTURE_ID, SOURCE_CODE)
/

create index STRUCTURE_TYPE_STR_ID_IDX
  on STRUCTURE (TYPE_STRUCTURE_ID)
/

create unique index TYPE_STRUCTURE_UN
  on TYPE_STRUCTURE (CODE)
/

create table ROLE_PRIVILEGE_MODELE
(
  ROLE_CODE VARCHAR2(100) not null,
  PRIVILEGE_ID NUMBER not null
    constraint ROLE_PRIV_MOD_PRIV_ID_FK
    references PRIVILEGE
    on delete cascade,
  constraint ROLE_PRIV_MOD_PK
  primary key (ROLE_CODE, PRIVILEGE_ID)
)
/

create table RECAP_BU
(
  ID NUMBER not null
    primary key,
  RDV_BU_ID    NUMBER
    constraint RECAP_RDV_ID_FK
    references RDV_BU,
  DIFFUSION_ID NUMBER
    constraint RECAP_DIFFUSION_ID_FK
    references DIFFUSION,
  THESE_ID     NUMBER
    constraint RECAP_THESE_ID_FK
    references THESE
)
/
ALTER TABLE RECAP_BU ADD CONSTRAINT RECAP_RDV_ID_FK       FOREIGN KEY (RDV_BU_ID) REFERENCES RDV_BU (ID);
ALTER TABLE RECAP_BU ADD CONSTRAINT RECAP_DIFFUSION_ID_FK FOREIGN KEY (DIFFUSION_ID) REFERENCES DIFFUSION (ID);
ALTER TABLE RECAP_BU ADD CONSTRAINT RECAP_THESE_ID_FK     FOREIGN KEY (THESE_ID) REFERENCES THESE (ID);

create table ROLE_MODELE
(
  ID NUMBER not null
    primary key,
  LIBELLE VARCHAR2(100) not null,
  ROLE_ID VARCHAR2(100) not null,
  STRUCTURE_TYPE NUMBER default NULL not null
    constraint ROLEMODELE_STRUCTURETYPE_FK
    references TYPE_STRUCTURE
)
/

create table INDIVIDU_RECH
(
  ID NUMBER not null
    constraint INDIVIDU_RECH_PK
    primary key,
  HAYSTACK CLOB
)
/

create table MAIL_CONFIRMATION
(
  ID NUMBER not null
    primary key,
  INDIVIDU_ID NUMBER not null
    constraint MAILCONFIRMATION_INDIVIDUID_FK
    references INDIVIDU,
  EMAIL VARCHAR2(256) not null,
  ETAT VARCHAR2(1),
  CODE VARCHAR2(19)
)
/

create unique index MAIL_CONFIRMATION_CODE_UINDEX
  on MAIL_CONFIRMATION (CODE)
/

create table STRUCTURE_SUBSTIT
(
  ID NUMBER not null
    constraint STR_SUBSTIT_PK
    primary key,
  FROM_STRUCTURE_ID NUMBER not null
    constraint STR_SUBSTIT_STR_FROM_FK
    references STRUCTURE
    on delete cascade,
  TO_STRUCTURE_ID NUMBER not null
    constraint STR_SUBSTIT_STR_TO_FK
    references STRUCTURE
    on delete cascade,
  HISTO_CREATION DATE default SYSDATE not null,
  HISTO_MODIFICATION DATE default SYSDATE not null,
  HISTO_DESTRUCTION DATE,
  HISTO_CREATEUR_ID NUMBER
    constraint STR_SUBSTIT_CREATEUR_FK
    references UTILISATEUR,
  HISTO_MODIFICATEUR_ID NUMBER
    constraint STR_SUBSTIT_MODIFICATEUR_FK
    references UTILISATEUR,
  HISTO_DESTRUCTEUR_ID NUMBER
    constraint STR_SUBSTIT_DESTRUCTEUR_FK
    references UTILISATEUR
)
/

create index STR_SUBSTIT_STR_FROM_IDX
  on STRUCTURE_SUBSTIT (FROM_STRUCTURE_ID)
/

create index STR_SUBSTIT_STR_TO_IDX
  on STRUCTURE_SUBSTIT (TO_STRUCTURE_ID)
/

create unique index STR_SUBSTIT_UNIQUE
  on STRUCTURE_SUBSTIT (FROM_STRUCTURE_ID, TO_STRUCTURE_ID)
/

CREATE TABLE ETABLISSEMENT_RATTACH
(
  ID number PRIMARY KEY NOT NULL,
  UNITE_ID number NOT NULL,
  ETABLISSEMENT_ID number NOT NULL,
  PRINCIPAL int,
  CONSTRAINT RATTACHEMENT_UNITE_ID FOREIGN KEY (UNITE_ID) REFERENCES UNITE_RECH (ID) ON DELETE CASCADE,
  CONSTRAINT RATTACHEMENT_ETAB_ID FOREIGN KEY (ETABLISSEMENT_ID) REFERENCES ETABLISSEMENT (ID) ON DELETE CASCADE
);
CREATE UNIQUE INDEX ETABLISSEMENT_RATTACH_ID_uindex ON ETABLISSEMENT_RATTACH (ID);
CREATE SEQUENCE ETABLISSEMENT_RATTACH_ID_SEQ;

create table TMP_STRUCTURE
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,
  TYPE_STRUCTURE_ID VARCHAR2(64) not null,
  SIGLE VARCHAR2(64),
  LIBELLE VARCHAR2(200) not null,
  CODE_PAYS VARCHAR2(64),
  LIBELLE_PAYS VARCHAR2(200)
)
/

create index TMP_STRUCTURE_SOURCE_CODE_IDX
  on TMP_STRUCTURE (SOURCE_CODE)
/

create index TMP_STRUCTURE_SOURCE_ID_IDX
  on TMP_STRUCTURE (SOURCE_ID)
/

create index TMP_STRUCTURE_TYPE_ID_IDX
  on TMP_STRUCTURE (TYPE_STRUCTURE_ID)
/

create table TMP_ECOLE_DOCT
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64) not null,
  STRUCTURE_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null
)
/

create index TMP_ECOLE_DOCT_SOURCE_CODE_IDX
  on TMP_ECOLE_DOCT (SOURCE_CODE)
/

create index TMP_ECOLE_DOCT_SOURCE_ID_IDX
  on TMP_ECOLE_DOCT (SOURCE_ID)
/

create index TMP_ECOLE_DOCT_STRUCT_ID_IDX
  on TMP_ECOLE_DOCT (STRUCTURE_ID)
/

create unique index TMP_ECOLE_DOCT_UNIQ
  on TMP_ECOLE_DOCT (ID, STRUCTURE_ID)
/

create table TMP_UNITE_RECH
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64) not null,
  STRUCTURE_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null
)
/

create index TMP_UNITE_RECH_SOURCE_CODE_IDX
  on TMP_UNITE_RECH (SOURCE_CODE)
/

create index TMP_UNITE_RECH_SOURCE_ID_IDX
  on TMP_UNITE_RECH (SOURCE_ID)
/

create index TMP_UNITE_RECH_STRUCT_ID_IDX
  on TMP_UNITE_RECH (STRUCTURE_ID)
/

create unique index TMP_UNITE_RECH_UNIQ
  on TMP_UNITE_RECH (ID, STRUCTURE_ID)
/

create table TMP_ETABLISSEMENT
(
  INSERT_DATE DATE default sysdate,
  ID VARCHAR2(64),
  ETABLISSEMENT_ID VARCHAR2(64) not null,
  STRUCTURE_ID VARCHAR2(64 char) not null,
  SOURCE_ID VARCHAR2(64 char) not null,
  SOURCE_CODE VARCHAR2(64) not null,
  CODE VARCHAR2(64) not null
)
/

create index TMP_ETAB_SOURCE_CODE_IDX
  on TMP_ETABLISSEMENT (SOURCE_CODE)
/

create index TMP_ETAB_SOURCE_ID_IDX
  on TMP_ETABLISSEMENT (SOURCE_ID)
/

create index TMP_ETAB_STRUCT_ID_IDX
  on TMP_ETABLISSEMENT (STRUCTURE_ID)
/

create unique index TMP_ETAB_UNIQ
  on TMP_ETABLISSEMENT (ID, STRUCTURE_ID)
/

create view SRC_INDIVIDU as
  SELECT
    NULL                                     AS id,
    tmp.SOURCE_CODE,
    src.id                                   AS SOURCE_ID,
    TYPE,
    civ                                      AS CIVILITE,
    lib_nom_usu_ind                          AS NOM_USUEL,
    lib_nom_pat_ind                          AS NOM_PATRONYMIQUE,
    lib_pr1_ind                              AS PRENOM1,
    lib_pr2_ind                              AS PRENOM2,
    lib_pr3_ind                              AS PRENOM3,
    EMAIL,
    dat_nai_per                              AS DATE_NAISSANCE,
    lib_nat                                  AS NATIONALITE
  FROM TMP_INDIVIDU tmp
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create view SRC_DOCTORANT as
  SELECT
    NULL                                     AS id,
    tmp.SOURCE_CODE,
    src.id                                   AS source_id,
    i.id                                     AS individu_id,
    e.id                                     AS etablissement_id
  FROM TMP_DOCTORANT tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
    JOIN INDIVIDU i ON i.SOURCE_CODE = tmp.INDIVIDU_ID
/

create view SRC_THESE as
  SELECT
    NULL                            AS id,
    tmp.SOURCE_CODE                 AS SOURCE_CODE,
    src.ID                          AS source_id,
    e.id                            AS etablissement_id,
    d.id                            AS doctorant_id,
    coalesce(ed_substit.id, ed.id)  AS ecole_doct_id,
    coalesce(ur_substit.id, ur.id)  AS unite_rech_id,
    --     ed.id  AS ecole_doct_id,
    --     ur.id  AS unite_rech_id,
    ed.id                           AS ecole_doct_id_orig,
    ur.id                           AS unite_rech_id_orig,
    tmp.lib_ths                     AS titre,
    tmp.eta_ths                     AS etat_these,
    to_number(tmp.cod_neg_tre)      AS resultat,
    tmp.lib_int1_dis                AS lib_disc,
    tmp.dat_deb_ths                 AS date_prem_insc,
    tmp.dat_prev_sou                AS date_prev_soutenance,
    tmp.dat_sou_ths                 AS date_soutenance,
    tmp.dat_fin_cfd_ths             AS date_fin_confid,
    tmp.lib_etab_cotut              AS lib_etab_cotut,
    tmp.lib_pays_cotut              AS lib_pays_cotut,
    tmp.correction_possible         AS CORREC_AUTORISEE,
    tem_sou_aut_ths                 AS soutenance_autoris,
    dat_aut_sou_ths                 AS date_autoris_soutenance,
    tem_avenant_cotut               AS tem_avenant_cotut
  FROM TMP_THESE tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
    JOIN DOCTORANT d ON d.SOURCE_CODE = tmp.DOCTORANT_ID

    LEFT JOIN ECOLE_DOCT ed ON ed.SOURCE_CODE = tmp.ECOLE_DOCT_ID
    LEFT JOIN UNITE_RECH ur ON ur.SOURCE_CODE = tmp.UNITE_RECH_ID

    LEFT JOIN STRUCTURE_SUBSTIT ss_ed on ss_ed.FROM_STRUCTURE_ID = ed.STRUCTURE_ID
    LEFT JOIN ECOLE_DOCT ed_substit on ed_substit.STRUCTURE_ID = ss_ed.TO_STRUCTURE_ID

    LEFT JOIN STRUCTURE_SUBSTIT ss_ur on ss_ur.FROM_STRUCTURE_ID = ur.STRUCTURE_ID
    LEFT JOIN UNITE_RECH ur_substit on ur_substit.STRUCTURE_ID = ss_ur.TO_STRUCTURE_ID
/

create view SRC_ROLE as
  SELECT
    NULL                       AS id,
    tmp.SOURCE_CODE            AS SOURCE_CODE,
    src.ID                     AS source_id,
    e.id                       AS etablissement_id,
    tmp.LIB_ROJ                AS libelle,
    to_char(tmp.id)            AS code,
    tmp.LIB_ROJ||' '||e.CODE   AS role_id,
    1                          AS these_dep,
    s.ID                       AS STRUCTURE_ID,
    NULL                       AS TYPE_STRUCTURE_DEPENDANT_ID
  FROM TMP_ROLE tmp
    JOIN ETABLISSEMENT e ON e.code = tmp.ETABLISSEMENT_ID
    JOIN STRUCTURE s ON s.id = e.STRUCTURE_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create view SRC_ACTEUR as
  SELECT
    NULL                                     AS id,
    tmp.SOURCE_CODE,
    src.ID                                   AS SOURCE_ID,
    i.id                                     AS INDIVIDU_ID,
    t.id                                     AS THESE_ID,
    r.id                                     AS ROLE_ID,
    tmp.LIB_CPS                              AS QUALITE,
    tmp.LIB_ETB                              AS ETABLISSEMENT,
    tmp.LIB_ROJ_COMPL                        AS LIB_ROLE_COMPL
  FROM TMP_ACTEUR tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
    JOIN INDIVIDU i ON i.SOURCE_CODE = tmp.INDIVIDU_ID
    JOIN THESE t ON t.SOURCE_CODE = tmp.THESE_ID
    JOIN ROLE r ON r.SOURCE_CODE = tmp.ROLE_ID
/

create view SRC_VARIABLE as
  SELECT
    NULL                                     AS id,
    tmp.SOURCE_CODE,
    src.ID                                   AS SOURCE_ID,
    e.id                                     AS ETABLISSEMENT_ID,
    tmp.COD_VAP                              AS CODE,
    tmp.lib_vap                              AS DESCRIPTION,
    tmp.par_vap                              AS VALEUR,
    tmp.DATE_DEB_VALIDITE,
    tmp.DATE_FIN_VALIDITE
  FROM TMP_VARIABLE tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create view V_SITU_ARCHIVAB_VO as
  SELECT
    t.id AS these_id,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VO'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_ARCHIVAB_VOC as
  SELECT
    t.id AS these_id,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_ARCHIVAB_VA as
  SELECT
    t.id AS these_id,
    f.RETRAITEMENT,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_ARCHIVAB_VAC as
  SELECT
    t.id AS these_id,
    f.RETRAITEMENT,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_RDV_BU_VALIDATION_BU as
  SELECT
    t.id AS these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id and v.HISTO_DESTRUCTEUR_ID is null
    JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'RDV_BU'
/

create view V_SITU_AUTORIS_DIFF_THESE as
  SELECT
    t.id AS these_id,
    d.id AS diffusion_id
  FROM these t
    JOIN DIFFUSION d ON d.THESE_ID = t.id and d.HISTO_DESTRUCTEUR_ID is null
/

create view V_SITU_SIGNALEMENT_THESE as
  SELECT
    t.id AS these_id,
    d.id AS description_id
  FROM these t
    JOIN METADONNEE_THESE d ON d.THESE_ID = t.id
/

create view V_SITU_RDV_BU_SAISIE_DOCT as
  SELECT
    t.id AS these_id,
    CASE WHEN r.COORD_DOCTORANT IS NOT NULL AND r.DISPO_DOCTORANT IS NOT NULL
      THEN 1 ELSE 0 END ok
  FROM these t
    JOIN RDV_BU r ON r.THESE_ID = t.id
/

create view V_SITU_RDV_BU_SAISIE_BU as
  SELECT
    t.id AS these_id,
    CASE WHEN r.VERSION_ARCHIVABLE_FOURNIE = 1 and r.CONVENTION_MEL_SIGNEE = 1 and r.EXEMPL_PAPIER_FOURNI = 1
              and r.PAGE_TITRE_CONFORME = 1 and r.MOTS_CLES_RAMEAU is not null
      THEN 1 ELSE 0 END ok
  FROM these t
    JOIN RDV_BU r ON r.THESE_ID = t.id
/

create view V_SITU_ATTESTATIONS as
  SELECT
    t.id AS these_id,
    a.id AS attestation_id
  FROM these t
    JOIN ATTESTATION a ON a.THESE_ID = t.id and a.HISTO_DESTRUCTEUR_ID is null
/

create view V_SITU_DEPOT_VO as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND RETRAITEMENT IS NULL AND
                      f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER vf ON f.VERSION_FICHIER_ID = vf.ID AND vf.CODE = 'VO'
/

create view V_SITU_DEPOT_VOC as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND RETRAITEMENT IS NULL AND
                      f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER vf ON f.VERSION_FICHIER_ID = vf.ID AND vf.CODE = 'VOC'
/

create view V_SITU_DEPOT_VA as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
/

create view V_SITU_DEPOT_VAC as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
/

create view V_SITU_VERIF_VA as
  SELECT
    t.id AS these_id,
    f.EST_CONFORME
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
/

create view V_SITU_VERIF_VAC as
  SELECT
    t.id AS these_id,
    f.EST_CONFORME
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
/

create view V_SITU_DEPOT_VC_VALID_DOCT as
  SELECT
    t.id AS these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id and v.HISTO_DESTRUCTEUR_ID is null
    JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'DEPOT_THESE_CORRIGEE'
/

create view V_SITU_DEPOT_VC_VALID_DIR as
  WITH validations_attendues AS (
      SELECT a.THESE_ID, a.INDIVIDU_ID, tv.ID as TYPE_VALIDATION_ID
      FROM ACTEUR a
        JOIN ROLE r on a.ROLE_ID = r.ID and r.CODE = 'D' -- directeur de thèse
        JOIN TYPE_VALIDATION tv on tv.code = 'CORRECTION_THESE'
      where a.HISTO_DESTRUCTION is null
  )
  SELECT
    ROWNUM as id,
    t.id AS these_id,
    va.INDIVIDU_ID,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM validations_attendues va
    JOIN these t on va.THESE_ID = t.id
    LEFT JOIN VALIDATION v ON v.THESE_ID = t.id and
                              v.INDIVIDU_ID = va.INDIVIDU_ID and -- suppose que l'INDIVIDU_ID soit enregistré lors de la validation
                              v.HISTO_DESTRUCTEUR_ID is null and
                              v.TYPE_VALIDATION_ID = va.TYPE_VALIDATION_ID
/

create view V_SITU_DEPOT_PV_SOUT as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id and f.HISTO_DESTRUCTION is null and f.HISTO_DESTRUCTION is null
    JOIN NATURE_FICHIER nf on f.NATURE_ID = nf.id and nf.CODE = 'PV_SOUTENANCE'
/

create view V_SITU_DEPOT_RAPPORT_SOUT as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id and f.HISTO_DESTRUCTION is null and f.HISTO_DESTRUCTION is null
    JOIN NATURE_FICHIER nf on f.NATURE_ID = nf.id and nf.CODE = 'RAPPORT_SOUTENANCE'
/

create view V_SITU_ATTESTATIONS_VOC as
  SELECT
    t.id AS these_id,
    a.id AS attestation_id
  FROM these t
    JOIN ATTESTATION a ON a.THESE_ID = t.id and a.HISTO_DESTRUCTEUR_ID is null
    -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
/

create view V_SITU_AUTORIS_DIFF_THESE_VOC as
  SELECT
    t.id AS these_id,
    d.id AS diffusion_id
  FROM these t
    JOIN DIFFUSION d ON d.THESE_ID = t.id and d.HISTO_DESTRUCTEUR_ID is null
    -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
/

create view V_SITU_VERSION_PAPIER_CORRIGEE as
  SELECT
    t.id AS these_id,
    v.id as validation_id
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id
    JOIN TYPE_VALIDATION tv ON tv.ID = v.TYPE_VALIDATION_ID
  WHERE tv.CODE='VERSION_PAPIER_CORRIGEE'
/

create view V_WF_ETAPE_PERTIN as
  SELECT
    to_number(these_id) these_id,
    to_number(etape_id) etape_id,
    code,
    ORDRE,
    ROWNUM id
  FROM (
    --
    -- DEPOT_VERSION_ORIGINALE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE'

    UNION ALL

    --
    -- ATTESTATIONS : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS'

    UNION ALL

    --
    -- AUTORISATION_DIFFUSION_THESE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE'

    UNION ALL

    --
    -- SIGNALEMENT_THESE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'SIGNALEMENT_THESE'

    UNION ALL

    --
    -- ARCHIVABILITE_VERSION_ORIGINALE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE'

    UNION ALL

    --
    -- DEPOT_VERSION_ARCHIVAGE : étape pertinente si version originale non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE'
      JOIN V_SITU_ARCHIVAB_VO situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0

    UNION ALL

    --
    -- ARCHIVABILITE_VERSION_ARCHIVAGE : étape pertinente si version originale non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE'
      JOIN V_SITU_ARCHIVAB_VO situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0

    UNION ALL

    --
    -- VERIFICATION_VERSION_ARCHIVAGE : étape pertinente si version d'archivage archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE'
      JOIN V_SITU_ARCHIVAB_VA situ ON situ.these_id = t.id AND situ.EST_VALIDE = 1

    UNION ALL

    --
    -- RDV_BU_SAISIE_DOCTORANT : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_DOCTORANT'

    UNION ALL

    --
    -- RDV_BU_SAISIE_BU : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_BU'

    UNION ALL

    --
    -- RDV_BU_VALIDATION_BU : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_VALIDATION_BU'

    UNION ALL





    --
    -- DEPOT_VERSION_ORIGINALE_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- ATTESTATIONS_VERSION_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS_VERSION_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- DEPOT_VERSION_ARCHIVAGE_CORRIGEE : étape pertinente si version originale corrigée non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'
      JOIN V_SITU_ARCHIVAB_VOC situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE : étape pertinente si version originale corrigée non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'
      JOIN V_SITU_ARCHIVAB_VOC situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE : étape pertinente si version d'archivage corrigée archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'
      JOIN V_SITU_ARCHIVAB_VAC situ ON situ.these_id = t.id AND situ.EST_VALIDE = 1
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

  )
/

create view V_WORKFLOW as
  SELECT
    ROWNUM id,
    t."THESE_ID",t."ETAPE_ID",t."CODE",t."ORDRE",t."FRANCHIE",t."RESULTAT",t."OBJECTIF"
  FROM (
         --
         -- DEPOT_VERSION_ORIGINALE : franchie si version originale déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE'
           LEFT JOIN V_SITU_DEPOT_VO v ON v.these_id = t.id

         UNION ALL

         --
         -- ATTESTATIONS : franchie si données saisies
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.attestation_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.attestation_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS'
           LEFT JOIN V_SITU_ATTESTATIONS v ON v.these_id = t.id

         UNION ALL

         --
         -- AUTORISATION_DIFFUSION_THESE : franchie si données saisies
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.diffusion_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.diffusion_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE'
           LEFT JOIN V_SITU_AUTORIS_DIFF_THESE v ON v.these_id = t.id

         UNION ALL

         --
         -- SIGNALEMENT_THESE : franchie si données saisies
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.description_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.description_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'SIGNALEMENT_THESE'
           LEFT JOIN V_SITU_SIGNALEMENT_THESE v ON v.these_id = t.id

         UNION ALL

         --
         -- ARCHIVABILITE_VERSION_ORIGINALE : franchie si l'archivabilité de la version originale a été testée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.THESE_ID IS NULL THEN 0 ELSE 1 END franchie,
           --            CASE WHEN v.THESE_ID IS NULL THEN
           --              0 -- test d'archivabilité inexistant
           --            ELSE
           --              CASE WHEN v.EST_VALIDE IS NULL THEN
           --                1 -- test d'archivabilité existant mais résultat indéterminé (plantage)
           --              ELSE
           --                CASE WHEN v.EST_VALIDE = 1 THEN
           --                  1 -- test d'archivabilité réussi
           --                ELSE
           --                  0 -- test d'archivabilité échoué
           --                END
           --              END
           --            END franchie,
           CASE WHEN v.EST_VALIDE IS NULL OR v.EST_VALIDE = 0 THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE'
           LEFT JOIN V_SITU_ARCHIVAB_VO v ON v.these_id = t.id

         UNION ALL

         --
         -- DEPOT_VERSION_ARCHIVAGE : franchie si version d'archivage déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE'
           LEFT JOIN V_SITU_DEPOT_VA v ON v.these_id = t.id
           LEFT JOIN fichier f ON f.id = v.fichier_id

         UNION ALL

         --
         -- ARCHIVABILITE_VERSION_ARCHIVAGE : franchie si l'archivabilité de la version d'archivage a été testée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_VALIDE IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.EST_VALIDE IS NULL OR v.EST_VALIDE = 0
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE'
           LEFT JOIN V_SITU_ARCHIVAB_VA v ON v.these_id = t.id

         UNION ALL

         --
         -- VERIFICATION_VERSION_ARCHIVAGE : franchie si vérification de la version originale effectuée (peu importe la réponse)
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_CONFORME IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.EST_CONFORME IS NULL OR v.EST_CONFORME = 0
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE'
           LEFT JOIN V_SITU_VERIF_VA v ON v.these_id = t.id

         UNION ALL

         --
         -- RDV_BU_SAISIE_DOCTORANT : franchie si données doctorant saisies
         --
         SELECT
           t.id AS                      these_id,
           e.id AS                      etape_id,
           e.code,
           e.ORDRE,
           coalesce(v.ok, 0)            franchie,
           (CASE WHEN rdv.COORD_DOCTORANT IS NULL
             THEN 0
            ELSE 1 END +
            CASE WHEN rdv.DISPO_DOCTORANT IS NULL
              THEN 0
            ELSE 1 END)                 resultat,
           2                            objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_DOCTORANT'
           LEFT JOIN V_SITU_RDV_BU_SAISIE_DOCT v ON v.these_id = t.id
           LEFT JOIN RDV_BU rdv ON rdv.THESE_ID = t.id

         UNION ALL

         --          --
         --          -- RDV_BU_SAISIE_BU : franchie si données BU saisies
         --          --
         --          SELECT
         --            t.id AS                                                                          these_id,
         --            e.id AS                                                                          etape_id,
         --            e.code,
         --            e.ORDRE,
         --            coalesce(v.ok, 0)                                                                franchie,
         --            CASE WHEN rdv.MOTS_CLES_RAMEAU IS NULL THEN 0 ELSE 1 END +
         --            coalesce(rdv.VERSION_ARCHIVABLE_FOURNIE, 0) +
         --            coalesce(rdv.EXEMPL_PAPIER_FOURNI, 0) +
         --            coalesce(rdv.CONVENTION_MEL_SIGNEE, 0)                                           resultat,
         --            4                                                                                objectif
         --          FROM these t
         --            JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_BU'
         --            LEFT JOIN V_SITU_RDV_BU_SAISIE_BU v ON v.these_id = t.id
         --            LEFT JOIN RDV_BU rdv ON rdv.THESE_ID = t.id
         --
         --       UNION ALL

         --
         -- RDV_BU_VALIDATION_BU : franchie si données BU saisies ET une validation BU existe
         --
         SELECT
           t.id AS               these_id,
           e.id AS               etape_id,
           e.code,
           e.ORDRE,
           coalesce(vs.ok, 0) * coalesce(v.valide, 0) franchie,
           coalesce(vs.ok, 0) + coalesce(v.valide, 0) resultat,
           2 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'RDV_BU_VALIDATION_BU'
           LEFT JOIN V_SITU_RDV_BU_SAISIE_BU vs ON vs.these_id = t.id
           LEFT JOIN V_SITU_RDV_BU_VALIDATION_BU v ON v.these_id = t.id

         UNION ALL




         --
         -- DEPOT_VERSION_ORIGINALE_CORRIGEE : franchie si version originale corrigée déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'
           LEFT JOIN V_SITU_DEPOT_VOC v ON v.these_id = t.id

         UNION ALL

         --
         -- ATTESTATIONS_VERSION_CORRIGEE : franchie si données saisies
         --
         SELECT
           t.id AS these_id,
           e.id AS etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.attestation_id IS NULL THEN 0 ELSE 1 END franchie,
           CASE WHEN v.attestation_id IS NULL THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS_VERSION_CORRIGEE'
           LEFT JOIN V_SITU_ATTESTATIONS_VOC v ON v.these_id = t.id

         UNION ALL

         --
         -- AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE : franchie si données saisies
         --
         SELECT
           t.id AS these_id,
           e.id AS etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.diffusion_id IS NULL THEN 0 ELSE 1 END franchie,
           CASE WHEN v.diffusion_id IS NULL THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE'
           LEFT JOIN V_SITU_AUTORIS_DIFF_THESE_VOC v ON v.these_id = t.id

         UNION ALL

         --
         -- ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE : franchie si l'archivabilité de la version originale corrigée a été testée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.THESE_ID IS NULL THEN 0 ELSE 1 END franchie,
           CASE WHEN v.EST_VALIDE IS NULL OR v.EST_VALIDE = 0 THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'
           LEFT JOIN V_SITU_ARCHIVAB_VOC v ON v.these_id = t.id

         UNION ALL

         --
         -- DEPOT_VERSION_ARCHIVAGE_CORRIGEE : franchie si version d'archivage corrigée déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'
           LEFT JOIN V_SITU_DEPOT_VAC v ON v.these_id = t.id
           LEFT JOIN fichier f ON f.id = v.fichier_id

         UNION ALL

         --
         -- ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE : franchie si la version d'archivage corrigée est archivable
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_VALIDE = 1 THEN 1 ELSE 0 END franchie,
           CASE WHEN v.EST_VALIDE = 1 THEN 1 ELSE 0 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'
           LEFT JOIN V_SITU_ARCHIVAB_VAC v ON v.these_id = t.id

         UNION ALL

         --
         -- VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE : franchie si la version corrigée est certifiée conforme
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_CONFORME = 1 THEN 1 ELSE 0 END franchie,
           CASE WHEN v.EST_CONFORME = 1 THEN 1 ELSE 0 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'
           LEFT JOIN V_SITU_VERIF_VAC v ON v.these_id = t.id

         UNION ALL

         --
         -- DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT : franchie si la validation attendue existe
         --
         SELECT
           t.id AS               these_id,
           e.id AS               etape_id,
           e.code,
           e.ORDRE,
           coalesce(v.valide, 0) franchie,
           coalesce(v.valide, 0) resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'
           LEFT JOIN V_SITU_DEPOT_VC_VALID_DOCT v ON v.these_id = t.id

         UNION ALL

         --
         -- DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR : franchie si toutes les validations attendues existent
         --
         select * from (
           WITH tmp AS (
               SELECT
                 these_id,
                 sum(valide)   AS resultat,
                 count(valide) AS objectif
               FROM V_SITU_DEPOT_VC_VALID_DIR
               GROUP BY these_id
           )
           SELECT
             t.id AS                 these_id,
             e.id AS                 etape_id,
             e.code,
             e.ORDRE,
             coalesce(v.resultat, 0) franchie,
             coalesce(v.resultat, 0) resultat,
             v.objectif
           FROM these t
             JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'
             LEFT JOIN tmp v ON v.these_id = t.id
         )

         UNION ALL
         --
         -- REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE  : franchie pas pour le moment
         --

         select * from (
           WITH tmp_last AS (
               SELECT
                 THESE_ID as these_id,
                 count(THESE_ID) AS resultat
               FROM V_SITU_VERSION_PAPIER_CORRIGEE
               GROUP BY THESE_ID
           )
           SELECT
             t.id AS                 these_id,
             e.id AS                 etape_id,
             e.code,
             e.ORDRE,
             coalesce(tl.resultat, 0) franchie,
             0,
             1
           FROM these t
             JOIN WF_ETAPE e ON e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'
             LEFT JOIN tmp_last tl ON tl.these_id = t.id
         )
         --          e.code,
         --          e.ORDRE,
         --          0 franchie,
         --          0 resultat,
         --          1 objectif
         --        FROM V_SITU_VERSION_PAPIER_CORRIGEE
         --          JOIN WF_ETAPE e ON e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'



         -- LEFT JOIN V_SITU_DEPOT_VO v ON v.these_id = t.id



       ) t
    JOIN V_WF_ETAPE_PERTIN v ON t.these_id = v.these_id AND t.etape_id = v.etape_id
/

create view V_IMPORT_TAB_COLS as
  WITH importable_tables (table_name )AS (
    SELECT
      t.table_name
    FROM
      user_tab_cols c
      join user_tables t on t.table_name = c.table_name
    WHERE
      c.column_name = 'SOURCE_CODE'

    MINUS

    SELECT
      mview_name table_name
    FROM
      USER_MVIEWS
  ), c_values (table_name, column_name, c_table_name, c_column_name) AS (
      SELECT
        tc.table_name,
        tc.column_name,
        pcc.table_name c_table_name,
        pcc.column_name c_column_name
      FROM
        user_tab_cols tc
        JOIN USER_CONS_COLUMNS cc ON cc.table_name = tc.table_name AND cc.column_name = tc.column_name
        JOIN USER_CONSTRAINTS c ON c.constraint_name = cc.constraint_name
        JOIN USER_CONSTRAINTS pc ON pc.constraint_name = c.r_constraint_name
        JOIN USER_CONS_COLUMNS pcc ON pcc.constraint_name = pc.constraint_name
      WHERE
        c.constraint_type = 'R' AND pc.constraint_type = 'P'
  )
  SELECT
    tc.table_name,
    tc.column_name,
    tc.data_type,
    CASE WHEN tc.char_length = 0 THEN NULL ELSE tc.char_length END length,
    CASE WHEN tc.nullable = 'Y' THEN 1 ELSE 0 END nullable,
    CASE WHEN tc.data_default IS NOT NULL THEN 1 ELSE 0 END has_default,
    cv.c_table_name,
    cv.c_column_name,
    CASE WHEN stc.table_name IS NULL THEN 0 ELSE 1 END AS import_actif
  FROM
    user_tab_cols tc
    JOIN importable_tables t ON t.table_name = tc.table_name
    LEFT JOIN c_values cv ON cv.table_name = tc.table_name AND cv.column_name = tc.column_name
    LEFT JOIN user_tab_cols stc ON stc.table_name = 'SRC_' || tc.table_name AND stc.column_name = tc.column_name
  WHERE
    tc.column_name not like 'HISTO_%'
  ORDER BY
    tc.table_name, tc.column_id
/

create view V_DIFF_VARIABLE as
  select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."CODE",diff."DATE_DEB_VALIDITE",diff."DATE_FIN_VALIDITE",diff."DESCRIPTION",diff."ETABLISSEMENT_ID",diff."VALEUR",diff."U_CODE",diff."U_DATE_DEB_VALIDITE",diff."U_DATE_FIN_VALIDITE",diff."U_DESCRIPTION",diff."U_ETABLISSEMENT_ID",diff."U_VALEUR" from (SELECT
                                                                                                                                                                                                                                                                                                                                              COALESCE( D.id, S.id ) id,
                                                                                                                                                                                                                                                                                                                                              COALESCE( S.source_id, D.source_id ) source_id,
                                                                                                                                                                                                                                                                                                                                              COALESCE( S.source_code, D.source_code ) source_code,
                                                                                                                                                                                                                                                                                                                                              CASE
                                                                                                                                                                                                                                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
                                                                                                                                                                                                                                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
                                                                                                                                                                                                                                                                                                                                              WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
                                                                                                                                                                                                                                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.CODE ELSE S.CODE END CODE,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_DEB_VALIDITE ELSE S.DATE_DEB_VALIDITE END DATE_DEB_VALIDITE,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_FIN_VALIDITE ELSE S.DATE_FIN_VALIDITE END DATE_FIN_VALIDITE,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DESCRIPTION ELSE S.DESCRIPTION END DESCRIPTION,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ETABLISSEMENT_ID ELSE S.ETABLISSEMENT_ID END ETABLISSEMENT_ID,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.VALEUR ELSE S.VALEUR END VALEUR,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.CODE <> S.CODE OR (D.CODE IS NULL AND S.CODE IS NOT NULL) OR (D.CODE IS NOT NULL AND S.CODE IS NULL) THEN 1 ELSE 0 END U_CODE,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.DATE_DEB_VALIDITE <> S.DATE_DEB_VALIDITE OR (D.DATE_DEB_VALIDITE IS NULL AND S.DATE_DEB_VALIDITE IS NOT NULL) OR (D.DATE_DEB_VALIDITE IS NOT NULL AND S.DATE_DEB_VALIDITE IS NULL) THEN 1 ELSE 0 END U_DATE_DEB_VALIDITE,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.DATE_FIN_VALIDITE <> S.DATE_FIN_VALIDITE OR (D.DATE_FIN_VALIDITE IS NULL AND S.DATE_FIN_VALIDITE IS NOT NULL) OR (D.DATE_FIN_VALIDITE IS NOT NULL AND S.DATE_FIN_VALIDITE IS NULL) THEN 1 ELSE 0 END U_DATE_FIN_VALIDITE,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.DESCRIPTION <> S.DESCRIPTION OR (D.DESCRIPTION IS NULL AND S.DESCRIPTION IS NOT NULL) OR (D.DESCRIPTION IS NOT NULL AND S.DESCRIPTION IS NULL) THEN 1 ELSE 0 END U_DESCRIPTION,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.ETABLISSEMENT_ID <> S.ETABLISSEMENT_ID OR (D.ETABLISSEMENT_ID IS NULL AND S.ETABLISSEMENT_ID IS NOT NULL) OR (D.ETABLISSEMENT_ID IS NOT NULL AND S.ETABLISSEMENT_ID IS NULL) THEN 1 ELSE 0 END U_ETABLISSEMENT_ID,
                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.VALEUR <> S.VALEUR OR (D.VALEUR IS NULL AND S.VALEUR IS NOT NULL) OR (D.VALEUR IS NOT NULL AND S.VALEUR IS NULL) THEN 1 ELSE 0 END U_VALEUR
                                                                                                                                                                                                                                                                                                                                            FROM
                                                                                                                                                                                                                                                                                                                                              VARIABLE D
                                                                                                                                                                                                                                                                                                                                              FULL JOIN SRC_VARIABLE S ON S.source_id = D.source_id AND S.source_code = D.source_code
                                                                                                                                                                                                                                                                                                                                            WHERE
                                                                                                                                                                                                                                                                                                                                              (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
                                                                                                                                                                                                                                                                                                                                              OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
                                                                                                                                                                                                                                                                                                                                              OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
                                                                                                                                                                                                                                                                                                                                              OR D.CODE <> S.CODE OR (D.CODE IS NULL AND S.CODE IS NOT NULL) OR (D.CODE IS NOT NULL AND S.CODE IS NULL)
                                                                                                                                                                                                                                                                                                                                              OR D.DATE_DEB_VALIDITE <> S.DATE_DEB_VALIDITE OR (D.DATE_DEB_VALIDITE IS NULL AND S.DATE_DEB_VALIDITE IS NOT NULL) OR (D.DATE_DEB_VALIDITE IS NOT NULL AND S.DATE_DEB_VALIDITE IS NULL)
                                                                                                                                                                                                                                                                                                                                              OR D.DATE_FIN_VALIDITE <> S.DATE_FIN_VALIDITE OR (D.DATE_FIN_VALIDITE IS NULL AND S.DATE_FIN_VALIDITE IS NOT NULL) OR (D.DATE_FIN_VALIDITE IS NOT NULL AND S.DATE_FIN_VALIDITE IS NULL)
                                                                                                                                                                                                                                                                                                                                              OR D.DESCRIPTION <> S.DESCRIPTION OR (D.DESCRIPTION IS NULL AND S.DESCRIPTION IS NOT NULL) OR (D.DESCRIPTION IS NOT NULL AND S.DESCRIPTION IS NULL)
                                                                                                                                                                                                                                                                                                                                              OR D.ETABLISSEMENT_ID <> S.ETABLISSEMENT_ID OR (D.ETABLISSEMENT_ID IS NULL AND S.ETABLISSEMENT_ID IS NOT NULL) OR (D.ETABLISSEMENT_ID IS NOT NULL AND S.ETABLISSEMENT_ID IS NULL)
                                                                                                                                                                                                                                                                                                                                              OR D.VALEUR <> S.VALEUR OR (D.VALEUR IS NULL AND S.VALEUR IS NOT NULL) OR (D.VALEUR IS NOT NULL AND S.VALEUR IS NULL)
                                                                                                                                                                                                                                                                                                                                           ) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/

create view V_DIFF_THESE as
  select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."CORREC_AUTORISEE",diff."DATE_AUTORIS_SOUTENANCE",diff."DATE_FIN_CONFID",diff."DATE_PREM_INSC",diff."DATE_PREV_SOUTENANCE",diff."DATE_SOUTENANCE",diff."DOCTORANT_ID",diff."ECOLE_DOCT_ID",diff."ETABLISSEMENT_ID",diff."ETAT_THESE",diff."LIB_DISC",diff."LIB_ETAB_COTUT",diff."LIB_PAYS_COTUT",diff."RESULTAT",diff."SOUTENANCE_AUTORIS",diff."TEM_AVENANT_COTUT",diff."TITRE",diff."UNITE_RECH_ID",diff."U_CORREC_AUTORISEE",diff."U_DATE_AUTORIS_SOUTENANCE",diff."U_DATE_FIN_CONFID",diff."U_DATE_PREM_INSC",diff."U_DATE_PREV_SOUTENANCE",diff."U_DATE_SOUTENANCE",diff."U_DOCTORANT_ID",diff."U_ECOLE_DOCT_ID",diff."U_ETABLISSEMENT_ID",diff."U_ETAT_THESE",diff."U_LIB_DISC",diff."U_LIB_ETAB_COTUT",diff."U_LIB_PAYS_COTUT",diff."U_RESULTAT",diff."U_SOUTENANCE_AUTORIS",diff."U_TEM_AVENANT_COTUT",diff."U_TITRE",diff."U_UNITE_RECH_ID" from (SELECT
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              COALESCE( D.id, S.id ) id,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              COALESCE( S.source_id, D.source_id ) source_id,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              COALESCE( S.source_code, D.source_code ) source_code,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.CORREC_AUTORISEE ELSE S.CORREC_AUTORISEE END CORREC_AUTORISEE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_AUTORIS_SOUTENANCE ELSE S.DATE_AUTORIS_SOUTENANCE END DATE_AUTORIS_SOUTENANCE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_FIN_CONFID ELSE S.DATE_FIN_CONFID END DATE_FIN_CONFID,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_PREM_INSC ELSE S.DATE_PREM_INSC END DATE_PREM_INSC,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_PREV_SOUTENANCE ELSE S.DATE_PREV_SOUTENANCE END DATE_PREV_SOUTENANCE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_SOUTENANCE ELSE S.DATE_SOUTENANCE END DATE_SOUTENANCE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DOCTORANT_ID ELSE S.DOCTORANT_ID END DOCTORANT_ID,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ECOLE_DOCT_ID ELSE S.ECOLE_DOCT_ID END ECOLE_DOCT_ID,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ETABLISSEMENT_ID ELSE S.ETABLISSEMENT_ID END ETABLISSEMENT_ID,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ETAT_THESE ELSE S.ETAT_THESE END ETAT_THESE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.LIB_DISC ELSE S.LIB_DISC END LIB_DISC,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.LIB_ETAB_COTUT ELSE S.LIB_ETAB_COTUT END LIB_ETAB_COTUT,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.LIB_PAYS_COTUT ELSE S.LIB_PAYS_COTUT END LIB_PAYS_COTUT,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.RESULTAT ELSE S.RESULTAT END RESULTAT,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.SOUTENANCE_AUTORIS ELSE S.SOUTENANCE_AUTORIS END SOUTENANCE_AUTORIS,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.TEM_AVENANT_COTUT ELSE S.TEM_AVENANT_COTUT END TEM_AVENANT_COTUT,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.TITRE ELSE S.TITRE END TITRE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.UNITE_RECH_ID ELSE S.UNITE_RECH_ID END UNITE_RECH_ID,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.CORREC_AUTORISEE <> S.CORREC_AUTORISEE OR (D.CORREC_AUTORISEE IS NULL AND S.CORREC_AUTORISEE IS NOT NULL) OR (D.CORREC_AUTORISEE IS NOT NULL AND S.CORREC_AUTORISEE IS NULL) THEN 1 ELSE 0 END U_CORREC_AUTORISEE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.DATE_AUTORIS_SOUTENANCE <> S.DATE_AUTORIS_SOUTENANCE OR (D.DATE_AUTORIS_SOUTENANCE IS NULL AND S.DATE_AUTORIS_SOUTENANCE IS NOT NULL) OR (D.DATE_AUTORIS_SOUTENANCE IS NOT NULL AND S.DATE_AUTORIS_SOUTENANCE IS NULL) THEN 1 ELSE 0 END U_DATE_AUTORIS_SOUTENANCE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.DATE_FIN_CONFID <> S.DATE_FIN_CONFID OR (D.DATE_FIN_CONFID IS NULL AND S.DATE_FIN_CONFID IS NOT NULL) OR (D.DATE_FIN_CONFID IS NOT NULL AND S.DATE_FIN_CONFID IS NULL) THEN 1 ELSE 0 END U_DATE_FIN_CONFID,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.DATE_PREM_INSC <> S.DATE_PREM_INSC OR (D.DATE_PREM_INSC IS NULL AND S.DATE_PREM_INSC IS NOT NULL) OR (D.DATE_PREM_INSC IS NOT NULL AND S.DATE_PREM_INSC IS NULL) THEN 1 ELSE 0 END U_DATE_PREM_INSC,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.DATE_PREV_SOUTENANCE <> S.DATE_PREV_SOUTENANCE OR (D.DATE_PREV_SOUTENANCE IS NULL AND S.DATE_PREV_SOUTENANCE IS NOT NULL) OR (D.DATE_PREV_SOUTENANCE IS NOT NULL AND S.DATE_PREV_SOUTENANCE IS NULL) THEN 1 ELSE 0 END U_DATE_PREV_SOUTENANCE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.DATE_SOUTENANCE <> S.DATE_SOUTENANCE OR (D.DATE_SOUTENANCE IS NULL AND S.DATE_SOUTENANCE IS NOT NULL) OR (D.DATE_SOUTENANCE IS NOT NULL AND S.DATE_SOUTENANCE IS NULL) THEN 1 ELSE 0 END U_DATE_SOUTENANCE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.DOCTORANT_ID <> S.DOCTORANT_ID OR (D.DOCTORANT_ID IS NULL AND S.DOCTORANT_ID IS NOT NULL) OR (D.DOCTORANT_ID IS NOT NULL AND S.DOCTORANT_ID IS NULL) THEN 1 ELSE 0 END U_DOCTORANT_ID,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.ECOLE_DOCT_ID <> S.ECOLE_DOCT_ID OR (D.ECOLE_DOCT_ID IS NULL AND S.ECOLE_DOCT_ID IS NOT NULL) OR (D.ECOLE_DOCT_ID IS NOT NULL AND S.ECOLE_DOCT_ID IS NULL) THEN 1 ELSE 0 END U_ECOLE_DOCT_ID,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.ETABLISSEMENT_ID <> S.ETABLISSEMENT_ID OR (D.ETABLISSEMENT_ID IS NULL AND S.ETABLISSEMENT_ID IS NOT NULL) OR (D.ETABLISSEMENT_ID IS NOT NULL AND S.ETABLISSEMENT_ID IS NULL) THEN 1 ELSE 0 END U_ETABLISSEMENT_ID,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.ETAT_THESE <> S.ETAT_THESE OR (D.ETAT_THESE IS NULL AND S.ETAT_THESE IS NOT NULL) OR (D.ETAT_THESE IS NOT NULL AND S.ETAT_THESE IS NULL) THEN 1 ELSE 0 END U_ETAT_THESE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.LIB_DISC <> S.LIB_DISC OR (D.LIB_DISC IS NULL AND S.LIB_DISC IS NOT NULL) OR (D.LIB_DISC IS NOT NULL AND S.LIB_DISC IS NULL) THEN 1 ELSE 0 END U_LIB_DISC,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.LIB_ETAB_COTUT <> S.LIB_ETAB_COTUT OR (D.LIB_ETAB_COTUT IS NULL AND S.LIB_ETAB_COTUT IS NOT NULL) OR (D.LIB_ETAB_COTUT IS NOT NULL AND S.LIB_ETAB_COTUT IS NULL) THEN 1 ELSE 0 END U_LIB_ETAB_COTUT,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.LIB_PAYS_COTUT <> S.LIB_PAYS_COTUT OR (D.LIB_PAYS_COTUT IS NULL AND S.LIB_PAYS_COTUT IS NOT NULL) OR (D.LIB_PAYS_COTUT IS NOT NULL AND S.LIB_PAYS_COTUT IS NULL) THEN 1 ELSE 0 END U_LIB_PAYS_COTUT,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.RESULTAT <> S.RESULTAT OR (D.RESULTAT IS NULL AND S.RESULTAT IS NOT NULL) OR (D.RESULTAT IS NOT NULL AND S.RESULTAT IS NULL) THEN 1 ELSE 0 END U_RESULTAT,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.SOUTENANCE_AUTORIS <> S.SOUTENANCE_AUTORIS OR (D.SOUTENANCE_AUTORIS IS NULL AND S.SOUTENANCE_AUTORIS IS NOT NULL) OR (D.SOUTENANCE_AUTORIS IS NOT NULL AND S.SOUTENANCE_AUTORIS IS NULL) THEN 1 ELSE 0 END U_SOUTENANCE_AUTORIS,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.TEM_AVENANT_COTUT <> S.TEM_AVENANT_COTUT OR (D.TEM_AVENANT_COTUT IS NULL AND S.TEM_AVENANT_COTUT IS NOT NULL) OR (D.TEM_AVENANT_COTUT IS NOT NULL AND S.TEM_AVENANT_COTUT IS NULL) THEN 1 ELSE 0 END U_TEM_AVENANT_COTUT,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.TITRE <> S.TITRE OR (D.TITRE IS NULL AND S.TITRE IS NOT NULL) OR (D.TITRE IS NOT NULL AND S.TITRE IS NULL) THEN 1 ELSE 0 END U_TITRE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              CASE WHEN D.UNITE_RECH_ID <> S.UNITE_RECH_ID OR (D.UNITE_RECH_ID IS NULL AND S.UNITE_RECH_ID IS NOT NULL) OR (D.UNITE_RECH_ID IS NOT NULL AND S.UNITE_RECH_ID IS NULL) THEN 1 ELSE 0 END U_UNITE_RECH_ID
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            FROM
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              THESE D
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              FULL JOIN SRC_THESE S ON S.source_id = D.source_id AND S.source_code = D.source_code
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            WHERE
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.CORREC_AUTORISEE <> S.CORREC_AUTORISEE OR (D.CORREC_AUTORISEE IS NULL AND S.CORREC_AUTORISEE IS NOT NULL) OR (D.CORREC_AUTORISEE IS NOT NULL AND S.CORREC_AUTORISEE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.DATE_AUTORIS_SOUTENANCE <> S.DATE_AUTORIS_SOUTENANCE OR (D.DATE_AUTORIS_SOUTENANCE IS NULL AND S.DATE_AUTORIS_SOUTENANCE IS NOT NULL) OR (D.DATE_AUTORIS_SOUTENANCE IS NOT NULL AND S.DATE_AUTORIS_SOUTENANCE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.DATE_FIN_CONFID <> S.DATE_FIN_CONFID OR (D.DATE_FIN_CONFID IS NULL AND S.DATE_FIN_CONFID IS NOT NULL) OR (D.DATE_FIN_CONFID IS NOT NULL AND S.DATE_FIN_CONFID IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.DATE_PREM_INSC <> S.DATE_PREM_INSC OR (D.DATE_PREM_INSC IS NULL AND S.DATE_PREM_INSC IS NOT NULL) OR (D.DATE_PREM_INSC IS NOT NULL AND S.DATE_PREM_INSC IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.DATE_PREV_SOUTENANCE <> S.DATE_PREV_SOUTENANCE OR (D.DATE_PREV_SOUTENANCE IS NULL AND S.DATE_PREV_SOUTENANCE IS NOT NULL) OR (D.DATE_PREV_SOUTENANCE IS NOT NULL AND S.DATE_PREV_SOUTENANCE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.DATE_SOUTENANCE <> S.DATE_SOUTENANCE OR (D.DATE_SOUTENANCE IS NULL AND S.DATE_SOUTENANCE IS NOT NULL) OR (D.DATE_SOUTENANCE IS NOT NULL AND S.DATE_SOUTENANCE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.DOCTORANT_ID <> S.DOCTORANT_ID OR (D.DOCTORANT_ID IS NULL AND S.DOCTORANT_ID IS NOT NULL) OR (D.DOCTORANT_ID IS NOT NULL AND S.DOCTORANT_ID IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.ECOLE_DOCT_ID <> S.ECOLE_DOCT_ID OR (D.ECOLE_DOCT_ID IS NULL AND S.ECOLE_DOCT_ID IS NOT NULL) OR (D.ECOLE_DOCT_ID IS NOT NULL AND S.ECOLE_DOCT_ID IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.ETABLISSEMENT_ID <> S.ETABLISSEMENT_ID OR (D.ETABLISSEMENT_ID IS NULL AND S.ETABLISSEMENT_ID IS NOT NULL) OR (D.ETABLISSEMENT_ID IS NOT NULL AND S.ETABLISSEMENT_ID IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.ETAT_THESE <> S.ETAT_THESE OR (D.ETAT_THESE IS NULL AND S.ETAT_THESE IS NOT NULL) OR (D.ETAT_THESE IS NOT NULL AND S.ETAT_THESE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.LIB_DISC <> S.LIB_DISC OR (D.LIB_DISC IS NULL AND S.LIB_DISC IS NOT NULL) OR (D.LIB_DISC IS NOT NULL AND S.LIB_DISC IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.LIB_ETAB_COTUT <> S.LIB_ETAB_COTUT OR (D.LIB_ETAB_COTUT IS NULL AND S.LIB_ETAB_COTUT IS NOT NULL) OR (D.LIB_ETAB_COTUT IS NOT NULL AND S.LIB_ETAB_COTUT IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.LIB_PAYS_COTUT <> S.LIB_PAYS_COTUT OR (D.LIB_PAYS_COTUT IS NULL AND S.LIB_PAYS_COTUT IS NOT NULL) OR (D.LIB_PAYS_COTUT IS NOT NULL AND S.LIB_PAYS_COTUT IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.RESULTAT <> S.RESULTAT OR (D.RESULTAT IS NULL AND S.RESULTAT IS NOT NULL) OR (D.RESULTAT IS NOT NULL AND S.RESULTAT IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.SOUTENANCE_AUTORIS <> S.SOUTENANCE_AUTORIS OR (D.SOUTENANCE_AUTORIS IS NULL AND S.SOUTENANCE_AUTORIS IS NOT NULL) OR (D.SOUTENANCE_AUTORIS IS NOT NULL AND S.SOUTENANCE_AUTORIS IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.TEM_AVENANT_COTUT <> S.TEM_AVENANT_COTUT OR (D.TEM_AVENANT_COTUT IS NULL AND S.TEM_AVENANT_COTUT IS NOT NULL) OR (D.TEM_AVENANT_COTUT IS NOT NULL AND S.TEM_AVENANT_COTUT IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.TITRE <> S.TITRE OR (D.TITRE IS NULL AND S.TITRE IS NOT NULL) OR (D.TITRE IS NOT NULL AND S.TITRE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              OR D.UNITE_RECH_ID <> S.UNITE_RECH_ID OR (D.UNITE_RECH_ID IS NULL AND S.UNITE_RECH_ID IS NOT NULL) OR (D.UNITE_RECH_ID IS NOT NULL AND S.UNITE_RECH_ID IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/

create view V_DIFF_ROLE as
  select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."CODE",diff."LIBELLE",diff."ROLE_ID",diff."STRUCTURE_ID",diff."THESE_DEP",diff."TYPE_STRUCTURE_DEPENDANT_ID",diff."U_CODE",diff."U_LIBELLE",diff."U_ROLE_ID",diff."U_STRUCTURE_ID",diff."U_THESE_DEP",diff."U_TYPE_STRUCTURE_DEPENDANT_ID" from (SELECT
                                                                                                                                                                                                                                                                                                                                    COALESCE( D.id, S.id ) id,
                                                                                                                                                                                                                                                                                                                                    COALESCE( S.source_id, D.source_id ) source_id,
                                                                                                                                                                                                                                                                                                                                    COALESCE( S.source_code, D.source_code ) source_code,
                                                                                                                                                                                                                                                                                                                                    CASE
                                                                                                                                                                                                                                                                                                                                    WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
                                                                                                                                                                                                                                                                                                                                    WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
                                                                                                                                                                                                                                                                                                                                    WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
                                                                                                                                                                                                                                                                                                                                    WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.CODE ELSE S.CODE END CODE,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.LIBELLE ELSE S.LIBELLE END LIBELLE,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ROLE_ID ELSE S.ROLE_ID END ROLE_ID,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.STRUCTURE_ID ELSE S.STRUCTURE_ID END STRUCTURE_ID,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.THESE_DEP ELSE S.THESE_DEP END THESE_DEP,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.TYPE_STRUCTURE_DEPENDANT_ID ELSE S.TYPE_STRUCTURE_DEPENDANT_ID END TYPE_STRUCTURE_DEPENDANT_ID,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN D.CODE <> S.CODE OR (D.CODE IS NULL AND S.CODE IS NOT NULL) OR (D.CODE IS NOT NULL AND S.CODE IS NULL) THEN 1 ELSE 0 END U_CODE,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN D.LIBELLE <> S.LIBELLE OR (D.LIBELLE IS NULL AND S.LIBELLE IS NOT NULL) OR (D.LIBELLE IS NOT NULL AND S.LIBELLE IS NULL) THEN 1 ELSE 0 END U_LIBELLE,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN D.ROLE_ID <> S.ROLE_ID OR (D.ROLE_ID IS NULL AND S.ROLE_ID IS NOT NULL) OR (D.ROLE_ID IS NOT NULL AND S.ROLE_ID IS NULL) THEN 1 ELSE 0 END U_ROLE_ID,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN D.STRUCTURE_ID <> S.STRUCTURE_ID OR (D.STRUCTURE_ID IS NULL AND S.STRUCTURE_ID IS NOT NULL) OR (D.STRUCTURE_ID IS NOT NULL AND S.STRUCTURE_ID IS NULL) THEN 1 ELSE 0 END U_STRUCTURE_ID,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN D.THESE_DEP <> S.THESE_DEP OR (D.THESE_DEP IS NULL AND S.THESE_DEP IS NOT NULL) OR (D.THESE_DEP IS NOT NULL AND S.THESE_DEP IS NULL) THEN 1 ELSE 0 END U_THESE_DEP,
                                                                                                                                                                                                                                                                                                                                    CASE WHEN D.TYPE_STRUCTURE_DEPENDANT_ID <> S.TYPE_STRUCTURE_DEPENDANT_ID OR (D.TYPE_STRUCTURE_DEPENDANT_ID IS NULL AND S.TYPE_STRUCTURE_DEPENDANT_ID IS NOT NULL) OR (D.TYPE_STRUCTURE_DEPENDANT_ID IS NOT NULL AND S.TYPE_STRUCTURE_DEPENDANT_ID IS NULL) THEN 1 ELSE 0 END U_TYPE_STRUCTURE_DEPENDANT_ID
                                                                                                                                                                                                                                                                                                                                  FROM
                                                                                                                                                                                                                                                                                                                                    ROLE D
                                                                                                                                                                                                                                                                                                                                    FULL JOIN SRC_ROLE S ON S.source_id = D.source_id AND S.source_code = D.source_code
                                                                                                                                                                                                                                                                                                                                  WHERE
                                                                                                                                                                                                                                                                                                                                    (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
                                                                                                                                                                                                                                                                                                                                    OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
                                                                                                                                                                                                                                                                                                                                    OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
                                                                                                                                                                                                                                                                                                                                    OR D.CODE <> S.CODE OR (D.CODE IS NULL AND S.CODE IS NOT NULL) OR (D.CODE IS NOT NULL AND S.CODE IS NULL)
                                                                                                                                                                                                                                                                                                                                    OR D.LIBELLE <> S.LIBELLE OR (D.LIBELLE IS NULL AND S.LIBELLE IS NOT NULL) OR (D.LIBELLE IS NOT NULL AND S.LIBELLE IS NULL)
                                                                                                                                                                                                                                                                                                                                    OR D.ROLE_ID <> S.ROLE_ID OR (D.ROLE_ID IS NULL AND S.ROLE_ID IS NOT NULL) OR (D.ROLE_ID IS NOT NULL AND S.ROLE_ID IS NULL)
                                                                                                                                                                                                                                                                                                                                    OR D.STRUCTURE_ID <> S.STRUCTURE_ID OR (D.STRUCTURE_ID IS NULL AND S.STRUCTURE_ID IS NOT NULL) OR (D.STRUCTURE_ID IS NOT NULL AND S.STRUCTURE_ID IS NULL)
                                                                                                                                                                                                                                                                                                                                    OR D.THESE_DEP <> S.THESE_DEP OR (D.THESE_DEP IS NULL AND S.THESE_DEP IS NOT NULL) OR (D.THESE_DEP IS NOT NULL AND S.THESE_DEP IS NULL)
                                                                                                                                                                                                                                                                                                                                    OR D.TYPE_STRUCTURE_DEPENDANT_ID <> S.TYPE_STRUCTURE_DEPENDANT_ID OR (D.TYPE_STRUCTURE_DEPENDANT_ID IS NULL AND S.TYPE_STRUCTURE_DEPENDANT_ID IS NOT NULL) OR (D.TYPE_STRUCTURE_DEPENDANT_ID IS NOT NULL AND S.TYPE_STRUCTURE_DEPENDANT_ID IS NULL)
                                                                                                                                                                                                                                                                                                                                 ) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/

create view V_DIFF_INDIVIDU as
  select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."CIVILITE",diff."DATE_NAISSANCE",diff."EMAIL",diff."NATIONALITE",diff."NOM_PATRONYMIQUE",diff."NOM_USUEL",diff."PRENOM1",diff."PRENOM2",diff."PRENOM3",diff."TYPE",diff."U_CIVILITE",diff."U_DATE_NAISSANCE",diff."U_EMAIL",diff."U_NATIONALITE",diff."U_NOM_PATRONYMIQUE",diff."U_NOM_USUEL",diff."U_PRENOM1",diff."U_PRENOM2",diff."U_PRENOM3",diff."U_TYPE" from (SELECT
                                                                                                                                                                                                                                                                                                                                                                                                                                                        COALESCE( D.id, S.id ) id,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        COALESCE( S.source_id, D.source_id ) source_id,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        COALESCE( S.source_code, D.source_code ) source_code,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE
                                                                                                                                                                                                                                                                                                                                                                                                                                                        WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
                                                                                                                                                                                                                                                                                                                                                                                                                                                        WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
                                                                                                                                                                                                                                                                                                                                                                                                                                                        WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
                                                                                                                                                                                                                                                                                                                                                                                                                                                        WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.CIVILITE ELSE S.CIVILITE END CIVILITE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_NAISSANCE ELSE S.DATE_NAISSANCE END DATE_NAISSANCE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.EMAIL ELSE S.EMAIL END EMAIL,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.NATIONALITE ELSE S.NATIONALITE END NATIONALITE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.NOM_PATRONYMIQUE ELSE S.NOM_PATRONYMIQUE END NOM_PATRONYMIQUE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.NOM_USUEL ELSE S.NOM_USUEL END NOM_USUEL,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.PRENOM1 ELSE S.PRENOM1 END PRENOM1,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.PRENOM2 ELSE S.PRENOM2 END PRENOM2,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.PRENOM3 ELSE S.PRENOM3 END PRENOM3,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.TYPE ELSE S.TYPE END TYPE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN D.CIVILITE <> S.CIVILITE OR (D.CIVILITE IS NULL AND S.CIVILITE IS NOT NULL) OR (D.CIVILITE IS NOT NULL AND S.CIVILITE IS NULL) THEN 1 ELSE 0 END U_CIVILITE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN D.DATE_NAISSANCE <> S.DATE_NAISSANCE OR (D.DATE_NAISSANCE IS NULL AND S.DATE_NAISSANCE IS NOT NULL) OR (D.DATE_NAISSANCE IS NOT NULL AND S.DATE_NAISSANCE IS NULL) THEN 1 ELSE 0 END U_DATE_NAISSANCE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN D.EMAIL <> S.EMAIL OR (D.EMAIL IS NULL AND S.EMAIL IS NOT NULL) OR (D.EMAIL IS NOT NULL AND S.EMAIL IS NULL) THEN 1 ELSE 0 END U_EMAIL,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN D.NATIONALITE <> S.NATIONALITE OR (D.NATIONALITE IS NULL AND S.NATIONALITE IS NOT NULL) OR (D.NATIONALITE IS NOT NULL AND S.NATIONALITE IS NULL) THEN 1 ELSE 0 END U_NATIONALITE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN D.NOM_PATRONYMIQUE <> S.NOM_PATRONYMIQUE OR (D.NOM_PATRONYMIQUE IS NULL AND S.NOM_PATRONYMIQUE IS NOT NULL) OR (D.NOM_PATRONYMIQUE IS NOT NULL AND S.NOM_PATRONYMIQUE IS NULL) THEN 1 ELSE 0 END U_NOM_PATRONYMIQUE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN D.NOM_USUEL <> S.NOM_USUEL OR (D.NOM_USUEL IS NULL AND S.NOM_USUEL IS NOT NULL) OR (D.NOM_USUEL IS NOT NULL AND S.NOM_USUEL IS NULL) THEN 1 ELSE 0 END U_NOM_USUEL,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN D.PRENOM1 <> S.PRENOM1 OR (D.PRENOM1 IS NULL AND S.PRENOM1 IS NOT NULL) OR (D.PRENOM1 IS NOT NULL AND S.PRENOM1 IS NULL) THEN 1 ELSE 0 END U_PRENOM1,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN D.PRENOM2 <> S.PRENOM2 OR (D.PRENOM2 IS NULL AND S.PRENOM2 IS NOT NULL) OR (D.PRENOM2 IS NOT NULL AND S.PRENOM2 IS NULL) THEN 1 ELSE 0 END U_PRENOM2,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN D.PRENOM3 <> S.PRENOM3 OR (D.PRENOM3 IS NULL AND S.PRENOM3 IS NOT NULL) OR (D.PRENOM3 IS NOT NULL AND S.PRENOM3 IS NULL) THEN 1 ELSE 0 END U_PRENOM3,
                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE WHEN D.TYPE <> S.TYPE OR (D.TYPE IS NULL AND S.TYPE IS NOT NULL) OR (D.TYPE IS NOT NULL AND S.TYPE IS NULL) THEN 1 ELSE 0 END U_TYPE
                                                                                                                                                                                                                                                                                                                                                                                                                                                      FROM
                                                                                                                                                                                                                                                                                                                                                                                                                                                        INDIVIDU D
                                                                                                                                                                                                                                                                                                                                                                                                                                                        FULL JOIN SRC_INDIVIDU S ON S.source_id = D.source_id AND S.source_code = D.source_code
                                                                                                                                                                                                                                                                                                                                                                                                                                                      WHERE
                                                                                                                                                                                                                                                                                                                                                                                                                                                        (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR D.CIVILITE <> S.CIVILITE OR (D.CIVILITE IS NULL AND S.CIVILITE IS NOT NULL) OR (D.CIVILITE IS NOT NULL AND S.CIVILITE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR D.DATE_NAISSANCE <> S.DATE_NAISSANCE OR (D.DATE_NAISSANCE IS NULL AND S.DATE_NAISSANCE IS NOT NULL) OR (D.DATE_NAISSANCE IS NOT NULL AND S.DATE_NAISSANCE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR D.EMAIL <> S.EMAIL OR (D.EMAIL IS NULL AND S.EMAIL IS NOT NULL) OR (D.EMAIL IS NOT NULL AND S.EMAIL IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR D.NATIONALITE <> S.NATIONALITE OR (D.NATIONALITE IS NULL AND S.NATIONALITE IS NOT NULL) OR (D.NATIONALITE IS NOT NULL AND S.NATIONALITE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR D.NOM_PATRONYMIQUE <> S.NOM_PATRONYMIQUE OR (D.NOM_PATRONYMIQUE IS NULL AND S.NOM_PATRONYMIQUE IS NOT NULL) OR (D.NOM_PATRONYMIQUE IS NOT NULL AND S.NOM_PATRONYMIQUE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR D.NOM_USUEL <> S.NOM_USUEL OR (D.NOM_USUEL IS NULL AND S.NOM_USUEL IS NOT NULL) OR (D.NOM_USUEL IS NOT NULL AND S.NOM_USUEL IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR D.PRENOM1 <> S.PRENOM1 OR (D.PRENOM1 IS NULL AND S.PRENOM1 IS NOT NULL) OR (D.PRENOM1 IS NOT NULL AND S.PRENOM1 IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR D.PRENOM2 <> S.PRENOM2 OR (D.PRENOM2 IS NULL AND S.PRENOM2 IS NOT NULL) OR (D.PRENOM2 IS NOT NULL AND S.PRENOM2 IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR D.PRENOM3 <> S.PRENOM3 OR (D.PRENOM3 IS NULL AND S.PRENOM3 IS NOT NULL) OR (D.PRENOM3 IS NOT NULL AND S.PRENOM3 IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                        OR D.TYPE <> S.TYPE OR (D.TYPE IS NULL AND S.TYPE IS NOT NULL) OR (D.TYPE IS NOT NULL AND S.TYPE IS NULL)
                                                                                                                                                                                                                                                                                                                                                                                                                                                     ) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/

create view V_DIFF_DOCTORANT as
  select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."ETABLISSEMENT_ID",diff."INDIVIDU_ID",diff."U_ETABLISSEMENT_ID",diff."U_INDIVIDU_ID" from (SELECT
                                                                                                                                                                              COALESCE( D.id, S.id ) id,
                                                                                                                                                                              COALESCE( S.source_id, D.source_id ) source_id,
                                                                                                                                                                              COALESCE( S.source_code, D.source_code ) source_code,
                                                                                                                                                                              CASE
                                                                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
                                                                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
                                                                                                                                                                              WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
                                                                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ETABLISSEMENT_ID ELSE S.ETABLISSEMENT_ID END ETABLISSEMENT_ID,
                                                                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.INDIVIDU_ID ELSE S.INDIVIDU_ID END INDIVIDU_ID,
                                                                                                                                                                              CASE WHEN D.ETABLISSEMENT_ID <> S.ETABLISSEMENT_ID OR (D.ETABLISSEMENT_ID IS NULL AND S.ETABLISSEMENT_ID IS NOT NULL) OR (D.ETABLISSEMENT_ID IS NOT NULL AND S.ETABLISSEMENT_ID IS NULL) THEN 1 ELSE 0 END U_ETABLISSEMENT_ID,
                                                                                                                                                                              CASE WHEN D.INDIVIDU_ID <> S.INDIVIDU_ID OR (D.INDIVIDU_ID IS NULL AND S.INDIVIDU_ID IS NOT NULL) OR (D.INDIVIDU_ID IS NOT NULL AND S.INDIVIDU_ID IS NULL) THEN 1 ELSE 0 END U_INDIVIDU_ID
                                                                                                                                                                            FROM
                                                                                                                                                                              DOCTORANT D
                                                                                                                                                                              FULL JOIN SRC_DOCTORANT S ON S.source_id = D.source_id AND S.source_code = D.source_code
                                                                                                                                                                            WHERE
                                                                                                                                                                              (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
                                                                                                                                                                              OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
                                                                                                                                                                              OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
                                                                                                                                                                              OR D.ETABLISSEMENT_ID <> S.ETABLISSEMENT_ID OR (D.ETABLISSEMENT_ID IS NULL AND S.ETABLISSEMENT_ID IS NOT NULL) OR (D.ETABLISSEMENT_ID IS NOT NULL AND S.ETABLISSEMENT_ID IS NULL)
                                                                                                                                                                              OR D.INDIVIDU_ID <> S.INDIVIDU_ID OR (D.INDIVIDU_ID IS NULL AND S.INDIVIDU_ID IS NOT NULL) OR (D.INDIVIDU_ID IS NOT NULL AND S.INDIVIDU_ID IS NULL)
                                                                                                                                                                           ) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/

create view V_DIFF_ACTEUR as
  select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."ETABLISSEMENT",diff."INDIVIDU_ID",diff."LIB_ROLE_COMPL",diff."QUALITE",diff."ROLE_ID",diff."THESE_ID",diff."U_ETABLISSEMENT",diff."U_INDIVIDU_ID",diff."U_LIB_ROLE_COMPL",diff."U_QUALITE",diff."U_ROLE_ID",diff."U_THESE_ID" from (SELECT
                                                                                                                                                                                                                                                                                                                        COALESCE( D.id, S.id ) id,
                                                                                                                                                                                                                                                                                                                        COALESCE( S.source_id, D.source_id ) source_id,
                                                                                                                                                                                                                                                                                                                        COALESCE( S.source_code, D.source_code ) source_code,
                                                                                                                                                                                                                                                                                                                        CASE
                                                                                                                                                                                                                                                                                                                        WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
                                                                                                                                                                                                                                                                                                                        WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
                                                                                                                                                                                                                                                                                                                        WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
                                                                                                                                                                                                                                                                                                                        WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ETABLISSEMENT ELSE S.ETABLISSEMENT END ETABLISSEMENT,
                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.INDIVIDU_ID ELSE S.INDIVIDU_ID END INDIVIDU_ID,
                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.LIB_ROLE_COMPL ELSE S.LIB_ROLE_COMPL END LIB_ROLE_COMPL,
                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.QUALITE ELSE S.QUALITE END QUALITE,
                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ROLE_ID ELSE S.ROLE_ID END ROLE_ID,
                                                                                                                                                                                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.THESE_ID ELSE S.THESE_ID END THESE_ID,
                                                                                                                                                                                                                                                                                                                        CASE WHEN D.ETABLISSEMENT <> S.ETABLISSEMENT OR (D.ETABLISSEMENT IS NULL AND S.ETABLISSEMENT IS NOT NULL) OR (D.ETABLISSEMENT IS NOT NULL AND S.ETABLISSEMENT IS NULL) THEN 1 ELSE 0 END U_ETABLISSEMENT,
                                                                                                                                                                                                                                                                                                                        CASE WHEN D.INDIVIDU_ID <> S.INDIVIDU_ID OR (D.INDIVIDU_ID IS NULL AND S.INDIVIDU_ID IS NOT NULL) OR (D.INDIVIDU_ID IS NOT NULL AND S.INDIVIDU_ID IS NULL) THEN 1 ELSE 0 END U_INDIVIDU_ID,
                                                                                                                                                                                                                                                                                                                        CASE WHEN D.LIB_ROLE_COMPL <> S.LIB_ROLE_COMPL OR (D.LIB_ROLE_COMPL IS NULL AND S.LIB_ROLE_COMPL IS NOT NULL) OR (D.LIB_ROLE_COMPL IS NOT NULL AND S.LIB_ROLE_COMPL IS NULL) THEN 1 ELSE 0 END U_LIB_ROLE_COMPL,
                                                                                                                                                                                                                                                                                                                        CASE WHEN D.QUALITE <> S.QUALITE OR (D.QUALITE IS NULL AND S.QUALITE IS NOT NULL) OR (D.QUALITE IS NOT NULL AND S.QUALITE IS NULL) THEN 1 ELSE 0 END U_QUALITE,
                                                                                                                                                                                                                                                                                                                        CASE WHEN D.ROLE_ID <> S.ROLE_ID OR (D.ROLE_ID IS NULL AND S.ROLE_ID IS NOT NULL) OR (D.ROLE_ID IS NOT NULL AND S.ROLE_ID IS NULL) THEN 1 ELSE 0 END U_ROLE_ID,
                                                                                                                                                                                                                                                                                                                        CASE WHEN D.THESE_ID <> S.THESE_ID OR (D.THESE_ID IS NULL AND S.THESE_ID IS NOT NULL) OR (D.THESE_ID IS NOT NULL AND S.THESE_ID IS NULL) THEN 1 ELSE 0 END U_THESE_ID
                                                                                                                                                                                                                                                                                                                      FROM
                                                                                                                                                                                                                                                                                                                        ACTEUR D
                                                                                                                                                                                                                                                                                                                        FULL JOIN SRC_ACTEUR S ON S.source_id = D.source_id AND S.source_code = D.source_code
                                                                                                                                                                                                                                                                                                                      WHERE
                                                                                                                                                                                                                                                                                                                        (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
                                                                                                                                                                                                                                                                                                                        OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
                                                                                                                                                                                                                                                                                                                        OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
                                                                                                                                                                                                                                                                                                                        OR D.ETABLISSEMENT <> S.ETABLISSEMENT OR (D.ETABLISSEMENT IS NULL AND S.ETABLISSEMENT IS NOT NULL) OR (D.ETABLISSEMENT IS NOT NULL AND S.ETABLISSEMENT IS NULL)
                                                                                                                                                                                                                                                                                                                        OR D.INDIVIDU_ID <> S.INDIVIDU_ID OR (D.INDIVIDU_ID IS NULL AND S.INDIVIDU_ID IS NOT NULL) OR (D.INDIVIDU_ID IS NOT NULL AND S.INDIVIDU_ID IS NULL)
                                                                                                                                                                                                                                                                                                                        OR D.LIB_ROLE_COMPL <> S.LIB_ROLE_COMPL OR (D.LIB_ROLE_COMPL IS NULL AND S.LIB_ROLE_COMPL IS NOT NULL) OR (D.LIB_ROLE_COMPL IS NOT NULL AND S.LIB_ROLE_COMPL IS NULL)
                                                                                                                                                                                                                                                                                                                        OR D.QUALITE <> S.QUALITE OR (D.QUALITE IS NULL AND S.QUALITE IS NOT NULL) OR (D.QUALITE IS NOT NULL AND S.QUALITE IS NULL)
                                                                                                                                                                                                                                                                                                                        OR D.ROLE_ID <> S.ROLE_ID OR (D.ROLE_ID IS NULL AND S.ROLE_ID IS NOT NULL) OR (D.ROLE_ID IS NOT NULL AND S.ROLE_ID IS NULL)
                                                                                                                                                                                                                                                                                                                        OR D.THESE_ID <> S.THESE_ID OR (D.THESE_ID IS NULL AND S.THESE_ID IS NOT NULL) OR (D.THESE_ID IS NOT NULL AND S.THESE_ID IS NULL)
                                                                                                                                                                                                                                                                                                                     ) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/

create view V_INDIVIDU_ROLE as
  select
    i.id utilisateur_id, i.NOM_USUEL, i.PRENOM1, i.EMAIL, i.SOURCE_CODE,
    r.ROLE_ID, r.id
  from INDIVIDU_ROLE ir
    join INDIVIDU i on i.id = ir.INDIVIDU_ID
    join role r on r.id = ir.ROLE_ID
  order by NOM_USUEL, PRENOM1, r.ROLE_ID
/

create view SRC_STRUCTURE as
  SELECT
    NULL              AS id,
    tmp.SOURCE_CODE   as SOURCE_CODE,
    src.id            AS SOURCE_ID,
    ts.id             as TYPE_STRUCTURE_ID,
    tmp.SIGLE,
    tmp.LIBELLE,
    tmp.CODE_PAYS,
    tmp.LIBELLE_PAYS
  FROM TMP_STRUCTURE tmp
    JOIN TYPE_STRUCTURE ts on ts.CODE = tmp.TYPE_STRUCTURE_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create view SRC_ECOLE_DOCT as
  SELECT
    NULL              AS id,
    tmp.SOURCE_CODE   as SOURCE_CODE,
    src.id            AS SOURCE_ID,
    s.ID              as STRUCTURE_ID
  FROM TMP_ECOLE_DOCT tmp
    JOIN STRUCTURE s on s.SOURCE_CODE = tmp.STRUCTURE_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create view SRC_UNITE_RECH as
  SELECT
    NULL              AS id,
    tmp.SOURCE_CODE   as SOURCE_CODE,
    src.id            AS SOURCE_ID,
    s.ID              as STRUCTURE_ID
  FROM TMP_UNITE_RECH tmp
    JOIN STRUCTURE s on s.SOURCE_CODE = tmp.STRUCTURE_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create view SRC_THESE_2 as
  SELECT
    NULL                            AS id,
    tmp.SOURCE_CODE                 AS SOURCE_CODE,
    src.ID                          AS source_id,
    e.id                            AS etablissement_id,
    d.id                            AS doctorant_id,
    ed.id                           AS ecole_doct_id_orig,
    ur.id                           AS unite_rech_id_orig,
    coalesce(ed_substit.id, ed.id)  AS ecole_doct_id,
    coalesce(ur_substit.id, ur.id)  AS unite_rech_id,
    tmp.lib_ths                     AS titre,
    tmp.eta_ths                     AS etat_these,
    to_number(tmp.cod_neg_tre)      AS resultat,
    tmp.lib_int1_dis                AS lib_disc,
    tmp.dat_deb_ths                 AS date_prem_insc,
    tmp.dat_prev_sou                AS date_prev_soutenance,
    tmp.dat_sou_ths                 AS date_soutenance,
    tmp.dat_fin_cfd_ths             AS date_fin_confid,
    tmp.lib_etab_cotut              AS lib_etab_cotut,
    tmp.lib_pays_cotut              AS lib_pays_cotut,
    tmp.correction_possible         AS CORREC_AUTORISEE,
    tem_sou_aut_ths                 AS soutenance_autoris,
    dat_aut_sou_ths                 AS date_autoris_soutenance,
    tem_avenant_cotut               AS tem_avenant_cotut
  FROM TMP_THESE tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
    JOIN DOCTORANT d ON d.SOURCE_CODE = tmp.DOCTORANT_ID

    LEFT JOIN ECOLE_DOCT ed ON ed.SOURCE_CODE = tmp.ECOLE_DOCT_ID
    LEFT JOIN UNITE_RECH ur ON ur.SOURCE_CODE = tmp.UNITE_RECH_ID

    LEFT JOIN STRUCTURE_SUBSTIT ss_ed on ss_ed.FROM_STRUCTURE_ID = ed.STRUCTURE_ID
    LEFT JOIN ECOLE_DOCT ed_substit on ed_substit.STRUCTURE_ID = ss_ed.TO_STRUCTURE_ID

    LEFT JOIN STRUCTURE_SUBSTIT ss_ur on ss_ur.FROM_STRUCTURE_ID = ur.STRUCTURE_ID
    LEFT JOIN ECOLE_DOCT ur_substit on ur_substit.STRUCTURE_ID = ss_ur.TO_STRUCTURE_ID
/

create view SRC_ETABLISSEMENT as
  SELECT
    NULL              AS id,
    tmp.SOURCE_CODE   as SOURCE_CODE,
    tmp.CODE          as CODE,
    src.id            AS SOURCE_ID,
    s.ID              as STRUCTURE_ID
  FROM TMP_ETABLISSEMENT tmp
    JOIN STRUCTURE s on s.SOURCE_CODE = tmp.STRUCTURE_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create view V_DIFF_UNITE_RECH as
  select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."STRUCTURE_ID",diff."U_STRUCTURE_ID" from (SELECT
                                                                                                                              COALESCE( D.id, S.id ) id,
                                                                                                                              COALESCE( S.source_id, D.source_id ) source_id,
                                                                                                                              COALESCE( S.source_code, D.source_code ) source_code,
                                                                                                                              CASE
                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
                                                                                                                              WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.STRUCTURE_ID ELSE S.STRUCTURE_ID END STRUCTURE_ID,
                                                                                                                              CASE WHEN D.STRUCTURE_ID <> S.STRUCTURE_ID OR (D.STRUCTURE_ID IS NULL AND S.STRUCTURE_ID IS NOT NULL) OR (D.STRUCTURE_ID IS NOT NULL AND S.STRUCTURE_ID IS NULL) THEN 1 ELSE 0 END U_STRUCTURE_ID
                                                                                                                            FROM
                                                                                                                              UNITE_RECH D
                                                                                                                              FULL JOIN SRC_UNITE_RECH S ON S.source_id = D.source_id AND S.source_code = D.source_code
                                                                                                                            WHERE
                                                                                                                              (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
                                                                                                                              OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
                                                                                                                              OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
                                                                                                                              OR D.STRUCTURE_ID <> S.STRUCTURE_ID OR (D.STRUCTURE_ID IS NULL AND S.STRUCTURE_ID IS NOT NULL) OR (D.STRUCTURE_ID IS NOT NULL AND S.STRUCTURE_ID IS NULL)
                                                                                                                           ) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/

create view V_DIFF_STRUCTURE as
  select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."LIBELLE",diff."SIGLE",diff."TYPE_STRUCTURE_ID",diff."U_LIBELLE",diff."U_SIGLE",diff."U_TYPE_STRUCTURE_ID" from (SELECT
                                                                                                                                                                                                    COALESCE( D.id, S.id ) id,
                                                                                                                                                                                                    COALESCE( S.source_id, D.source_id ) source_id,
                                                                                                                                                                                                    COALESCE( S.source_code, D.source_code ) source_code,
                                                                                                                                                                                                    CASE
                                                                                                                                                                                                    WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
                                                                                                                                                                                                    WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
                                                                                                                                                                                                    WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
                                                                                                                                                                                                    WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
                                                                                                                                                                                                    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.LIBELLE ELSE S.LIBELLE END LIBELLE,
                                                                                                                                                                                                    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.SIGLE ELSE S.SIGLE END SIGLE,
                                                                                                                                                                                                    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.TYPE_STRUCTURE_ID ELSE S.TYPE_STRUCTURE_ID END TYPE_STRUCTURE_ID,
                                                                                                                                                                                                    CASE WHEN D.LIBELLE <> S.LIBELLE OR (D.LIBELLE IS NULL AND S.LIBELLE IS NOT NULL) OR (D.LIBELLE IS NOT NULL AND S.LIBELLE IS NULL) THEN 1 ELSE 0 END U_LIBELLE,
                                                                                                                                                                                                    CASE WHEN D.SIGLE <> S.SIGLE OR (D.SIGLE IS NULL AND S.SIGLE IS NOT NULL) OR (D.SIGLE IS NOT NULL AND S.SIGLE IS NULL) THEN 1 ELSE 0 END U_SIGLE,
                                                                                                                                                                                                    CASE WHEN D.TYPE_STRUCTURE_ID <> S.TYPE_STRUCTURE_ID OR (D.TYPE_STRUCTURE_ID IS NULL AND S.TYPE_STRUCTURE_ID IS NOT NULL) OR (D.TYPE_STRUCTURE_ID IS NOT NULL AND S.TYPE_STRUCTURE_ID IS NULL) THEN 1 ELSE 0 END U_TYPE_STRUCTURE_ID
                                                                                                                                                                                                  FROM
                                                                                                                                                                                                    STRUCTURE D
                                                                                                                                                                                                    FULL JOIN SRC_STRUCTURE S ON S.source_id = D.source_id AND S.source_code = D.source_code
                                                                                                                                                                                                  WHERE
                                                                                                                                                                                                    (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
                                                                                                                                                                                                    OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
                                                                                                                                                                                                    OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
                                                                                                                                                                                                    OR D.LIBELLE <> S.LIBELLE OR (D.LIBELLE IS NULL AND S.LIBELLE IS NOT NULL) OR (D.LIBELLE IS NOT NULL AND S.LIBELLE IS NULL)
                                                                                                                                                                                                    OR D.SIGLE <> S.SIGLE OR (D.SIGLE IS NULL AND S.SIGLE IS NOT NULL) OR (D.SIGLE IS NOT NULL AND S.SIGLE IS NULL)
                                                                                                                                                                                                    OR D.TYPE_STRUCTURE_ID <> S.TYPE_STRUCTURE_ID OR (D.TYPE_STRUCTURE_ID IS NULL AND S.TYPE_STRUCTURE_ID IS NOT NULL) OR (D.TYPE_STRUCTURE_ID IS NOT NULL AND S.TYPE_STRUCTURE_ID IS NULL)
                                                                                                                                                                                                 ) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/

create view V_DIFF_ETABLISSEMENT as
  select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."CODE",diff."STRUCTURE_ID",diff."U_CODE",diff."U_STRUCTURE_ID" from (SELECT
                                                                                                                                                        COALESCE( D.id, S.id ) id,
                                                                                                                                                        COALESCE( S.source_id, D.source_id ) source_id,
                                                                                                                                                        COALESCE( S.source_code, D.source_code ) source_code,
                                                                                                                                                        CASE
                                                                                                                                                        WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
                                                                                                                                                        WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
                                                                                                                                                        WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
                                                                                                                                                        WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.CODE ELSE S.CODE END CODE,
                                                                                                                                                        CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.STRUCTURE_ID ELSE S.STRUCTURE_ID END STRUCTURE_ID,
                                                                                                                                                        CASE WHEN D.CODE <> S.CODE OR (D.CODE IS NULL AND S.CODE IS NOT NULL) OR (D.CODE IS NOT NULL AND S.CODE IS NULL) THEN 1 ELSE 0 END U_CODE,
                                                                                                                                                        CASE WHEN D.STRUCTURE_ID <> S.STRUCTURE_ID OR (D.STRUCTURE_ID IS NULL AND S.STRUCTURE_ID IS NOT NULL) OR (D.STRUCTURE_ID IS NOT NULL AND S.STRUCTURE_ID IS NULL) THEN 1 ELSE 0 END U_STRUCTURE_ID
                                                                                                                                                      FROM
                                                                                                                                                        ETABLISSEMENT D
                                                                                                                                                        FULL JOIN SRC_ETABLISSEMENT S ON S.source_id = D.source_id AND S.source_code = D.source_code
                                                                                                                                                      WHERE
                                                                                                                                                        (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
                                                                                                                                                        OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
                                                                                                                                                        OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
                                                                                                                                                        OR D.CODE <> S.CODE OR (D.CODE IS NULL AND S.CODE IS NOT NULL) OR (D.CODE IS NOT NULL AND S.CODE IS NULL)
                                                                                                                                                        OR D.STRUCTURE_ID <> S.STRUCTURE_ID OR (D.STRUCTURE_ID IS NULL AND S.STRUCTURE_ID IS NOT NULL) OR (D.STRUCTURE_ID IS NOT NULL AND S.STRUCTURE_ID IS NULL)
                                                                                                                                                     ) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/

create view V_DIFF_ECOLE_DOCT as
  select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."STRUCTURE_ID",diff."U_STRUCTURE_ID" from (SELECT
                                                                                                                              COALESCE( D.id, S.id ) id,
                                                                                                                              COALESCE( S.source_id, D.source_id ) source_id,
                                                                                                                              COALESCE( S.source_code, D.source_code ) source_code,
                                                                                                                              CASE
                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
                                                                                                                              WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
                                                                                                                              WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
                                                                                                                              CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.STRUCTURE_ID ELSE S.STRUCTURE_ID END STRUCTURE_ID,
                                                                                                                              CASE WHEN D.STRUCTURE_ID <> S.STRUCTURE_ID OR (D.STRUCTURE_ID IS NULL AND S.STRUCTURE_ID IS NOT NULL) OR (D.STRUCTURE_ID IS NOT NULL AND S.STRUCTURE_ID IS NULL) THEN 1 ELSE 0 END U_STRUCTURE_ID
                                                                                                                            FROM
                                                                                                                              ECOLE_DOCT D
                                                                                                                              FULL JOIN SRC_ECOLE_DOCT S ON S.source_id = D.source_id AND S.source_code = D.source_code
                                                                                                                            WHERE
                                                                                                                              (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
                                                                                                                              OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
                                                                                                                              OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
                                                                                                                              OR D.STRUCTURE_ID <> S.STRUCTURE_ID OR (D.STRUCTURE_ID IS NULL AND S.STRUCTURE_ID IS NOT NULL) OR (D.STRUCTURE_ID IS NOT NULL AND S.STRUCTURE_ID IS NULL)
                                                                                                                           ) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/

create PACKAGE UNICAEN_IMPORT AS

  PROCEDURE set_current_user(p_current_user IN INTEGER);
  FUNCTION get_current_user return INTEGER;

  FUNCTION get_current_annee RETURN INTEGER;
  PROCEDURE set_current_annee (p_current_annee INTEGER);

  FUNCTION get_sql_criterion( table_name varchar2, sql_criterion VARCHAR2 ) RETURN CLOB;
  PROCEDURE SYNC_LOG( message CLOB, table_name VARCHAR2 DEFAULT NULL, source_code VARCHAR2 DEFAULT NULL );

  -- AUTOMATIC GENERATION --

  PROCEDURE MAJ_INDIVIDU(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_THESE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_ACTEUR(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_VARIABLE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_UNITE_RECH(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_STRUCTURE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_ROLE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_ETABLISSEMENT(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_ECOLE_DOCT(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');
  PROCEDURE MAJ_DOCTORANT(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '');

  -- END OF AUTOMATIC GENERATION --
END UNICAEN_IMPORT;
/

create PACKAGE BODY UNICAEN_IMPORT AS

  v_current_user INTEGER;
  v_current_annee INTEGER;



  FUNCTION get_current_user RETURN INTEGER IS
    BEGIN
      IF v_current_user IS NULL THEN
        v_current_user := 1; -- A remplacer par l'utilisateur (ID de la table USER) qui sera le créateur ou le modificateur des données
      END IF;
      RETURN v_current_user;
    END get_current_user;

  PROCEDURE set_current_user (p_current_user INTEGER) is
    BEGIN
      v_current_user := p_current_user;
    END set_current_user;



  FUNCTION get_current_annee RETURN INTEGER IS
    BEGIN
      IF v_current_annee IS NULL THEN
        v_current_annee := NULL; -- A remplacer par l'année d'import souhaitée (si vous avez de l'annualisation de prévue dans votre BDD)
      END IF;
      RETURN v_current_annee;
    END get_current_annee;

  PROCEDURE set_current_annee (p_current_annee INTEGER) IS
    BEGIN
      v_current_annee := p_current_annee;
    END set_current_annee;



  FUNCTION get_sql_criterion( table_name varchar2, sql_criterion VARCHAR2 ) RETURN CLOB IS
    BEGIN
      IF sql_criterion <> '' OR sql_criterion IS NOT NULL THEN
        RETURN sql_criterion;
      END IF;
      RETURN '';
      /* Exemple d'usage :

      RETURN CASE table_name
        WHEN 'INTERVENANT' THEN -- Met à jour toutes les données sauf le statut, qui sera traité à part
          'WHERE IMPORT_ACTION IN (''delete'',''update'',''undelete'')'

        WHEN 'AFFECTATION_RECHERCHE' THEN
          'WHERE INTERVENANT_ID IS NOT NULL'

        WHEN 'ADRESSE_INTERVENANT' THEN
          'WHERE INTERVENANT_ID IS NOT NULL'

        WHEN 'ELEMENT_TAUX_REGIMES' THEN
          'WHERE IMPORT_ACTION IN (''delete'',''insert'',''undelete'')'

        ELSE
          ''
      END;*/
    END;



  PROCEDURE SYNC_LOG( message CLOB, table_name VARCHAR2 DEFAULT NULL, source_code VARCHAR2 DEFAULT NULL ) IS
    BEGIN
      INSERT INTO SYNC_LOG("ID","DATE_SYNC","MESSAGE","TABLE_NAME","SOURCE_CODE") VALUES (SYNC_LOG_ID_SEQ.NEXTVAL, SYSDATE, message,table_name,source_code);
    END SYNC_LOG;



  FUNCTION IN_COLUMN_LIST( VALEUR VARCHAR2, CHAMPS CLOB ) RETURN NUMERIC IS
    BEGIN
      IF REGEXP_LIKE(CHAMPS, '(^|,)[ \t\r\n\v\f]*' || VALEUR || '[ \t\r\n\v\f]*(,|$)') THEN RETURN 1; END IF;
      RETURN 0;
    END;


  -- AUTOMATIC GENERATION --

  PROCEDURE MAJ_INDIVIDU(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '') IS
    TYPE r_cursor IS REF CURSOR;
    sql_query CLOB;
    diff_cur r_cursor;
    diff_row V_DIFF_INDIVIDU%ROWTYPE;
    BEGIN
      sql_query := 'SELECT V_DIFF_INDIVIDU.* FROM V_DIFF_INDIVIDU ' || get_sql_criterion('INDIVIDU',SQL_CRITERION);
      OPEN diff_cur FOR sql_query;
      LOOP
        FETCH diff_cur INTO diff_row; EXIT WHEN diff_cur%NOTFOUND;
        BEGIN

          CASE diff_row.import_action
            WHEN 'insert' THEN
            INSERT INTO INDIVIDU
            ( id, CIVILITE,DATE_NAISSANCE,EMAIL,NATIONALITE,NOM_PATRONYMIQUE,NOM_USUEL,PRENOM1,PRENOM2,PRENOM3,TYPE, source_id, source_code, histo_createur_id, histo_modificateur_id )
            VALUES
              ( COALESCE(diff_row.id,INDIVIDU_ID_SEQ.NEXTVAL), diff_row.CIVILITE,diff_row.DATE_NAISSANCE,diff_row.EMAIL,diff_row.NATIONALITE,diff_row.NOM_PATRONYMIQUE,diff_row.NOM_USUEL,diff_row.PRENOM1,diff_row.PRENOM2,diff_row.PRENOM3,diff_row.TYPE, diff_row.source_id, diff_row.source_code, get_current_user, get_current_user );

            WHEN 'update' THEN
            IF (diff_row.u_CIVILITE = 1 AND IN_COLUMN_LIST('CIVILITE',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET CIVILITE = diff_row.CIVILITE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_NAISSANCE = 1 AND IN_COLUMN_LIST('DATE_NAISSANCE',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET DATE_NAISSANCE = diff_row.DATE_NAISSANCE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_EMAIL = 1 AND IN_COLUMN_LIST('EMAIL',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET EMAIL = diff_row.EMAIL WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_NATIONALITE = 1 AND IN_COLUMN_LIST('NATIONALITE',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET NATIONALITE = diff_row.NATIONALITE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_NOM_PATRONYMIQUE = 1 AND IN_COLUMN_LIST('NOM_PATRONYMIQUE',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET NOM_PATRONYMIQUE = diff_row.NOM_PATRONYMIQUE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_NOM_USUEL = 1 AND IN_COLUMN_LIST('NOM_USUEL',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET NOM_USUEL = diff_row.NOM_USUEL WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_PRENOM1 = 1 AND IN_COLUMN_LIST('PRENOM1',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET PRENOM1 = diff_row.PRENOM1 WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_PRENOM2 = 1 AND IN_COLUMN_LIST('PRENOM2',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET PRENOM2 = diff_row.PRENOM2 WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_PRENOM3 = 1 AND IN_COLUMN_LIST('PRENOM3',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET PRENOM3 = diff_row.PRENOM3 WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_TYPE = 1 AND IN_COLUMN_LIST('TYPE',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET TYPE = diff_row.TYPE WHERE ID = diff_row.id; END IF;

            WHEN 'delete' THEN
            UPDATE INDIVIDU SET histo_destruction = SYSDATE, histo_destructeur_id = get_current_user WHERE ID = diff_row.id;

            WHEN 'undelete' THEN
            IF (diff_row.u_CIVILITE = 1 AND IN_COLUMN_LIST('CIVILITE',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET CIVILITE = diff_row.CIVILITE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_NAISSANCE = 1 AND IN_COLUMN_LIST('DATE_NAISSANCE',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET DATE_NAISSANCE = diff_row.DATE_NAISSANCE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_EMAIL = 1 AND IN_COLUMN_LIST('EMAIL',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET EMAIL = diff_row.EMAIL WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_NATIONALITE = 1 AND IN_COLUMN_LIST('NATIONALITE',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET NATIONALITE = diff_row.NATIONALITE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_NOM_PATRONYMIQUE = 1 AND IN_COLUMN_LIST('NOM_PATRONYMIQUE',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET NOM_PATRONYMIQUE = diff_row.NOM_PATRONYMIQUE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_NOM_USUEL = 1 AND IN_COLUMN_LIST('NOM_USUEL',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET NOM_USUEL = diff_row.NOM_USUEL WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_PRENOM1 = 1 AND IN_COLUMN_LIST('PRENOM1',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET PRENOM1 = diff_row.PRENOM1 WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_PRENOM2 = 1 AND IN_COLUMN_LIST('PRENOM2',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET PRENOM2 = diff_row.PRENOM2 WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_PRENOM3 = 1 AND IN_COLUMN_LIST('PRENOM3',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET PRENOM3 = diff_row.PRENOM3 WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_TYPE = 1 AND IN_COLUMN_LIST('TYPE',IGNORE_UPD_COLS) = 0) THEN UPDATE INDIVIDU SET TYPE = diff_row.TYPE WHERE ID = diff_row.id; END IF;
            UPDATE INDIVIDU SET histo_destruction = NULL, histo_destructeur_id = NULL WHERE ID = diff_row.id;

          END CASE;

          EXCEPTION WHEN OTHERS THEN
          UNICAEN_IMPORT.SYNC_LOG( SQLERRM, 'INDIVIDU', diff_row.source_code );
        END;
      END LOOP;
      CLOSE diff_cur;

    END MAJ_INDIVIDU;



  PROCEDURE MAJ_THESE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '') IS
    TYPE r_cursor IS REF CURSOR;
    sql_query CLOB;
    diff_cur r_cursor;
    diff_row V_DIFF_THESE%ROWTYPE;
    BEGIN
      sql_query := 'SELECT V_DIFF_THESE.* FROM V_DIFF_THESE ' || get_sql_criterion('THESE',SQL_CRITERION);
      OPEN diff_cur FOR sql_query;
      LOOP
        FETCH diff_cur INTO diff_row; EXIT WHEN diff_cur%NOTFOUND;
        BEGIN

          CASE diff_row.import_action
            WHEN 'insert' THEN
            INSERT INTO THESE
            ( id, CORREC_AUTORISEE,DATE_AUTORIS_SOUTENANCE,DATE_FIN_CONFID,DATE_PREM_INSC,DATE_PREV_SOUTENANCE,DATE_SOUTENANCE,DOCTORANT_ID,ECOLE_DOCT_ID,ETABLISSEMENT_ID,ETAT_THESE,LIB_DISC,LIB_ETAB_COTUT,LIB_PAYS_COTUT,RESULTAT,SOUTENANCE_AUTORIS,TEM_AVENANT_COTUT,TITRE,UNITE_RECH_ID, source_id, source_code, histo_createur_id, histo_modificateur_id )
            VALUES
              ( COALESCE(diff_row.id,THESE_ID_SEQ.NEXTVAL), diff_row.CORREC_AUTORISEE,diff_row.DATE_AUTORIS_SOUTENANCE,diff_row.DATE_FIN_CONFID,diff_row.DATE_PREM_INSC,diff_row.DATE_PREV_SOUTENANCE,diff_row.DATE_SOUTENANCE,diff_row.DOCTORANT_ID,diff_row.ECOLE_DOCT_ID,diff_row.ETABLISSEMENT_ID,diff_row.ETAT_THESE,diff_row.LIB_DISC,diff_row.LIB_ETAB_COTUT,diff_row.LIB_PAYS_COTUT,diff_row.RESULTAT,diff_row.SOUTENANCE_AUTORIS,diff_row.TEM_AVENANT_COTUT,diff_row.TITRE,diff_row.UNITE_RECH_ID, diff_row.source_id, diff_row.source_code, get_current_user, get_current_user );

            WHEN 'update' THEN
            IF (diff_row.u_CORREC_AUTORISEE = 1 AND IN_COLUMN_LIST('CORREC_AUTORISEE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET CORREC_AUTORISEE = diff_row.CORREC_AUTORISEE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_AUTORIS_SOUTENANCE = 1 AND IN_COLUMN_LIST('DATE_AUTORIS_SOUTENANCE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DATE_AUTORIS_SOUTENANCE = diff_row.DATE_AUTORIS_SOUTENANCE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_FIN_CONFID = 1 AND IN_COLUMN_LIST('DATE_FIN_CONFID',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DATE_FIN_CONFID = diff_row.DATE_FIN_CONFID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_PREM_INSC = 1 AND IN_COLUMN_LIST('DATE_PREM_INSC',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DATE_PREM_INSC = diff_row.DATE_PREM_INSC WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_PREV_SOUTENANCE = 1 AND IN_COLUMN_LIST('DATE_PREV_SOUTENANCE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DATE_PREV_SOUTENANCE = diff_row.DATE_PREV_SOUTENANCE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_SOUTENANCE = 1 AND IN_COLUMN_LIST('DATE_SOUTENANCE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DATE_SOUTENANCE = diff_row.DATE_SOUTENANCE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DOCTORANT_ID = 1 AND IN_COLUMN_LIST('DOCTORANT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DOCTORANT_ID = diff_row.DOCTORANT_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ECOLE_DOCT_ID = 1 AND IN_COLUMN_LIST('ECOLE_DOCT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET ECOLE_DOCT_ID = diff_row.ECOLE_DOCT_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ETABLISSEMENT_ID = 1 AND IN_COLUMN_LIST('ETABLISSEMENT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET ETABLISSEMENT_ID = diff_row.ETABLISSEMENT_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ETAT_THESE = 1 AND IN_COLUMN_LIST('ETAT_THESE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET ETAT_THESE = diff_row.ETAT_THESE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_LIB_DISC = 1 AND IN_COLUMN_LIST('LIB_DISC',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET LIB_DISC = diff_row.LIB_DISC WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_LIB_ETAB_COTUT = 1 AND IN_COLUMN_LIST('LIB_ETAB_COTUT',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET LIB_ETAB_COTUT = diff_row.LIB_ETAB_COTUT WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_LIB_PAYS_COTUT = 1 AND IN_COLUMN_LIST('LIB_PAYS_COTUT',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET LIB_PAYS_COTUT = diff_row.LIB_PAYS_COTUT WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_RESULTAT = 1 AND IN_COLUMN_LIST('RESULTAT',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET RESULTAT = diff_row.RESULTAT WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_SOUTENANCE_AUTORIS = 1 AND IN_COLUMN_LIST('SOUTENANCE_AUTORIS',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET SOUTENANCE_AUTORIS = diff_row.SOUTENANCE_AUTORIS WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_TEM_AVENANT_COTUT = 1 AND IN_COLUMN_LIST('TEM_AVENANT_COTUT',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET TEM_AVENANT_COTUT = diff_row.TEM_AVENANT_COTUT WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_TITRE = 1 AND IN_COLUMN_LIST('TITRE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET TITRE = diff_row.TITRE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_UNITE_RECH_ID = 1 AND IN_COLUMN_LIST('UNITE_RECH_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET UNITE_RECH_ID = diff_row.UNITE_RECH_ID WHERE ID = diff_row.id; END IF;

            WHEN 'delete' THEN
            UPDATE THESE SET histo_destruction = SYSDATE, histo_destructeur_id = get_current_user WHERE ID = diff_row.id;

            WHEN 'undelete' THEN
            IF (diff_row.u_CORREC_AUTORISEE = 1 AND IN_COLUMN_LIST('CORREC_AUTORISEE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET CORREC_AUTORISEE = diff_row.CORREC_AUTORISEE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_AUTORIS_SOUTENANCE = 1 AND IN_COLUMN_LIST('DATE_AUTORIS_SOUTENANCE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DATE_AUTORIS_SOUTENANCE = diff_row.DATE_AUTORIS_SOUTENANCE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_FIN_CONFID = 1 AND IN_COLUMN_LIST('DATE_FIN_CONFID',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DATE_FIN_CONFID = diff_row.DATE_FIN_CONFID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_PREM_INSC = 1 AND IN_COLUMN_LIST('DATE_PREM_INSC',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DATE_PREM_INSC = diff_row.DATE_PREM_INSC WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_PREV_SOUTENANCE = 1 AND IN_COLUMN_LIST('DATE_PREV_SOUTENANCE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DATE_PREV_SOUTENANCE = diff_row.DATE_PREV_SOUTENANCE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_SOUTENANCE = 1 AND IN_COLUMN_LIST('DATE_SOUTENANCE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DATE_SOUTENANCE = diff_row.DATE_SOUTENANCE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DOCTORANT_ID = 1 AND IN_COLUMN_LIST('DOCTORANT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET DOCTORANT_ID = diff_row.DOCTORANT_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ECOLE_DOCT_ID = 1 AND IN_COLUMN_LIST('ECOLE_DOCT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET ECOLE_DOCT_ID = diff_row.ECOLE_DOCT_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ETABLISSEMENT_ID = 1 AND IN_COLUMN_LIST('ETABLISSEMENT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET ETABLISSEMENT_ID = diff_row.ETABLISSEMENT_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ETAT_THESE = 1 AND IN_COLUMN_LIST('ETAT_THESE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET ETAT_THESE = diff_row.ETAT_THESE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_LIB_DISC = 1 AND IN_COLUMN_LIST('LIB_DISC',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET LIB_DISC = diff_row.LIB_DISC WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_LIB_ETAB_COTUT = 1 AND IN_COLUMN_LIST('LIB_ETAB_COTUT',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET LIB_ETAB_COTUT = diff_row.LIB_ETAB_COTUT WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_LIB_PAYS_COTUT = 1 AND IN_COLUMN_LIST('LIB_PAYS_COTUT',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET LIB_PAYS_COTUT = diff_row.LIB_PAYS_COTUT WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_RESULTAT = 1 AND IN_COLUMN_LIST('RESULTAT',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET RESULTAT = diff_row.RESULTAT WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_SOUTENANCE_AUTORIS = 1 AND IN_COLUMN_LIST('SOUTENANCE_AUTORIS',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET SOUTENANCE_AUTORIS = diff_row.SOUTENANCE_AUTORIS WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_TEM_AVENANT_COTUT = 1 AND IN_COLUMN_LIST('TEM_AVENANT_COTUT',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET TEM_AVENANT_COTUT = diff_row.TEM_AVENANT_COTUT WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_TITRE = 1 AND IN_COLUMN_LIST('TITRE',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET TITRE = diff_row.TITRE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_UNITE_RECH_ID = 1 AND IN_COLUMN_LIST('UNITE_RECH_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE THESE SET UNITE_RECH_ID = diff_row.UNITE_RECH_ID WHERE ID = diff_row.id; END IF;
            UPDATE THESE SET histo_destruction = NULL, histo_destructeur_id = NULL WHERE ID = diff_row.id;

          END CASE;

          EXCEPTION WHEN OTHERS THEN
          UNICAEN_IMPORT.SYNC_LOG( SQLERRM, 'THESE', diff_row.source_code );
        END;
      END LOOP;
      CLOSE diff_cur;

    END MAJ_THESE;



  PROCEDURE MAJ_ACTEUR(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '') IS
    TYPE r_cursor IS REF CURSOR;
    sql_query CLOB;
    diff_cur r_cursor;
    diff_row V_DIFF_ACTEUR%ROWTYPE;
    BEGIN
      sql_query := 'SELECT V_DIFF_ACTEUR.* FROM V_DIFF_ACTEUR ' || get_sql_criterion('ACTEUR',SQL_CRITERION);
      OPEN diff_cur FOR sql_query;
      LOOP
        FETCH diff_cur INTO diff_row; EXIT WHEN diff_cur%NOTFOUND;
        BEGIN

          CASE diff_row.import_action
            WHEN 'insert' THEN
            INSERT INTO ACTEUR
            ( id, ETABLISSEMENT,INDIVIDU_ID,LIB_ROLE_COMPL,QUALITE,ROLE_ID,THESE_ID, source_id, source_code, histo_createur_id, histo_modificateur_id )
            VALUES
              ( COALESCE(diff_row.id,ACTEUR_ID_SEQ.NEXTVAL), diff_row.ETABLISSEMENT,diff_row.INDIVIDU_ID,diff_row.LIB_ROLE_COMPL,diff_row.QUALITE,diff_row.ROLE_ID,diff_row.THESE_ID, diff_row.source_id, diff_row.source_code, get_current_user, get_current_user );

            WHEN 'update' THEN
            IF (diff_row.u_ETABLISSEMENT = 1 AND IN_COLUMN_LIST('ETABLISSEMENT',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET ETABLISSEMENT = diff_row.ETABLISSEMENT WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_INDIVIDU_ID = 1 AND IN_COLUMN_LIST('INDIVIDU_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET INDIVIDU_ID = diff_row.INDIVIDU_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_LIB_ROLE_COMPL = 1 AND IN_COLUMN_LIST('LIB_ROLE_COMPL',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET LIB_ROLE_COMPL = diff_row.LIB_ROLE_COMPL WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_QUALITE = 1 AND IN_COLUMN_LIST('QUALITE',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET QUALITE = diff_row.QUALITE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ROLE_ID = 1 AND IN_COLUMN_LIST('ROLE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET ROLE_ID = diff_row.ROLE_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_THESE_ID = 1 AND IN_COLUMN_LIST('THESE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET THESE_ID = diff_row.THESE_ID WHERE ID = diff_row.id; END IF;

            WHEN 'delete' THEN
            UPDATE ACTEUR SET histo_destruction = SYSDATE, histo_destructeur_id = get_current_user WHERE ID = diff_row.id;

            WHEN 'undelete' THEN
            IF (diff_row.u_ETABLISSEMENT = 1 AND IN_COLUMN_LIST('ETABLISSEMENT',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET ETABLISSEMENT = diff_row.ETABLISSEMENT WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_INDIVIDU_ID = 1 AND IN_COLUMN_LIST('INDIVIDU_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET INDIVIDU_ID = diff_row.INDIVIDU_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_LIB_ROLE_COMPL = 1 AND IN_COLUMN_LIST('LIB_ROLE_COMPL',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET LIB_ROLE_COMPL = diff_row.LIB_ROLE_COMPL WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_QUALITE = 1 AND IN_COLUMN_LIST('QUALITE',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET QUALITE = diff_row.QUALITE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ROLE_ID = 1 AND IN_COLUMN_LIST('ROLE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET ROLE_ID = diff_row.ROLE_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_THESE_ID = 1 AND IN_COLUMN_LIST('THESE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ACTEUR SET THESE_ID = diff_row.THESE_ID WHERE ID = diff_row.id; END IF;
            UPDATE ACTEUR SET histo_destruction = NULL, histo_destructeur_id = NULL WHERE ID = diff_row.id;

          END CASE;

          EXCEPTION WHEN OTHERS THEN
          UNICAEN_IMPORT.SYNC_LOG( SQLERRM, 'ACTEUR', diff_row.source_code );
        END;
      END LOOP;
      CLOSE diff_cur;

    END MAJ_ACTEUR;



  PROCEDURE MAJ_VARIABLE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '') IS
    TYPE r_cursor IS REF CURSOR;
    sql_query CLOB;
    diff_cur r_cursor;
    diff_row V_DIFF_VARIABLE%ROWTYPE;
    BEGIN
      sql_query := 'SELECT V_DIFF_VARIABLE.* FROM V_DIFF_VARIABLE ' || get_sql_criterion('VARIABLE',SQL_CRITERION);
      OPEN diff_cur FOR sql_query;
      LOOP
        FETCH diff_cur INTO diff_row; EXIT WHEN diff_cur%NOTFOUND;
        BEGIN

          CASE diff_row.import_action
            WHEN 'insert' THEN
            INSERT INTO VARIABLE
            ( id, CODE,DATE_DEB_VALIDITE,DATE_FIN_VALIDITE,DESCRIPTION,ETABLISSEMENT_ID,VALEUR, source_id, source_code, histo_createur_id, histo_modificateur_id )
            VALUES
              ( COALESCE(diff_row.id,VARIABLE_ID_SEQ.NEXTVAL), diff_row.CODE,diff_row.DATE_DEB_VALIDITE,diff_row.DATE_FIN_VALIDITE,diff_row.DESCRIPTION,diff_row.ETABLISSEMENT_ID,diff_row.VALEUR, diff_row.source_id, diff_row.source_code, get_current_user, get_current_user );

            WHEN 'update' THEN
            IF (diff_row.u_CODE = 1 AND IN_COLUMN_LIST('CODE',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET CODE = diff_row.CODE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_DEB_VALIDITE = 1 AND IN_COLUMN_LIST('DATE_DEB_VALIDITE',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET DATE_DEB_VALIDITE = diff_row.DATE_DEB_VALIDITE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_FIN_VALIDITE = 1 AND IN_COLUMN_LIST('DATE_FIN_VALIDITE',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET DATE_FIN_VALIDITE = diff_row.DATE_FIN_VALIDITE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DESCRIPTION = 1 AND IN_COLUMN_LIST('DESCRIPTION',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET DESCRIPTION = diff_row.DESCRIPTION WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ETABLISSEMENT_ID = 1 AND IN_COLUMN_LIST('ETABLISSEMENT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET ETABLISSEMENT_ID = diff_row.ETABLISSEMENT_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_VALEUR = 1 AND IN_COLUMN_LIST('VALEUR',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET VALEUR = diff_row.VALEUR WHERE ID = diff_row.id; END IF;

            WHEN 'delete' THEN
            UPDATE VARIABLE SET histo_destruction = SYSDATE, histo_destructeur_id = get_current_user WHERE ID = diff_row.id;

            WHEN 'undelete' THEN
            IF (diff_row.u_CODE = 1 AND IN_COLUMN_LIST('CODE',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET CODE = diff_row.CODE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_DEB_VALIDITE = 1 AND IN_COLUMN_LIST('DATE_DEB_VALIDITE',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET DATE_DEB_VALIDITE = diff_row.DATE_DEB_VALIDITE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DATE_FIN_VALIDITE = 1 AND IN_COLUMN_LIST('DATE_FIN_VALIDITE',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET DATE_FIN_VALIDITE = diff_row.DATE_FIN_VALIDITE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_DESCRIPTION = 1 AND IN_COLUMN_LIST('DESCRIPTION',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET DESCRIPTION = diff_row.DESCRIPTION WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ETABLISSEMENT_ID = 1 AND IN_COLUMN_LIST('ETABLISSEMENT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET ETABLISSEMENT_ID = diff_row.ETABLISSEMENT_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_VALEUR = 1 AND IN_COLUMN_LIST('VALEUR',IGNORE_UPD_COLS) = 0) THEN UPDATE VARIABLE SET VALEUR = diff_row.VALEUR WHERE ID = diff_row.id; END IF;
            UPDATE VARIABLE SET histo_destruction = NULL, histo_destructeur_id = NULL WHERE ID = diff_row.id;

          END CASE;

          EXCEPTION WHEN OTHERS THEN
          UNICAEN_IMPORT.SYNC_LOG( SQLERRM, 'VARIABLE', diff_row.source_code );
        END;
      END LOOP;
      CLOSE diff_cur;

    END MAJ_VARIABLE;



  PROCEDURE MAJ_UNITE_RECH(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '') IS
    TYPE r_cursor IS REF CURSOR;
    sql_query CLOB;
    diff_cur r_cursor;
    diff_row V_DIFF_UNITE_RECH%ROWTYPE;
    BEGIN
      sql_query := 'SELECT V_DIFF_UNITE_RECH.* FROM V_DIFF_UNITE_RECH ' || get_sql_criterion('UNITE_RECH',SQL_CRITERION);
      OPEN diff_cur FOR sql_query;
      LOOP
        FETCH diff_cur INTO diff_row; EXIT WHEN diff_cur%NOTFOUND;
        BEGIN

          CASE diff_row.import_action
            WHEN 'insert' THEN
            INSERT INTO UNITE_RECH
            ( id, STRUCTURE_ID, source_id, source_code, histo_createur_id, histo_modificateur_id )
            VALUES
              ( COALESCE(diff_row.id,UNITE_RECH_ID_SEQ.NEXTVAL), diff_row.STRUCTURE_ID, diff_row.source_id, diff_row.source_code, get_current_user, get_current_user );

            WHEN 'update' THEN
            IF (diff_row.u_STRUCTURE_ID = 1 AND IN_COLUMN_LIST('STRUCTURE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE UNITE_RECH SET STRUCTURE_ID = diff_row.STRUCTURE_ID WHERE ID = diff_row.id; END IF;

            WHEN 'delete' THEN
            UPDATE UNITE_RECH SET histo_destruction = SYSDATE, histo_destructeur_id = get_current_user WHERE ID = diff_row.id;

            WHEN 'undelete' THEN
            IF (diff_row.u_STRUCTURE_ID = 1 AND IN_COLUMN_LIST('STRUCTURE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE UNITE_RECH SET STRUCTURE_ID = diff_row.STRUCTURE_ID WHERE ID = diff_row.id; END IF;
            UPDATE UNITE_RECH SET histo_destruction = NULL, histo_destructeur_id = NULL WHERE ID = diff_row.id;

          END CASE;

          EXCEPTION WHEN OTHERS THEN
          UNICAEN_IMPORT.SYNC_LOG( SQLERRM, 'UNITE_RECH', diff_row.source_code );
        END;
      END LOOP;
      CLOSE diff_cur;

    END MAJ_UNITE_RECH;



  PROCEDURE MAJ_STRUCTURE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '') IS
    TYPE r_cursor IS REF CURSOR;
    sql_query CLOB;
    diff_cur r_cursor;
    diff_row V_DIFF_STRUCTURE%ROWTYPE;
    BEGIN
      sql_query := 'SELECT V_DIFF_STRUCTURE.* FROM V_DIFF_STRUCTURE ' || get_sql_criterion('STRUCTURE',SQL_CRITERION);
      OPEN diff_cur FOR sql_query;
      LOOP
        FETCH diff_cur INTO diff_row; EXIT WHEN diff_cur%NOTFOUND;
        BEGIN

          CASE diff_row.import_action
            WHEN 'insert' THEN
            INSERT INTO STRUCTURE
            ( id, LIBELLE,SIGLE,TYPE_STRUCTURE_ID, source_id, source_code, histo_createur_id, histo_modificateur_id )
            VALUES
              ( COALESCE(diff_row.id,STRUCTURE_ID_SEQ.NEXTVAL), diff_row.LIBELLE,diff_row.SIGLE,diff_row.TYPE_STRUCTURE_ID, diff_row.source_id, diff_row.source_code, get_current_user, get_current_user );

            WHEN 'update' THEN
            IF (diff_row.u_LIBELLE = 1 AND IN_COLUMN_LIST('LIBELLE',IGNORE_UPD_COLS) = 0) THEN UPDATE STRUCTURE SET LIBELLE = diff_row.LIBELLE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_SIGLE = 1 AND IN_COLUMN_LIST('SIGLE',IGNORE_UPD_COLS) = 0) THEN UPDATE STRUCTURE SET SIGLE = diff_row.SIGLE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_TYPE_STRUCTURE_ID = 1 AND IN_COLUMN_LIST('TYPE_STRUCTURE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE STRUCTURE SET TYPE_STRUCTURE_ID = diff_row.TYPE_STRUCTURE_ID WHERE ID = diff_row.id; END IF;

            WHEN 'delete' THEN
            UPDATE STRUCTURE SET histo_destruction = SYSDATE, histo_destructeur_id = get_current_user WHERE ID = diff_row.id;

            WHEN 'undelete' THEN
            IF (diff_row.u_LIBELLE = 1 AND IN_COLUMN_LIST('LIBELLE',IGNORE_UPD_COLS) = 0) THEN UPDATE STRUCTURE SET LIBELLE = diff_row.LIBELLE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_SIGLE = 1 AND IN_COLUMN_LIST('SIGLE',IGNORE_UPD_COLS) = 0) THEN UPDATE STRUCTURE SET SIGLE = diff_row.SIGLE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_TYPE_STRUCTURE_ID = 1 AND IN_COLUMN_LIST('TYPE_STRUCTURE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE STRUCTURE SET TYPE_STRUCTURE_ID = diff_row.TYPE_STRUCTURE_ID WHERE ID = diff_row.id; END IF;
            UPDATE STRUCTURE SET histo_destruction = NULL, histo_destructeur_id = NULL WHERE ID = diff_row.id;

          END CASE;

          EXCEPTION WHEN OTHERS THEN
          UNICAEN_IMPORT.SYNC_LOG( SQLERRM, 'STRUCTURE', diff_row.source_code );
        END;
      END LOOP;
      CLOSE diff_cur;

    END MAJ_STRUCTURE;



  PROCEDURE MAJ_ROLE(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '') IS
    TYPE r_cursor IS REF CURSOR;
    sql_query CLOB;
    diff_cur r_cursor;
    diff_row V_DIFF_ROLE%ROWTYPE;
    BEGIN
      sql_query := 'SELECT V_DIFF_ROLE.* FROM V_DIFF_ROLE ' || get_sql_criterion('ROLE',SQL_CRITERION);
      OPEN diff_cur FOR sql_query;
      LOOP
        FETCH diff_cur INTO diff_row; EXIT WHEN diff_cur%NOTFOUND;
        BEGIN

          CASE diff_row.import_action
            WHEN 'insert' THEN
            INSERT INTO ROLE
            ( id, CODE,LIBELLE,ROLE_ID,STRUCTURE_ID,THESE_DEP,TYPE_STRUCTURE_DEPENDANT_ID, source_id, source_code, histo_createur_id, histo_modificateur_id )
            VALUES
              ( COALESCE(diff_row.id,ROLE_ID_SEQ.NEXTVAL), diff_row.CODE,diff_row.LIBELLE,diff_row.ROLE_ID,diff_row.STRUCTURE_ID,diff_row.THESE_DEP,diff_row.TYPE_STRUCTURE_DEPENDANT_ID, diff_row.source_id, diff_row.source_code, get_current_user, get_current_user );

            WHEN 'update' THEN
            IF (diff_row.u_CODE = 1 AND IN_COLUMN_LIST('CODE',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET CODE = diff_row.CODE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_LIBELLE = 1 AND IN_COLUMN_LIST('LIBELLE',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET LIBELLE = diff_row.LIBELLE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ROLE_ID = 1 AND IN_COLUMN_LIST('ROLE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET ROLE_ID = diff_row.ROLE_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_STRUCTURE_ID = 1 AND IN_COLUMN_LIST('STRUCTURE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET STRUCTURE_ID = diff_row.STRUCTURE_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_THESE_DEP = 1 AND IN_COLUMN_LIST('THESE_DEP',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET THESE_DEP = diff_row.THESE_DEP WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_TYPE_STRUCTURE_DEPENDANT_ID = 1 AND IN_COLUMN_LIST('TYPE_STRUCTURE_DEPENDANT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET TYPE_STRUCTURE_DEPENDANT_ID = diff_row.TYPE_STRUCTURE_DEPENDANT_ID WHERE ID = diff_row.id; END IF;

            WHEN 'delete' THEN
            UPDATE ROLE SET histo_destruction = SYSDATE, histo_destructeur_id = get_current_user WHERE ID = diff_row.id;

            WHEN 'undelete' THEN
            IF (diff_row.u_CODE = 1 AND IN_COLUMN_LIST('CODE',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET CODE = diff_row.CODE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_LIBELLE = 1 AND IN_COLUMN_LIST('LIBELLE',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET LIBELLE = diff_row.LIBELLE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_ROLE_ID = 1 AND IN_COLUMN_LIST('ROLE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET ROLE_ID = diff_row.ROLE_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_STRUCTURE_ID = 1 AND IN_COLUMN_LIST('STRUCTURE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET STRUCTURE_ID = diff_row.STRUCTURE_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_THESE_DEP = 1 AND IN_COLUMN_LIST('THESE_DEP',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET THESE_DEP = diff_row.THESE_DEP WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_TYPE_STRUCTURE_DEPENDANT_ID = 1 AND IN_COLUMN_LIST('TYPE_STRUCTURE_DEPENDANT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ROLE SET TYPE_STRUCTURE_DEPENDANT_ID = diff_row.TYPE_STRUCTURE_DEPENDANT_ID WHERE ID = diff_row.id; END IF;
            UPDATE ROLE SET histo_destruction = NULL, histo_destructeur_id = NULL WHERE ID = diff_row.id;

          END CASE;

          EXCEPTION WHEN OTHERS THEN
          UNICAEN_IMPORT.SYNC_LOG( SQLERRM, 'ROLE', diff_row.source_code );
        END;
      END LOOP;
      CLOSE diff_cur;

    END MAJ_ROLE;



  PROCEDURE MAJ_ETABLISSEMENT(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '') IS
    TYPE r_cursor IS REF CURSOR;
    sql_query CLOB;
    diff_cur r_cursor;
    diff_row V_DIFF_ETABLISSEMENT%ROWTYPE;
    BEGIN
      sql_query := 'SELECT V_DIFF_ETABLISSEMENT.* FROM V_DIFF_ETABLISSEMENT ' || get_sql_criterion('ETABLISSEMENT',SQL_CRITERION);
      OPEN diff_cur FOR sql_query;
      LOOP
        FETCH diff_cur INTO diff_row; EXIT WHEN diff_cur%NOTFOUND;
        BEGIN

          CASE diff_row.import_action
            WHEN 'insert' THEN
            INSERT INTO ETABLISSEMENT
            ( id, CODE,STRUCTURE_ID, source_id, source_code, histo_createur_id, histo_modificateur_id )
            VALUES
              ( COALESCE(diff_row.id,ETABLISSEMENT_ID_SEQ.NEXTVAL), diff_row.CODE,diff_row.STRUCTURE_ID, diff_row.source_id, diff_row.source_code, get_current_user, get_current_user );

            WHEN 'update' THEN
            IF (diff_row.u_CODE = 1 AND IN_COLUMN_LIST('CODE',IGNORE_UPD_COLS) = 0) THEN UPDATE ETABLISSEMENT SET CODE = diff_row.CODE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_STRUCTURE_ID = 1 AND IN_COLUMN_LIST('STRUCTURE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ETABLISSEMENT SET STRUCTURE_ID = diff_row.STRUCTURE_ID WHERE ID = diff_row.id; END IF;

            WHEN 'delete' THEN
            UPDATE ETABLISSEMENT SET histo_destruction = SYSDATE, histo_destructeur_id = get_current_user WHERE ID = diff_row.id;

            WHEN 'undelete' THEN
            IF (diff_row.u_CODE = 1 AND IN_COLUMN_LIST('CODE',IGNORE_UPD_COLS) = 0) THEN UPDATE ETABLISSEMENT SET CODE = diff_row.CODE WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_STRUCTURE_ID = 1 AND IN_COLUMN_LIST('STRUCTURE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ETABLISSEMENT SET STRUCTURE_ID = diff_row.STRUCTURE_ID WHERE ID = diff_row.id; END IF;
            UPDATE ETABLISSEMENT SET histo_destruction = NULL, histo_destructeur_id = NULL WHERE ID = diff_row.id;

          END CASE;

          EXCEPTION WHEN OTHERS THEN
          UNICAEN_IMPORT.SYNC_LOG( SQLERRM, 'ETABLISSEMENT', diff_row.source_code );
        END;
      END LOOP;
      CLOSE diff_cur;

    END MAJ_ETABLISSEMENT;



  PROCEDURE MAJ_ECOLE_DOCT(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '') IS
    TYPE r_cursor IS REF CURSOR;
    sql_query CLOB;
    diff_cur r_cursor;
    diff_row V_DIFF_ECOLE_DOCT%ROWTYPE;
    BEGIN
      sql_query := 'SELECT V_DIFF_ECOLE_DOCT.* FROM V_DIFF_ECOLE_DOCT ' || get_sql_criterion('ECOLE_DOCT',SQL_CRITERION);
      OPEN diff_cur FOR sql_query;
      LOOP
        FETCH diff_cur INTO diff_row; EXIT WHEN diff_cur%NOTFOUND;
        BEGIN

          CASE diff_row.import_action
            WHEN 'insert' THEN
            INSERT INTO ECOLE_DOCT
            ( id, STRUCTURE_ID, source_id, source_code, histo_createur_id, histo_modificateur_id )
            VALUES
              ( COALESCE(diff_row.id,ECOLE_DOCT_ID_SEQ.NEXTVAL), diff_row.STRUCTURE_ID, diff_row.source_id, diff_row.source_code, get_current_user, get_current_user );

            WHEN 'update' THEN
            IF (diff_row.u_STRUCTURE_ID = 1 AND IN_COLUMN_LIST('STRUCTURE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ECOLE_DOCT SET STRUCTURE_ID = diff_row.STRUCTURE_ID WHERE ID = diff_row.id; END IF;

            WHEN 'delete' THEN
            UPDATE ECOLE_DOCT SET histo_destruction = SYSDATE, histo_destructeur_id = get_current_user WHERE ID = diff_row.id;

            WHEN 'undelete' THEN
            IF (diff_row.u_STRUCTURE_ID = 1 AND IN_COLUMN_LIST('STRUCTURE_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE ECOLE_DOCT SET STRUCTURE_ID = diff_row.STRUCTURE_ID WHERE ID = diff_row.id; END IF;
            UPDATE ECOLE_DOCT SET histo_destruction = NULL, histo_destructeur_id = NULL WHERE ID = diff_row.id;

          END CASE;

          EXCEPTION WHEN OTHERS THEN
          UNICAEN_IMPORT.SYNC_LOG( SQLERRM, 'ECOLE_DOCT', diff_row.source_code );
        END;
      END LOOP;
      CLOSE diff_cur;

    END MAJ_ECOLE_DOCT;



  PROCEDURE MAJ_DOCTORANT(SQL_CRITERION CLOB DEFAULT '', IGNORE_UPD_COLS CLOB DEFAULT '') IS
    TYPE r_cursor IS REF CURSOR;
    sql_query CLOB;
    diff_cur r_cursor;
    diff_row V_DIFF_DOCTORANT%ROWTYPE;
    BEGIN
      sql_query := 'SELECT V_DIFF_DOCTORANT.* FROM V_DIFF_DOCTORANT ' || get_sql_criterion('DOCTORANT',SQL_CRITERION);
      OPEN diff_cur FOR sql_query;
      LOOP
        FETCH diff_cur INTO diff_row; EXIT WHEN diff_cur%NOTFOUND;
        BEGIN

          CASE diff_row.import_action
            WHEN 'insert' THEN
            INSERT INTO DOCTORANT
            ( id, ETABLISSEMENT_ID,INDIVIDU_ID, source_id, source_code, histo_createur_id, histo_modificateur_id )
            VALUES
              ( COALESCE(diff_row.id,DOCTORANT_ID_SEQ.NEXTVAL), diff_row.ETABLISSEMENT_ID,diff_row.INDIVIDU_ID, diff_row.source_id, diff_row.source_code, get_current_user, get_current_user );

            WHEN 'update' THEN
            IF (diff_row.u_ETABLISSEMENT_ID = 1 AND IN_COLUMN_LIST('ETABLISSEMENT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE DOCTORANT SET ETABLISSEMENT_ID = diff_row.ETABLISSEMENT_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_INDIVIDU_ID = 1 AND IN_COLUMN_LIST('INDIVIDU_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE DOCTORANT SET INDIVIDU_ID = diff_row.INDIVIDU_ID WHERE ID = diff_row.id; END IF;

            WHEN 'delete' THEN
            UPDATE DOCTORANT SET histo_destruction = SYSDATE, histo_destructeur_id = get_current_user WHERE ID = diff_row.id;

            WHEN 'undelete' THEN
            IF (diff_row.u_ETABLISSEMENT_ID = 1 AND IN_COLUMN_LIST('ETABLISSEMENT_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE DOCTORANT SET ETABLISSEMENT_ID = diff_row.ETABLISSEMENT_ID WHERE ID = diff_row.id; END IF;
            IF (diff_row.u_INDIVIDU_ID = 1 AND IN_COLUMN_LIST('INDIVIDU_ID',IGNORE_UPD_COLS) = 0) THEN UPDATE DOCTORANT SET INDIVIDU_ID = diff_row.INDIVIDU_ID WHERE ID = diff_row.id; END IF;
            UPDATE DOCTORANT SET histo_destruction = NULL, histo_destructeur_id = NULL WHERE ID = diff_row.id;

          END CASE;

          EXCEPTION WHEN OTHERS THEN
          UNICAEN_IMPORT.SYNC_LOG( SQLERRM, 'DOCTORANT', diff_row.source_code );
        END;
      END LOOP;
      CLOSE diff_cur;

    END MAJ_DOCTORANT;

  -- END OF AUTOMATIC GENERATION --
END UNICAEN_IMPORT;
/

create PACKAGE "APP_IMPORT" IS

  PROCEDURE REFRESH_MV( mview_name VARCHAR2 );
  PROCEDURE SYNC_TABLES;
  PROCEDURE SYNCHRONISATION;

  PROCEDURE STORE_OBSERV_RESULTS;

END APP_IMPORT;
/

create PACKAGE BODY "APP_IMPORT"
IS

  PROCEDURE REFRESH_MV( mview_name VARCHAR2 ) IS
    BEGIN
      DBMS_MVIEW.REFRESH(mview_name, 'C');
      EXCEPTION WHEN OTHERS THEN
      UNICAEN_IMPORT.SYNC_LOG( SQLERRM, mview_name );
    END;

  PROCEDURE SYNC_TABLES
  IS
    BEGIN
      -- mise à jour des tables à partir des vues sources
      -- NB: l'ordre importe !
      UNICAEN_IMPORT.MAJ_INDIVIDU();
      UNICAEN_IMPORT.MAJ_DOCTORANT();
      UNICAEN_IMPORT.MAJ_THESE();
      UNICAEN_IMPORT.MAJ_ROLE();
      UNICAEN_IMPORT.MAJ_ACTEUR();
      UNICAEN_IMPORT.MAJ_VARIABLE();
      UNICAEN_IMPORT.MAJ_STRUCTURE();
      UNICAEN_IMPORT.MAJ_ETABLISSEMENT();
      UNICAEN_IMPORT.MAJ_ECOLE_DOCT();
      UNICAEN_IMPORT.MAJ_UNITE_RECH();
      REFRESH_MV('MV_RECHERCHE_THESE'); -- NB: à faire en dernier
    END;

  --
  -- Recherche des changements de type UPDATE concernant la colonne de table observée et
  -- enregistrement de ces changements dans une table.
  --
  PROCEDURE STORE_UPDATE_OBSERV_RESULT(observ IMPORT_OBSERV%ROWTYPE)
  IS
    u_col_name VARCHAR2(50) := 'U_' || observ.column_name;
    where_to_value CLOB := 'v.' || observ.column_name || case when observ.to_value is null then ' is null' else ' = ''' || observ.to_value || '''' end;
    i_query clob := 'select v.source_code, t.' || observ.column_name || ' || ''>'' || v.' || observ.column_name || ' detail ' ||
                    'from v_diff_' || observ.table_name || ' v join ' || observ.table_name || ' t on t.source_code = v.source_code where ' || u_col_name || ' = 1 and ' || where_to_value || ' order by v.source_code';
    TYPE r_cursor is REF CURSOR;
    rc r_cursor;
    l_id CLOB;
    l_detail CLOB;
    BEGIN
      OPEN rc FOR i_query;
      LOOP
        FETCH rc INTO l_id, l_detail;
        EXIT WHEN rc%NOTFOUND;
        --DBMS_OUTPUT.PUT_LINE(l_id); DBMS_OUTPUT.PUT_LINE(l_detail);
        insert into IMPORT_OBSERV_RESULT(ID, IMPORT_OBSERV_ID, DATE_CREATION, SOURCE_CODE, RESULTAT) values
          (IMPORT_OBSERV_RESULT_ID_SEQ.nextval, observ.id, sysdate, l_id, l_detail);
      END LOOP;
    END;


  PROCEDURE STORE_OBSERV_RESULTS
  IS
    BEGIN
      for observ in (select * from IMPORT_OBSERV where enabled = 1) loop
        if (observ.operation = 'UPDATE') then
          STORE_UPDATE_OBSERV_RESULT(observ);
        end if;
      end loop;
    END;


  PROCEDURE SYNCHRONISATION
  IS
    BEGIN
      --       STORE_OBSERV_RESULTS;
      SYNC_TABLES;
    END;

END APP_IMPORT;
/

create PACKAGE UNICAEN_ORACLE AS

  FUNCTION implode(i_query VARCHAR2, i_seperator VARCHAR2 DEFAULT ',') RETURN VARCHAR2;

  FUNCTION STR_REDUCE( str CLOB ) RETURN CLOB;

  FUNCTION STR_FIND( haystack CLOB, needle VARCHAR2 ) RETURN NUMERIC;

  FUNCTION LIKED( haystack CLOB, needle CLOB ) RETURN NUMERIC;

  FUNCTION COMPRISE_ENTRE( date_debut DATE, date_fin DATE, date_obs DATE DEFAULT NULL, inclusif NUMERIC DEFAULT 0 ) RETURN NUMERIC;

END UNICAEN_ORACLE;
/

-- create view V_RECHERCHE_INDIVIDU as
--   SELECT
--     ID,
--     NOM_USUEL,
--     NOM_PATRONYMIQUE,
--     PRENOM1,
--     PRENOM2,
--     PRENOM3,
--     EMAIL,
--     SOURCE_CODE
--     ,trim(UNICAEN_ORACLE.str_reduce(NOM_USUEL || ' ' || NOM_PATRONYMIQUE || ' ' || PRENOM1 || ' ' || SOURCE_CODE)) HAYSTACK
--   FROM individu
-- /

create PACKAGE BODY UNICAEN_ORACLE AS

  FUNCTION implode(i_query VARCHAR2, i_seperator VARCHAR2 DEFAULT ',') RETURN VARCHAR2 AS
    l_return CLOB:='';
    l_temp CLOB;
    TYPE r_cursor is REF CURSOR;
    rc r_cursor;
    BEGIN
      OPEN rc FOR i_query;
      LOOP
        FETCH rc INTO L_TEMP;
        EXIT WHEN RC%NOTFOUND;
        l_return:=l_return||L_TEMP||i_seperator;
      END LOOP;
      RETURN RTRIM(l_return,i_seperator);
    END;

  FUNCTION STR_REDUCE( str CLOB ) RETURN CLOB IS
    BEGIN
      RETURN utl_raw.cast_to_varchar2((nlssort(str, 'nls_sort=binary_ai')));
    END;

  FUNCTION STR_FIND( haystack CLOB, needle VARCHAR2 ) RETURN NUMERIC IS
    BEGIN
      IF STR_REDUCE( haystack ) LIKE STR_REDUCE( '%' || needle || '%' ) THEN RETURN 1; END IF;
      RETURN 0;
    END;

  FUNCTION LIKED( haystack CLOB, needle CLOB ) RETURN NUMERIC IS
    BEGIN
      RETURN CASE WHEN STR_REDUCE(haystack) LIKE STR_REDUCE(needle) THEN 1 ELSE 0 END;
    END;

  FUNCTION COMPRISE_ENTRE( date_debut DATE, date_fin DATE, date_obs DATE DEFAULT NULL, inclusif NUMERIC DEFAULT 0 ) RETURN NUMERIC IS
    d_deb DATE;
    d_fin DATE;
    d_obs DATE;
    res NUMERIC;
    BEGIN
      IF inclusif = 1 THEN
        d_obs := TRUNC( COALESCE( d_obs     , SYSDATE ) );
        d_deb := TRUNC( COALESCE( date_debut, d_obs   ) );
        d_fin := TRUNC( COALESCE( date_fin  , d_obs   ) );
        IF d_obs BETWEEN d_deb AND d_fin THEN
          RETURN 1;
        ELSE
          RETURN 0;
        END IF;
      ELSE
        d_obs := TRUNC( COALESCE( d_obs, SYSDATE ) );
        d_deb := TRUNC( date_debut );
        d_fin := TRUNC( date_fin   );

        IF d_deb IS NOT NULL AND NOT d_deb <= d_obs THEN
          RETURN 0;
        END IF;
        IF d_fin IS NOT NULL AND NOT d_obs < d_fin THEN
          RETURN 0;
        END IF;
        RETURN 1;
      END IF;
    END;

END UNICAEN_ORACLE;
/

create PACKAGE          "APP_WORKFLOW" AS

  function atteignable(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC;
  function atteignable2(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC;

END APP_WORKFLOW;
/

create PACKAGE BODY          "APP_WORKFLOW"
AS

  function atteignable(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC AS
    v_ordre numeric;
    BEGIN
      select ordre into v_ordre from wf_etape where id = p_etape_id;
      --DBMS_OUTPUT.PUT_LINE('ordre ' || v_ordre);
      for row in (
      select code, ORDRE, franchie, resultat, objectif
      from V_WORKFLOW v
      where v.these_id = p_these_id and v.ordre < v_ordre
      order by v.ordre
      ) loop
        --DBMS_OUTPUT.PUT_LINE(rpad(row.ordre, 5) || ' ' || row.code || ' : ' || row.franchie);
        if row.franchie = 0 then
          return 0;
        end if;
      end loop;

      RETURN 1;
    END atteignable;



  function atteignable2(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC AS
    v_ordre numeric;
    BEGIN
      select ordre into v_ordre from wf_etape where id = p_etape_id;
      --DBMS_OUTPUT.PUT_LINE('ordre ' || v_ordre);
      for row in (
      select code, ORDRE, franchie, resultat, objectif
      from V_WORKFLOW v
      where v.these_id = p_these_id and v.ordre < v_ordre
      order by v.ordre
      ) loop
        --DBMS_OUTPUT.PUT_LINE(rpad(row.ordre, 5) || ' ' || row.code || ' : ' || row.franchie);
        if row.franchie = 0 then
          return 0;
        end if;
      end loop;

      RETURN 1;
    END atteignable2;

END APP_WORKFLOW;
/

create function individu_haystack(
  NOM_USUEL varchar2,
  NOM_PATRONYMIQUE varchar2,
  PRENOM1 varchar2,
  EMAIL varchar2,
  SOURCE_CODE varchar2) RETURN VARCHAR2
AS
  BEGIN
    return trim(UNICAEN_ORACLE.str_reduce(
                    NOM_USUEL || ' ' || PRENOM1 || ' ' || NOM_PATRONYMIQUE || ' ' || PRENOM1 || ' ' ||
                    PRENOM1 || ' ' || NOM_USUEL || ' ' || PRENOM1 || ' ' || NOM_PATRONYMIQUE || ' ' ||
                    EMAIL || ' ' ||
                    SOURCE_CODE
                ));
  END;
/

create trigger INDIVIDU_RECH_UPDATE
  after insert or update of NOM_USUEL,NOM_PATRONYMIQUE,PRENOM1,PRENOM2,PRENOM3,SOURCE_CODE or delete
  on 	INDIVIDU
  for each row
  DECLARE
    v_haystack CLOB := individu_haystack(:new.NOM_USUEL, :new.NOM_PATRONYMIQUE, :new.PRENOM1, :new.EMAIL, :new.SOURCE_CODE);
  BEGIN
    IF INSERTING THEN
      insert into INDIVIDU_RECH(ID, HAYSTACK) values (:new.ID, v_haystack);
    END IF;
    IF UPDATING THEN
      UPDATE INDIVIDU_RECH SET HAYSTACK = v_haystack where ID = :new.ID;
    END IF;
    IF DELETING THEN
      delete from INDIVIDU_RECH where id = :old.ID;
    END IF;
  END;
/






create materialized view MV_RECHERCHE_THESE
refresh force on demand
  as
    with acteurs as (
        select a.these_id, i.nom_usuel, INDIVIDU_ID
        from individu i
          join acteur a on i.id = a.individu_id
          join these t on t.id = a.these_id
          join role r on a.role_id = r.id and r.CODE in ('D') -- directeur de thèse
    )
    select
      t.source_code code_these,
      d.source_code code_doctorant,
      ed.source_code code_ecole_doct,
      trim(UNICAEN_ORACLE.str_reduce(
               t.COD_UNIT_RECH || ' ' || t.TITRE || ' ' ||
               d.SOURCE_CODE || ' ' || id.NOM_PATRONYMIQUE || ' ' || id.NOM_USUEL || ' ' || id.PRENOM1 || ' ' ||
               a.nom_usuel)) as haystack
    from these t
      join doctorant d on d.id = t.doctorant_id
      join individu id on id.id = d.INDIVIDU_ID
      join these th on th.source_code = t.source_code
      --join mv_thesard mvd on mvd.source_code = d.source_code
      left join ecole_doct ed on t.ecole_doct_id = ed.id
      left join acteurs a on a.these_id = t.id
      left join individu ia on ia.id = a.INDIVIDU_ID
/
