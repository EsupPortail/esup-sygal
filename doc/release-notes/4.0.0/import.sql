
alter table import_log add import_hash varchar(64);
alter table import_log add id bigserial primary key;



drop table tmp_acteur ;
drop table tmp_doctorant ;
drop table tmp_ecole_doct ;
drop table tmp_etablissement ;
drop table tmp_financement ;
drop table tmp_individu ;
drop table tmp_origine_financement ;
drop table tmp_role ;
drop table tmp_structure ;
drop table tmp_these ;
drop table tmp_these_annee_univ ;
drop table tmp_titre_acces ;
drop table tmp_unite_rech ;
drop table tmp_variable ;

create table tmp_acteur
(
    insert_date timestamp default ('now'::text)::timestamp without time zone,
    id varchar(64),
    etablissement_id varchar(64) not null,
    source_id varchar(64) not null,
    source_code varchar(64) not null,
    individu_id varchar(64) not null,
    these_id varchar(64) not null,
    role_id varchar(64) not null,
    lib_cps varchar(200),
    cod_cps varchar(50),
    cod_roj_compl varchar(50),
    lib_roj_compl varchar(200),
    tem_hab_rch_per varchar(1),
    tem_rap_recu varchar(1),
    acteur_etablissement_id varchar(64),
    source_insert_date timestamp default ('now'::text)::timestamp without time zone
);

create index tmp_acteur_source_code_index
    on tmp_acteur (source_code);

create index tmp_acteur_source_id_index
    on tmp_acteur (source_id);

create unique index tmp_acteur_uniq
    on tmp_acteur (id, etablissement_id);



create table tmp_acteur
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    individu_id varchar(64) not null,
    these_id varchar(64) not null,
    role_id varchar(64) not null,
    lib_cps varchar(200),
    cod_cps varchar(50),
    cod_roj_compl varchar(50),
    lib_roj_compl varchar(200),
    tem_hab_rch_per varchar(1),
    tem_rap_recu varchar(1),
    acteur_etablissement_id varchar(64),
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_modification timestamp(0),
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);

create table tmp_doctorant
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    individu_id varchar(64) not null,
    ine varchar(64),
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_modification timestamp(0),
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);

create table tmp_ecole_doct
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    structure_id varchar(64) not null,
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_modification timestamp(0),
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);

create table tmp_etablissement
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    structure_id varchar(64) not null,
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_modification timestamp(0),
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);

create table tmp_financement
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    these_id varchar(50) not null,
    annee varchar(50) not null,
    origine_financement_id varchar(50) not null,
    complement_financement varchar(200),
    quotite_financement varchar(50),
    date_debut_financement timestamp,
    date_fin_financement timestamp,
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    code_type_financement varchar(8),
    libelle_type_financement varchar(100),
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_modification timestamp(0),
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);

create table tmp_individu
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    type varchar(32),
    civ varchar(5),
    lib_nom_usu_ind varchar(60) not null,
    lib_nom_pat_ind varchar(60) not null,
    lib_pr1_ind varchar(60) not null,
    lib_pr2_ind varchar(60),
    lib_pr3_ind varchar(60),
    email varchar(255),
    dat_nai_per timestamp,
    lib_nat varchar(128),
    supann_id varchar(30),
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_modification timestamp(0),
    histo_createur_id bigint not null,
    histo_destructeur_id bigint,
    histo_modificateur_id bigint,
    histo_destruction timestamp(0)
);

create table tmp_origine_financement
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    cod_ofi varchar(50) not null,
    lic_ofi varchar(50) not null,
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    lib_ofi varchar(200) not null,
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_modification timestamp(0),
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_destructeur_id bigint,
    histo_modificateur_id bigint
);

create table tmp_role
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    lic_roj varchar(50),
    lib_roj varchar(200),
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_destructeur_id bigint,
    histo_modificateur_id bigint,
    histo_modification timestamp(0)
);

