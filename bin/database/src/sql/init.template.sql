--
-- INITIALISATIONS de données.
--
-- Attention, ce sript doit être adapté à l'établissement d'installation :
--
--      '{ETAB_CODE}' : code unique identifiant l'établissement, ex: 'UNILIM'
--      '{ETAB_SIGLE}' : sigle ou libellé court de l'établissement, ex: 'Unilim'
--      '{ETAB_LIBELLE}' : libellé complet de l'établissement, ex: 'Université de Limoges'
--      '{ETAB_DOMAINE}' : domaine de l'établissement, ex: 'unilim.fr'
--

--
-- Création de l'établissement.
--
-- 1/ STRUCTURE
--
insert into structure (id, source_code, code, sigle, libelle, type_structure_id, source_id, histo_createur_id, histo_modificateur_id)
select
    nextval('structure_id_seq'),
    '{ETAB_CODE}',
    '{ETAB_CODE}',
    '{ETAB_SIGLE}',
    '{ETAB_LIBELLE}',
    1, 1,
    1, 1
;

--
-- 2/ ETABLISSEMENT
--
insert into etablissement (id, structure_id, domaine, source_code, est_comue, est_membre, source_id, histo_createur_id, histo_modificateur_id)
select
    nextval('etablissement_id_seq'),
    s.id,
    '{ETAB_DOMAINE}',
    '{ETAB_CODE}',
    false,
    true,
    1,
    1, 1
from structure s
where s.source_code = '{ETAB_CODE}'
;

--
-- Création des sources de données importables, ex: Apogée.
--
delete from source where code <> 'SYGAL::sygal'
;
insert into source (id, code, libelle, importable, etablissement_id)
select 2, source_code||'::apogee', 'Apogée '||source_code, true, id
from etablissement
where source_code = '{ETAB_CODE}'
  and {SOURCE_APOGEE} = 1
;
insert into source (id, code, libelle, importable, etablissement_id)
select 3, source_code||'::physalis', 'Physalis '||source_code, true, id
from etablissement
where source_code = '{ETAB_CODE}'
  and {SOURCE_PHYSALIS} = 1
;

--
-- Rôles par établissement.
--
insert into role (
    id,
    code,
    libelle,
    source_code,
    source_id,
    role_id,
    these_dep,
    histo_createur_id,
    histo_modificateur_id,
    structure_id,
    type_structure_dependant_id
)
with tmp(code, libelle, these_dep) as (
    select 'ADMIN',     'Administrateur',             false union
    select 'MDD',       'Maison du doctorat',         false union
    select 'BU',        'Bibliothèque universitaire', false union
    select 'DOCTORANT', 'Doctorant',                  true
)
select
    nextval('role_id_seq'),
    tmp.code,
    tmp.libelle,
    s.source_code || '::' || tmp.code,
    1,
    tmp.libelle || ' ' || s.source_code,
    tmp.these_dep,
    1,
    1,
    s.id,
    1
from tmp, structure s
where s.source_code = '{ETAB_CODE}'
;

--
-- Accord des privilèges de "gestion des privilèges" au rôle ADMIN_TECH.
-- NB: cela débloque l'accès au menu "Droits d'accès" dans l'appli.
--
insert into role_privilege(role_id, privilege_id)
select r.id, p.id
from role r, privilege p, categorie_privilege cp
where r.SOURCE_CODE = 'ADMIN_TECH'
  and cp.CODE = 'droit'
  and p.CATEGORIE_ID = cp.id
;


--
-- Peuplement des VM.
--
refresh materialized view mv_recherche_these;
