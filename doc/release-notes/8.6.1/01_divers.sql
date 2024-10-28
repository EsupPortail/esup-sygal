--
-- 8.6.1
--

create or replace procedure unicaen_indicateur_recreate_matviews()
    language plpgsql
as $$declare
    v_result indicateur;
    v_name varchar;
    v_template varchar = 'create materialized view %s as %s';
begin
    raise notice '%', 'Création des vues matérialisées manquantes...';
    for v_result in
        select i.* from indicateur i
                            left join pg_matviews mv on schemaname = 'public' and matviewname = 'mv_indicateur_'||i.id
        where mv.matviewname is null
        order by i.id
        loop
            v_name = 'mv_indicateur_'||v_result.id;
            raise notice '%', format('- %s...', v_name);
            execute format(v_template, v_name, v_result.requete);
        end loop;
    raise notice '%', 'Terminé.';
end
$$;


--
-- On cascade delete => restrict sur des contraintes de référence.
--

alter table rapport drop constraint rapport_annuel_fichier_fk;
alter table rapport add constraint rapport_annuel_fichier_fk foreign key (fichier_id) references fichier on delete restrict;

alter table validite_fichier drop constraint validite_fichier_ffk;
alter table validite_fichier add constraint validite_fichier_ffk foreign key (fichier_id) references fichier on delete restrict;


--
-- role__create_roles_from_profils_for_structure() :
-- suppression du ménage systématique des attributions de privilèges car des rôles ne sont pas liés à un profil.
--

create or replace function public.role__create_roles_from_profils_for_structure(p_structure_id bigint) returns void
    language plpgsql
as
$$begin
    --
    -- Procédure de création des rôles manquants à partir des profils structure-dépendants,
    -- pour la structure spécifiée.
    --

    insert into role (
        id,
        code,
        libelle,
        source_code,
        source_id,
        role_id,
        histo_createur_id,
        structure_id,
        type_structure_dependant_id
    )
    select
        nextval('role_id_seq'),
        p.role_id,
        p.libelle,
        s.source_code || '::' || p.role_id,
        app_source_id(),
        p.libelle || ' ' || case when ts.code = 'etablissement' then s.source_code else s.code end,
        app_utilisateur_id(),
        s.id,
        s.type_structure_id
    from structure s
             join type_structure ts on s.type_structure_id = ts.id
             join profil p on p.structure_type = s.type_structure_id
    where s.id = p_structure_id
    on conflict do nothing;

    --
    -- Création des profil_to_role manquants
    --
    insert into profil_to_role(profil_id, role_id)
    select p.id, r.id
    from role r
             join profil p on p.role_id = r.code
    where r.structure_id = p_structure_id
      and not exists (select * from profil_to_role p2r where p2r.role_id = r.id)
    order by code;

    --
    -- Attribution automatique des privilèges aux rôles, d'après ce qui est spécifié dans :
    --   - PROFIL_TO_ROLE (profils appliqués à chaque rôle) et
    --   - PROFIL_PRIVILEGE (privilèges accordés à chaque profil).
    --
    insert into role_privilege (role_id, privilege_id)
    select p2r.role_id, pp.privilege_id
    from profil_to_role p2r
             join role r on p2r.role_id = r.id and r.structure_id = p_structure_id
             join profil pr on pr.id = p2r.profil_id
             join profil_privilege pp on pp.profil_id = pr.id
    where not exists ( select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
    order by pr.role_id
    ;

    -- ATTENTION : ne pas faire de suppression des 'role_privilege' en trop d'après le contenu de 'profil_to_role' et de 'profil_privilege'
    -- parce que des rôles ne sont associés à aucun profil (ex: "Authentifié") et ces derniers se verraient retirer tous leurs privilèges !
end
$$;