create table tmp_structure
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    type_structure_id varchar(64) not null,
    code_pays varchar(64),
    libelle_pays varchar(200),
    code varchar(64),
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    libelle varchar(200) not null,
    sigle varchar(64),
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0),
    histo_modificateur_id bigint,
    histo_destruction timestamp(0),
    histo_destructeur_id bigint
);

create table tmp_these
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    dat_abandon timestamp,
    dat_aut_sou_ths timestamp,
    unite_rech_id varchar(64),
    dat_sou_ths timestamp,
    lib_int1_dis varchar(200),
    lib_etab_cotut varchar(60),
    lib_pays_cotut varchar(40),
    cod_neg_tre varchar(1),
    tem_avenant_cotut varchar(1),
    lib_ths varchar(2048),
    dat_transfert_dep timestamp,
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    correction_effectuee varchar(30) default 'null'::character varying,
    dat_fin_cfd_ths timestamp,
    dat_deb_ths timestamp,
    correction_possible varchar(30),
    doctorant_id varchar(64) not null,
    ecole_doct_id varchar(64),
    dat_prev_sou timestamp,
    annee_univ_1ere_insc bigint,
    tem_sou_aut_ths varchar(1),
    eta_ths varchar(20),
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0),
    histo_modificateur_id bigint,
    histo_destructeur_id bigint,
    histo_destruction timestamp(0)
);

create table tmp_these_annee_univ
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    annee_univ bigint,
    these_id varchar(50) not null,
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_modification timestamp(0),
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_destructeur_id bigint,
    histo_modificateur_id bigint
);

create table tmp_titre_acces
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    these_id varchar(50) not null,
    titre_acces_interne_externe varchar(1),
    libelle_titre_acces varchar(200),
    type_etb_titre_acces varchar(50),
    libelle_etb_titre_acces varchar(200),
    code_dept_titre_acces varchar(20),
    code_pays_titre_acces varchar(20),
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint,
    histo_modification timestamp(0)
);

create table tmp_unite_rech
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    structure_id varchar(64) not null,
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_modification timestamp(0),
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);

create table tmp_variable
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id bigint not null,
    source_code varchar(64) not null,
    cod_vap varchar(50),
    lib_vap varchar(300),
    par_vap varchar(200),
    date_deb_validite timestamp not null,
    date_fin_validite timestamp not null,
    source_insert_date timestamp default ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) default LOCALTIMESTAMP(0) not null,
    histo_modification timestamp(0),
    histo_destruction timestamp(0),
    histo_createur_id bigint not null,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);

create sequence tmp_acteur_id_seq;
alter sequence tmp_acteur_id_seq owned by tmp_acteur.id;

create sequence tmp_doctorant_id_seq;
alter sequence tmp_doctorant_id_seq owned by tmp_doctorant.id;

create sequence tmp_ecole_doct_id_seq;
alter sequence tmp_ecole_doct_id_seq owned by tmp_ecole_doct.id;

create sequence tmp_etablissement_id_seq;
alter sequence tmp_etablissement_id_seq owned by tmp_etablissement.id;

create sequence tmp_financement_id_seq;
alter sequence tmp_financement_id_seq owned by tmp_financement.id;

create sequence tmp_individu_id_seq;
alter sequence tmp_individu_id_seq owned by tmp_individu.id;

create sequence tmp_origine_financement_id_seq;
alter sequence tmp_origine_financement_id_seq owned by tmp_origine_financement.id;

create sequence tmp_role_id_seq;
alter sequence tmp_role_id_seq owned by tmp_role.id;

create sequence tmp_structure_id_seq;
alter sequence tmp_structure_id_seq owned by tmp_structure.id;

create sequence tmp_these_id_seq;
alter sequence tmp_these_id_seq owned by tmp_these.id;

create sequence tmp_these_annee_univ_id_seq;
alter sequence tmp_these_annee_univ_id_seq owned by tmp_these_annee_univ.id;

create sequence tmp_titre_acces_id_seq;
alter sequence tmp_titre_acces_id_seq owned by tmp_titre_acces.id;

create sequence tmp_unite_rech_id_seq;
alter sequence tmp_unite_rech_id_seq owned by tmp_unite_rech.id;

