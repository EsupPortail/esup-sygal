--
-- Flux Inscriptions Administratives
--

/********************************************
    agrandissement de individu.source_code
 ********************************************/

call unicaen_indicateur_delete_matviews();

drop view if exists v_individu_doublon;

drop view if exists v_diff_acteur_these;
drop view src_acteur_these;

drop view if exists v_diff_doctorant;
drop view src_doctorant;

drop view if exists v_diff_individu;
drop view src_individu;

alter table individu
    alter column source_code type varchar(256) using source_code::varchar(256);

create view v_individu_doublon(id, source_code, nom_patronymique, prenom1, date_naissance, npd) as
WITH individus_npd AS (
    SELECT COALESCE(pre.npd_force, substit_npd_individu(pre.*)) AS npd,
           pre.id,
           pre.source_code,
           pre.nom_patronymique,
           pre.prenom1,
           pre.date_naissance
    FROM individu pre
    WHERE pre.source_id <> app_source_id()
), npds(npd) AS (
    SELECT individus_npd.npd,
           count(*) AS count
    FROM individus_npd
    GROUP BY individus_npd.npd
    HAVING count(*) > 1
)
SELECT i.id,
       i.source_code,
       i.nom_patronymique,
       i.prenom1,
       i.date_naissance,
       npds.npd
FROM npds,
     individus_npd i
WHERE i.npd::text = npds.npd::text;

create view src_acteur_these(id, source_id, source_code, these_id, role_id, individu_id, etablissement_id, qualite) as
WITH pre AS (
    SELECT NULL::bigint AS id,
           tmp.source_code,
           src.id AS source_id,
           i.id AS individu_id,
           t.id AS these_id,
           r.id AS role_id,
           eact.id AS etablissement_id,
           tmp.lib_cps AS qualite
    FROM tmp_acteur tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN individu i ON i.source_code::text = tmp.individu_id::text
             JOIN these t ON t.source_code::text = tmp.these_id::text
             JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text = 'P'::text
             LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
    UNION ALL
    SELECT NULL::bigint AS id,
           tmp.source_code::text || 'P'::text AS source_code,
           src.id AS source_id,
           i.id AS individu_id,
           t.id AS these_id,
           r_pj.id AS role_id,
           eact.id AS etablissement_id,
           tmp.lib_cps AS qualite
    FROM tmp_acteur tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN individu i ON i.source_code::text = tmp.individu_id::text
             JOIN these t ON t.source_code::text = tmp.these_id::text
             JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text = 'M'::text
             JOIN role r_pj ON r_pj.code::text = 'P'::text AND r_pj.structure_id = r.structure_id
             LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
    WHERE tmp.lib_roj_compl::text = 'Président du jury'::text
    UNION ALL
    SELECT NULL::bigint AS id,
           tmp.source_code,
           src.id AS source_id,
           i.id AS individu_id,
           t.id AS these_id,
           r.id AS role_id,
           eact.id AS etablissement_id,
           tmp.lib_cps AS qualite
    FROM tmp_acteur tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN individu i ON i.source_code::text = tmp.individu_id::text
             JOIN these t ON t.source_code::text = tmp.these_id::text
             JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text <> 'P'::text
             LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
)
SELECT pre.id,
       pre.source_id,
       pre.source_code,
       pre.these_id,
       pre.role_id,
       COALESCE(isub.to_id, pre.individu_id) AS individu_id,
       COALESCE(esub.to_id, pre.etablissement_id) AS etablissement_id,
       pre.qualite
FROM pre
         LEFT JOIN substit_individu isub ON isub.from_id = pre.individu_id
         LEFT JOIN substit_etablissement esub ON esub.from_id = pre.etablissement_id;

