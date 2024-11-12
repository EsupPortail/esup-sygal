--
-- 9.1.0
--

--
-- Remplacement de la vue matérialisée "mv_recherche_these" par une simple vue 'these_rech'
-- pour ne plus avoir de pb de recherche de thèse infructueuse nécessitant un refresh.
-- Les temps de réponse n'ont pas l'air d'exploser !
--

create or replace function str_reduce(str text) returns text
    language plpgsql
as $$
BEGIN
    -- return lower(public.unaccent(str));
    return lower(public.unaccent(replace(replace(str, chr(10), ''), chr(13), '')));
END;
$$;

drop view if exists these_rech;

drop function if exists these_rech_compute_haystack;

create function these_rech_compute_haystack(
    eds_code varchar,
    urs_code varchar,
    t_titre varchar,
    d_source_code varchar,
    id_nom_patronymique varchar,
    id_nom_usuel varchar,
    id_prenom1 varchar,
    a_agg varchar
) returns varchar
    language plpgsql as
$$begin
    return btrim(str_reduce(
            'code-ed{' || COALESCE(eds_code, '') || '} ' ||
            'code-ur{' || COALESCE(urs_code, '') || '} ' ||
            'titre{' || t_titre || '} ' ||
            'doctorant-numero{' || substr(d_source_code, "position"(d_source_code, '::') + 2) || '} ' ||
            'doctorant-nom{' || id_nom_patronymique || ' ' || id_nom_usuel || '} ' ||
            'doctorant-prenom{' || id_prenom1 || '} ' ||
            'directeur-nom{' || a_agg || '} '
        ));
end;
$$;

create view these_rech as
WITH acteurs AS (
    SELECT a.these_id,
           string_agg(COALESCE(ia.nom_usuel::text, ''::text), ' '::text) AS agg
    FROM acteur a
             JOIN these t on a.these_id = t.id and t.histo_destruction is null
             JOIN role r ON a.role_id = r.id AND r.code in ('D', 'K') -- dir, codir de thèse
             JOIN individu ia ON ia.id = a.individu_id and ia.histo_destruction is null
    WHERE a.histo_destruction IS NULL
    GROUP BY 1
)
SELECT 'now'::text::timestamp without time zone AS date_creation,
       t.source_code AS code_these,
       d.source_code AS code_doctorant,
       ed.source_code AS code_ecole_doct,
       these_rech_compute_haystack(eds.code,
                                   urs.code,
                                   t.titre,
                                   d.source_code,
                                   id.nom_patronymique,
                                   id.nom_usuel,
                                   id.prenom1,
                                   a.agg::character varying) AS haystack
FROM these t
         JOIN doctorant d ON d.id = t.doctorant_id
         JOIN individu id ON id.id = d.individu_id
         LEFT JOIN ecole_doct ed ON t.ecole_doct_id = ed.id
         LEFT JOIN structure eds ON ed.structure_id = eds.id
         LEFT JOIN unite_rech ur ON t.unite_rech_id = ur.id
         LEFT JOIN structure urs ON ur.structure_id = urs.id
         LEFT JOIN acteurs a ON a.these_id = t.id;



/********************************************* Idée abandonnée ****************************************************/
--
-- Mise à jour auto de la vue matérialisée "mv_recherche_these" à l'aide d'un trigger.
--
--
-- drop table if exists mv_recherche_these_update_log;
--
-- create table mv_recherche_these_update_log (
--     id bigserial primary key,
--     started_on timestamp not null,
--     ended_on timestamp not null,
--     table_name varchar not null,
--     log varchar(128) not null
-- );
--
-- drop trigger if exists zz__mv_recherche_these_refresh on these;
-- drop trigger if exists zz__mv_recherche_these_refresh on doctorant;
-- drop trigger if exists zz__mv_recherche_these_refresh on structure;
-- drop trigger if exists zz__mv_recherche_these_refresh on individu;
-- drop trigger if exists zz__mv_recherche_these_refresh on acteur;
--
-- drop function if exists refresh_mv_recherche_these cascade;
--
-- create function refresh_mv_recherche_these() returns trigger
--     language plpgsql as
-- $$declare
--     p_table_name varchar = tg_argv[0];
--     startedon timestamp;
-- begin
--     if (p_table_name = 'these') then
--         -- rien à vérifier en plus des conditions mises dans le 'after' du trigger
--
--     elseif (p_table_name = 'acteur') then
--         -- rien à vérifier en plus des conditions mises dans le 'after' du trigger
--
--     elseif (p_table_name = 'doctorant') then
--         -- verif au moins que le doctorant est référencé dans une thèse
--         if (not exists(select from these t
--                        where t.doctorant_id = new.id and t.histo_destruction is null)) then
--             return new;
--         end if;
--
--     elseif (p_table_name = 'structure') then
--         -- verif qu'il s'agit d'une structure de type ED ou UR et qu'elle est référencée dans une thèse
--         if (not exists(select from ecole_doct ed
--                        join these t on t.ecole_doct_id = ed.id and t.histo_destruction is null
--                        where ed.structure_id = new.id and ed.histo_destruction is null)
--             and not exists(select from unite_rech ur
--                            join these t on t.unite_rech_id = ur.id and t.histo_destruction is null
--                            where ur.structure_id = new.id and ur.histo_destruction is null)) then
--             return new;
--         end if;
--
--     elseif (p_table_name = 'individu') then
--         -- verif qu'il s'agit d'un individu doctorant ou acteur de thèse
--         if (not exists(select from doctorant d
--                        where d.individu_id = new.id and d.histo_destruction is null)
--             and not exists(select from acteur a
--                            where a.individu_id  = new.id and a.histo_destruction is null)) then
--             return new;
--         end if;
--
--     else
--         -- autre cas non prévu, stop !
--         return new;
--     end if;
--
--     startedon = clock_timestamp();
--     refresh materialized view mv_recherche_these;
--     insert into mv_recherche_these_update_log(table_name, started_on, ended_on, log)
--     values (p_table_name, startedon, clock_timestamp(), format('Trigger %s : %s on %s', tg_name, tg_op, tg_table_name));
--
--     return new;
-- end;
-- $$;
--
-- create trigger zz__mv_recherche_these_refresh
--     after insert or update of titre, doctorant_id, ecole_doct_id, unite_rech_id on these
--     for each row
--     when ( new.histo_destruction is null )
-- execute function refresh_mv_recherche_these('these');
--
-- create trigger zz__mv_recherche_these_refresh
--     after /*insert or */update of source_code on doctorant
--     for each row
--     when ( new.histo_destruction is null )
-- execute function refresh_mv_recherche_these('doctorant');
--
-- create trigger zz__mv_recherche_these_refresh
--     after /*insert or */update of code on structure
--     for each row
--     when ( new.histo_destruction is null and new.type_structure_id in (2, 3) )
-- execute function refresh_mv_recherche_these('structure');
--
-- create trigger zz__mv_recherche_these_refresh
--     after /*insert or */update of nom_patronymique, nom_usuel, prenom1 on individu
--     for each row
--     when ( new.histo_destruction is null )
-- execute function refresh_mv_recherche_these('individu');
--
-- create trigger zz__mv_recherche_these_refresh
--     after insert or update of role_id, histo_destruction on acteur
--     for each row
-- execute function refresh_mv_recherche_these('acteur');
