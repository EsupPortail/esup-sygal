rename ROLE_MODELE to PROFIL;
rename ROLE_MODELE_PRIVILEGE to PROFIL_PRIVILEGE;

create sequence PROFIL_ID_SEQ;
--
-- Avance d'une sequence.
--
declare
  maxid integer;
  seqnextval integer;
begin
  select max(PROFIL.id) into maxid from PROFIL;
  LOOP
    select PROFIL_ID_SEQ.nextval into seqnextval from dual;
    EXIT WHEN seqnextval >= maxid;
  END LOOP;
end;

create unique index PROFIL_ROLE_ID_uindex on PROFIL (ROLE_ID);

alter table PROFIL_PRIVILEGE rename column ROLE_ID to PROFIL_ID;
alter table PROFIL_PRIVILEGE add constraint PROFIL_PRIVILEGE_PROFIL_ID_fk foreign key (PROFIL_ID) references PROFIL;

create table PROFIL_TO_ROLE (
	PROFIL_ID integer not null constraint PROFIL_TO_ROLE_PROFIL_ID_fk references PROFIL,
	ROLE_ID integer not null constraint PROFIL_TO_ROLE_ROLE_ID_fk references ROLE,
	constraint PROFIL_TO_ROLE_pk primary key (PROFIL_ID, ROLE_ID)
);