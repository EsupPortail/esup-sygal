--
-- 9.3.1
--

create table _metadata
(
    code varchar(128) primary key,
    libelle varchar(512) not null,
    valeur text,
    description text,
    last_modified timestamp default current_timestamp
);

insert into _metadata (code, libelle, valeur)
values ('pg_version', 'Version de postgres requise', '15.5'),
       ('schema_version', 'Version du schéma de base de données', '9.3.1');


/*
create or replace function matviews__check_column_dropable(column_name character varying) returns void
    language plpgsql
as
$$DECLARE
    v_found text;
BEGIN
    with sel as (
        SELECT t.relname, t.relkind, a.attname,
               pg_catalog.format_type(a.atttypid, a.atttypmod) atttype
        FROM pg_attribute a
                 JOIN pg_class t on a.attrelid = t.oid
                 JOIN pg_namespace s on t.relnamespace = s.oid
        WHERE t.relkind = 'm' -- r = ordinary table, v = view, m = materialized view
          and a.attnum > 0 -- ordinary columns
          AND NOT a.attisdropped
          AND s.nspname = 'public'
          --   AND t.relname = 'mv_name'
          and a.attname = column_name
--           and a.attname = 'code_sise_disc'
        ORDER BY t.relname, t.relkind, a.attname
    )
    select string_agg(distinct(relname), ', ') into v_found
    from sel;

    if v_found is not null then
        raise exception 'La colonne % ne pourra pas être supprimée car elle est référencée dans les vues matérialisées suivantes : %', column_name, v_found;
    end if;
END;
$$;
*/
