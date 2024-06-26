--========================================================================================================
--
--      Élagage des données (diminution du volume de données en vue d'une bdd de démo par exemple).
--
--========================================================================================================

--
-- Table temporaire pour spécifier la config de l'élagage des données :
--   - Nom de la table "centrale", càd contenant les données centrales/majeures que l'on va conserver,
--     et qui détermineront les données liées de près ou de loin que l'on va conserver aussi.
--   - Requête SQL chargée de sélectionner les id de ces données centrales.
--
create table db_prune_tmp_central_data_params(
    table_name varchar(128) not null unique,
    fetch_ids_sql text not null
);
comment on column db_prune_tmp_central_data_params.table_name is 'Nom de la table centrale';
comment on column db_prune_tmp_central_data_params.fetch_ids_sql is 'Requête SQL permettant de sélectionner les id des données centrales';
insert into db_prune_tmp_central_data_params(table_name, fetch_ids_sql)
values ('these',
        '(select id from these where etat_these in (''S'') order by date_prem_insc desc limit 20) union all
         (select id from these where etat_these in (''E'') order by date_prem_insc desc limit 20)'
       );

--
-- VM pour la liste des tables à écarter de la recherche de données à élaguer.
--
drop materialized view if exists db_prune_tmp_v_excluded_tables;
create materialized view db_prune_tmp_v_excluded_tables(table_name) as
    -- tables nomenclatures
    select 'admission_etat' union
    select 'admission_type_validation' union
    select 'categorie_privilege' union
    select 'discipline_sise' union
    select 'domaine_hal' union
    select 'domaine_scientifique' union
    select 'formation_enquete_categorie' union
    select 'formation_etat' union
    select 'nature_fichier' union
    select 'origine_financement' union
    select 'pays' union
    select 'role' union
    select 'role_privilege' union
    select 'source' union
    select 'soutenance_etat' union
    select 'soutenance_qualite' union
    select 'soutenance_qualite_sup' union
    select 'type_rapport' union
    select 'type_structure' union
    select 'type_validation' union
    select 'unicaen_avis_type' union
    select 'version_fichier' union
    -- tables temporaires, de logs, etc.
    select table_name from information_schema.tables
    where table_schema = 'public'
      and (table_name like 'tmp_%' or table_name like 'substit_%' or table_name like '%_log')
;


--
-- VM pour la liste de toutes les relation existantes entre les tables.
--
drop materialized view if exists db_prune_tmp_v_fks cascade;
create materialized view db_prune_tmp_v_fks(source_table, target_table, fk_column) as
    select kcu.table_name as source_table,
           rel_tco.table_name as target_table,
           kcu.column_name as fk_column
    from information_schema.table_constraints tco
             join information_schema.key_column_usage kcu on tco.constraint_schema::name = kcu.constraint_schema::name and tco.constraint_name::name = kcu.constraint_name::name
             join information_schema.referential_constraints rco on tco.constraint_schema::name = rco.constraint_schema::name and tco.constraint_name::name = rco.constraint_name::name
             join information_schema.table_constraints rel_tco on rco.unique_constraint_schema::name = rel_tco.constraint_schema::name and rco.unique_constraint_name::name = rel_tco.constraint_name::name
    where tco.constraint_type::text = 'FOREIGN KEY'::text;

--
-- Génération des 'select' des données liées de près ou de loin en amont (de la table centrale) à conserver  :
--   - parcours récursif pour sélectionner les données liées de près ou de loin en amont de la table centrale, JUSQU'À cette dernière.
--
drop materialized view if exists db_prune_tmp_v_fks_amont_sql;
create materialized view db_prune_tmp_v_fks_amont_sql as
    with recursive fks_from(source_table, target_table, fk_column, depth, path, sql, accu)
        as (select source_table,
                  target_table,
                  fk_column,
                  1,
                  v.source_table||' > '||v.target_table,
                  format('select t.id from %s t where t.%s in (%s)', v.source_table, fk_column, ct.fetch_ids_sql),
                  ARRAY [v.target_table]
           from db_prune_tmp_v_fks v,
                db_prune_tmp_central_data_params ct
           where v.target_table = ct.table_name and v.source_table not in (select table_name from db_prune_tmp_v_excluded_tables)
           union
           select v.source_table,
                  v.target_table,
                  v.fk_column,
                  fks_from.depth + 1,
                  v.source_table||' > '||fks_from.path,
                  format('select t.id from %s t where t.%s in (%s)', v.source_table, v.fk_column, fks_from.sql),
                  fks_from.accu || ARRAY [v.target_table]
           from db_prune_tmp_v_fks v
                    join fks_from on v.target_table = fks_from.source_table
               and fks_from.source_table != ALL (fks_from.accu)
               and v.source_table not in (select table_name from db_prune_tmp_v_excluded_tables)
        )
    select source_table, target_table, fk_column, depth, sql, path
    from fks_from
;

--
-- Génération des 'select' des données liées de près ou de loin en aval (de la table centrale) à conserver  :
--   - Parcours récursif pour sélectionner les données liées de près ou de loin en aval de la table centrale, À PARTIR DE cette dernière.
--   - Pour chaque table rencontrée, on inclue toutes les données qui seraient référencées dans une table mère directe.
--
drop materialized view if exists db_prune_tmp_v_fks_aval_sql;
create materialized view db_prune_tmp_v_fks_aval_sql as
    with recursive fks_to(source_table, target_table, fk_column, depth, subdepth, path, sql, accu)
       as (select source_table,
                  target_table,
                  fk_column,
                  1, 1,
                  upper(v.source_table)||'--'||v.fk_column||'-->'||upper(v.target_table),
                  format('select t.* from %s t join %I r1 on t.id = r1.%s', v.target_table, ct.table_name, fk_column),
                  ARRAY [v.source_table]
           from db_prune_tmp_v_fks v,
                db_prune_tmp_central_data_params ct
           where v.source_table = ct.table_name and v.target_table not in (select table_name from db_prune_tmp_v_excluded_tables)
           union
           select v.source_table,
                  v.target_table,
                  v.fk_column,
                  fks_to.depth + 1, 1,
                  fks_to.path||'--'||v.fk_column||'-->'||upper(v.target_table),
                  format('select t.* from %s t join (%s) r%s on t.id = r%s.%s', v.target_table, fks_to.sql, fks_to.depth + 1, fks_to.depth + 1, v.fk_column),
                  fks_to.accu || ARRAY [v.source_table]
           from db_prune_tmp_v_fks v
                    join fks_to on v.source_table = fks_to.target_table
               and fks_to.target_table != ALL (fks_to.accu) -- évite les boucles infinies
               and v.target_table not in (select table_name from db_prune_tmp_v_excluded_tables)
        )
    select source_table, target_table, fk_column, depth, subdepth, sql, path
    from fks_to
    union
    select v.source_table,
           v.target_table,
           v.fk_column,
           depth, 2,
           format('select t.* from %s t join %s r on t.id = r.%s', v.target_table, v.source_table, v.fk_column),
           '  '||upper(v.source_table)||'--'||v.fk_column||'-->'||upper(v.target_table)
    from db_prune_tmp_v_fks v
             join fks_to on fks_to.target_table = v.target_table
;

--
-- Génération des 'delete' ordonnés nécessaires à l'élégage des données :
--    - Parcours des tables liées de près ou de loin à la table centrale, en amont (tables mères).
--    - Table centrale.
--    - Parcours des tables liées de près ou de loin à la table centrale, en aval (tables filles).
--
drop function if exists db_prune_tmp_func_generate_sql;
create or replace function db_prune_tmp_func_generate_sql()
    returns TABLE(step int, ordering bigint, depth int, table_name varchar, description varchar, sql varchar)
    language plpgsql
as
$$begin
    return query
        select 1 as step,
               row_number() over (order by v.depth desc, v.source_table) as ordering, -- Ordre important : on commence avec les tables les plus "éloignées"
               v.depth,
               v.source_table::varchar,
               format('Parcours des tables liées de près ou de loin à %I en amont (tables mères)', ct.table_name)::varchar,
               format('delete from %s where id not in (%s)', v.source_table, string_agg(v.sql, chr(10)||'union'||chr(10)))::varchar
        from db_prune_tmp_v_fks_amont_sql v,
             db_prune_tmp_central_data_params ct
        group by ct.table_name, v.depth, v.source_table
        union all
        select 2,
               1,
               0,
               ct.table_name,
               format('Table centrale "%I"', ct.table_name)::varchar,
               format('delete from %I where id not in (%s)', ct.table_name, ct.fetch_ids_sql)::varchar
        from db_prune_tmp_central_data_params ct
        union all
        select 3,
               row_number() over (order by v.depth, v.target_table), -- Ordre important : on commence avec les tables les plus "proches"
               v.depth,
               v.target_table::varchar,
               format('Parcours des tables liées de près ou de loin à %I en aval (tables filles)', ct.table_name)::varchar,
               format('delete from %I where id not in (select distinct id from (%s) t)', v.target_table, string_agg(v.sql, chr(10)||'union'||chr(10) order by v.subdepth))::varchar
        from db_prune_tmp_v_fks_aval_sql v,
             db_prune_tmp_central_data_params ct
        group by ct.table_name, v.depth, v.target_table
        --
        order by step, ordering;
end
$$;

--
-- Lancement des delete, avec affichage du nombre de suppressions.
--
drop procedure if exists db_prune_tmp_proc_prune;
create or replace procedure db_prune_tmp_proc_prune()
    language plpgsql
as
$$declare
    v_result record;
    v_count int;
    v_template varchar = 'with deleted as (%s returning *) select count(*) from deleted';
begin
    raise notice '%', 'Diminution du volume de données';
    raise notice '%', '===============================';
    refresh materialized view db_prune_tmp_v_fks;
    refresh materialized view db_prune_tmp_v_excluded_tables;
    refresh materialized view db_prune_tmp_v_fks_amont_sql;
    refresh materialized view db_prune_tmp_v_fks_aval_sql;
    raise notice '%', 'Refresh des vues matérialisées : Terminé';
    for v_result in
        select step, ordering, depth, table_name, description, sql
        from db_prune_tmp_func_generate_sql()
        order by step, ordering
        loop
            execute format(v_template, v_result.sql) into v_count;
            raise notice '  %', format('%s > Suppressions dans "%s" : %s', v_result.description, v_result.table_name, v_count);
        end loop;
end
$$;

--
-- Ménage
--
-- drop materialized view if exists db_prune_tmp_v_fks cascade;
-- drop materialized view if exists db_prune_tmp_v_excluded_tables;
-- drop materialized view if exists db_prune_tmp_v_central_records;
-- drop materialized view if exists db_prune_tmp_v_fks_amont_sql;
-- drop materialized view if exists db_prune_tmp_v_fks_aval_sql;
-- drop table if exists db_prune_tmp_central_data_params;
-- drop procedure if exists db_prune_tmp_proc_prune;
-- drop function if exists db_prune_tmp_func_generate_sql;


/*
--
-- Viusalisation des 'delete' générés.
--
select * from db_prune_tmp_func_generate_sql()
         order by step, ordering;

------------------------------------------------------------------------------------------------------------
--                                         !!! Élagage des données !!!
------------------------------------------------------------------------------------------------------------
call substit__set_enable_engine(false);
call db_prune_tmp_proc_prune();
call substit__set_enable_engine(true);
--> ~9min (sygal-db-dev)
*/
