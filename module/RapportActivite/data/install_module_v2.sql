
drop table if exists rapport_activite cascade;
create table rapport_activite
(
    id bigint not null
        constraint rapport_activite_pkey
            primary key,
    these_id bigint not null
        constraint rapport_activite_these_fk
            references these
            on delete cascade,
    fichier_id bigint /*not null*/
        constraint rapport_annuel_fichier_fk
            references fichier
            on delete cascade,
    annee_univ bigint not null,
    est_fin_contrat boolean not null,
    description_projet_recherche text,
    principaux_resultats_obtenus text,
    productions_scientifiques text,
    formations_specifiques text,
    formations_transversales text,
    actions_diffusion_culture_scientifique text,
    autres_activites text,
    calendrier_previonnel_finalisation text,
    preparation_apres_these text,
    perspectives_apres_these text,
    commentaires text,
    histo_createur_id bigint not null
        constraint rapport_activite_hc_fk
            references utilisateur
            on delete cascade,
    histo_modificateur_id bigint
        constraint rapport_activite_hm_fk
            references utilisateur
            on delete cascade,
    histo_destructeur_id bigint
        constraint rapport_activite_hd_fk
            references utilisateur
            on delete cascade,
    histo_creation timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modification timestamp,
    histo_destruction timestamp,
    z_old_rapport_id bigint
);

create index rapport_activite_these_idx
    on rapport_activite (these_id);

create index rapport_activite_fichier_idx
    on rapport_activite (fichier_id);

create index rapport_activite_hcfk_idx
    on rapport_activite (histo_createur_id);

create index rapport_activite_hdfk_idx
    on rapport_activite (histo_destructeur_id);

create index rapport_activite_hmfk_idx
    on rapport_activite (histo_modificateur_id);

create unique index rapport_activite_uniq_un1
    on rapport_activite (these_id, annee_univ, est_fin_contrat, histo_destruction)
    where (histo_destruction IS NOT NULL);

create unique index rapport_activite_uniq_un2
    on rapport_activite (these_id, annee_univ, est_fin_contrat)
    where (histo_destruction IS NULL);

create sequence if not exists rapport_activite_id_seq ;

drop table if exists rapport_activite_avis;
create table rapport_activite_avis
(
    id bigint not null
        constraint rapport_activite_avis_pkey
            primary key,
    rapport_id bigint not null
        constraint rapport_activite_avis_rapport_fk
            references rapport_activite,
    avis_id bigint
        constraint rapport_activite_avis__unicaen_avis__fk
            references unicaen_avis,
    histo_createur_id bigint not null
        constraint rapport_activite_avis_hc_fk
            references utilisateur
            on delete cascade,
    histo_modificateur_id bigint
        constraint rapport_activite_avis_hm_fk
            references utilisateur
            on delete cascade,
    histo_destructeur_id bigint
        constraint rapport_activite_avis_hd_fk
            references utilisateur
            on delete cascade,
    histo_creation timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modification timestamp,
    histo_destruction timestamp
);

create index rapport_activite_avis_hcfk_idx
    on rapport_activite_avis (histo_createur_id);

create index rapport_activite_avis_hdfk_idx
    on rapport_activite_avis (histo_destructeur_id);

create index rapport_activite_avis_hmfk_idx
    on rapport_activite_avis (histo_modificateur_id);

create index rapport_activite_avis_rapport_idx
    on rapport_activite_avis (rapport_id);

create index rapport_activite_avis_avis_idx
    on rapport_activite_avis (avis_id);

create sequence if not exists rapport_activite_avis_id_seq ;

drop table if exists rapport_activite_validation;
create table rapport_activite_validation
(
    id bigint not null
        constraint rapport_activite_validation_pkey
            primary key,
    type_validation_id bigint not null
        constraint rapport_activite_validation_type_fk
            references type_validation,
    rapport_id bigint not null
        constraint rapport_activite_validation_rapport_fk
            references rapport_activite,
    individu_id bigint
        constraint rapport_activite_validation_indiv_id_fk
            references individu,
    histo_creation timestamp default ('now'::text)::timestamp without time zone not null,
    histo_createur_id bigint default 1 not null,
    histo_modification timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint default 1 not null,
    histo_destruction timestamp,
    histo_destructeur_id bigint
);