create sequence tmp_variable_id_seq;
alter sequence tmp_variable_id_seq owned by tmp_variable.id;

create view src_acteur(id, source_code, source_id, individu_id, these_id, role_id, acteur_etablissement_id, qualite, lib_role_compl) as
SELECT NULL::text                         AS id,
       tmp.source_code,
       src.id                             AS source_id,
       i.id                               AS individu_id,
       t.id                               AS these_id,
       r.id                               AS role_id,
       COALESCE(etab_substit.id, eact.id) AS acteur_etablissement_id,
       tmp.lib_cps                        AS qualite,
       tmp.lib_roj_compl                  AS lib_role_compl
FROM tmp_acteur tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN individu i ON i.source_code::text = tmp.individu_id::text
         JOIN these t ON t.source_code::text = tmp.these_id::text
         JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text = 'P'::text
         LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
         LEFT JOIN structure_substit ss_ed ON ss_ed.from_structure_id = eact.structure_id
         LEFT JOIN etablissement etab_substit ON etab_substit.structure_id = ss_ed.to_structure_id
UNION ALL
SELECT NULL::text                         AS id,
       tmp.source_code::text || 'P'::text AS source_code,
       src.id                             AS source_id,
       i.id                               AS individu_id,
       t.id                               AS these_id,
       r_pj.id                            AS role_id,
       COALESCE(etab_substit.id, eact.id) AS acteur_etablissement_id,
       tmp.lib_cps                        AS qualite,
       NULL::character varying            AS lib_role_compl
FROM tmp_acteur tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN individu i ON i.source_code::text = tmp.individu_id::text
         JOIN these t ON t.source_code::text = tmp.these_id::text
         JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text = 'M'::text
         JOIN role r_pj ON r_pj.code::text = 'P'::text AND r_pj.structure_id = r.structure_id
         LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
         LEFT JOIN structure_substit ss_ed ON ss_ed.from_structure_id = eact.structure_id
         LEFT JOIN etablissement etab_substit ON etab_substit.structure_id = ss_ed.to_structure_id
WHERE tmp.lib_roj_compl::text = 'Président du jury'::text
UNION ALL
SELECT NULL::text                         AS id,
       tmp.source_code,
       src.id                             AS source_id,
       i.id                               AS individu_id,
       t.id                               AS these_id,
       r.id                               AS role_id,
       COALESCE(etab_substit.id, eact.id) AS acteur_etablissement_id,
       tmp.lib_cps                        AS qualite,
       NULL::character varying            AS lib_role_compl
FROM tmp_acteur tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN individu i ON i.source_code::text = tmp.individu_id::text
         JOIN these t ON t.source_code::text = tmp.these_id::text
         JOIN role r ON r.source_code::text = tmp.role_id::text AND r.code::text <> 'P'::text
         LEFT JOIN etablissement eact ON eact.source_code::text = tmp.acteur_etablissement_id::text
         LEFT JOIN structure_substit ss_ed ON ss_ed.from_structure_id = eact.structure_id
         LEFT JOIN etablissement etab_substit ON etab_substit.structure_id = ss_ed.to_structure_id;

create view src_doctorant(id, source_code, ine, source_id, individu_id, etablissement_id) as
SELECT NULL::text AS id,
       tmp.source_code,
       tmp.ine,
       src.id     AS source_id,
       i.id       AS individu_id,
       e.id       AS etablissement_id
FROM tmp_doctorant tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.id = src.etablissement_id
         JOIN individu i ON i.source_code::text = tmp.individu_id::text;

create view src_ecole_doct(id, source_code, source_id, structure_id) as
SELECT NULL::text AS id,
       tmp.source_code,
       src.id     AS source_id,
       s.id       AS structure_id
FROM tmp_ecole_doct tmp
         JOIN structure s ON s.source_code::text = tmp.structure_id::text
         JOIN source src ON src.id = tmp.source_id;

create view src_etablissement(id, source_code, source_id, code, structure_id) as
SELECT NULL::text      AS id,
       tmp.source_code,
       src.id          AS source_id,
       tmp.source_code AS code,
       s.id            AS structure_id
