--
-- 10.2.0
--

alter table soutenance_justificatif
    drop constraint justificatif_fichier_these_fk;
alter table soutenance_justificatif
    add constraint justificatif_fichier_these_fk
        foreign key (fichier_these_id) references fichier_these
            on delete cascade;



create or replace function privilege__grant_privileges_to_profiles(categorycode character varying, privilegecodes character varying[], profileroleids character varying[]) returns void
    language plpgsql
as
$$declare
    v_priv_code varchar;
    v_priv unicaen_privilege_privilege;
    v_prof_role_id varchar;
    v_prof profil;
BEGIN
    foreach v_priv_code in ARRAY privilegecodes loop
            if not exists (select from unicaen_privilege_privilege where code = v_priv_code) then
                raise warning '[WARN] Role spécifié introuvable avec le code suivant : %', v_priv_code;
            end if;
            foreach v_prof_role_id in ARRAY profileroleids loop
                    if not exists (select from profil where role_id = v_prof_role_id) then
                        raise warning '[WARN] Profil introuvable avec le role_id suivant %', v_priv_code;
                    end if;
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