create index rapport_activite_validation_hcfk_idx
    on rapport_activite_validation (histo_createur_id);

create index rapport_activite_validation_hdfk_idx
    on rapport_activite_validation (histo_destructeur_id);

create index rapport_activite_validation_hmfk_idx
    on rapport_activite_validation (histo_modificateur_id);

create index rapport_activite_validation_indiv_idx
    on rapport_activite_validation (individu_id);

create index rapport_activite_validation_rapport_idx
    on rapport_activite_validation (rapport_id);

create index rapport_activite_validation_type_idx
    on rapport_activite_validation (type_validation_id);

create unique index rapport_activite_validation_un_1
    on rapport_activite_validation (type_validation_id, rapport_id, individu_id, histo_destruction)
    where (histo_destruction IS NOT NULL);

create unique index rapport_activite_validation_un_2
    on rapport_activite_validation (type_validation_id, rapport_id, individu_id)
    where (histo_destruction IS NULL);

create sequence if not exists rapport_activite_validation_id_seq ;


--------------------------- Migration de données ---------------------------

-- recup des anciens rapports d'activité
insert into rapport_activite(
    id, these_id, fichier_id, annee_univ, est_fin_contrat,
    description_projet_recherche, principaux_resultats_obtenus, productions_scientifiques, actions_diffusion_culture_scientifique,
    histo_createur_id, histo_modificateur_id,
    histo_destructeur_id, histo_creation, histo_modification, histo_destruction,
    z_old_rapport_id)
select
    nextval('rapport_activite_id_seq'), these_id, fichier_id, annee_univ, est_final,
    null, null, null, null,
    histo_createur_id, histo_modificateur_id,
    histo_destructeur_id, histo_creation, histo_modification, histo_destruction,
    id
from rapport
where type_rapport_id = 1
;

UPDATE unicaen_avis_type SET libelle = 'Complétude du rapport d''activité (ancien module)'
WHERE code = 'AVIS_RAPPORT_ACTIVITE_GEST';

-- recup des anciens avis sur les rapports d'activité
insert into rapport_activite_avis(id, rapport_id, avis_id, histo_createur_id, histo_modificateur_id,
                                  histo_destructeur_id, histo_creation, histo_modification, histo_destruction)
select nextval('rapport_activite_avis_id_seq'), r.id, a.avis_id,  a.histo_createur_id, a.histo_modificateur_id,
       a.histo_destructeur_id, a.histo_creation, a.histo_modification, a.histo_destruction
from rapport_avis a
         join rapport_activite r on r.z_old_rapport_id = a.rapport_id
;

-- recup des anciennes validations sur les rapports d'activité
insert into rapport_activite_validation(id, rapport_id, type_validation_id, individu_id,
                                        histo_createur_id, histo_modificateur_id,
                                        histo_destructeur_id, histo_creation, histo_modification, histo_destruction)
select nextval('rapport_activite_validation_id_seq'), r.id, a.type_validation_id, a.individu_id,
       a.histo_createur_id, a.histo_modificateur_id,
       a.histo_destructeur_id, a.histo_creation, a.histo_modification, a.histo_destruction
from rapport_validation a
         join rapport_activite r on r.z_old_rapport_id = a.rapport_id
;

--------------------------- PRIVILEGES ---------------------------

insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (
    select 210, 'ajouter-tout', 'Ajouter un rapport d''activité concernant toute thèse' union
    select 220, 'ajouter-sien', 'Ajouter un rapport d''activité concernant ses thèses' union
    select 225, 'modifier-tout', 'Modifier un rapport d''activité concernant toute thèse' union
    select 226, 'modifier-sien', 'Modifier un rapport d''activité concernant ses thèses' union
    select 230, 'consulter-tout', 'Consulter un rapport d''activité concernant toute thèse' union
    select 240, 'consulter-sien', 'Consulter un rapport d''activité concernant ses thèses' union
    select 245, 'generer-tout', 'Générer un rapport d''activité au format PDF concernant toute thèse' union
    select 246, 'generer-sien', 'Générer un rapport d''activité au format PDF concernant ses thèses'
)
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d
         join CATEGORIE_PRIVILEGE cp on cp.CODE = 'rapport-activite'
