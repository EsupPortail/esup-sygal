--
-- BOOTSTRAP
--

--
-- Sources de données ESUP-SyGAL.
--
delete from source where code = 'SYGAL::sygal'
;
insert into source (id, code, libelle, importable)
values (1, 'SYGAL::sygal', 'ESUP-SyGAL', false)
;

--
-- Pseudo-utilisateur 'sygal-app'.
--
delete from utilisateur where username = 'sygal-app'
;
insert into utilisateur (id, username, display_name, password)
values (1, 'sygal-app', 'Application ESUP-SyGAL', 'ldap')
;

--
-- Rôles multi-établissements.
--
delete from role where source_code in ('ADMIN_TECH', 'OBSERV')
;
insert into role (id, code, libelle, source_code, source_id, role_id, these_dep, histo_createur_id, histo_modificateur_id)
  values (1, 'ADMIN_TECH', 'Administrateur technique', 'ADMIN_TECH', 1, 'Administrateur technique', false, 1, 1)
;
insert into role (id, code, libelle, source_code, source_id, role_id, these_dep, histo_createur_id, histo_modificateur_id)
  values (2 /*241*/, 'OBSERV', 'Observateur', 'OBSERV', 1, 'Observateur', false, 1, 1)
;

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
delete from etablissement where source_code  = 'ETAB_INCONNU'
;
delete from structure where source_code  = 'ETAB_INCONNU'
;
insert into structure(id, libelle, type_structure_id, histo_createur_id, histo_modificateur_id, source_id, source_code, code)
select nextval('structure_id_seq'),
       'Établissement inconnu',
       1, -- type etab
       1, 1,
       1, -- source sygal
       'ETAB_INCONNU', -- source code unique
       'INCONNU' -- code
;
insert into etablissement(id, structure_id, histo_createur_id, histo_modificateur_id, source_id, source_code)
select nextval('etablissement_id_seq'),
       currval('structure_id_seq'),
       1, 1,
       1, -- source sygal
       'ETAB_INCONNU' -- source code unique, idem structure
;

-- --
-- -- Avance de sequences.
-- --
-- declare
--   maxid integer;
--   seqnextval integer;
-- begin
--   select max(id) into maxid from UTILISATEUR;
--   LOOP
--     select nextval('UTILISATEUR_ID_SEQ') into seqnextval from dual;
--     EXIT WHEN seqnextval >= maxid;
--   END LOOP;
-- end;
--
-- declare
--   maxid integer;
--   seqnextval integer;
-- begin
--   select max(id) into maxid from ROLE;
--   LOOP
--     select nextval('ROLE_ID_SEQ') into seqnextval from dual;
--     EXIT WHEN seqnextval >= maxid;
--   END LOOP;
-- end;

