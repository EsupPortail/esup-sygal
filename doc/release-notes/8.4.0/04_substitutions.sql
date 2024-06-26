
--
-- Activation ou désactivation des triggers du moteur de substitutions (détection automatique de doublon).
--   NB : Les triggers 'substit_trigger_on_*' ne sont pas touchés.
--
drop procedure if exists substit__set_enabled_engine;
create or replace procedure substit__set_enabled_engine(enabled boolean = true)
    language plpgsql
as
$$declare
    v_cmd varchar = case when enabled = true then 'enable' else 'disable' end;
    v_cmd_str varchar = case when enabled = true then 'Activation' else 'Désactivation' end;
    v_status_str varchar = case when enabled = true then 'activé' else 'désactivé' end;
    v_tg_data record;
begin
    raise notice '%', format('%s du moteur de substitutions...', v_cmd_str);

    -- action sur les triggers 'substit_trigger_*'
    for v_tg_data in
        select distinct event_object_table, trigger_name
        from information_schema.triggers
        where trigger_name ilike 'substit\_trigger\_%' and trigger_name not ilike 'substit\_trigger\_on\_%'
        loop
            execute format('alter table %I %s trigger %I', v_tg_data.event_object_table, v_cmd, v_tg_data.trigger_name);
            raise notice '- %', format('Trigger %L sur la table %L : %s', v_tg_data.trigger_name, v_tg_data.event_object_table, v_status_str);
        end loop;

    -- RIEN sur les triggers 'substit_trigger_on_*'
    for v_tg_data in
        select distinct event_object_table, trigger_name
        from information_schema.triggers
        where trigger_name ilike 'substit\_trigger\_on\_%'
        loop
            raise notice '- %', format('Trigger %L sur la table %L : aucun changement', v_tg_data.trigger_name, v_tg_data.event_object_table);
        end loop;
end
$$;