create view src_doctorant(id, individu_id, etablissement_id, source_code, source_id, ine, code_apprenant_in_source) as
WITH pre AS (
    SELECT NULL::bigint AS id,
           tmp.source_code,
           tmp.code_apprenant_in_source,
           tmp.ine,
           src.id AS source_id,
           i.id AS individu_id,
           e.id AS etablissement_id
    FROM tmp_doctorant tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN etablissement e ON e.id = src.etablissement_id
             JOIN individu i ON i.source_code::text = tmp.individu_id::text
)
SELECT pre.id,
       COALESCE(isub.to_id, pre.individu_id) AS individu_id,
       COALESCE(esub.to_id, pre.etablissement_id) AS etablissement_id,
       pre.source_code,
       pre.source_id,
       pre.ine,
       pre.code_apprenant_in_source
FROM pre
         LEFT JOIN substit_individu isub ON isub.from_id = pre.individu_id
         LEFT JOIN substit_etablissement esub ON esub.from_id = pre.etablissement_id;

create view src_individu(id, source_code, source_id, type, supann_id, civilite, nom_usuel, nom_patronymique, prenom1, prenom2, prenom3, email, date_naissance, nationalite, pays_id_nationalite) as
WITH pre AS (
    SELECT NULL::bigint AS id,
           tmp.source_code,
           src.id AS source_id,
           tmp.type,
           tmp.supann_id,
           tmp.civ AS civilite,
           tmp.lib_nom_usu_ind AS nom_usuel,
           tmp.lib_nom_pat_ind AS nom_patronymique,
           tmp.lib_pr1_ind AS prenom1,
           tmp.lib_pr2_ind AS prenom2,
           tmp.lib_pr3_ind AS prenom3,
           tmp.email,
           tmp.dat_nai_per AS date_naissance,
           tmp.lib_nat AS nationalite,
           p.id AS pays_id_nationalite
    FROM tmp_individu tmp
             JOIN source src ON src.id = tmp.source_id
             LEFT JOIN pays p ON p.code_pays_apogee::text = tmp.cod_pay_nat::text
)
SELECT pre.id,
       pre.source_code,
       pre.source_id,
       pre.type,
       pre.supann_id,
       pre.civilite,
       pre.nom_usuel,
       pre.nom_patronymique,
       pre.prenom1,
       pre.prenom2,
       pre.prenom3,
       pre.email,
       pre.date_naissance,
       pre.nationalite,
       pre.pays_id_nationalite
FROM pre;

call unicaen_indicateur_recreate_matviews();


/********************************************
         inscription_administrative
 ********************************************/

drop view if exists src_inscription_administrative cascade;

drop table if exists tmp_inscription_administrative;
create table tmp_inscription_administrative
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,

    source_id bigint not null,
    source_code varchar(128) not null, -- Id Pegase inscription (merge request en cours)

    ecole_doct_id varchar(32) not null,

    date_inscription date not null,
    date_annulation date,
    principale bool not null,
    statut_inscription varchar(32) not null,
    chemin varchar(128) not null,
    code_structure_etablissement_du_chemin varchar(64) not null,
    formation varchar(64),
    cesure varchar(32),
    mobilite varchar(32),
    origine varchar(32),
    --regime_inscription_code varchar(32), -- utile ?
    regime_inscription_libelle varchar(64),
    no_candidat varchar(32),

    periode_code varchar(32) not null,
    periode_libelle varchar(32) not null,
    periode_date_debut date not null,
    periode_date_fin date not null,
    periode_annee_universitaire smallint not null,

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

