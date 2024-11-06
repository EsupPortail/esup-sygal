--
-- Divers
--
-- Version 8.0.0
--

--
-- Restauration des valeurs originales de clés étrangères :
-- modif pour n'afficher une notice qu'en cas de remplacements faits (et pas systématiquement).
--
create or replace function substit_restore_foreign_key_value(p_type varchar, p_tab_name varchar, p_col_name varchar, p_from_id bigint, p_to_id bigint) returns int
    language plpgsql
as
$$declare
    v_id record;
    v_count int = 0;
    v_message text;
    v_fkr substit_fk_replacement;
begin
    --
    -- Restauration de la valeur de la clé étrangère originale dans une table.
    --

    v_message = format('Restaurations FK %s.%s %s => %s :', upper(p_tab_name), p_col_name, p_to_id, p_from_id);

    for v_fkr in
        select * from substit_fk_replacement
        where type = p_type and table_name = p_tab_name and column_name = p_col_name
          and from_id = p_from_id and to_id = p_to_id
        loop
            for v_id in execute format('update %I set %I = %s where id = %s and %I = %s returning id',
                                       p_tab_name, p_col_name, p_from_id, v_fkr.record_id, p_col_name, p_to_id)
                loop
                    v_count = v_count + 1;
                    perform substit_insert_log(p_type, 'FK_RESTORE', p_from_id, p_to_id, null, v_message||' '||v_id);
                end loop;
        end loop;
    if v_count > 0 then
        delete from substit_fk_replacement
        where type = p_type and table_name = p_tab_name and column_name = p_col_name and from_id = p_from_id and to_id = p_to_id;
        raise notice '% % faites.', v_message, v_count;
    end if;

    return v_count;
end
$$;

--
-- Modification de la fonction de suppression d'une substitution 'individu' :
-- en cas d'échec de la suppression du substituant à cause d'une contrainte d'intégrité (i.e. il est référencé
-- dans d'autres tables) :
--   - dans le cas où il ne reste qu'1 substitué dans la substitution on remplace partout l'id du substituant
--     par celui du dernier substitué, ce qui nous permettra ensuite de procéder à la suppression prévue.
--   - sinon, on logue simplement le problème.
--
create or replace function substit_delete_substitution(type character varying, p_substituant_id bigint) returns void
    language plpgsql
as
$$declare
    v_count int;
    v_message text;
    v_stack text;
    v_substit record;
