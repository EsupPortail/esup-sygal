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