FROM tmp_etablissement tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN structure s ON s.source_code::text = tmp.structure_id::text;

create view src_financement(id, source_code, source_id, these_id, annee, origine_financement_id, complement_financement, quotite_financement, date_debut, date_fin, code_type_financement, libelle_type_financement) as
SELECT NULL::text                 AS id,
       tmp.source_code,
       src.id                     AS source_id,
       t.id                       AS these_id,
       tmp.annee::numeric         AS annee,
       ofi.id                     AS origine_financement_id,
       tmp.complement_financement,
       tmp.quotite_financement,
       tmp.date_debut_financement AS date_debut,
       tmp.date_fin_financement   AS date_fin,
       tmp.code_type_financement,
       tmp.libelle_type_financement
FROM tmp_financement tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN these t ON t.source_code::text = tmp.these_id::text
         JOIN origine_financement ofi ON ofi.source_code::text = tmp.origine_financement_id::text;

create view src_individu(id, source_code, source_id, type, supann_id, civilite, nom_usuel, nom_patronymique, prenom1, prenom2, prenom3, email, date_naissance, nationalite) as
SELECT NULL::text          AS id,
       tmp.source_code,
       src.id              AS source_id,
       tmp.type,
       tmp.supann_id,
       tmp.civ             AS civilite,
       tmp.lib_nom_usu_ind AS nom_usuel,
       tmp.lib_nom_pat_ind AS nom_patronymique,
       tmp.lib_pr1_ind     AS prenom1,
       tmp.lib_pr2_ind     AS prenom2,
       tmp.lib_pr3_ind     AS prenom3,
       tmp.email,
       tmp.dat_nai_per     AS date_naissance,
       tmp.lib_nat         AS nationalite
FROM tmp_individu tmp
         JOIN source src ON src.id = tmp.source_id;

create view src_origine_financement(id, source_code, source_id, code, libelle_court, libelle_long) as
SELECT NULL::text  AS id,
       tmp.source_code,
       src.id      AS source_id,
       tmp.cod_ofi AS code,
       tmp.lic_ofi AS libelle_court,
       tmp.lib_ofi AS libelle_long
FROM tmp_origine_financement tmp
         JOIN source src ON src.id = tmp.source_id;

create view src_role(id, source_code, source_id, libelle, code, role_id, these_dep, structure_id, type_structure_dependant_id) as
SELECT NULL::text                                       AS id,
       tmp.source_code,
       src.id                                           AS source_id,
       tmp.lib_roj                                      AS libelle,
       tmp.source_code                                  AS code,
       (tmp.lib_roj::text || ' '::text) || s.code::text AS role_id,
       true                                             AS these_dep,
       s.id                                             AS structure_id,
       NULL::bigint                                     AS type_structure_dependant_id
FROM tmp_role tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.id = src.etablissement_id
         JOIN structure s ON s.id = e.structure_id;

create view src_structure(id, source_code, code, source_id, type_structure_id, sigle, libelle) as
SELECT NULL::text      AS id,
       tmp.source_code,
       tmp.source_code AS code,
       src.id          AS source_id,
       ts.id           AS type_structure_id,
       tmp.sigle,
       tmp.libelle
FROM tmp_structure tmp
         JOIN type_structure ts ON ts.code::text = tmp.type_structure_id::text
         JOIN source src ON src.id = tmp.source_id;

