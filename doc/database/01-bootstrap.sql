--
-- BOOTSTRAP
--

--
-- Sources de données SyGAL.
--
delete from SOURCE where CODE = 'SYGAL::sygal'
/
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE)
VALUES (1, 'SYGAL::sygal', 'SyGAL', 0)
/

--
-- Pseudo-utilisateur 'sygal-app'.
--
delete from UTILISATEUR where USERNAME = 'sygal-app'
/
INSERT INTO UTILISATEUR (ID, USERNAME, DISPLAY_NAME, PASSWORD)
VALUES (1, 'sygal-app', 'Application SyGAL', 'ldap')
/

--
-- Rôles multi-établissements.
--
delete from ROLE where SOURCE_CODE in ('ADMIN_TECH', 'OBSERV')
/
INSERT INTO ROLE (ID, CODE, LIBELLE, SOURCE_CODE, SOURCE_ID, ROLE_ID, THESE_DEP, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
  VALUES (1, 'ADMIN_TECH', 'Administrateur technique', 'ADMIN_TECH', 1, 'Administrateur technique', 0, 1, 1)
/
INSERT INTO ROLE (ID, CODE, LIBELLE, ROLE_ID, SOURCE_CODE, SOURCE_ID, THESE_DEP, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
  VALUES (2 /*241*/, 'OBSERV', 'Observateur', 'Observateur', 'OBSERV', 1, 0, 1, 1)
/

-- drop sequence INDIVIDU_ID_SEQ;
-- drop sequence UTILISATEUR_ID_SEQ;
-- drop sequence STRUCTURE_ID_SEQ;
-- drop sequence ETABLISSEMENT_ID_SEQ;
-- CREATE SEQUENCE  "INDIVIDU_ID_SEQ"  MINVALUE 1 MAXVALUE 9999999999999999999999999999 INCREMENT BY 1 NOORDER  NOCYCLE ;
-- CREATE SEQUENCE  "UTILISATEUR_ID_SEQ"  MINVALUE 1 MAXVALUE 9999999999999999999999999999 INCREMENT BY 1 NOORDER  NOCYCLE ;
-- CREATE SEQUENCE  "STRUCTURE_ID_SEQ"  MINVALUE 1 MAXVALUE 9999999999999999999999999999 INCREMENT BY 1 NOORDER  NOCYCLE ;
-- CREATE SEQUENCE  "ETABLISSEMENT_ID_SEQ"  MINVALUE 1 MAXVALUE 9999999999999999999999999999 INCREMENT BY 1 NOORDER  NOCYCLE ;

--
-- L'établissement inconnu.
--
delete from ETABLISSEMENT where SOURCE_CODE  = 'ETAB_INCONNU'
/
delete from STRUCTURE where SOURCE_CODE  = 'ETAB_INCONNU'
/
insert into STRUCTURE(ID, LIBELLE, TYPE_STRUCTURE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, SOURCE_ID, SOURCE_CODE, CODE)
select STRUCTURE_ID_SEQ.nextval,
       'Établissement inconnu',
       1, -- type etab
       1, 1,
       1, -- source sygal
       'ETAB_INCONNU', -- source code unique
       'INCONNU' -- code
from dual
/
insert into ETABLISSEMENT(ID, STRUCTURE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, SOURCE_ID, SOURCE_CODE)
select ETABLISSEMENT_ID_SEQ.nextval,
       STRUCTURE_ID_SEQ.currval,
       1, 1,
       1, -- source sygal
       'ETAB_INCONNU' -- source code unique, idem structure
from dual
/

--
-- Avance de sequences.
--
declare
  maxid integer;
  seqnextval integer;
begin
  select max(id) into maxid from UTILISATEUR;
  LOOP
    select UTILISATEUR_ID_SEQ.nextval into seqnextval from dual;
    EXIT WHEN seqnextval >= maxid;
  END LOOP;
end;
/
declare
  maxid integer;
  seqnextval integer;
begin
  select max(id) into maxid from ROLE;
  LOOP
    select ROLE_ID_SEQ.nextval into seqnextval from dual;
    EXIT WHEN seqnextval >= maxid;
  END LOOP;
end;
/
