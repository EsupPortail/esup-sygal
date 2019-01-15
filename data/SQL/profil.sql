
rename ROLE_MODELE to PROFIL;
rename ROLE_PRIVILEGE_MODELE to PROFIL_PRIVILEGE;

alter table PROFIL add description varchar2(1024);

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

truncate table PROFIL_PRIVILEGE;

alter table PROFIL modify STructure_type null;

alter table PROFIL_PRIVILEGE drop constraint ROLE_PRIV_MOD_PK;
alter table PROFIL_PRIVILEGE drop column ROLE_CODE;
alter table PROFIL_PRIVILEGE add PROFIL_ID number;
alter table PROFIL_PRIVILEGE add constraint PROFIL_PRIVILEGE_PROFIL_ID_fk foreign key (PROFIL_ID) references PROFIL;
alter table PROFIL_PRIVILEGE add constraint PROFIL_PRIVILEGE_PK primary key (PROFIL_ID, PRIVILEGE_ID);

create table PROFIL_TO_ROLE (
                              PROFIL_ID integer not null constraint PROFIL_TO_ROLE_PROFIL_ID_fk references PROFIL,
                              ROLE_ID integer not null constraint PROFIL_TO_ROLE_ROLE_ID_fk references ROLE,
                              constraint PROFIL_TO_ROLE_pk primary key (PROFIL_ID, ROLE_ID)
);

-- INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (1, 'Unité de recherche', 'UR', 3, null);
-- INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (2, 'École doctorale', 'ED', 2, null);
-- INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (3, 'Administrateur', 'ADMIN', 1, 'Administrateur d''établissement');
-- INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (4, 'Bureau des doctorats', 'BDD', 1, null);
-- INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (5, 'Bibliothèque universitaire', 'BU', 1, null);
INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (6, 'Administrateur technique', 'ADMIN_TECH', null, null);
INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (7, 'Doctorant', 'DOCTORANT', null, null);
INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (8, 'Observateur', 'OBSERV', null, null);
INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (9, 'Directeur', 'D', null, null);
INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (10, 'Co-directeur', 'K', null, null);
INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (11, 'Rapporteur', 'R', null, null);
INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (12, 'Membre', 'M', null, null);
INSERT INTO PROFIL (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE, DESCRIPTION) VALUES (61, 'Doctorant sans dépôt', 'NODEPOT', null, 'Rôle de doctorant temporaire');

INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (36, 19, 'consultation-toutes-structures', 'Consultation de toutes les substitutions', 200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (37, 19, 'consultation-sa-structure', 'Consultation de la substitution de sa structure', 300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (38, 19, 'modification-toutes-structures', 'Modification de toutes les substitutions ', 400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (39, 19, 'modification-sa-structure', 'Modification de la substitution de sa structure', 500);
