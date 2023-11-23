--
-- Nouvelles fonctions d'ajout/retrait de privileges.
--
create or replace function privilege__grant_privileges_to_profiles(categorycode varchar, privilegecodes varchar[], profileroleids varchar[]) returns void
    language plpgsql
as
$$declare
    v_priv_code varchar;
    v_prof_role_id varchar;
BEGIN
    foreach v_priv_code in ARRAY privilegecodes loop
            foreach v_prof_role_id in ARRAY profileroleids loop
                    insert into profil_privilege (privilege_id, profil_id)
                    select p.id as privilege_id, profil.id as profil_id
                    from profil
                             join categorie_privilege cp on cp.code = categoryCode
                             join privilege p on p.categorie_id = cp.id and p.code = v_priv_code
                    where profil.role_id = v_prof_role_id
                    on conflict do nothing;
                end loop;
        end loop;
    perform privilege__update_role_privilege();
END;
$$;

create or replace function privilege__revoke_privileges_to_profiles(categorycode varchar, privilegecodes varchar[], profileroleids varchar[]) returns void
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
                                 join privilege p on pp.privilege_id = p.id
                                 join categorie_privilege cp on p.categorie_id = cp.id
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


--
-- Nouveaux privilèges Substitutions.
--
select privilege__revoke_privileges_to_profiles(
    'substitution',
    ARRAY['automatique','consultation-toutes-structures','consultation-sa-structure','modification-toutes-structures','modification-sa-structure'],
    ARRAY['ADMIN_TECH']
);

delete from privilege where categorie_id = (select id from categorie_privilege where code = 'substitution');

insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (
    select 10, 'consulter', 'Consulter les substitutions, doublons, etc.' union all
    select 20, 'modifier', 'Modifier les substitutions'
)
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d join CATEGORIE_PRIVILEGE cp on cp.CODE = 'substitution';

select privilege__grant_privileges_to_profiles('substitution', ARRAY['consulter', 'modifier'], ARRAY['ADMIN_TECH']);
