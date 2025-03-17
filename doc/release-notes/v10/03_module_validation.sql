call unicaen_indicateur_delete_matviews();

----------------------------------------------- these ----------------------------------------------

alter table validation rename to validation_these;

alter index validation_hcfk_idx rename to validation_these_hcfk_idx;
alter index validation_hdfk_idx rename to validation_these_hdfk_idx;
alter index validation_hmfk_idx rename to validation_these_hmfk_idx;
alter index validation_type_idx rename to validation_these_type_idx;
alter index validation_individu_idx rename to validation_these_individu_idx;

alter table validation_these rename constraint validation_pkey to validation_these_pkey;
alter table validation_these rename constraint validation_individu_id_fk to validation_these_individu_id_fk;
alter table validation_these rename constraint validation_hcfk to validation_these_hcfk;
alter table validation_these rename constraint validation_hmfk to validation_these_hmfk;
alter table validation_these rename constraint validation_hdfk to validation_these_hdfk;

alter sequence validation_id_seq rename to validation_these_id_seq;

create table validation
(
    id                    bigint                                                       not null
        primary key,
    type_validation_id    bigint                                                       not null
        constraint validation_type_validation_fk
            references type_validation,
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_createur_id     bigint    default 1                                          not null
        constraint validation_hcfk
            references utilisateur,
    histo_modification    timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint    default 1                                          not null
        constraint validation_hmfk
            references utilisateur,
    histo_destruction     timestamp,
    histo_destructeur_id  bigint
        constraint validation_hdfk
            references utilisateur
);

create index validation_hcfk_idx
    on validation (histo_createur_id);
create index validation_hdfk_idx
    on validation (histo_destructeur_id);
create index validation_hmfk_idx
    on validation (histo_modificateur_id);
create index validation_type_idx
    on validation (type_validation_id);

create sequence validation_id_seq owned by validation.id;
alter table validation alter column id set default nextval('validation_id_seq');

alter table validation_these add validation_id bigint constraint validation_these_validation_fk references validation;

create index validation_these_validation_idx on validation_these (validation_id);

create or replace procedure create_validations_from_validation_these() language plpgsql as
$$declare
    v validation_these;
begin
    for v in select * from validation_these loop
        insert into validation(id, type_validation_id, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id)
        values (v.id, v.type_validation_id, v.histo_creation, v.histo_createur_id, v.histo_modification, v.histo_modificateur_id, v.histo_destruction, v.histo_destructeur_id);
        update validation_these set validation_id = v.id where id = v.id;
    end loop;
    perform setval('validation_id_seq', coalesce(max(id),1)) from validation;
end;
$$;

call create_validations_from_validation_these();

alter table validation_these alter column validation_id set not null;


UPDATE indicateur SET requete = e'SELECT t.*
FROM THESE T
         LEFT JOIN VALIDATION_THESE VT ON T.ID = VT.THESE_ID
         LEFT JOIN VALIDATION V ON V.ID = VT.VALIDATION_ID
         LEFT JOIN TYPE_VALIDATION N on V.TYPE_VALIDATION_ID = N.ID
WHERE T.DATE_SOUTENANCE > (current_timestamp - interval \'2 months\')
  AND T.ETAT_THESE = \'E\'
  AND N.CODE = \'PAGE_DE_COUVERTURE\'
  AND VT.ID IS NULL;'
WHERE id = 2 and libelle = 'Pas de PDC à 2 mois';




----------------------------------------------- HDR ----------------------------------------------


create table public.validation_hdr
(
    id                    bigint                                                       not null
        constraint validation_hdr_pkey
            primary key,
    hdr_id              bigint                                                       not null
        constraint validation_hdr_fk
            references public.hdr,
    individu_id           bigint
        constraint validation_hdr_individu_id_fk
            references public.individu,
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_createur_id     bigint    default 1                                          not null
        constraint validation_hdr_hcfk
            references public.utilisateur,
    histo_modification    timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint    default 1                                          not null
        constraint validation_hdr_hmfk
            references public.utilisateur,
    histo_destruction     timestamp,
    histo_destructeur_id  bigint
        constraint validation_hdr_hdfk
            references public.utilisateur,
    validation_id         bigint                                                       not null
        constraint validation_hdr_validation_fk
            references public.validation
);

create index validation_hdr_hcfk_idx
    on public.validation_hdr (histo_createur_id);
create index validation_hdr_hdfk_idx
    on public.validation_hdr (histo_destructeur_id);
create index validation_hdr_hmfk_idx
    on public.validation_hdr (histo_modificateur_id);
create index validation_hdr_individu_idx
    on public.validation_hdr (individu_id);
create index validation_hdr_idx
    on public.validation_hdr (hdr_id);
create index validation_hdr_validation_idx
    on public.validation_hdr (validation_id);


create sequence validation_hdr_id_seq owned by validation_hdr.id;
alter table validation_hdr alter column id set default nextval('validation_hdr_id_seq');


ALTER TABLE soutenance_avis ADD COLUMN validation_hdr_id bigint REFERENCES validation_hdr(id);
ALTER TABLE soutenance_avis RENAME COLUMN validation_id TO validation_these_id;