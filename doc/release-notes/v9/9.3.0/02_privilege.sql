--
-- 9.3.0
--

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
