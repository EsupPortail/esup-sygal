--
-- Petite amélioration en évitant un calcul inutile du NPD.
--
create or replace function substit_update_substitution_if_exists(type character varying, p_substitue record) returns boolean
    language plpgsql
as $$declare
    v_npd varchar(256);
    v_count int;
    v_data record;
    v_substit_record record;
begin
    --
    -- Recherche et mise à jour de la substitution existante spécifiée par l'enregistrement substitué.
    --
    -- Retourne `true` s'il n'y a plus rien à faire (càd que le cas de l'enregistrement est traité) ;
    -- ou `false` dans le cas où il faudra rechercher si l'enregistrement est en doublon et doit faire l'objet
    -- d'une nouvelle substitution.
    --

    raise notice 'Recherche si l''enregistrement % est substitué...', p_substitue.id;

    execute format('select * from substit_%s where from_id = %s', type, p_substitue.id) into v_substit_record;
    if v_substit_record.to_id is null then
        raise notice '=> Aucune substitution trouvée.';
        return false;
    end if;

    if p_substitue.source_id = app_source_id() then
        raise notice '=> Oui mais l''enregistrement est dans la source application.';
        raise notice '=> Retrait de l''enregistrement et mise à jour du substituant...';
        perform substit_remove_from_substitution(type, v_substit_record.from_id, v_substit_record.to_id);
        return true;
    else
        --
        -- calcul du npd de l'enregistrement (sauf si le npd est forcé).
        --
        execute format('select coalesce($1, substit_npd_%s($2))', type) using p_substitue.npd_force, p_substitue into v_npd;

        if v_substit_record.npd <> v_npd then
            raise notice '=> Oui mais le NPD de l''enregistrement (%) a changé par rapport à celui de la substitution (%).', v_npd, v_substit_record.npd;
            raise notice '=> Retrait de l''enregistrement et mise à jour du substituant...';
            perform substit_remove_from_substitution(type, v_substit_record.from_id, v_substit_record.to_id);
            return false;
        elseif v_substit_record.npd = v_npd then
            raise notice '=> Oui et le NPD de l''enregistrement égale celui de la substitution (%).', v_npd;
            raise notice '=> Mise à jour de l''enregistrement substituant...';
            execute format('select count(*) from substit_fetch_data_for_substituant_%s(%L) limit 1', type, v_npd) into v_count;
            if v_count = 0 then
                raise exception 'Impossible de mettre à jour le substituant car aucun doublon de type % trouvé avec le NPD %', type, v_npd;
            end if;
            execute format('select * from substit_fetch_data_for_substituant_%s(%L) limit 1', type, v_npd) into v_data;
            perform substit_update_substituant(type, v_substit_record.to_id, v_data);
            return true;
        end if;
    end if;

    return false;
end
$$;
