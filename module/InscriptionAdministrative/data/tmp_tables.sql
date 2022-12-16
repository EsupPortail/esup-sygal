
alter table source add synchro_delete_interdit boolean default false not null;
comment on column source.synchro_delete_interdit is 'Indique si dans le cadre d''une synchro l''opération ''delete'' est interdite. L''interdire est utile lorsque les données sources sont obtenues de façon incrémentale au fil du temps et non pas de façon exhaustive en une seule fois.';

create sequence if not exists source_id_seq;

-- Sources correspondant aux instances pégase.
-- NB : le 'code' doit matcher avec le 'instancePegase' du contrat d'API.
insert into source (id, code, libelle, importable, synchro_delete_interdit, etablissement_id)
select nextval('source_id_seq'), 'PEGASE_INSA', 'Instance Pégase INSA', true, true/*delete interdit*/, 5 union
select nextval('source_id_seq'), 'PEGASE_UCN', 'Instance Pégase UCN', true, true/*delete interdit*/, 2;

alter table tmp_doctorant add code_apprenant_in_source varchar(64);
alter table doctorant add code_apprenant_in_source varchar(64);

drop view v_diff_doctorant;
drop view src_doctorant;
create view src_doctorant(id, source_code, code_apprenant_in_source, ine, source_id, individu_id, etablissement_id) as
SELECT NULL::text AS id,
       tmp.source_code,
       tmp.code_apprenant_in_source,
       tmp.ine,
       src.id     AS source_id,
       i.id       AS individu_id,
       e.id       AS etablissement_id
FROM tmp_doctorant tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.id = src.etablissement_id
         JOIN individu i ON i.source_code::text = tmp.individu_id::text;


--drop table if exists tmp_inscription_administrative;
create table tmp_inscription_administrative
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,

    source_id bigint not null,
    source_code varchar(128) not null, -- Id Pegase inscription (merge request en cours)

    doctorant_id varchar(64) not null,
    ecole_doct_id varchar(32),

    no_candidat varchar(32),
    date_inscription date,
    date_annulation date,
    cesure varchar(32),
    chemin varchar(128),
    --code_structure_etablissement_du_chemin varchar(32),
    formation varchar(64),
    mobilite varchar(32),
    origine varchar(32),
    --periode varchar(32), => cf. table dédiée
    principale bool,
    --regime_inscription_code varchar(32), -- utile ?
    regime_inscription_libelle varchar(64),
    statut_inscription varchar(32),

    periode_code varchar(32) not null,
    periode_libelle varchar(32) not null,
    periode_date_debut date not null,
    periode_date_fin date,
    periode_annee_universitaire smallint,

    histo_creation timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0),
    histo_modificateur_id bigint,
    histo_destruction timestamp(0),
    histo_destructeur_id bigint
 );

 create index tmp_inscription_administrative_source_code_index
     on tmp_inscription_administrative (source_code);
 create index tmp_inscription_administrative_source_id_index
     on tmp_inscription_administrative (source_id);
 create unique index tmp_inscription_administrative_unique_index
     on tmp_inscription_administrative (source_id, source_code);
alter table tmp_inscription_administrative
    add constraint tmp_inscription_administrative_source_id_fk
        foreign key (source_id) references source;

--drop table inscription_administrative;
create table inscription_administrative
(
    id bigserial constraint inscription_administrative_pkey primary key,
    source_id bigint not null constraint inscription_administrative_source_fk references source,
    source_code varchar(128) not null,
    doctorant_id bigint not null constraint inscription_administrative_doctorant_fk references doctorant,
    ecole_doct_id bigint constraint inscription_administrative_ecole_doct_fk references ecole_doct,
    no_candidat varchar(32),
    date_inscription date,
    date_annulation date,
    cesure varchar(32),
    chemin varchar(128),
    formation varchar(64),
    mobilite varchar(32),
    origine varchar(32),
    principale boolean,
    regime_inscription_libelle varchar(64),
    statut_inscription varchar(32),
    periode_code varchar(32) not null,
    periode_libelle varchar(32) not null,
    periode_date_debut date not null,
    periode_date_fin date,
    periode_annee_universitaire smallint,
    histo_creation timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0) without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp(0) without time zone,
    histo_destructeur_id bigint
);

create index inscription_administrative_source_code_index
    on inscription_administrative (source_code);
create index inscription_administrative_source_id_index
    on inscription_administrative (source_id);
create unique index inscription_administrative_unique_index
    on inscription_administrative (source_id, source_code);
alter table inscription_administrative
    add constraint inscription_administrative_source_id_fk
        foreign key (source_id) references source;

create index inscription_administrative_doctorant_id_idx
    on inscription_administrative (doctorant_id);

create index inscription_administrative_ecole_doct_id_idx
    on inscription_administrative (ecole_doct_id);

create index inscription_administrative_hcfk_idx
    on inscription_administrative (histo_createur_id);

create index inscription_administrative_hdfk_idx
    on inscription_administrative (histo_destructeur_id);

create index inscription_administrative_hmfk_idx
    on inscription_administrative (histo_modificateur_id);

--drop view src_inscription_administrative;
create or replace view src_inscription_administrative as
SELECT null::bigint as id,
    tmp.source_code,
    src.id as source_id,
    d.id as doctorant_id,
    ed.id as ecole_doct_id,
    tmp.no_candidat,
    tmp.date_inscription,
    tmp.date_annulation,
    tmp.cesure,
    tmp.chemin,
    tmp.formation,
    tmp.mobilite,
    tmp.origine,
    tmp.principale,
    tmp.regime_inscription_libelle,
    tmp.statut_inscription,
    tmp.periode_code,
    tmp.periode_libelle,
    tmp.periode_date_debut,
    tmp.periode_date_fin,
    tmp.periode_annee_universitaire
FROM tmp_inscription_administrative tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN doctorant d ON d.source_code = tmp.doctorant_id
         LEFT JOIN ecole_doct ed ON ed.source_code = tmp.ecole_doct_id
;

alter table tmp_doctorant alter column ine set not null;



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
