--
-- Ajout des triggers pour créer automatiquement les rôles associés à toute nouvelle structure créée (Etab/ED/UR).
--

create or replace function role__create_roles_from_profils_for_structure(p_structure_id bigint) returns void
    language plpgsql
as $$begin
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

    --
    -- Ménage systématique : suppression des attributions de privilèges à des rôles si elles n'existent pas dans :
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
end
$$;


create or replace function structure_roles_update_trigger_on_etablissement() returns trigger
    language plpgsql
as $$begin
    --
    -- Fonction du trigger permettant de réagir aux ajouts de structures afin de créer les rôles manquants.
    --
    if (TG_OP = 'INSERT' or TG_OP = 'UPDATE') and new.est_etab_inscription = true then
        -- on s'intéresse seulement aux établissements d'inscription
        if (new.est_etab_inscription = false) then
            return new;
        end if;
        raise notice '[Trigger % on %] Creation des roles pour l''Etablissement %', TG_NAME, TG_TABLE_NAME, new.id;
        -- création des rôles manquants
        perform role__create_roles_from_profils_for_structure(new.structure_id);
        return new;
    else
        return coalesce(new, old);
    end if;
end
$$;

create or replace function structure_roles_update_trigger_on_ecole_doct() returns trigger
    language plpgsql
as $$begin
    --
    -- Fonction du trigger permettant de réagir aux ajouts de structures afin de créer les rôles manquants.
    --
    if TG_OP = 'INSERT' or TG_OP = 'UPDATE' then
        raise notice '[Trigger % on %] Creation des roles pour l''ED %', TG_NAME, TG_TABLE_NAME, new.id;
        -- création des rôles manquants
        perform role__create_roles_from_profils_for_structure(new.structure_id);
        return new;
    else
        return coalesce(new, old);
    end if;
end
$$;

create or replace function structure_roles_update_trigger_on_unite_rech() returns trigger
    language plpgsql
as $$begin
    --
    -- Fonction du trigger permettant de réagir aux ajouts de structures afin de créer les rôles manquants.
    --
    if TG_OP = 'INSERT' or TG_OP = 'UPDATE' then
        raise notice '[Trigger % on %] Creation des roles pour l''UR %', TG_NAME, TG_TABLE_NAME, new.id;
        -- création des rôles manquants
        perform role__create_roles_from_profils_for_structure(new.structure_id);
        return new;
    else
        return coalesce(new, old);
    end if;
end
$$;


create trigger aa_structure_roles_update_trigger_on_etablissement
    after insert or update of est_etab_inscription
    on etablissement
    for each row
execute procedure structure_roles_update_trigger_on_etablissement();

create trigger aa_structure_roles_update_trigger_on_ecole_doct
    after insert
    on ecole_doct
    for each row
execute procedure structure_roles_update_trigger_on_ecole_doct();

create trigger aa_structure_roles_update_trigger_on_unite_rech
    after insert
    on unite_rech
    for each row
execute procedure structure_roles_update_trigger_on_unite_rech();
