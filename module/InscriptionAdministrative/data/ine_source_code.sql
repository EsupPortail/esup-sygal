--
-- Etude : INE comme source_code doctorant
--

-- doctorant.ine en double
with tmp as (
    select source_id, ine, count(*) from doctorant
    --where histo_destruction is null
    group by source_id, ine having count(*) > 1
)
select i.nom_usuel, i.prenom1, d.* from doctorant d
join individu i on d.individu_id = i.id and i.histo_destruction is null
join tmp on tmp.ine = d.ine
--where d.histo_destruction is not null and not exists (select * from these where doctorant_id = d.id)
order by d.ine;


-- colonne de sauvegarde du source_code :
alter table doctorant add source_code_sav varchar(64);
update doctorant set source_code_sav = source_code;
-- update doctorant set source_code = source_code_sav;

drop index doctorant_source_code_uniq;
create unique index doctorant_source_code_uniq_1 on doctorant (source_id, source_code, histo_destruction) where (histo_destruction IS NOT NULL);
create unique index doctorant_source_code_uniq_2 on doctorant (source_id, source_code) where (histo_destruction IS NULL);
--create unique index doctorant_source_code_uniq on doctorant (source_code);

-- correction des source_code
update doctorant set source_code = substr(source_code, 0, strpos(source_code, '::')) || '::' || coalesce(trim(ine), gen_random_uuid()::text)
    where histo_destruction is null;

-- simulation de l'INE comme source_code dans src_doctorant
create or replace view src_doctorant(id, source_code, code_apprenant_in_source, ine, source_id, individu_id, etablissement_id) as
SELECT NULL::text AS id,
       --tmp.source_code,
       (substr(tmp.source_code, 0, strpos(tmp.source_code, '::')) || '::' || trim(ine)) ::varchar(64) as source_code,
       tmp.code_apprenant_in_source,
       tmp.ine,
       src.id     AS source_id,
       i.id       AS individu_id,
       e.id       AS etablissement_id
FROM tmp_doctorant tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.id = src.etablissement_id
         JOIN individu i ON i.source_code::text = tmp.individu_id::text;
