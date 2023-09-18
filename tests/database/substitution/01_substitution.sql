--
-- Substitutions
--

--drop table substit_log;
create table substit_log (
    id bigserial primary key,
    type varchar(128) not null,
    substitue_id bigint,
    substituant_id bigint not null,
    npd varchar(256),
    log text not null,
    created_on timestamp default current_timestamp not null
);


--
-- Vue listant toutes les clés étrangères (FK), la table source et la table destination associées.
--
-- Utilisée pour la correction les valeurs des clés étrangères substituées.
--
-- drop view v_substit_foreign_keys;
create or replace view v_substit_foreign_keys as
select kcu.table_name as source_table,
       rel_tco.table_name as target_table,
       kcu.column_name as fk_column,
       kcu.constraint_name
from information_schema.table_constraints tco
         join information_schema.key_column_usage kcu
              on tco.constraint_schema = kcu.constraint_schema
                  and tco.constraint_name = kcu.constraint_name
         join information_schema.referential_constraints rco
              on tco.constraint_schema = rco.constraint_schema
                  and tco.constraint_name = rco.constraint_name
         join information_schema.table_constraints rel_tco
              on rco.unique_constraint_schema = rel_tco.constraint_schema
                  and rco.unique_constraint_name = rel_tco.constraint_name
where tco.constraint_type = 'FOREIGN KEY';


create or replace function normalized_string(str varchar) returns varchar
    language plpgsql
as
$$begin
    --
    -- Fonction de normalisation d'une chaîne de caractères.
    --

    return unaccent(
        replace(
            translate(
                regexp_replace(lower(str), '[_ ''"\.,@\-]', '', 'g'),
                'åãñ', 'aan' -- translate
            ),
            'æ', 'ae' -- replace
        )
    );
end;
$$;


create or replace function app_utilisateur_id() returns bigint
    language plpgsql
as
$$begin
    --
    -- Retourne l'id du pseudo-utilisateur correspondant à l'application.
    --
    -- Les substituants sont créés/modifiés par cet utilisateur.
    --

    return 1; -- todo : comment mieux repérer l'utilisateur ?
end;
$$;


create or replace function app_source_id() returns bigint
    language plpgsql
as
$$begin
    --
    -- Retourne l'id de la source correspondant à l'application.
    --
    -- Les substituants sont créés dans cette source.
    -- Les doublons ne sont pas recherchés dans cette source.
    --

    return 1; -- todo : identifier la source à partir de son code ? mais quid du temps de réponse ?
end;
$$;


create or replace function app_source_source_code() returns varchar
    language plpgsql
as
$$begin
    --
    -- Fournit un 'source_code' adapté à la source correspondant à l'application.
    --

    return 'SyGAL::'||trunc(100000000000000*random());
end;
$$;


create or replace function substit_insert_log(type varchar,
                                              substitue_id bigint,
                                              substituant_id bigint,
                                              npd varchar,
                                              log text) returns void
    language plpgsql
as
$$begin
    insert into substit_log(type, substitue_id, substituant_id, npd, log)
    values (type, substitue_id, substituant_id, npd, log);
end
$$;


create or replace function substit_create_substitution_if_required(type varchar, p_npd varchar) returns void
    language plpgsql
as
$$declare
    data record;
    cursor_doublons refcursor;
    doublon_record record;
    substituant_record_id bigint;
begin
    --
    -- Crée si nécessaire une subsitution pour un NPD donné.
    --

    raise notice 'Création si necessaire d''une substitution avec le NPD %...', p_npd;

    open cursor_doublons for
        execute format('select i.* from pre_%s i join v_%s_doublon v on v.id = i.id where v.npd = %L', type, type, p_npd);
    fetch next from cursor_doublons into doublon_record;
    if found then
        execute format('select * from substit_fetch_data_for_substituant_%s(%L) limit 1', type, p_npd) into data;
        execute format('select substit_create_substituant_%s($1)', type) using data into substituant_record_id;
        perform substit_insert_log(type, null, substituant_record_id, p_npd,
                                   format('Nouvel enregistrement substituant : %s', substituant_record_id));

        while found loop
            raise notice '- Doublon %', doublon_record;
            perform substit_add_to_substitution(type, doublon_record.id, p_npd, substituant_record_id);
            fetch next from cursor_doublons into doublon_record;
        end loop;
    else
        raise notice '=> Aucun doublon trouvé avec le NPD %', p_npd;
    end if;
    close cursor_doublons;
end
$$;


