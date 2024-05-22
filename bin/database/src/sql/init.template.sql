--
-- INITIALISATIONS de données.
--
-- Attention, ce sript doit être adapté à l'établissement d'installation :
--
--      '{ETAB_CODE}' : code unique identifiant l'établissement, ex: 'UNILIM'
--      '{ETAB_SIGLE}' : sigle ou libellé court de l'établissement, ex: 'Unilim'
--      '{ETAB_LIBELLE}' : libellé complet de l'établissement, ex: 'Université de Limoges'
--      '{ETAB_DOMAINE}' : domaine de l'établissement, ex: 'unilim.fr'
--      '{EMAIL_ASSISTANCE}' : Adresse électronique d'assistance
--      '{EMAIL_BIBLIOTHEQUE}' : Adresse électronique par les aspects Bibliothèque
--      '{EMAIL_DOCTORAT}' : Adresse électronique par les aspects Doctorat
--

--
-- Création de l'établissement.
--
-- 1/ STRUCTURE
--
insert into structure (id, type_structure_id,
                       source_code, source_id,
                       code, sigle, libelle,
                       histo_createur_id, histo_modificateur_id)
select
    nextval('structure_id_seq'), 1, '{ETAB_CODE}', 1,
    '{ETAB_CODE}', '{ETAB_SIGLE}', '{ETAB_LIBELLE}',
    1, 1
;

--
-- 2/ ETABLISSEMENT
--
insert into etablissement (id, structure_id, domaine, source_code, source_id,
                           est_comue, est_membre, est_etab_inscription,
                           email_assistance, email_bibliotheque, email_doctorat,
                           histo_createur_id, histo_modificateur_id)
select
    nextval('etablissement_id_seq'), s.id, '{ETAB_DOMAINE}', '{ETAB_CODE}', 1,
    false, true, true,
    '{EMAIL_ASSISTANCE}', '{EMAIL_BIBLIOTHEQUE}', '{EMAIL_DOCTORAT}',
    1, 1
from structure s
where s.source_code = '{ETAB_CODE}'
;

--
-- Création des sources de données importables, ex: Apogée.
--
insert into source (id, code, libelle, importable, etablissement_id)
select nextval('source_id_seq'), source_code||'::apogee', 'Apogée '||source_code, true, id
from etablissement
where source_code = '{ETAB_CODE}'
  and '{SOURCE_APOGEE}' = '1'
;
insert into source (id, code, libelle, importable, etablissement_id)
select nextval('source_id_seq'), source_code||'::physalis', 'Physalis '||source_code, true, id
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
