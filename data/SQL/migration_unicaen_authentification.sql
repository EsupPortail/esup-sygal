
--
--
-- Migration vers unicaen/authentification, privilege et utilisateur.
--
--

alter table categorie_privilege rename to UNICAEN_PRIVILEGE_CATEGORIE;
alter table privilege rename to UNICAEN_PRIVILEGE_privilege;

alter table UNICAEN_PRIVILEGE_categorie add NAMESPACE VARCHAR(255);

update role set is_default = false where is_default is null;
alter table role alter column is_default set not null;

-- update unicaen_privilege_privilege set code = 'privilege_voir' where code  = 'privilege-visualisation';
-- update unicaen_privilege_privilege set code = 'privilege_affecter' where code  = 'privilege-edition';

INSERT INTO UNICAEN_PRIVILEGE_CATEGORIE (
    CODE,
    LIBELLE,
    NAMESPACE,
    ORDRE)
values
--     ('utilisateur', 'Gestion des utilisateurs', 'UnicaenUtilisateur\Provider\Privilege', 1),
--     ('role', 'Gestion des rôles', 'UnicaenUtilisateur\Provider\Privilege', 2),
('privilege', 'Gestion des privilèges', 'UnicaenPrivilege\Provider\Privilege', 3)
ON CONFLICT (CODE) DO
    UPDATE SET
               LIBELLE=excluded.LIBELLE,
               NAMESPACE=excluded.NAMESPACE,
               ORDRE=excluded.ORDRE;

WITH d(code, lib, ordre) AS (
    SELECT 'privilege_voir', 'Afficher les privilèges', 1 UNION
    SELECT 'privilege_ajouter' , 'Ajouter un privilège', 2 UNION
    SELECT 'privilege_modifier' , 'Modifier un privilège', 3 UNION
    SELECT 'privilege_supprimer' , 'Supprimer un privilège', 4 UNION
    SELECT 'privilege_affecter' , 'Attribuer un privilège', 5
)
INSERT INTO unicaen_privilege_privilege(CATEGORIE_ID, CODE, LIBELLE, ORDRE)
SELECT cp.id, d.code, d.lib, d.ordre
FROM d
         JOIN unicaen_privilege_categorie cp ON cp.CODE = 'privilege'
ON CONFLICT (CATEGORIE_ID, CODE) DO
    UPDATE SET
               LIBELLE=excluded.LIBELLE,
               ORDRE=excluded.ORDRE;

--drop function privilege__grant_privilege_to_profile;
create function privilege__grant_privilege_to_profile(categorycode character varying, privilegecode character varying, profileroleid character varying) returns void
    language plpgsql
as
$$
BEGIN
    -- insertion dans 'profil_privilege' (si pas déjà fait)
    insert into profil_privilege (privilege_id, profil_id)
    select p.id as privilege_id, profil.id as profil_id
    from profil
             join unicaen_privilege_categorie cp on cp.code = categoryCode
             join unicaen_privilege_privilege p on p.categorie_id = cp.id and p.code = privilegeCode
    where profil.role_id = profileroleid
      and not exists(
        select * from profil_privilege where privilege_id = p.id and profil_id = profil.id
    );

    perform privilege__update_role_privilege();
END;
$$;

select privilege__grant_privilege_to_profile('privilege', 'privilege_voir', 'ADMIN_TECH');
select privilege__grant_privilege_to_profile('privilege', 'privilege_ajouter', 'ADMIN_TECH');
select privilege__grant_privilege_to_profile('privilege', 'privilege_modifier', 'ADMIN_TECH');
select privilege__grant_privilege_to_profile('privilege', 'privilege_supprimer', 'ADMIN_TECH');
select privilege__grant_privilege_to_profile('privilege', 'privilege_affecter', 'ADMIN_TECH');

INSERT INTO UNICAEN_PRIVILEGE_CATEGORIE (
    CODE,
    LIBELLE,
    NAMESPACE,
    ORDRE)
values
    ('utilisateur', 'Gestion des utilisateurs', 'UnicaenUtilisateur\Provider\Privilege', 1),
    ('role', 'Gestion des rôles', 'UnicaenUtilisateur\Provider\Privilege', 1)
ON CONFLICT (CODE) DO
    UPDATE SET
               LIBELLE=excluded.LIBELLE,
               NAMESPACE=excluded.NAMESPACE,
               ORDRE=excluded.ORDRE;

WITH d(code, lib, ordre) AS (
    SELECT 'role_afficher', 'Consulter les rôles', 1 UNION
    SELECT 'role_modifier', 'Modifier un rôle', 2 UNION
    SELECT 'role_effacer', 'Supprimer un rôle', 3
)
INSERT INTO unicaen_privilege_privilege(CATEGORIE_ID, CODE, LIBELLE, ORDRE)
SELECT cp.id, d.code, d.lib, d.ordre
FROM d
         JOIN unicaen_privilege_categorie cp ON cp.CODE = 'role'
ON CONFLICT (CATEGORIE_ID, CODE) DO
    UPDATE SET
               LIBELLE=excluded.LIBELLE,
               ORDRE=excluded.ORDRE;

select privilege__grant_privilege_to_profile('role', 'role_afficher', 'ADMIN_TECH');
select privilege__grant_privilege_to_profile('role', 'role_modifier', 'ADMIN_TECH');
select privilege__grant_privilege_to_profile('role', 'role_effacer', 'ADMIN_TECH');