drop table if exists inscription_administrative cascade;
create table inscription_administrative
(
    id bigserial constraint inscription_administrative_pkey primary key,
    source_id bigint not null constraint inscription_administrative_source_fk references source,
    source_code varchar(128) not null,

    ecole_doct_id bigint not null constraint inscription_administrative_ecole_doct_fk references ecole_doct,

    date_inscription date not null,
    date_annulation date,
    principale boolean not null,
    statut_inscription varchar(32) not null,
    chemin varchar(128) not null,
    code_structure_etablissement_du_chemin varchar(64) not null,
    formation varchar(64),
    cesure varchar(32),
    mobilite varchar(32),
    origine varchar(32),
    --regime_inscription_code varchar(32), -- utile ?
    regime_inscription_libelle varchar(64),
    no_candidat varchar(32),

    periode_code varchar(32) not null,
    periode_libelle varchar(32) not null,
    periode_date_debut date not null,
    periode_date_fin date not null,
    periode_annee_universitaire smallint not null,

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

create index inscription_administrative_ecole_doct_id_idx
    on inscription_administrative (ecole_doct_id);
create index inscription_administrative_admission_idx
    on inscription_administrative (histo_createur_id);
create index inscription_administrative_hdfk_idx
    on inscription_administrative (histo_destructeur_id);
create index inscription_administrative_hmfk_idx
    on inscription_administrative (histo_modificateur_id);

drop view if exists v_diff_inscription_administrative;
drop view if exists src_inscription_administrative;
create or replace view src_inscription_administrative as
with pre as (
    SELECT null::bigint as id,
           tmp.source_code,
           src.id as source_id,
           ed.id as ecole_doct_id,
           tmp.no_candidat,
           tmp.date_inscription,
           tmp.date_annulation,
           tmp.cesure,
           tmp.chemin,
           tmp.code_structure_etablissement_du_chemin,
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
             LEFT JOIN ecole_doct ed ON ed.source_code = tmp.ecole_doct_id
)
SELECT pre.id,
       pre.source_code,
       pre.source_id,
       COALESCE(edsub.to_id, pre.ecole_doct_id) AS ecole_doct_id,
       pre.no_candidat,
       pre.date_inscription,
       pre.date_annulation,
       pre.cesure,
       pre.chemin,
       pre.code_structure_etablissement_du_chemin,
       pre.formation,
       pre.mobilite,
       pre.origine,
       pre.principale,
       pre.regime_inscription_libelle,
       pre.statut_inscription,
       pre.periode_code,
       pre.periode_libelle,
       pre.periode_date_debut,
       pre.periode_date_fin,
       pre.periode_annee_universitaire
FROM pre
         LEFT JOIN substit_ecole_doct edsub ON edsub.from_id = pre.ecole_doct_id
;


/********************************************
     inscription_administrative_apprenant
 ********************************************/

drop view if exists src_inscription_administrative_apprenant cascade;

drop table if exists tmp_inscription_administrative_apprenant;
create table tmp_inscription_administrative_apprenant
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,

    source_id bigint not null,
    source_code varchar(128) not null, -- Id Pegase inscription

    inscription_administrative_id varchar(256) not null,

    code varchar(32) not null,
    INE varchar(32) not null,
    deuxieme_prenom varchar(128),
    genre varchar(32) not null,
    mail_personnel varchar(128),
    mail_urgence varchar(128),
    nom_de_naissance varchar(128) not null,
    nom_usuel varchar(128) not null,
    prenom varchar(128) not null,
    prenom_usage varchar(128),
    telephone_personnel varchar(32),
    telephone_urgence varchar(32),
    troisieme_prenom varchar(128),

    -- naissance
    commune_de_naissance varchar(128),
    commune_de_naissance_etranger varchar(128),
    date_d_obtention_de_la_deuxieme_nationalite date,
    date_de_naissance date not null,
    deuxieme_nationalite varchar(128),
    libelle_commune_de_naissance varchar(128),
    libelle_deuxieme_nationalite varchar(128),
    libelle_nationalite varchar(128),
    libelle_pays_de_naissance varchar(128),
    nationalite varchar(128) not null,
    pays_de_naissance varchar(128) not null, -- code ISO-alpha 2

--     -- adresseResidenceUniversitaire :
--     codeInsee varchar(128) not null,
--     codePostal varchar(128) not null,
--     libelleCommune varchar(128) not null,
--     libellePays varchar(128) not null,
--     ligne1OuEtage varchar(128) not null,
--     ligne2OuBatiment varchar(128) not null,
--     ligne3OuVoie varchar(128) not null,
--     ligne4OuComplement varchar(128) not null,
--     ligne5Etranger varchar(128) not null,
--     pays varchar(128) not null,
--     -- adresseFixeHorsPeriodeUniversitaire :
--     codeInsee varchar(128) not null,
--     codePostal varchar(128) not null,
--     libelleCommune varchar(128) not null,
--     libellePays varchar(128) not null,
--     ligne1OuEtage varchar(128) not null,
--     ligne2OuBatiment varchar(128) not null,
--     ligne3OuVoie varchar(128) not null,
--     ligne4OuComplement varchar(128) not null,
--     ligne5Etranger varchar(128) not null,
--     pays varchar(128) not null,

    histo_creation timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0),
    histo_modificateur_id bigint,
    histo_destruction timestamp(0),
    histo_destructeur_id bigint
);