create or replace function substit_update_substituant(type varchar, p_substituant_id bigint, data record) returns void
    language plpgsql
as
$$begin
    --
    -- Mise à jour des attributs de l'enregistrement substituant spécifié, à partir des valeurs spécifiées.
    --

    raise notice 'Mise à jour du substituant % avec les valeurs %', p_substituant_id, data;

    execute format('select substit_update_substituant_%s(%s, $1)', type, p_substituant_id) using data;

    perform substit_insert_log(type, null, p_substituant_id, null,
                               format('Mise à jour du substituant %s', p_substituant_id));
end
$$;


create or replace function substit_delete_substitution(type varchar, p_substituant_id bigint) returns void
    language plpgsql
as
$$declare
    v_substit_record record;
    v_cursor_substit refcursor;
begin
    --
    -- Supprime une substitution, spécifiée par l'enregistrement substituant.
    --

    -- NB : supprimer physiquement une substitution (et le substituant) dès lors qu'il ne reste qu'un enregistrement substitué
    -- est une mauvaise idée car l'enregistrement substituant peut être référencé dans des tables.

    raise notice 'Historisation du substituant % et de la substitution associée...', p_substituant_id;

    open v_cursor_substit for execute format('select * from %s_substit where to_id = %s', type, p_substituant_id);
    fetch next from v_cursor_substit into v_substit_record;
    while found loop
        execute format('update %s_substit set histo_destruction = current_timestamp, histo_destructeur_id = 1
                       where id = %s', type, v_substit_record.id);
        perform substit_insert_log(type, v_substit_record.from_id, p_substituant_id, v_substit_record.npd,
                                   format('Historisation de la substitution de %s par %s', v_substit_record.from_id, v_substit_record.to_id));
        fetch next from v_cursor_substit into v_substit_record;
    end loop;
    close v_cursor_substit;

    execute format('update %I set histo_destruction = current_timestamp, histo_destructeur_id = 1
                   where id = %s', type, p_substituant_id);
    perform substit_insert_log(type, null, substituant_record_id, null,
                               format('Historisation du substituant %s', p_substituant_id));

    raise notice '=> Fait.';
end
$$;


create or replace function substit_add_to_substitution(type varchar,
                                                       p_substitue_id bigint,
                                                       p_substitue_npd varchar,
                                                       p_substituant_id bigint) returns void
    language plpgsql
as
$$declare
--     log text;
begin
    --
    -- Ajout d'un enregistrement dans une substitution existante spécifiée par l'id de l'enregistrement substituant.
    --

    raise notice 'Ajout de l''enregistrement % à la substitution par %...', p_substitue_id, p_substituant_id;

    execute format('insert into %s_substit (from_id, to_id, npd, histo_createur_id)
                    values (%s, %s, %L, 1)', type, p_substitue_id, p_substituant_id, p_substitue_npd);

    perform substit_insert_log(type, p_substituant_id, p_substitue_id, p_substitue_npd,
                               format('Ajout de %s à la substitution par %s', p_substitue_id, p_substituant_id));
end
$$;


create or replace function substit_remove_from_substitution(type varchar,
                                                            p_substitue_id bigint,
                                                            p_substituant_id bigint) returns record
    language plpgsql
as
$$declare
    substit_record record;
    data record;
--     log text;
begin
    --
    -- Retrait d'un enregistrement d'une substitution spécifiée par l'enregistrement substituant.
    --

    raise notice 'Historisation de la substitution de % par le substituant %...', p_substitue_id, p_substituant_id;

    execute format('update %s_substit set histo_destruction = current_timestamp, histo_destructeur_id = 1
        where from_id = %s and to_id = %s
        returning *', type, p_substitue_id, p_substituant_id) into substit_record;

    raise notice '=> %', substit_record;

    perform substit_insert_log(type, p_substituant_id, p_substitue_id, null,
                               format('Historisation de la substitution de %s par %s', p_substitue_id, p_substituant_id));

    -- mise à jour de la substitution
    execute format('select * from substit_fetch_data_for_substituant_%s(%L) limit 1', type, substit_record.npd) into data;
    -- NB : si aucune donnée n'est retournée, c'est que la substitution n'a plus de raison d'être (0 doublon).
    if data is null then
        raise notice 'Aucune donnée trouvée donc suppression de la substitution...';
        perform substit_delete_substitution(type, substit_record.to_id);
    else
        perform substit_update_substituant(type, substit_record.to_id, data);
    end if;

    return substit_record;
end
$$;


create or replace function substit_update_substitution_if_exists(type varchar,
                                                                 p_npd varchar,
                                                                 p_substitue record) returns bool
    language plpgsql
as
$$declare
    v_data record;
    v_substit_record record;
begin
    --
    -- Recherche et mise à jour de la substitution existante dont l'enregistrement spécifié est l'enregistrement substitué.
    --
    -- Retourne `true` s'il n'y a plus rien à faire (càd que le cas de l'enregistrement est traité) ;
    -- ou `false` dans le cas où il faudra rechercher si l'enregistrement est en doublon et doit faire l'objet d'une substitution.
    --

    raise notice 'Recherche si l''enregistrement % est substitué...', p_substitue.id;

    execute format('select * from %s_substit where histo_destruction is null and from_id = %s', type, p_substitue.id)
        into v_substit_record;
    if v_substit_record.to_id is not null then
        if p_substitue.source_id = app_source_id() then
            raise notice '=> Oui mais l''enregistrement est dans la source application.';
            raise notice '=> Retrait de l''enregistrement et mise à jour du substituant...';
            perform substit_remove_from_substitution(type, v_substit_record.from_id, v_substit_record.to_id);
            return true;
        elseif p_substitue.histo_destruction is not null then
            raise notice '=> Oui mais l''enregistrement est historisé.';
            raise notice '=> Retrait de l''enregistrement et mise à jour du substituant...';
            perform substit_remove_from_substitution(type, v_substit_record.from_id, v_substit_record.to_id);
            return true;
        elseif v_substit_record.npd = p_npd then
            raise notice '=> Oui et le NPD de l''enregistrement égale celui de la substitution (%).', p_npd;
            raise notice '=> Mise à jour de l''enregistrement substituant...';
            execute format('select * from substit_fetch_data_for_substituant_%s(%L) limit 1', type, p_npd) into v_data;
            perform substit_update_substituant(type, v_substit_record.to_id, v_data);
            return true;
        else
            raise notice '=> Oui mais le NPD de l''enregistrement (%) a changé par rapport à celui de la substitution (%).', p_npd, v_substit_record.npd;
            raise notice '=> Retrait de l''enregistrement et mise à jour du substituant...';
            perform substit_remove_from_substitution(type, v_substit_record.from_id, v_substit_record.to_id);
        end if;
    else
        raise notice '=> Aucune substitution trouvée.';
    end if;

    return false;
end
$$;


--drop function substit_trigger_on_pre_fct;
create or replace function substit_trigger_on_pre_fct() returns trigger
    language plpgsql
as
$$declare
    type varchar = tg_argv[0]; -- 'individu', 'doctorant', 'structure', etc.
    APP_SOURCE_ID bigint = app_source_id(); -- source correspondant à l'application
    operation varchar(32);
    operation_desc text;
    data record;
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
        -- calcul du npd de l'enregistrement (sauf si le npd est forcé).
        --
        execute format('select coalesce($1, substit_npd_%s($2))', type) using new.npd_force, new into v_npd;

        --
        -- recherche et mise à jour éventuelle d'une substitution existante de cet enregistrement.
        --
        perform substit_update_substitution_if_exists(type, v_npd, new);

        return new;
    end if;

    ----------------------------------------- suppression ------------------------------------
    if operation = 'DELETE' then
        raise notice '[DELETE] %', operation_desc;
        raise notice 'Enregistrement : %', old;

        --
        -- calcul du npd de l'enregistrement (sauf si le npd est forcé).
        --
        execute format('select coalesce($1, substit_npd_%s($2))', type) using old.npd_force, old into v_npd;

        --
        -- recherche et mise à jour éventuelle d'une substitution existante de cet enregistrement.
        --
        perform substit_update_substitution_if_exists(type, v_npd, old);

        return old;
    end if;

    ----------------------------------------- ajout ou restauration ------------------------------------
    if operation = 'INSERT' then
        raise notice '[INSERT] %', operation_desc;
        raise notice 'Enregistrement : %', new;

        --
        -- calcul du npd de l'enregistrement (sauf si le npd est forcé).
        --
        execute format('select coalesce($1, substit_npd_%s($2))', type) using new.npd_force, new into v_npd;

        --
        -- recherche d'une substitution avec ce npd.
        --
        raise notice 'Recherche d''une substitution existante avec le NPD %...', v_npd;
        execute format('select * from %s_substit where histo_destruction is null and npd = %L limit 1', type, v_npd) into substit_record;
        -- si une subsitution existe, ajout de l'enregistrement à celle-ci.
        if substit_record.to_id is not null then
            raise notice '=> %...', substit_record;
            perform substit_add_to_substitution(type, new.id, v_npd, substit_record.to_id);
            -- mise à jour de l'enregistrement substituant
            execute format('select * from substit_fetch_data_for_substituant_%s(%L) limit 1', type, v_npd) into data;
            perform substit_update_substituant(type, substit_record.to_id, data);

            return new;
        end if;

        raise notice '=> Aucune';

        ----> à ce stade, aucune substitution n'existe avec ce npd. <----

        --
        -- création de la substitution si le nouvel enregistrement est un doublon d'un enregistrement existant.
        --
        perform substit_create_substitution_if_required(type, v_npd);

        return new;
    end if;

    ----------------------------------------- modification ------------------------------------
    if operation = 'UPDATE' then
        raise notice '[UPDATE] %', operation_desc;
        raise notice 'Enregistrement avant : %', old;
        raise notice 'Enregistrement après : %', new;

        --
        -- calcul du npd de l'enregistrement (sauf si le npd est forcé).
        --
        execute format('select coalesce($1, substit_npd_%s($2))', type) using new.npd_force, new into v_npd;

        --
        -- recherche et mise à jour éventuelle d'une substitution existante de cet enregistrement.
        --
        if substit_update_substitution_if_exists(type, v_npd, new) = true then
            return new;
        end if;

        --
        -- recherche d'une substitution existante pour le npd de l'enregistrement.
        --
        raise notice 'Recherche d''une substitution existante avec le NPD % de l''enregistrement modifié...', v_npd;
        execute format('select * from %s_substit where histo_destruction is null and npd = %L limit 1', type, v_npd) into substit_record;
        if substit_record.to_id is not null then
            raise notice '=> %', substit_record;
            perform substit_add_to_substitution(type, new.id, v_npd, substit_record.to_id);
            -- mise à jour de l'enregistrement substituant
            execute format('select * from substit_fetch_data_for_substituant_%s(%L) limit 1', type, v_npd) into data;
            perform substit_update_substituant(type, substit_record.to_id, data);
        else
            raise notice '=> Aucune substitution trouvée.';
            -- création éventuelle d'une substitution si le nouvel enregistrement est un doublon d'un enregistrement existant.
            perform substit_create_substitution_if_required(type, v_npd);
        end if;

        return new;
    end if;
end
$$;


-- drop function substit_trigger_on_substit_fct;
create or replace function substit_trigger_on_substit_fct() returns trigger
    language plpgsql
as
$$declare
    type varchar = tg_argv[0]; -- 'individu', 'doctorant', 'structure', etc.
    v_count smallint;
begin
    --
    -- Fonction du trigger permettant de réagir à l'apparition (insert ou dehistorisation) ou disparition (delete ou historisation)
    -- d'une substitution d'enregistrement.
    --

    if TG_OP = 'INSERT' or TG_OP = 'UPDATE' and old.histo_destruction is not null and new.histo_destruction is null then
        --
        -- Apparition d'une substitution.
        --
        raise notice 'Apparition de la substitution % (%) : % => %.', new.id, type, new.from_id, new.to_id;
        -- remplacements des valeurs des FK par l'id du substituant
        perform substit_replace_foreign_keys_values(type, new.from_id, new.to_id);
        -- suppression du substitué (mais il continue d'exister dans la table PRE_XXX)
        perform substit_remove_substitue(type, new.from_id, new.to_id);

    elsif TG_OP = 'DELETE' or TG_OP = 'UPDATE' and old.histo_destruction is null and new.histo_destruction is not null then
        --
        -- Disparition d'une substitution.
        --
        raise notice 'Disparition de la substitution % (%) : % => %.', old.id, type, old.from_id, old.to_id;
        execute format('select count(*) from %s_substit where histo_destruction is null and to_id = %s', type, old.to_id) into v_count;
        if v_count = 0 then
            -- Si c'est la dernière substitution qui est supprimée...
            -- création/restauration du substitué dans la table finale
            perform substit_restore_substitue(type, old.from_id, old.to_id);
            -- remplacements des valeurs des FK par l'id du substitué
            perform substit_replace_foreign_keys_values(type, old.to_id, old.from_id);
        end if;

    end if;

    return coalesce(new, old);
end
$$;


-- drop function substit_replace_foreign_keys_values;
create or replace function substit_replace_foreign_keys_values(p_table varchar, p_from_id bigint, p_to_id bigint) returns void
    language plpgsql
as
$$declare
    v_substit_fk v_substit_foreign_keys;
    v_col_name varchar(100);
    v_tab_name varchar(100);
    v_id bigint;
    v_message text;
begin
    --
    -- Parcours des tables ayant une clé étrangère vers la table spécifiée, pour remplacer les valeurs de clés étrangères.
    -- Les remplacements qui déclenchent une erreur d'unicité ne sont pas gérés : il ne sont tout simplement pas faits.
    --

    for v_substit_fk in
        select * from v_substit_foreign_keys
                 where target_table = p_table
                   and source_table not in (p_table, 'substit_'||p_table,
                                           'etablissement', 'ecole_doct', 'unite_rech') -- todo : à virer, ce n'est pas la solution !
                 order by source_table
    loop
        v_tab_name = v_substit_fk.source_table;
        v_col_name = v_substit_fk.fk_column;

        ---------> todo : >>>>>>>>>>> empecher le remplacement de certaines FK précises <<<<<<<<<<<<

        v_message = format('Correction de la FK %L dans %L : ', v_col_name, v_tab_name);

        -- Remplacement éventuelle de la valeur de la clé étrangère.
        -- NB : pas possible d'écarter les historisés et de màj histo_modification car toutes les tables n'ont pas les colonnes histo_* !
        begin
            for v_id in
                execute format('update %I set %I = %s where %I = %s returning id', v_tab_name, v_col_name, p_to_id, v_col_name, p_from_id, v_tab_name) loop
                    perform substit_insert_log(p_table, p_from_id, p_to_id, null, v_message || format('id %s : %s => %s', v_id, p_from_id, p_to_id));
                    v_message = v_message || chr(10) || format('- id %s : %s => %s', v_id, p_from_id, p_to_id);
            end loop;
            if v_id is not null then
                raise notice '%', v_message;
            end if;

            exception WHEN unique_violation THEN
                -- échec du remplacement à cause d'une erreur d'unicité
                perform substit_insert_log(p_table, p_from_id, p_to_id, null, v_message || format('%s => %s impossible (problème d''unicité)', p_from_id, p_to_id));
                v_message = v_message || chr(10) || format('- %s => %s impossible (problème d''unicité)', p_from_id, p_to_id);
                raise notice '%', v_message;
                -- todo : historiser la ligne puisqu'il y aen a forcément une autre ? (mais la table n'a pas forcément de colonne d'histo)
        end;
    end loop;
end
$$;


-- drop function substit_remove_substitue;
create or replace function substit_remove_substitue(type varchar, p_substitue_id bigint, p_substituant_id bigint) returns void
    language plpgsql
as
$$declare
    APP_SOURCE_ID bigint = app_source_id(); -- source correspondant à l'application
begin
    raise notice 'Historisation du substitué %', p_substitue_id;

    execute format(
        'update %I set histo_destruction = current_timestamp, histo_destructeur_id = %s where id = %s',
        type, APP_SOURCE_ID, p_substitue_id);

    perform substit_insert_log(type, p_substitue_id, p_substituant_id, null, format('Historisation du substitué %s', p_substitue_id));
end
$$;


-- drop function substit_restore_substitue;
create or replace function substit_restore_substitue(type varchar, p_substitue_id bigint, p_substituant_id bigint) returns void
    language plpgsql
as
$$declare
    v_message text;
    v_record record;
begin
    v_message = format('Restauration du substitué %s dans %L : ', p_substitue_id, type);

    execute format('select * from %I where id = %s', type, p_substitue_id) into v_record;

    -- si le substitué historisé est trouvé, on le restaure
    if v_record.id is not null and v_record.histo_destruction is not null then
        execute format('update %I set histo_destruction = null, histo_destructeur_id = null where id = %s', type, p_substitue_id);
        v_message = v_message || 'dehistorisation ok.';
    elsif v_record.id is not null and v_record.histo_destruction is null then
        -- on ne devrait pas être dans ce cas, le substitué est censé avoir été historisé.
        v_message = v_message || 'inutile (substitué non historisé).';
    elsif v_record.id is null then
        -- si le substitué n'est pas trouvé, on laisse tomber
        v_message = v_message || 'impossible (introuvable).';
    end if;

    raise notice '%', v_message;

    perform substit_insert_log(type, p_substitue_id, p_substituant_id, null, v_message);
end
$$;