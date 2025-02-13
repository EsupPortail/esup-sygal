alter table acteur rename to acteur_these;

alter table acteur_these add soutenance_membre_id bigint references soutenance_membre on delete set null ;
create index acteur_these_soutenance_membre_id_idx
    on acteur_these(soutenance_membre_id);
create index acteur_these_qualite_id_idx
    on acteur_these (qualite_id);
create index acteur_these_ed_id_idx
    on acteur_these (acteur_ecoledoct_id);
create index acteur_these_ur_id_idx
    on acteur_these (unite_rech_id);

alter table soutenance_membre rename column acteur_id to acteur_id_sav;

update acteur_these a
    set soutenance_membre_id = sm.id
    from soutenance_membre sm
    join soutenance_proposition sp on sm.proposition_id = sp.id and sp.histo_destruction is null
    join these t on sp.these_id = t.id and t.histo_destruction is null and etat_these <> 'A'
    where sm.acteur_id_sav = a.id and sm.histo_destruction is null;
/* verif :
select * from soutenance_membre sm
         left join acteur_these a on sm.id = a.soutenance_membre_id
         where sm.acteur_id_sav <> a.id;
*/

/***** traque des acteurs figurant plusieurs fois pour un même soutenance_membre :
    select acteur_id, count(sm.id)
        from soutenance_membre sm
            join soutenance_proposition sp on sm.proposition_id = sp.id and sp.histo_destruction is null
            join these t on sp.these_id = t.id and t.histo_destruction is null and etat_these <> 'A'
        where sm.histo_destruction is null
        group by acteur_id
        having count(sm.id) > 1;
******/

create sequence acteur_these_id_seq owned by acteur_these.id;
alter table acteur_these alter column id set default nextval('acteur_these_id_seq');


----------------------------------------- HDR -------------------------------------------------

create table acteur_hdr
(
    id                     bigint                                                       not null
        constraint acteur_hdr_pkey
            primary key,
    individu_id            bigint                                                       not null
        constraint acteur_hdr_indiv_fk
            references individu,
    hdr_id               bigint                                                       not null
        constraint acteur_hdr_hdr_fk
            references hdr,
    role_id                bigint                                                       not null
        constraint acteur_hdr_role_id_fk
            references role,
    source_code            varchar(64)                                                  not null,
    source_id              bigint                                                       not null
        constraint acteur_hdr_source_fk
            references source,
    histo_createur_id      bigint                                                       not null
        constraint acteur_hdr_hc_fk
            references utilisateur,
    histo_creation         timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id  bigint
        constraint acteur_hdr_hm_fk
            references utilisateur,
    histo_modification     timestamp,
    histo_destructeur_id   bigint
        constraint acteur_hdr_hd_fk
            references utilisateur,
    histo_destruction      timestamp,
    qualite_id             bigint
        constraint fk_qualite_id
            references soutenance_qualite,
    etablissement_id       bigint
        constraint acteur_hdr_etab_id_fk
            references etablissement,
    unite_rech_id          bigint
        constraint acteur_hdr_unite_rech_id_fk
            references unite_rech
            on delete set null,
    ecole_doct_id    bigint
        constraint acteur_hdr_ecole_doct_id_fk
            references ecole_doct,
    soutenance_membre_id   bigint
                                                                                        references soutenance_membre
                                                                                            on delete set null,
    exterieur              boolean   default false                                      not null,
    ordre                  smallint  default 1                                          not null
);

create index acteur_hdr_id_idx
    on acteur_hdr (hdr_id);
create index acteur_hdr_acteur_etab_id_idx
    on acteur_hdr (etablissement_id);
create index acteur_hdr_individu_id_idx
    on acteur_hdr (individu_id);
create index acteur_hdr_role_id_idx
    on acteur_hdr (role_id);
create index acteur_hdr_qualite_id_idx
    on acteur_hdr (qualite_id);
create index acteur_hdr_acteur_ed_id_idx
    on acteur_hdr (ecole_doct_id);
create index acteur_hdr_acteur_ur_id_idx
    on acteur_hdr (unite_rech_id);
create index acteur_hdr_soutenance_membre_id_idx
    on acteur_hdr (soutenance_membre_id);
create index acteur_hdr_histo_destruct_id_idx
    on acteur_hdr (histo_destructeur_id);
create index acteur_hdr_histo_modif_id_idx
    on acteur_hdr (histo_modificateur_id);
create unique index acteur_hdr_source_code_uniq
    on acteur_hdr (source_code);
create index acteur_hdr_source_id_idx
    on acteur_hdr (source_id);
create sequence acteur_hdr_id_seq owned by acteur_hdr.id;
alter table acteur_hdr alter column id set default nextval('acteur_hdr_id_seq');




create function hdr_rech_compute_haystack(eds_code character varying,
                                          urs_code character varying,
                                          d_source_code character varying,
                                          id_nom_patronymique character varying,
                                          id_nom_usuel character varying,
                                          id_prenom1 character varying,
                                          a_agg character varying) returns character varying
    language plpgsql
as
$$begin
    return btrim(str_reduce(
            'code-ed{' || COALESCE(eds_code, '') || '} ' ||
            'code-ur{' || COALESCE(urs_code, '') || '} ' ||
            'doctorant-numero{' || substr(d_source_code, "position"(d_source_code, '::') + 2) || '} ' ||
            'doctorant-nom{' || id_nom_patronymique || ' ' || id_nom_usuel || '} ' ||
            'doctorant-prenom{' || id_prenom1 || '} ' ||
            'directeur-nom{' || a_agg || '} '
                 ));
end;
$$;



create view hdr_rech as
WITH acteurs AS (
    SELECT a.hdr_id,
           string_agg(COALESCE(ia.nom_usuel::text, ''::text), ' '::text) AS agg
    FROM acteur_hdr a
             JOIN hdr t on a.hdr_id = t.id and t.histo_destruction is null
             JOIN role r ON a.role_id = r.id AND r.code in ('HDR_GARANT')
             JOIN individu ia ON ia.id = a.individu_id and ia.histo_destruction is null
    WHERE a.histo_destruction IS NULL
    GROUP BY 1
)
SELECT 'now'::text::timestamp without time zone AS date_creation,
       t.source_code AS code_hdr,
       d.source_code AS code_candidat,
       ed.source_code AS code_ecole_doct,
       hdr_rech_compute_haystack(eds.code,
                                 urs.code,
                                 d.source_code,
                                 id.nom_patronymique,
                                 id.nom_usuel,
                                 id.prenom1,
                                 a.agg::character varying) AS haystack
FROM hdr t
         JOIN candidat_hdr d ON d.id = t.candidat_id
         JOIN individu id ON id.id = d.individu_id
         LEFT JOIN ecole_doct ed ON t.ecole_doct_id = ed.id
         LEFT JOIN structure eds ON ed.structure_id = eds.id
         LEFT JOIN unite_rech ur ON t.unite_rech_id = ur.id
         LEFT JOIN structure urs ON ur.structure_id = urs.id
         LEFT JOIN acteurs a ON a.hdr_id = t.id;