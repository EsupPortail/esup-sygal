insert into nature_fichier (id, code, libelle)
select nextval('nature_fichier_id_seq'), 'SIGNATURE_RAPPORT_ACTIVITE', 'Signature figurant sur la page de validation d''un rapport d''activité';


insert into privilege(id, categorie_id, code, libelle, ordre)
select nextval('privilege_id_seq'), c.id, 'modifier-avis-tout', 'Modifier un avis sur un rapport concernant toute thèse', 25
from categorie_privilege c where code = 'rapport-activite';
insert into privilege(id, categorie_id, code, libelle, ordre)
select nextval('privilege_id_seq'), c.id, 'modifier-avis-sien', 'Modifier un avis sur un rapport concernant ses thèses', 26
from categorie_privilege c where code = 'rapport-activite';
delete from privilege
where code in ('rechercher-tout', 'rechercher-sien')
  and categorie_id = (select id from categorie_privilege where code = 'rapport-activite') ;


alter table rapport_avis alter column avis drop not null;
alter table rapport_avis
    add avis_id bigint;
alter table rapport_avis
    add constraint rapport_avis__unicaen_avis__fk
        foreign key (avis_id) references unicaen_avis;


-- truncate table unicaen_avis_complem cascade ;
-- truncate table unicaen_avis_type_valeur_complem cascade ;
-- truncate table unicaen_avis_type_valeur cascade ;
-- truncate table unicaen_avis_type cascade ;
-- truncate table unicaen_avis_valeur cascade ;


--------------------------- Avis Gestionnaire ---------------------------

insert into unicaen_avis_type (code, libelle, description, ordre)
values ('AVIS_RAPPORT_ACTIVITE_GEST', 'Complétude du rapport d''activité', 'Point de vue Gestionnaire d''ED', 10);

insert into unicaen_avis_valeur (code, valeur, valeur_bool, tags)
with v (code, valeur, valeurb, tags) as (
    select 'AVIS_RAPPORT_ACTIVITE_VALEUR_COMPLET',   'Rapport complet',   true,  'icon-ok' union all
    select 'AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET', 'Rapport incomplet', false, 'icon-ko'
)
select code, valeur, valeurb, tags from v ;

insert into unicaen_avis_type_valeur (avis_type_id, avis_valeur_id)
select t.id, v.id
from unicaen_avis_type t, unicaen_avis_valeur v
where t.code = 'AVIS_RAPPORT_ACTIVITE_GEST'
  and v.code in ('AVIS_RAPPORT_ACTIVITE_VALEUR_COMPLET',
                 'AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET');

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    --select 'Rapport d''activité incomplet', 'checkbox' union all
    select 'PB_DOCTORANT',       true,  'checkbox', 'Pas de date/signature du doctorant' union all
    select 'PB_DIRECTION_THESE', true,  'checkbox', 'Manque l''avis/date/signature de la direction de thèse' union all
    select 'PB_DIRECTION_UR',    true,  'checkbox', 'Manque l''avis/date/signature du Directeur de l''Unité de Recherche' union all
    select 'PB_AUTRE',           true,  'checkbox', 'Autre (à préciser)' union all
    select 'PB_AUTRE_PRECISION', false, 'textarea', 'Précisions :' union all
    select 'PB_COMMENTAIRES',    false, 'textarea', 'Commentaires'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
              join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_GEST'
              join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code = 'AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET' ;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    select 'PB_COMMENTAIRES', false, 'textarea', 'Commentaires'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
              join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_GEST'
              join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code = 'AVIS_RAPPORT_ACTIVITE_VALEUR_COMPLET' ;

update unicaen_avis_type_valeur_complem
set parent_id = (select id from unicaen_avis_type_valeur_complem where code = 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__PB_AUTRE')
where code = 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__PB_AUTRE_PRECISION';

--------------------------- Avis Direction ---------------------------

insert into unicaen_avis_type (code, libelle, description, ordre)
values ('AVIS_RAPPORT_ACTIVITE_DIR', 'Avis de la direction d''ED', 'Point de vue de la Direction d''ED', 20);

insert into unicaen_avis_valeur (code, valeur, valeur_bool, tags)
with v (code, valeur, valeurb, tags) as (
    select 'AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET', 'Rapport incomplet', false, 'icon-ko' union all
    select 'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF',       'Avis positif',      true,  'icon-ok' union all
    select 'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF',       'Avis réservé',      false, 'icon-ko'
)
select code, valeur, valeurb, tags from v ;

insert into unicaen_avis_type_valeur (avis_type_id, avis_valeur_id)
select t.id, v.id
from unicaen_avis_type t, unicaen_avis_valeur v
where t.code = 'AVIS_RAPPORT_ACTIVITE_DIR'
  and v.code in ('AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF',
                 'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF',
                 'AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET') ;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    select 'PB_INFOS', false, 'information', 'Si le rapport est jugé incomplet, la balle retournera dans le camp des gestionnaires d''ED...'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
              join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_DIR'
              join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
    'AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET'
    ) ;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    select 'PB_MOTIF', true, 'textarea', 'Motif'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
              join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_DIR'
              join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
    'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF'
    ) ;

insert into unicaen_avis_type_valeur_complem (avis_type_valeur_id, code, obligatoire_un_au_moins, type, libelle)
with tmp (code, oblig, type, libelle) as (
    select 'PB_COMMENTAIRES', false, 'textarea', 'Commentaires'
)
select tv.id, concat(t.code,'__',v.code,'__',tmp.code), tmp.oblig, tmp.type, tmp.libelle
from tmp, unicaen_avis_type_valeur tv
      join unicaen_avis_type t on t.id = tv.avis_type_id and t.code = 'AVIS_RAPPORT_ACTIVITE_DIR'
      join unicaen_avis_valeur v on v.id = tv.avis_valeur_id and v.code in (
            'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF',
            'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF',
            'AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET'
    ) ;