;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'rapport-activite', 'ajouter-tout' union
    select 'rapport-activite', 'ajouter-sien' union
    select 'rapport-activite', 'modifier-tout' union
    select 'rapport-activite', 'modifier-sien' union
    select 'rapport-activite', 'consulter-tout' union
    select 'rapport-activite', 'consulter-sien' union
    select 'rapport-activite', 'generer-tout' union
    select 'rapport-activite', 'generer-sien'
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH', 'BDD'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'rapport-activite', 'lister-sien' union
    select 'rapport-activite', 'ajouter-sien' union
    select 'rapport-activite', 'modifier-sien' union
    select 'rapport-activite', 'consulter-sien' union
    select 'rapport-activite', 'telecharger-sien' union
    select 'rapport-activite', 'generer-sien'
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('D', 'DOCTORANT', 'K', 'GEST_UR', 'GEST_ED', 'RESP_UR', 'RESP_ED')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (
        select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id
    )
;

delete from profil_privilege pp1
where exists (
              select *
              from profil_privilege pp
                       join profil on pp.profil_id = profil.id and role_id in ('RESP_ED', 'GEST_ED')
                       join privilege p on pp.privilege_id = p.id
              where p.code in ('valider-sien', 'valider-tout', 'devalider-sien', 'devalider-tout')
                and pp.profil_id = pp1.profil_id and pp.privilege_id = pp1.privilege_id
          );

delete from ROLE_PRIVILEGE rp
where not exists (
        select *
        from PROFIL_TO_ROLE p2r
                 join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = p2r.PROFIL_ID
        where rp.role_id = p2r.role_id and rp.privilege_id = pp.privilege_id
    );

--------------------------- Validations ---------------------------

update type_validation
set code = 'RAPPORT_ACTIVITE_AUTO', libelle = 'Validation finale du rapport d''activité non dématérialisé (ancien module)'
where code = 'RAPPORT_ACTIVITE';
insert into type_validation(id, code, libelle)
select nextval('type_validation_id_seq'), 'RAPPORT_ACTIVITE_DOCTORANT', 'Validation du rapport d''activité par le doctorant';

INSERT INTO type_validation (id, code, libelle)
select nextval('type_validation_id_seq'), 'RAPPORT_ACTIVITE', 'Validation finale du rapport d''activité non dématérialisé (ancien module)';

--------------------------- Avis : retouches ---------------------------

--update unicaen_avis_type set code = 'AVIS_RAPPORT_ACTIVITE_GEST_ED' where code = 'AVIS_RAPPORT_ACTIVITE_GEST'; // pas décidé
update unicaen_avis_type set code = 'AVIS_RAPPORT_ACTIVITE_DIR_ED' where code = 'AVIS_RAPPORT_ACTIVITE_DIR';

update unicaen_avis_valeur set code = 'AVIS_RAPPORT_ACTIVITE_DIR_ED_VALEUR_INCOMPLET'
where code = 'AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET';

update unicaen_avis_type_valeur_complem set libelle = 'Si le rapport est jugé incomplet, la balle retournera dans le camp du doctorant...'
where code = 'AVIS_RAPPORT_ACTIVITE_DIR__AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET__PB_INFOS';

--------------------------- Avis Direction These ---------------------------

insert into unicaen_avis_type (code, libelle, description, ordre)
values ('AVIS_RAPPORT_ACTIVITE_DIR_THESE', 'Avis de la direction de thèse', 'Point de vue de la direction de thèse', 20);