create view src_these(id, source_code, source_id, etablissement_id, doctorant_id, ecole_doct_id, unite_rech_id, titre, etat_these, resultat, lib_disc, date_prem_insc, date_prev_soutenance, date_soutenance, date_fin_confid, lib_etab_cotut, lib_pays_cotut, correc_autorisee, correc_effectuee, soutenance_autoris, date_autoris_soutenance, tem_avenant_cotut, date_abandon, date_transfert) as
SELECT NULL::text                     AS id,
       tmp.source_code,
       src.id                         AS source_id,
       e.id                           AS etablissement_id,
       d.id                           AS doctorant_id,
       COALESCE(ed_substit.id, ed.id) AS ecole_doct_id,
       COALESCE(ur_substit.id, ur.id) AS unite_rech_id,
       tmp.lib_ths                    AS titre,
       tmp.eta_ths                    AS etat_these,
       tmp.cod_neg_tre::numeric       AS resultat,
       tmp.lib_int1_dis               AS lib_disc,
       tmp.dat_deb_ths                AS date_prem_insc,
       tmp.dat_prev_sou               AS date_prev_soutenance,
       tmp.dat_sou_ths                AS date_soutenance,
       tmp.dat_fin_cfd_ths            AS date_fin_confid,
       tmp.lib_etab_cotut,
       tmp.lib_pays_cotut,
       tmp.correction_possible        AS correc_autorisee,
       tmp.correction_effectuee       AS correc_effectuee,
       tmp.tem_sou_aut_ths            AS soutenance_autoris,
       tmp.dat_aut_sou_ths            AS date_autoris_soutenance,
       tmp.tem_avenant_cotut,
       tmp.dat_abandon                AS date_abandon,
       tmp.dat_transfert_dep          AS date_transfert
FROM tmp_these tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.id = src.etablissement_id
         JOIN doctorant d ON d.source_code::text = tmp.doctorant_id::text
         LEFT JOIN ecole_doct ed ON ed.source_code::text = tmp.ecole_doct_id::text
         LEFT JOIN unite_rech ur ON ur.source_code::text = tmp.unite_rech_id::text
         LEFT JOIN structure_substit ss_ed ON ss_ed.from_structure_id = ed.structure_id
         LEFT JOIN ecole_doct ed_substit ON ed_substit.structure_id = ss_ed.to_structure_id
         LEFT JOIN structure_substit ss_ur ON ss_ur.from_structure_id = ur.structure_id
         LEFT JOIN unite_rech ur_substit ON ur_substit.structure_id = ss_ur.to_structure_id;

create view src_these_annee_univ(id, source_code, source_id, these_id, annee_univ) as
SELECT NULL::text AS id,
       tmp.source_code,
       src.id     AS source_id,
       t.id       AS these_id,
       tmp.annee_univ
FROM tmp_these_annee_univ tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.structure_id = src.etablissement_id
         JOIN structure s ON s.id = e.structure_id
         JOIN these t ON t.source_code::text = tmp.these_id::text;

create view src_titre_acces(id, source_code, source_id, these_id, titre_acces_interne_externe, libelle_titre_acces, type_etb_titre_acces, libelle_etb_titre_acces, code_dept_titre_acces, code_pays_titre_acces) as
SELECT NULL::text AS id,
       tmp.source_code,
       src.id     AS source_id,
       t.id       AS these_id,
       tmp.titre_acces_interne_externe,
       tmp.libelle_titre_acces,
       tmp.type_etb_titre_acces,
       tmp.libelle_etb_titre_acces,
       tmp.code_dept_titre_acces,
       tmp.code_pays_titre_acces
FROM tmp_titre_acces tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.structure_id = src.etablissement_id
         JOIN structure s ON s.id = e.structure_id
         JOIN these t ON t.source_code::text = tmp.these_id::text;

create view src_unite_rech(id, source_code, source_id, structure_id) as
SELECT NULL::text AS id,
       tmp.source_code,
       src.id     AS source_id,
       s.id       AS structure_id
FROM tmp_unite_rech tmp
         JOIN structure s ON s.source_code::text = tmp.structure_id::text
         JOIN source src ON src.id = tmp.source_id;

create view src_variable(id, source_code, source_id, etablissement_id, code, description, valeur, date_deb_validite, date_fin_validite) as
SELECT NULL::text  AS id,
       tmp.source_code,
       src.id      AS source_id,
       e.id        AS etablissement_id,
       tmp.cod_vap AS code,
       tmp.lib_vap AS description,
       tmp.par_vap AS valeur,
       tmp.date_deb_validite,
       tmp.date_fin_validite
FROM tmp_variable tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.structure_id = src.etablissement_id
         JOIN structure s ON s.id = e.structure_id;

