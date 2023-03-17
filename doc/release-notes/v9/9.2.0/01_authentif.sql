
--
--
-- Migration vers unicaen/authentification, privilege et utilisateur.
--
--

alter table categorie_privilege rename to unicaen_privilege_categorie;
alter table privilege rename to unicaen_privilege_privilege;

alter table unicaen_privilege_categorie add NAMESPACE VARCHAR(255);

update role set is_default = false where is_default is null;
alter table role alter column is_default set not null;

-- update unicaen_privilege_privilege set code = 'privilege_voir' where code  = 'privilege-visualisation';
-- update unicaen_privilege_privilege set code = 'privilege_affecter' where code  = 'privilege-edition';

INSERT INTO unicaen_privilege_categorie (
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
create or replace function privilege__grant_privilege_to_profile(categorycode character varying, privilegecode character varying, profileroleid character varying) returns void
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

create or replace function privilege__revoke_privileges_to_profiles(categorycode character varying, privilegecodes character varying[], profileroleids character varying[]) returns void
    language plpgsql
as $$declare
    v_priv_code varchar;
    v_prof_role_id varchar;
BEGIN
    foreach v_priv_code in ARRAY privilegecodes loop
            foreach v_prof_role_id in ARRAY profileroleids loop
                    delete
                    from profil_privilege pp1
                    where exists(
                        select *
                        from profil_privilege pp
                                 join profil on pp.profil_id = profil.id and role_id = v_prof_role_id
                                 join unicaen_privilege_privilege p on pp.privilege_id = p.id
                                 join unicaen_privilege_categorie cp on p.categorie_id = cp.id
                        where p.code = v_priv_code
                          and cp.code = categoryCode
                          and pp.profil_id = pp1.profil_id
                          and pp.privilege_id = pp1.privilege_id
                    );
                end loop;
        end loop;
    perform privilege__update_role_privilege();
END;
$$;

select privilege__grant_privilege_to_profile('privilege', 'privilege_voir', 'ADMIN_TECH');
select privilege__grant_privilege_to_profile('privilege', 'privilege_ajouter', 'ADMIN_TECH');
select privilege__grant_privilege_to_profile('privilege', 'privilege_modifier', 'ADMIN_TECH');
select privilege__grant_privilege_to_profile('privilege', 'privilege_supprimer', 'ADMIN_TECH');
select privilege__grant_privilege_to_profile('privilege', 'privilege_affecter', 'ADMIN_TECH');

INSERT INTO unicaen_privilege_categorie (
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


--
-- Tables requises par unicaen/utilisateur mais non utilisée.
--

CREATE TABLE IF NOT EXISTS UNICAEN_UTILISATEUR_ROLE (
                                                        ID                    SERIAL        PRIMARY KEY,
                                                        ROLE_ID               VARCHAR(64)   NOT NULL,
                                                        LIBELLE               VARCHAR(255)  NOT NULL,
                                                        DESCRIPTION           TEXT          NOT NULL,
                                                        IS_DEFAULT            BOOLEAN       DEFAULT false NOT NULL,
                                                        IS_AUTO               BOOLEAN       DEFAULT false NOT NULL,
                                                        PARENT_ID             INTEGER,
                                                        LDAP_FILTER           VARCHAR(255)  DEFAULT NULL::character varying,
                                                        ACCESSIBLE_EXTERIEUR  BOOLEAN       DEFAULT true NOT NULL,
                                                        DISPLAYED  BOOLEAN       DEFAULT true NOT NULL,
                                                        CONSTRAINT FK_UNICAEN_UTILISATEUR_ROLE_PARENT FOREIGN KEY (PARENT_ID) REFERENCES UNICAEN_UTILISATEUR_ROLE (ID) DEFERRABLE INITIALLY IMMEDIATE
);
CREATE UNIQUE INDEX IF NOT EXISTS UN_UNICAEN_UTILISATEUR_ROLE_ROLE_ID ON UNICAEN_UTILISATEUR_ROLE (ROLE_ID);
CREATE INDEX IF NOT EXISTS IX_UNICAEN_UTILISATEUR_ROLE_PARENT ON UNICAEN_UTILISATEUR_ROLE (PARENT_ID);

CREATE TABLE IF NOT EXISTS UNICAEN_UTILISATEUR_USER (
                                                        ID                    SERIAL        PRIMARY KEY,
                                                        USERNAME              VARCHAR(255)  NOT NULL,
                                                        DISPLAY_NAME          VARCHAR(255)  NOT NULL,
                                                        EMAIL                 VARCHAR(255),
                                                        PASSWORD              VARCHAR(128)  DEFAULT 'application'::character varying NOT NULL,
                                                        STATE                 BOOLEAN       DEFAULT true NOT NULL,
                                                        PASSWORD_RESET_TOKEN  VARCHAR(256),
                                                        LAST_ROLE_ID          INTEGER,
                                                        CONSTRAINT UN_UNICAEN_UTILISATEUR_USER_USERNAME UNIQUE (USERNAME),
                                                        CONSTRAINT UN_UNICAEN_UTILISATEUR_USER_PASSWORD_RESET_TOKEN UNIQUE (PASSWORD_RESET_TOKEN),
                                                        CONSTRAINT FK_UNICAEN_UTILISATEUR_USER_LAST_ROLE FOREIGN KEY (LAST_ROLE_ID) REFERENCES UNICAEN_UTILISATEUR_ROLE(ID) DEFERRABLE INITIALLY IMMEDIATE
);
-- CREATE UNIQUE INDEX UN_UNICAEN_UTILISATEUR_USER_USERNAME ON UNICAEN_UTILISATEUR_USER(USERNAME);
-- CREATE UNIQUE INDEX UN_UNICAEN_UTILISATEUR_USER_PASSWORD_RESET_TOKEN ON UNICAEN_UTILISATEUR_USER(PASSWORD_RESET_TOKEN);
CREATE INDEX IF NOT EXISTS IX_UNICAEN_UTILISATEUR_USER_LAST_ROLE ON UNICAEN_UTILISATEUR_USER(LAST_ROLE_ID);

CREATE TABLE IF NOT EXISTS UNICAEN_UTILISATEUR_ROLE_LINKER (
                                                               USER_ID  INTEGER NOT NULL,
                                                               ROLE_ID         INTEGER NOT NULL,
                                                               CONSTRAINT PK_UNICAEN_UTILISATEUR_ROLE_LINKER PRIMARY KEY (USER_ID, ROLE_ID),
                                                               CONSTRAINT FK_UNICAEN_UTILISATEUR_ROLE_LINKER_USER FOREIGN KEY (USER_ID) REFERENCES UNICAEN_UTILISATEUR_USER (ID) DEFERRABLE INITIALLY IMMEDIATE,
                                                               CONSTRAINT FK_UNICAEN_UTILISATEUR_ROLE_LINKER_ROLE FOREIGN KEY (ROLE_ID) REFERENCES UNICAEN_UTILISATEUR_ROLE (ID) DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IF NOT EXISTS IX_UNICAEN_UTILISATEUR_ROLE_LINKER_USER ON UNICAEN_UTILISATEUR_ROLE_LINKER (USER_ID);
CREATE INDEX IF NOT EXISTS IX_UNICAEN_UTILISATEUR_ROLE_LINKER_ROLE ON UNICAEN_UTILISATEUR_ROLE_LINKER (ROLE_ID);