begin
    --
    -- Supprime une substitution, spécifiée par l'enregistrement substituant.
    --

    raise notice 'Suppression du substituant % et de la substitution associée...', p_substituant_id;

    execute format('select count(*) from substit_%s where to_id = %s', type, p_substituant_id) into v_count;

    begin
        -- NB : la suppression déclenche le trigger de la table 'substit_%s'
        execute format('delete from substit_%s where to_id = %s', type, p_substituant_id);
        perform substit_insert_log(type, 'SUBSTITUTION_SUPPR', null, p_substituant_id, null,
                                   format('Suppression des %s substitutions par %s', v_count, p_substituant_id));

        execute format('delete from %I where id = %s', type, p_substituant_id);
        perform substit_insert_log(type, 'SUBSTITUANT_SUPPR', null, p_substituant_id, null,
                                   format('Suppression du substituant %s', p_substituant_id));

    exception WHEN integrity_constraint_violation THEN
        -- échec de la suppression du substituant à cause d'une contrainte d'intégrité (i.e. il est référencé
        -- dans d'autres tables) : cela annule tout ce qui est entre `begin` et `exception`.
        v_message = format('Suppression du substituant %s impossible car il est utilisé dans au moins une table ' ||
                           '(contrainte d''intégrité) : %L', p_substituant_id, v_stack);
        raise notice '%', v_message;

        if v_count = 1 then
            -- dans le cas où il ne reste qu'1 substitué dans la substitution on remplace partout l'id du substituant
            -- par celui du dernier substitué, ce qui nous permettra ensuite de procéder à la suppression prévue.
            raise notice 'Un seul substitué restant donc remplacement de l''id du substituant par l''id du substitué...';
            -- remplacement de l'id du substituant par l'id du dernier substitué restant
            execute format('select * from substit_%s where to_id = %s limit 1', type, p_substituant_id) into v_substit;
            select substit_replace_foreign_keys_values(type, p_substituant_id, v_substit.from_id) into v_count;
            -- ensuite on peut supprimer le substituant
            perform substit_delete_substitution(type, p_substituant_id);
        else
            -- sinon, on logue simplement le problème.
            GET STACKED DIAGNOSTICS v_stack = MESSAGE_TEXT;
            perform substit_insert_log(type, 'SUBSTITUANT_SUPPR_PROBLEM', null, p_substituant_id, null, v_message);
        end if;
    end;

    raise notice '=> Terminé.';
end
$$;



--
-- Fonction substit_update_substitution_if_exists() :
--   - plus de transmission de l'argument NPD, la signature est plus explicite comme ça.
--
drop function substit_update_substitution_if_exists;
create function substit_update_substitution_if_exists(type character varying, p_substitue record) returns boolean
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

    --
    -- calcul du npd de l'enregistrement (sauf si le npd est forcé).
    --
    execute format('select coalesce($1, substit_npd_%s($2))', type) using p_substitue.npd_force, p_substitue into v_npd;

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
    elseif v_substit_record.npd <> v_npd then
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

    return false;
end
$$;


--
-- Modif de la fonction substit_create_substitution_if_required() :
--   - on renvoie désormais l'id du substituant éventuellement créé.
--
drop function substit_create_substitution_if_required(varchar, varchar);
create function substit_create_substitution_if_required(type character varying, p_npd character varying) returns bigint
    language plpgsql
as
$$declare
    data record;
    cursor_doublons refcursor;
    doublon_record record;
    substituant_record_id bigint;
begin
    --
    -- Crée si nécessaire une substitution pour un NPD donné.
    --

    raise notice 'Création si necessaire d''une substitution avec le NPD %...', p_npd;

    open cursor_doublons for
        execute format('select i.* from %s i join v_%s_doublon v on v.id = i.id where v.npd = %L', type, type, p_npd);
    fetch next from cursor_doublons into doublon_record;
    if found then
        execute format('select * from substit_fetch_data_for_substituant_%s(%L) limit 1', type, p_npd) into data;
        execute format('select substit_create_substituant_%s($1)', type) using data into substituant_record_id;
        perform substit_insert_log(type, 'SUBSTITUANT_CREATE', null, substituant_record_id, p_npd,
                                   format('Nouvel enregistrement substituant : %s', substituant_record_id));

        while found loop
                raise notice '- Doublon %', doublon_record;
                perform substit_add_to_substitution(type, doublon_record.id, p_npd, substituant_record_id);
                fetch next from cursor_doublons into doublon_record;
            end loop;
    else
        raise notice '=> Aucun doublon trouvé avec le NPD "%"', p_npd;
    end if;
    close cursor_doublons;

    return substituant_record_id;
end
$$;


--
-- Nouvelle fonction substit_update_or_create_substitution_with_npd() factorisant des opérations réalisées à la fois en cas
-- d'INSERT et d'UPDATE d'enregistrements, et utilisée pour lancer la création d'une substitution à partir d'un doublon depuis l'UI.
--
create or replace function substit_update_or_create_substitution_with_npd(type character varying,
                                                                          p_npd character varying,
                                                                          p_substitue_id bigint) returns bigint
    language plpgsql
as $$declare
    data record;
    substit_record record;
    v_substituant_id bigint;
begin
    --
    -- recherche d'une substitution existante avec ce npd.
    --
    raise notice 'Recherche d''une substitution existante avec le NPD % ...', p_npd;
    execute format('select * from substit_%s where npd = %L limit 1', type, p_npd) into substit_record;

    -- si une subsitution existe, ajout de l'enregistrement à celle-ci.
    if substit_record.to_id is not null then
        raise notice '=> Substitution trouvée : %', substit_record;
        perform substit_add_to_substitution(type, p_substitue_id, p_npd, substit_record.to_id);
        -- mise à jour de l'enregistrement substituant
        execute format('select * from substit_fetch_data_for_substituant_%s(%L) limit 1', type, p_npd) into data;
        perform substit_update_substituant(type, substit_record.to_id, data);

        return substit_record.to_id;
    end if;

    raise notice '=> Aucune substitution trouvée.';

    ----> à ce stade, aucune substitution n'existe avec ce npd. <----

    --
    -- création de la substitution si le nouvel enregistrement est un doublon d'un enregistrement existant.
    --
    select substit_create_substitution_if_required(type, p_npd) into v_substituant_id;

    return v_substituant_id;
end
$$;


--
-- Fonction trigger principale du moteur :
--   - utilisation de la nouvelle fonction substit_update_or_create_substitution_with_npd().
--   - la fonction substit_update_substitution_if_exists() ne prend plus de NPD.
--
create or replace function substit_trigger_fct() returns trigger
    language plpgsql
as $$declare
    type varchar = tg_argv[0]; -- 'individu', 'doctorant', 'structure', etc.
    APP_SOURCE_ID bigint = app_source_id(); -- source correspondant à l'application
    operation varchar(32);
    operation_desc text;
    v_npd varchar(256);
    substit_record record;
begin
    --
    -- Fonction du trigger permettant de réagir aux update et insert d'un enregistrement afin de
    -- tenir à jour la liste des substituions des enregistrements en doublons par un enregistrement supplémentaire (le substituant).
    --

    -- Rappel : la source correspondant à l'application est celle dans laquelle sont créés les enregistrements substituants.
    -- Et les enregistrements doublons (i.e. substitués) ne peuvent pas être dans cette source.

    -- opération en cours
    if TG_OP = 'INSERT' and new.source_id <> APP_SOURCE_ID then
        operation = 'INSERT';
        operation_desc = format('%s - Ajout d''un nouvel enregistrement : %s', upper(type), new);
    elseif TG_OP = 'UPDATE' and new.source_id <> APP_SOURCE_ID and old.histo_destruction is not null and new.histo_destruction is null then
        operation = 'INSERT';
        operation_desc = format('%s - Restauration d''un enregistrement : %s', upper(type), new);
    elseif TG_OP = 'UPDATE' and old.source_id = APP_SOURCE_ID and new.source_id <> APP_SOURCE_ID then
        operation = 'INSERT';
        operation_desc = format('%s - Arrivée d''un enregistrement dans la source %s : %s', upper(type), new.source_id, new);
        ---
    elseif TG_OP = 'DELETE' and old.source_id <> APP_SOURCE_ID then
        operation = 'DELETE';
        operation_desc = format('%s - Suppression d''un enregistrement : %s', upper(type), old);
        ---
    elseif TG_OP = 'UPDATE' and old.source_id <> APP_SOURCE_ID and old.histo_destruction is null and new.histo_destruction is not null then
        operation = 'HISTORISATION';
        operation_desc = format('%s - Historisation d''un enregistrement : %s', upper(type), new);
        ---
    elseif TG_OP = 'UPDATE' and old.source_id <> APP_SOURCE_ID and new.source_id = APP_SOURCE_ID then
        operation = 'UPDATE';
        operation_desc = format('%s - Arrivée d''un enregistrement dans la source application %s : %s', upper(type), new.source_id, new);
    elseif TG_OP = 'UPDATE' and old.source_id <> APP_SOURCE_ID then
        operation = 'UPDATE';
        operation_desc = format('%s - Modification d''un enregistrement : %s', upper(type), new);

    else return coalesce(new, old);
    end if;

    ----------------------------------------- historisation ------------------------------------
    if operation = 'HISTORISATION' then
        raise notice '[HISTORISATION] %', operation_desc;
        raise notice 'Enregistrement : %', new;

        --
        -- recherche et mise à jour éventuelle d'une substitution existante de cet enregistrement.
        --
        perform substit_update_substitution_if_exists(type, new);

        return new;

        ----------------------------------------- suppression ------------------------------------
    elseif operation = 'DELETE' then
        raise notice '[DELETE] %', operation_desc;
        raise notice 'Enregistrement : %', old;

        --
        -- recherche et mise à jour éventuelle d'une substitution existante de cet enregistrement.
        --
        perform substit_update_substitution_if_exists(type, old);

        return old;

        ----------------------------------------- ajout ou restauration ------------------------------------
    elseif operation = 'INSERT' then
        raise notice '[INSERT] %', operation_desc;
        raise notice 'Enregistrement : %', new;

        --
        -- Gestion particulière du cas où l'ajout est fait avec un INSERT...SELECT : le trigger se déclenche bien N fois
        -- pour chacune des N lignes insérées mais à chaque exécution c'est comme si la table contenait les N
        -- enregistrements en cours d'insertion ! Ce qui veut dire qu'un enregistrement en cours d'insertion peut avoir
        -- déjà été substitué lors d'une précédente exécution du trigger.
        -- Si c'est le cas, une erreur d'unicité dans SUBSTIT_XXXX est levée puisque l'on tente d'ajouter un substitué
        -- à une substitution où il est déjà présent.
        -- On doit donc tester systématiquement que l'enregistrement inséré n'est pas déjà substitué !
        --
        execute format('select to_id from substit_%s where from_id = %s', type, new.id) into substit_record;
        if substit_record.to_id is not null then
            return new;
        end if;

        --
        -- calcul du npd de l'enregistrement (sauf si le npd est forcé).
        --
        execute format('select coalesce($1, substit_npd_%s($2))', type) using new.npd_force, new into v_npd;

        --
        -- mise à jour ou création si nécessaire d'une substitution pour ce NPD.
        --
        perform substit_update_or_create_substitution_with_npd(type, v_npd, new.id);

        return new;

        ----------------------------------------- modification ------------------------------------
    elseif operation = 'UPDATE' then
        raise notice '[UPDATE] %', operation_desc;
        raise notice 'Enregistrement avant : %', old;
        raise notice 'Enregistrement après : %', new;

        --
        -- mise à jour de la substitution éventuelle de cet enregistrement.
        --
        if substit_update_substitution_if_exists(type, new) = true then
            return new;
        end if;

        --
        -- calcul du npd de l'enregistrement (sauf si le npd est forcé).
        --
        execute format('select coalesce($1, substit_npd_%s($2))', type) using new.npd_force, new into v_npd;

        --
        -- mise à jour ou création si nécessaire d'une substitution pour ce NPD.
        --
        perform substit_update_or_create_substitution_with_npd(type, v_npd, new.id);

        return new;
    end if;
end
$$;
