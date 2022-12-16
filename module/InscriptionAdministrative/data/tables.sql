--drop table if exists inscription_administrative;
create table inscription_administrative
(
    id bigserial,
    source_id bigint not null,
    source_code varchar(64) not null, -- Id Pegase inscription (merge request en cours)

    doctorant_id bigint,
    ecole_doctorale_id bigint,

    no_candidat varchar(32),
    date_inscription date,
    date_annulation date,
    cesure varchar(32),
    chemin varchar(128),
    code_structure_etablissement_du_chemin varchar(32),
    formation varchar(64),
    mobilite varchar(32),
    origine varchar(32),
    --periode varchar(32), => cf. table dédiée
    principale bool,
    --regime_inscription_code varchar(32), -- utile ?
    regime_inscription_libelle varchar(64),
    statut_inscription varchar(32),

    histo_creation timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0),
    histo_modificateur_id bigint,
    histo_destruction timestamp(0),
    histo_destructeur_id bigint
 );

 create index inscription_administrative_source_code_index
     on inscription_administrative (source_code);
 create index inscription_administrative_source_id_index
     on inscription_administrative (source_id);
 create unique index inscription_administrative_unique_index
     on inscription_administrative (source_id, source_code);



--drop table if exists inscription_administrative_periode;
create table inscription_administrative_periode
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,

    inscription_administrative_id varchar(64) not null,

    code varchar(32) not null,
    libelle varchar(32) not null,
    date_debut date not null,
    date_fin date,
    annee_universitaire smallint,

    histo_creation timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0),
    histo_modificateur_id bigint,
    histo_destruction timestamp(0),
    histo_destructeur_id bigint
);

create index inscription_administrative_source_code_index
    on inscription_administrative (source_code);
create index inscription_administrative_source_id_index
    on inscription_administrative (source_id);
create unique index inscription_administrative_unique_index
    on inscription_administrative (source_id, source_code);