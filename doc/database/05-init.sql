--
-- INIT
--
-- ATTENTION, ce sript doit être personnalisé pour votre contexte :
--
--      Remplacez "UCN"                          par le code SyGAL de votre établissement (ex: "UTLN").
--      Remplacez "Unicaen"                      par l'intitulé court de votre établissement (ex: "UTLN").
--      Remplacez "Université de Caen Normandie" par l'intitulé long de votre établissement (ex: "Université de Toulon").
--
-- Faites-le avec sed, ex :
--    cat 05-init.sql | sed "s/'UCN/\'UTLN\'/g" | sed "s/Unicaen/UTLN/g" | sed "s/Caen Normandie/Toulon/g" > 04-init-utln.sql
--


--
-- Création de l'établissement.
--
-- 1/ STRUCTURE
--    À personnaliser :
--      - colonnes SOURCE_CODE et CODE : remplacer 'UCN' par le code établissement choisi, ex: 'UNILIM'
--      - colonne SIGLE : remplacer 'Unicaen' par le sigle ou libellé court choisi, ex: 'Unilim'
--      - colonne LIBELLE : remplacer 'Université de Caen Normandie' par le libellé choisi, ex: 'Université de Limoges'
--
INSERT INTO STRUCTURE (ID, SOURCE_CODE, CODE, SIGLE, LIBELLE, TYPE_STRUCTURE_ID, SOURCE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
select
  STRUCTURE_ID_SEQ.nextval,
  'UCN',
  'UCN',
  'Unicaen',
  'Université de Caen Normandie',
  1, 1,
  1, 1
from dual;
/
--
-- 2/ ETABLISSEMENT
--    À personnaliser :
--      - colonne DOMAINE : remplacer 'unicaen.fr' par votre domaine, ex: 'unilim.fr'
--      - colonne SOURCE_CODE : remplacer 'UCN' par le code établissement choisi, ex: 'UNILIM'
--      - colonne EST_COMUE : mettre à 1 si l'établissement est une COMUE
--      - where : remplacer 'UCN' par le code établissement choisi, ex: 'UNILIM'
--
INSERT INTO ETABLISSEMENT (ID, STRUCTURE_ID, DOMAINE, SOURCE_CODE, EST_COMUE, EST_MEMBRE, SOURCE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
select
  ETABLISSEMENT_ID_SEQ.nextval,
  s.ID,
  'unicaen.fr',
  'UCN',
  0,
  1,
  1,
  1, 1
from STRUCTURE s
where s.SOURCE_CODE = 'UCN';
/

--
-- Création des sources de données importables, ex: Apogée.
--
--    À personnaliser :
--     - where : remplacer 'UCN' par le code établissement choisi, ex: 'UNILIM'
--
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE, ETABLISSEMENT_ID)
SELECT 2, SOURCE_CODE||'::apogee', 'Apogée '||SOURCE_CODE, 1, ID
from ETABLISSEMENT
where SOURCE_CODE = 'UCN' --> à remplacer par le code choisi *entre apostrophe*, ex: 'UCN'
/

--
-- Rôles par établissement.
--
--    À personnaliser :
--     - where : remplacer 'UCN' par le code établissement choisi, ex: 'UNILIM'
--
INSERT INTO ROLE (
  ID,
  CODE,
  LIBELLE,
  SOURCE_CODE,
  SOURCE_ID,
  ROLE_ID,
  THESE_DEP,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID,
  STRUCTURE_ID,
  TYPE_STRUCTURE_DEPENDANT_ID
)
WITH tmp(CODE, LIBELLE, THESE_DEP) as (
  select 'ADMIN',     'Administrateur',             0 from dual union
  select 'MDD',       'Maison du doctorat',       0 from dual union
  select 'BU',        'Bibliothèque universitaire', 0 from dual union
  select 'DOCTORANT', 'Doctorant',                  1 from dual
)
SELECT
  ROLE_ID_SEQ.nextval,
  tmp.CODE,
  tmp.LIBELLE,
  s.SOURCE_CODE || '::' || tmp.CODE,
  1,
  tmp.LIBELLE || ' ' || s.SOURCE_CODE,
  tmp.THESE_DEP,
  1,
  1,
  s.ID,
  1
FROM tmp, STRUCTURE s
WHERE s.SOURCE_CODE = 'UCN' --> à remplacer par le code choisi *entre apostrophe*, ex: 'UCN'
/

--
-- Accord des privilèges de gestion des privilèges au rôle ADMIN_TECH.
-- NB: cela débloque l'accès au menu "Droits d'accès" dans l'appli.
--
insert into ROLE_PRIVILEGE(ROLE_ID, PRIVILEGE_ID)
select r.id, p.id
from role r, privilege p, CATEGORIE_PRIVILEGE cp
where r.SOURCE_CODE = 'ADMIN_TECH'
  and cp.CODE = 'droit'
  and p.CATEGORIE_ID = cp.id
/
