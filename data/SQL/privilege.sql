--
-- Nouvelle catégorie.
--
insert into CATEGORIE_PRIVILEGE(ID, CODE, LIBELLE, ORDRE) values (CATEGORIE_PRIVILEGE_ID_SEQ.nextval, 'utilisateur', 'Utilisateur', 5);


--
-- Nouveau privilège.
--
insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
  select privilege_id_seq.nextval, cp.id, 'saisie-attestations', 'Modification des attestations', 3030
  from CATEGORIE_PRIVILEGE cp where cp.CODE = 'these';
commit;


--
-- Ajout d'un nouveau  privilège à des rôles.
--
insert into ROLE_PRIVILEGE(ROLE_ID, PRIVILEGE_ID)
  select r.id, p.id
    from USER_ROLE r, PRIVILEGE p
  where r.ROLE_ID in ('Bureau des doctorats', 'Bibliothèque universitaire', 'Administrateur', 'Doctorant', 'Administrateur technique')
    and p.CODE in ('saisie-attestations')
;


--
-- Mise en conformité brutale entre 2 bdd des CATEGORIE_PRIVILEGE, PRIVILEGE, ROLE_PRIVILEGE.
--
select 'delete from ROLE_PRIVILEGE;' from dual
union all
select 'delete from PRIVILEGE;' from dual
union all
select 'delete from CATEGORIE_PRIVILEGE;' from dual
union all
select 'INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (' || id || ', ''' || code ||''', ''' || replace(libelle, '''', '''''') || ''', ' || ordre || ');' from CATEGORIE_PRIVILEGE
union all
select 'INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (' || id || ', ' || CATEGORIE_ID || ', ''' || code ||''', ''' || replace(libelle, '''', '''''') || ''', ' || ordre || ');' from PRIVILEGE
union all
select 'INSERT INTO ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID) VALUES (' || ROLE_ID || ', ' || PRIVILEGE_ID || ');' from ROLE_PRIVILEGE;



--
-- Mise en conformité entre 2 bdd des libellés et ordre des privilèges.
--
-- 1/ Interrogation de la bdd de référence pour génération des update :
select 'update privilege set libelle = ''' || replace(libelle,'''','''''') || ''', ordre = ' || ordre || ' where categorie_id = ' || categorie_id || ' and code = ''' || code || ''';'
from privilege
order by categorie_id, code;
-- 2/ Exécution des update générés.


--
-- Mise en conformité entre 2 bdd des privilèges accordés aux rôles.
--
-- 1/ Interrogation de la bdd de référence pour génération des insert :
select 'insert into role_privilege(role_id, privilege_id) select r.id, p.id from user_role r, privilege p, categorie_privilege cp where p.categorie_id = cp.id and r.role_id = ''' || replace(r.role_id,'''','''''') || ''' and p.code = ''' || p.code || ''' and cp.code = ''' || cp.code || ''';'
from role_privilege rp
  join user_role r on rp.role_id = r.id
  join privilege p on rp.privilege_id = p.id
  join categorie_privilege cp on p.categorie_id = cp.id
order by r.role_id, p.code;
-- 2/ Vidage de la table dans la bdd cible :
truncate table role_privilege;
-- 3/ Exécution des insert générés à l'étape 1.
-- 4/ Vérif
select r.role_id, cp.code categorie, p.code privilege, p.id
from role_privilege rp
  join user_role r on rp.role_id = r.id
  join privilege p on rp.privilege_id = p.id
  join categorie_privilege cp on p.categorie_id = cp.id
order by r.role_id, p.code;


















