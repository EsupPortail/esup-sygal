--
-- Nouvelle catégorie.
--
insert into CATEGORIE_PRIVILEGE(ID, CODE, LIBELLE, ORDRE)
    select nextval('categorie_privilege_id_seq'), 'utilisateur', 'Utilisateur', 5;

--
-- Nouveaux privilèges.
--
insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (
    select 210, 'ajouter-tout', 'Ajouter un rapport d''activité concernant toute thèse' union
    select 220, 'ajouter-sien', 'Ajouter un rapport d''activité concernant ses thèses' union
    select 230, 'consulter-tout', 'Consulter un rapport d''activité concernant toute thèse' union
    select 240, 'consulter-sien', 'Consulter un rapport d''activité concernant ses thèses'
)
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d
         join CATEGORIE_PRIVILEGE cp on cp.CODE = 'rapport-activite'
;

--
-- Accord de privilèges à des profils.
--
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
    with data(categ, priv) as (
        select 'these', 'consultation-page-couverture' /*union
        select 'xxxxx', 'xxx'*/
    )
    select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
    from data
    join PROFIL on profil.ROLE_ID in (
        'ADMIN_TECH',
        'BDD', 'BU',
        'D', 'DOCTORANT', 'K', 'M', 'R',
        'ED', 'UR'
    )
    join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
    join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
    where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

--
-- Affectation de profils à des rôles.
-- NB: penser à créer ensuite les ROLE_PRIVILEGE.
--
insert into PROFIL_TO_ROLE (PROFIL_ID, ROLE_ID)
    with data(PROFIL_CODE, ROLE_ROLE_ID) as (
        select 'BDD', 'Maison du doctorat UCN' union
        select 'BDD', 'Maison du doctorat URN' union
        select 'BDD', 'Maison du doctorat ULHN' union
        select 'BDD', 'Maison du doctorat INSA'
    )
    select pr.id, r.id
    from data
    join PROFIL pr on pr.ROLE_ID = data.PROFIL_CODE
    join role r on r.ROLE_ID = data.ROLE_ROLE_ID
    where not exists (
        select * from PROFIL_TO_ROLE where PROFIL_ID = pr.id and ROLE_ID = r.id
    ) ;

--
-- Application du profil 'GEST_ED' aux roles 'GEST_ED'.
-- NB: penser à créer ensuite les ROLE_PRIVILEGE.
--
insert into profil_to_role (profil_id, role_id)
select p.id, r.id
from profil p, role r
where r.code = 'GEST_ED' and p.role_id = 'GEST_ED'
  and not exists (
        select * from profil_to_role where profil_id = p.id and role_id = r.id
    )
;

--
-- Attribution automatique des privilèges aux rôles, d'après ce qui est spécifié dans :
--   - PROFIL_TO_ROLE (profils appliqués à chaque rôle) et
--   - PROFIL_PRIVILEGE (privilèges accordés à chaque profil).
--
insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
join profil pr on pr.id = p2r.PROFIL_ID
join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (
    select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id
)
;
--
-- Inverse : suppression des attributions de privilèges à des rôles si elles n'existent pas dans :
--   - PROFIL_TO_ROLE (profils appliqués à chaque rôle) et
--   - PROFIL_PRIVILEGE (privilèges accordés à chaque profil).
--
delete from ROLE_PRIVILEGE rp
where not exists (
    select *
    from PROFIL_TO_ROLE p2r
    join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = p2r.PROFIL_ID
    where rp.role_id = p2r.role_id and rp.privilege_id = pp.privilege_id
);



--
-- Mise en conformité brutale entre 2 bdd des CATEGORIE_PRIVILEGE, PRIVILEGE, ROLE_PRIVILEGE.
--
select 'delete from ROLE_PRIVILEGE;'
union all
select 'delete from PRIVILEGE;'
union all
select 'delete from CATEGORIE_PRIVILEGE;'
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