create index tmp_inscription_administrative_apprenant_source_code_index
    on tmp_inscription_administrative_apprenant (source_code);
create index tmp_inscription_administrative_apprenant_source_id_index
    on tmp_inscription_administrative_apprenant (source_id);
create unique index tmp_inscription_administrative_apprenant_unique_index
    on tmp_inscription_administrative_apprenant (source_id, source_code);
alter table tmp_inscription_administrative_apprenant
    add constraint tmp_inscription_administrative_apprenant_source_id_fk
        foreign key (source_id) references source;

drop table if exists inscription_administrative_apprenant cascade;
create table inscription_administrative_apprenant
(
    id bigserial constraint inscription_administrative_apprenant_pkey primary key,
    source_id bigint not null constraint inscription_administrative_apprenant_source_fk references source,
    source_code varchar(128) not null,

    inscription_administrative_id bigint not null constraint inscription_administrative_apprenant_ia_fk references inscription_administrative,

    code varchar(32) not null,
    INE varchar(32) not null,
    deuxieme_prenom varchar(128),
    genre varchar(32) not null,
    mail_personnel varchar(128),
    mail_urgence varchar(128),
    nom_de_naissance varchar(128) not null,
    nom_usuel varchar(128) not null,
    prenom varchar(128) not null,
    prenom_usage varchar(128),
    telephone_personnel varchar(32),
    telephone_urgence varchar(32),
    troisieme_prenom varchar(128),

    -- naissance
    commune_de_naissance varchar(128),
    commune_de_naissance_etranger varchar(128),
    date_d_obtention_de_la_deuxieme_nationalite date,
    date_de_naissance date not null,
    deuxieme_nationalite varchar(128),
    libelle_commune_de_naissance varchar(128),
    libelle_deuxieme_nationalite varchar(128),
    libelle_nationalite varchar(128),
    libelle_pays_de_naissance varchar(128),
    nationalite varchar(128) not null,
    pays_de_naissance varchar(128) not null, -- code ISO-alpha 2

    haystack text not null,

    histo_creation timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0) without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp(0) without time zone,
    histo_destructeur_id bigint
);

create index inscription_administrative_apprenant_source_code_index
    on inscription_administrative_apprenant (source_code);
create index inscription_administrative_apprenant_source_id_index
    on inscription_administrative_apprenant (source_id);
create unique index inscription_administrative_apprenant_unique_index
    on inscription_administrative_apprenant (source_id, source_code);
alter table inscription_administrative_apprenant
    add constraint inscription_administrative_apprenant_source_id_fk
        foreign key (source_id) references source;

create index inscription_administrative_apprenant_ia_id_idx
    on inscription_administrative_apprenant (inscription_administrative_id);
create index inscription_administrative_apprenant_admission_idx
    on inscription_administrative_apprenant (histo_createur_id);
create index inscription_administrative_apprenant_hdfk_idx
    on inscription_administrative_apprenant (histo_destructeur_id);
