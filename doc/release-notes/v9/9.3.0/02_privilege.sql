--
-- 9.3.0
--

alter table profil_to_role add histo_creation timestamp default current_timestamp not null;
alter table profil_privilege add histo_creation timestamp default current_timestamp not null;
alter table role_privilege add histo_creation timestamp default current_timestamp not null;

create or replace function privilege__update_role_privilege() returns void
    language plpgsql
as $$
BEGIN
    -- Création des 'role_privilege' manquants d'après le contenu de 'profil_to_role' et de 'profil_privilege'
    insert into role_privilege (role_id, privilege_id)
    select p2r.role_id, pp.privilege_id
    from profil_to_role p2r
             join profil pr on pr.id = p2r.profil_id
             join profil_privilege pp on pp.profil_id = pr.id
    where not exists(
        select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id
    );

    -- Suppression des 'role_privilege' existant à tort : suppression de toute attribution de privilège à un rôle lié à un profil
    -- (via 'profil_to_role') mais dont ce dernier ne possède par ce privilège.
    -- ATTENTION : ne pas faire de suppression des 'role_privilege' en trop d'après uniquement le contenu de 'profil_to_role' et de 'profil_privilege'
    -- parce que des rôles ne sont associés à aucun profil (ex: "Authentifié") et ces derniers se verraient retirer tous leurs privilèges !
    with priv_attribue_a_un_role_ayant_un_profil_nayant_pas_ce_priv as (
        select rp.role_id, rp.privilege_id, r.libelle as role_libelle, r.structure_id, p.libelle as privilege_libelle
        from role_privilege rp
                 join unicaen_privilege_privilege p on rp.privilege_id = p.id
                 join role r on rp.role_id = r.id
                 join profil_to_role p2r on r.id = p2r.role_id
        where not exists(select * from profil_privilege pp where pp.profil_id = p2r.profil_id and pp.privilege_id = p.id)
    )
    delete from role_privilege rp
    where (rp.role_id, rp.privilege_id) in (
        select role_id, privilege_id from priv_attribue_a_un_role_ayant_un_profil_nayant_pas_ce_priv
    );
END;
$$;

create or replace function privilege__grant_privilege_to_profile(categorycode character varying, privilegecode character varying, profileroleid character varying) returns void
    language plpgsql
as $$
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

create or replace function privilege__grant_privileges_to_profiles(categorycode character varying, privilegecodes character varying[], profileroleids character varying[]) returns void
    language plpgsql
as $$declare
    v_priv_code varchar;
    v_prof_role_id varchar;
BEGIN
    foreach v_priv_code in ARRAY privilegecodes loop
            foreach v_prof_role_id in ARRAY profileroleids loop
                    insert into profil_privilege (privilege_id, profil_id)
                    select p.id as privilege_id, profil.id as profil_id
                    from profil
                             join unicaen_privilege_categorie cp on cp.code = categoryCode
                             join unicaen_privilege_privilege p on p.categorie_id = cp.id and p.code = v_priv_code
                    where profil.role_id = v_prof_role_id
                    on conflict do nothing;
                end loop;
        end loop;
    perform privilege__update_role_privilege();
END;
$$;

create or replace function privilege__revoke_privilege_to_profile(categorycode character varying, privilegecode character varying, profileroleid character varying) returns void
    language plpgsql
as
$$
BEGIN
    delete
    from profil_privilege pp1
    where exists(
        select *
        from profil_privilege pp
                 join profil on pp.profil_id = profil.id and role_id = profileRoleId
                 join unicaen_privilege_privilege p on pp.privilege_id = p.id
                 join unicaen_privilege_categorie cp on p.categorie_id = cp.id
        where p.code = privilegeCode
          and cp.code = categoryCode
          and pp.profil_id = pp1.profil_id
          and pp.privilege_id = pp1.privilege_id
    );

    perform privilege__update_role_privilege();
END;
$$;

create or replace function privilege__revoke_privileges_to_profiles(categorycode character varying, privilegecodes character varying[], profileroleids character varying[]) returns void
    language plpgsql
as
$$declare
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



INSERT INTO unicaen_privilege_privilege(CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'parametrecategorie_index', 'Affichage de l''index des paramètres', 10 UNION
    SELECT 'parametrecategorie_afficher', 'Affichage des détails d''une catégorie', 20 UNION
    SELECT 'parametrecategorie_ajouter', 'Ajouter une catégorie de paramètre', 30 UNION
    SELECT 'parametrecategorie_modifier', 'Modifier une catégorie de paramètre', 40 UNION
    SELECT 'parametrecategorie_supprimer', 'Supprimer une catégorie de paramètre', 60
)
SELECT cp.id, d.code, d.lib, d.ordre
FROM d
         JOIN unicaen_privilege_categorie cp ON cp.CODE = 'parametrecategorie'
on conflict (categorie_id, code) DO UPDATE set LIBELLE = EXCLUDED.libelle, ORDRE = EXCLUDED.ordre
;

INSERT INTO unicaen_privilege_categorie (code, libelle, ordre, namespace)
VALUES ('parametre', 'UnicaenParametre - Gestion des paramètres', 70001, 'UnicaenParametre\Provider\Privilege');

INSERT INTO unicaen_privilege_privilege(CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'parametre_afficher', 'Afficher un paramètre', 10 UNION
    SELECT 'parametre_afficher_masquer', 'Afficher un paramètre masqué', 15 UNION
    SELECT 'parametre_ajouter', 'Ajouter un paramètre', 20 UNION
    SELECT 'parametre_modifier', 'Modifier un paramètre', 30 UNION
    SELECT 'parametre_supprimer', 'Supprimer un paramètre', 50 UNION
    SELECT 'parametre_valeur', 'Modifier la valeur d''un parametre', 100
)
SELECT cp.id, d.code, d.lib, d.ordre
FROM d
         JOIN unicaen_privilege_categorie cp ON cp.CODE = 'parametre'
on conflict (categorie_id, code) DO UPDATE set LIBELLE = EXCLUDED.libelle, ORDRE = EXCLUDED.ordre;

select privilege__grant_privileges_to_profiles(
        'parametrecategorie',
        ARRAY['index','afficher','ajouter','modifier','supprimer'],
        ARRAY['ADMIN_TECH']
);
select privilege__grant_privileges_to_profiles(
        'parametre',
        ARRAY['afficher','afficher_masquer','ajouter','modifier','supprimer','valeur'],
        ARRAY['ADMIN_TECH']
);
