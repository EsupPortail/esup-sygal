--drop table CONTENU_FICHIER;
create table CONTENU_FICHIER
(
  ID NUMBER not null constraint CONTENU_FICHIER_PK primary key,
  FICHIER_ID VARCHAR2(38 char) not null constraint CONTENU_FICHIER_FICHIER_FK references FICHIER on delete cascade,
  DATA BLOB not null
);

create index CONTENU_FICHIER_FIDX on CONTENU_FICHIER (FICHIER_ID);

create sequence CONTENU_FICHIER_ID_SEQ;


insert into CONTENU_FICHIER(ID, FICHIER_ID, DATA)
    select CONTENU_FICHIER_ID_SEQ.nextval, f.id, f.CONTENU
      from FICHIER f
;

alter table FICHIER drop column CONTENU;