insert into unicaen_avis_type_valeur (avis_type_id, avis_valeur_id)
select t.id, v.id
from unicaen_avis_type t, unicaen_avis_valeur v
where t.code = 'AVIS_RAPPORT_ACTIVITE_DIR_THESE'
  and v.code in (
                 'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF',
                 'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
    ) ;

-- insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
-- with tmp (code, oblig, type, libelle) as (
--     select 'PB_INFOS', false, 'information', 'Si l''avis est négatif, la balle retournera dans le camp du doctorant...'
-- )
-- select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
-- from tmp, unicaen_avis_type_valeur tv
-- join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_DIR_THESE'
-- join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
--     'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
-- ) ;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    select 'PB_MOTIF', true, 'textarea', 'Motif'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
              join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_DIR_THESE'
              join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
    'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
    ) ;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    select 'PB_COMMENTAIRES', false, 'textarea', 'Commentaires'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
              join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_DIR_THESE'
              join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
                                                                                    'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF',
                                                                                    'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
    ) ;

--------------------------- Avis CoDirection These ---------------------------

insert into unicaen_avis_type (code, libelle, description, ordre)
values ('AVIS_RAPPORT_ACTIVITE_CODIR_THESE', 'Avis de la codirection de thèse', 'Point de vue de la codirection de thèse', 20);

insert into unicaen_avis_type_valeur (avis_type_id, avis_valeur_id)
select t.id, v.id
from unicaen_avis_type t, unicaen_avis_valeur v
where t.code = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE'
  and v.code in (
                 'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF',
                 'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
    ) ;

-- insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
-- with tmp (code, oblig, type, libelle) as (
--     select 'PB_INFOS', false, 'information', 'Si l''avis est négatif, la balle retournera dans le camp du doctorant...'
-- )
-- select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
-- from tmp, unicaen_avis_type_valeur tv
-- join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE'
-- join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
--     'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
-- ) ;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    select 'PB_MOTIF', true, 'textarea', 'Motif'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
              join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE'
              join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
    'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
    ) ;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    select 'PB_COMMENTAIRES', false, 'textarea', 'Commentaires'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
              join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE'
              join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
                                                                                    'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF',
                                                                                    'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
    ) ;

--------------------------- Avis Direction UR ---------------------------

insert into unicaen_avis_type (code, libelle, description, ordre)
values ('AVIS_RAPPORT_ACTIVITE_DIR_UR', 'Avis de la direction d''UR', 'Point de vue de la direction d''UR', 20);

insert into unicaen_avis_type_valeur (avis_type_id, avis_valeur_id)
select t.id, v.id
from unicaen_avis_type t, unicaen_avis_valeur v
where t.code = 'AVIS_RAPPORT_ACTIVITE_DIR_UR'
  and v.code in (
                 'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF',
                 'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
    ) ;

-- insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
-- with tmp (code, oblig, type, libelle) as (
--     select 'PB_INFOS', false, 'information', 'Si l''avis est négatif, la balle retournera dans le camp du doctorant...'
-- )
-- select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
-- from tmp, unicaen_avis_type_valeur tv
-- join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_DIR_UR'
-- join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
--     'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
-- ) ;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    select 'PB_MOTIF', true, 'textarea', 'Motif'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
              join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_DIR_UR'
              join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
    'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
    ) ;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    select 'PB_COMMENTAIRES', false, 'textarea', 'Commentaires'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
              join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_DIR_UR'
              join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
                                                                                    'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF',
                                                                                    'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
    ) ;

--------------------------------------------- CED -------------------------------------------

INSERT INTO structure (id, sigle, libelle, chemin_logo, type_structure_id,  histo_createur_id,
                       source_id, source_code, code, est_ferme, adresse, telephone, fax)
select nextval('structure_id_seq'), 'CED', 'Collège des écoles doctorales normandes', 'CED.png', 1, 1,
       1, 'SyGAL::63db82880b300', 'CED', false, 'Esplanade de la Paix - CS14032 – 14032 Caen cedex',
       '+33 (0)2 31 56 69 57', '+33 (0)2 31 56 69 51';
