--
-- Nouvelle catégorie.
--
insert into CATEGORIE_PRIVILEGE(ID, CODE, LIBELLE, ORDRE) values (CATEGORIE_PRIVILEGE_ID_SEQ.nextval, 'utilisateur', 'Utilisateur', 5);


--
-- Nouveau privilège.
--
insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
  select privilege_id_seq.nextval, cp.id, 'creation', 'Création d''école doctorale', 105
  from CATEGORIE_PRIVILEGE cp where cp.CODE = 'ecole-doctorale';

--
-- Ajout de ROLE_PRIVILEGE_MODELE.
--
INSERT INTO ROLE_PRIVILEGE_MODELE (ROLE_CODE, PRIVILEGE_ID)
  with data(role, categ, priv) as (
    select 'ADMIN',       'ecole-doctorale',  'creation' from dual union
    select 'ADMIN_TECH',  'ecole-doctorale',  'creation' from dual union
    select 'BU',          'ecole-doctorale',  'creation' from dual union
    select 'BDD',         'ecole-doctorale',  'creation' from dual union

    select 'ADMIN',       'unite-recherche',  'creation' from dual union
    select 'ADMIN_TECH',  'unite-recherche',  'creation' from dual union
    select 'BU',          'unite-recherche',  'creation' from dual union
    select 'BDD',         'unite-recherche',  'creation' from dual
  )
    select role, p.id
    from data
      join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
      join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
;

--
-- Application des modèles (ceux pas déjà appliqués).
--
insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
  SELECT r.id, p.ID
  from ROLE_PRIVILEGE_MODELE rpm
    join role r on r.CODE = rpm.ROLE_CODE
    join PRIVILEGE p on p.id = rpm.PRIVILEGE_ID
  where not exists (
      select * from ROLE_PRIVILEGE rp where rp.ROLE_ID = r.id and rp.PRIVILEGE_ID = p.id
  );


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
select 'insert into role_privilege(role_id, privilege_id) select r.id, p.id from role r, privilege p, categorie_privilege cp where p.categorie_id = cp.id and r.libelle = ''' || replace(r.role_id,'''','''''') || ''' and p.code = ''' || p.code || ''' and cp.code = ''' || cp.code || ''';'
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


INSERT INTO "OTH"."CATEGORIE_PRIVILEGE" ("ID", "CODE", "LIBELLE", "ORDRE") VALUES (19, 'substitution', 'Substitution de structures', 300)

INSERT INTO "OTH"."PRIVILEGE" ("ID", "CATEGORIE_ID", "CODE", "LIBELLE", "ORDRE") VALUES (250, 19, 'automatique', 'Substitution automatique de structures', 100)
INSERT INTO "OTH"."PRIVILEGE" ("ID", "CATEGORIE_ID", "CODE", "LIBELLE", "ORDRE") VALUES (251, 19, 'consultation-etablissement', 'Consultation des substitutions d''établissement', 200)
INSERT INTO "OTH"."PRIVILEGE" ("ID", "CATEGORIE_ID", "CODE", "LIBELLE", "ORDRE") VALUES (252, 19, 'consultation-ecole', 'Consultation des substitutions d''école doctorale', 300)
INSERT INTO "OTH"."PRIVILEGE" ("ID", "CATEGORIE_ID", "CODE", "LIBELLE", "ORDRE") VALUES (253, 19, 'consultation-unite', 'Consultation des substitutions d''unité de recherche', 400)
INSERT INTO "OTH"."PRIVILEGE" ("ID", "CATEGORIE_ID", "CODE", "LIBELLE", "ORDRE") VALUES (254, 19, 'modification-etablissement', 'Modification des substitutions d''établissement', 220)
INSERT INTO "OTH"."PRIVILEGE" ("ID", "CATEGORIE_ID", "CODE", "LIBELLE", "ORDRE") VALUES (255, 19, 'modification-ecole', 'Modification des substitutions d''école doctorale', 320)
INSERT INTO "OTH"."PRIVILEGE" ("ID", "CATEGORIE_ID", "CODE", "LIBELLE", "ORDRE") VALUES (256, 19, 'modification-unite', 'Modification des substitutions d''unité de recherche', 420)















