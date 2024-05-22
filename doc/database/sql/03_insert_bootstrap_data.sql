--
-- BOOTSTRAP
--

--
-- Sources de données.
--
delete from source where code = 'SYGAL::sygal';
delete from source where code = 'HAL';
insert into source (id, code, libelle, importable) values (1, 'SYGAL::sygal', 'ESUP-SyGAL', false);
insert into source (id, code, libelle, importable) values (2, 'HAL', 'HAL', true);
alter sequence source_id_seq restart with 3;

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
delete from role where source_code in ('user', 'ADMIN_TECH', 'OBSERV')
;
insert into role (id, code, libelle, source_code, source_id, role_id, these_dep, histo_createur_id)
values (nextval('role_id_seq'), 'user', 'Authentifié·e', 'user', 1, 'user', false, 1)
;
insert into role (id, code, libelle, source_code, source_id, role_id, these_dep, histo_createur_id)
values (nextval('role_id_seq'), 'ADMIN_TECH', 'Administrateur technique', 'ADMIN_TECH', 1, 'Administrateur technique', false, 1)
;
insert into role (id, code, libelle, source_code, source_id, role_id, these_dep, histo_createur_id)
values (nextval('role_id_seq'), 'OBSERV', 'Observateur', 'OBSERV', 1, 'Observateur', false, 1)
;

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
