--
-- Version 10.1.0
--

create or replace function transfert_these(fromtheseid bigint, totheseid bigint, excepttables character varying[] DEFAULT NULL::character varying[]) returns void
    language plpgsql
as
$$declare
    v_data record;
    v_id bigint;
    v_except_tables varchar[] = ARRAY['acteur', 'financement', 'these_annee_univ', 'titre_acces'];
BEGIN
    if excepttables is not null then
        v_except_tables = array_cat(v_except_tables, excepttables);
    end if;

    raise info 'Transfert des infos liées à la thèse % vers la thèse %...', fromtheseid, totheseid;

    for v_data in
        select table_name, column_name
        from information_schema.columns
        where column_name ilike '%these_id%' and
            table_name not ilike 'v\_%' and
            table_name not ilike 'src_%' and
            table_name not ilike 'tmp_%' and
            table_name not ilike '%\_log' and
            lower(table_name) <> all(v_except_tables)
        order by table_name
        loop
            raise info '  - %.% :', v_data.table_name, v_data.column_name;
            execute 'update '||v_data.table_name||' set '||v_data.column_name||' = $1 where '||v_data.column_name||' = $2 returning id'
                using totheseid, fromtheseid
                into v_id;
            if v_id is null then
                raise info 'rien à faire';
                continue;
            end if;
            insert into transfert_these_log(table_name, column_name, from_id, to_id)
            values (v_data.table_name, v_data.column_name, fromtheseid, totheseid);
            raise info '  - %.% : OK', v_data.table_name, v_data.column_name;
        end loop;

    raise info 'Terminé.';
    raise info '(Vue matérialisée ''%'' mise à jour.)', 'mv_recherche_these';
    raise info '(Remplacements éventuels inscrits dans la table ''%''.)', 'transfert_these_log';
END
$$;
