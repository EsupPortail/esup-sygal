--
-- Procédures utilitaires permettant de supprimer/recréer les vues matérialisées des indicateurs
-- (utile dans en cas d'impossibilité de mettre à jour un élément parce qu'il est utilisé dans le script d'une VM).
--

-- drop procedure if exists unicaen_indicateur_delete_matviews;
create or replace procedure unicaen_indicateur_delete_matviews()
    language plpgsql
as
$$declare
    v_result indicateur;
    v_name varchar;
    v_template varchar = 'drop materialized view %s';
begin
    raise notice '%', 'Suppression des vues matérialisées...';
    for v_result in
        select i.* from indicateur i
            join pg_matviews mv on schemaname = 'public' and matviewname = 'mv_indicateur_'||i.id
        order by matviewname
        loop
            v_name = 'mv_indicateur_'||v_result.id;
            execute format(v_template, v_name);
            raise notice '%', format('- %s', v_name);
        end loop;
    raise notice '%', 'Terminé.';
end
$$;


-- drop procedure if exists unicaen_indicateur_recreate_matviews;
create or replace procedure unicaen_indicateur_recreate_matviews()
    language plpgsql
as
$$declare
    v_result indicateur;
    v_name varchar;
    v_template varchar = 'create materialized view %s as %s';
begin
    raise notice '%', 'Création des vues matérialisées manquantes...';
    for v_result in
        select i.* from indicateur i
            left join pg_matviews mv on schemaname = 'public' and matviewname = 'mv_indicateur_'||i.id
        where mv.matviewname is null
        order by mv.matviewname
        loop
            v_name = 'mv_indicateur_'||v_result.id;
            execute format(v_template, v_name, v_result.requete);
            raise notice '%', format('- %s', v_name);
        end loop;
    raise notice '%', 'Terminé.';
end
$$;

-- call unicaen_indicateur_delete_matviews();
-- call unicaen_indicateur_recreate_matviews();