create index inscription_administrative_apprenant_hmfk_idx
    on inscription_administrative_apprenant (histo_modificateur_id);

drop view if exists src_inscription_administrative_apprenant cascade;
create or replace view src_inscription_administrative_apprenant as
SELECT null::bigint as id,
       tmp.source_code,
       src.id as source_id,

       ia.id as inscription_administrative_id,

       tmp.code,
       tmp.INE,
       tmp.deuxieme_prenom,
       tmp.genre,
       tmp.mail_personnel,
       tmp.mail_urgence,
       tmp.nom_de_naissance,
       tmp.nom_usuel,
       tmp.prenom,
       tmp.prenom_usage,
       tmp.telephone_personnel,
       tmp.telephone_urgence,
       tmp.troisieme_prenom,

       -- naissance
       tmp.commune_de_naissance,
       tmp.commune_de_naissance_etranger,
       tmp.date_d_obtention_de_la_deuxieme_nationalite,
       tmp.date_de_naissance,
       tmp.deuxieme_nationalite,
       tmp.libelle_commune_de_naissance,
       tmp.libelle_deuxieme_nationalite,
       tmp.libelle_nationalite,
       tmp.libelle_pays_de_naissance,
       tmp.nationalite,
       tmp.pays_de_naissance,

       btrim(str_reduce(
               tmp.code || ' ' ||
               tmp.INE || ' ' ||
               tmp.nom_de_naissance || ' ' ||
               tmp.nom_usuel || ' ' ||
               tmp.prenom || ' ' ||
               tmp.deuxieme_prenom || ' ' ||
               tmp.prenom_usage
             )) as haystack
FROM tmp_inscription_administrative_apprenant tmp
         JOIN source src ON src.id = tmp.source_id
         LEFT JOIN inscription_administrative ia ON ia.source_code = tmp.inscription_administrative_id
;

/********************************************
     inscription_administrative_doctorant
 ********************************************/

drop table if exists inscription_administrative_doctorant cascade;
create table inscription_administrative_doctorant
(
    id bigserial constraint inscription_administrative_doctorant_pkey primary key,

    inscription_administrative_id bigint not null constraint inscription_administrative_doctorant_ia_fk references inscription_administrative,
    doctorant_id bigint not null constraint inscription_administrative_doctorant_doctorant_fk references doctorant,

    histo_creation timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0) without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp(0) without time zone,
    histo_destructeur_id bigint
);

create index inscription_administrative_doctorant_ia_id_idx
    on inscription_administrative_doctorant (inscription_administrative_id);
create index inscription_administrative_doctorant_doctorant_id_idx
    on inscription_administrative_doctorant (doctorant_id);
create index inscription_administrative_doctorant_hcfk_idx
    on inscription_administrative_doctorant (histo_createur_id);
create index inscription_administrative_doctorant_hdfk_idx
    on inscription_administrative_doctorant (histo_destructeur_id);
create index inscription_administrative_doctorant_hmfk_idx
    on inscription_administrative_doctorant (histo_modificateur_id);


-- --
-- -- Création des sources de test correspondant à des instances pégase.
-- --
-- -- NB : le 'code' doit matcher avec le 'instancePegase' du contrat d'API.
-- -- NB : si la séquence 'source_id_seq' vient d'être créée, il peut être nécessaire de relancer la commande jusqu'à ce qu'elle passe.
-- insert into source (id, code, libelle, importable, synchro_delete_enabled, etablissement_id)
-- select nextval('source_id_seq'), 'PEGASE_INSA', 'Instance Pégase INSA', true, false/*delete interdit*/, e.id from etablissement e where e.source_code = 'INSA'/* union
-- select nextval('source_id_seq'), 'PEGASE_UCN', 'Instance Pégase UCN', true, false/*delete interdit*/, e.id from etablissement e where e.source_code = 'UCN'*/;
