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
    1,
    1,
    1, 1
;

--
-- 2/ ETABLISSEMENT
--
insert into etablissement (id, structure_id, domaine, source_code, source_id, est_comue, est_membre,
                           est_etab_inscription, histo_createur_id, histo_modificateur_id)
select
    nextval('etablissement_id_seq'),
    s.id,
    '{ETAB_DOMAINE}',
    '{ETAB_CODE}',
   1,
    false,
    true,
    true,
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
  and '{SOURCE_APOGEE}' = '1'
;
insert into source (id, code, libelle, importable, etablissement_id)
select 3, source_code||'::physalis', 'Physalis '||source_code, true, id
from etablissement
where source_code = '{ETAB_CODE}'
  and '{SOURCE_PHYSALIS}' = '1'
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
-- Accord de tous les privilèges au rôle ADMIN_TECH.
--
insert into profil_privilege(profil_id, privilege_id)
select pro.id, pri.id
from profil pro,
     privilege pri
where pro.role_id = 'ADMIN_TECH'
  and not exists(select * from profil_privilege where profil_id = pro.id and privilege_id = pri.id)
;
insert into role_privilege(role_id, privilege_id)
select r.id, pri.id
from role r,
     privilege pri
where r.code = 'ADMIN_TECH'
  and not exists(select * from role_privilege where role_id = r.id and privilege_id = pri.id)
;
insert into profil_to_role(profil_id, role_id)
select pro.id, r.id
from profil pro,
     role r
where pro.role_id = 'ADMIN_TECH'
  and r.code = 'ADMIN_TECH'
  and not exists(select * from profil_to_role where profil_id = pro.id and role_id = r.id)
;


--
-- Peuplement des VM.
--
refresh materialized view mv_recherche_these;
