--
-- INIT
--

--
-- Si votre client SQL ne supporte pas l'utilisation de paramètre du genre :codeEtablissement, remplacez dans ce script
-- toutes les occurences de ":codeEtablissement" par le code choisi *entre apostrophe*, ex: 'UCN'.
--
-- Si votre client SQL supporte les motifs du genre :codeEtablissement, il vous demandera de saisir une valeur
-- pour ce parameètre : entrez le code choisi *entre apostrophe*, ex: 'UCN'.
--

--
-- Etablissements
--
--
INSERT INTO STRUCTURE (ID, SOURCE_CODE, SIGLE, LIBELLE, TYPE_STRUCTURE_ID, SOURCE_ID, CODE, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
select STRUCTURE_ID_SEQ.nextval, :codeEtablissement, 'Unicaen', 'Université de Caen Normandie', 1, 1, :codeEtablissement, 1, 1 from dual;
INSERT INTO ETABLISSEMENT (ID, STRUCTURE_ID, DOMAINE, SOURCE_ID, SOURCE_CODE, EST_COMUE, EST_MEMBRE, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
select ETABLISSEMENT_ID_SEQ.nextval, s.ID, 'unicaen.fr', 1, :codeEtablissement, 0, 1, 1, 1 from STRUCTURE s where s.SOURCE_CODE = :codeEtablissement;

--
-- Sources de données importables.
--
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE, ETABLISSEMENT_ID)
  SELECT 2, SOURCE_CODE||'::apogee', 'Apogée '||SOURCE_CODE, 1, ID from STRUCTURE where SOURCE_CODE = :codeEtablissement;

--
-- Rôles par établissement.
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
  select 'BDD',       'Bureau des doctorats',       0 from dual union
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
WHERE s.SOURCE_CODE = :codeEtablissement;

--
-- Création de l'individu/utilisateur de test
--
INSERT INTO INDIVIDU (ID, CIVILITE, NOM_USUEL, NOM_PATRONYMIQUE, PRENOM1, EMAIL, SOURCE_CODE, SOURCE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, SUPANN_ID)
select INDIVIDU_ID_SEQ.nextval, 'M.', 'Premier', 'Premier', 'François', 'francois.premier@univ.fr', 'INCONNU::00012345', 1, 1, 1, '00012345' from dual;
INSERT INTO UTILISATEUR (ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD, INDIVIDU_ID)
select UTILISATEUR_ID_SEQ.nextval,
       'premierf@univ.fr', -- EPPN (si shibboleth activé) ou supannAliasLogin (si LDAP activé)
       'francois.premier@univ.fr',
       'François PREMIER',
       'shib', -- 'shib' (auth shibboleth), ou 'ldap' (auth LDAP), ou mdp bcrypté (auth locale)
        i.ID
from INDIVIDU i where i.SOURCE_CODE = 'INCONNU::00012345';

--
-- /!\ Attribution du rôle Admin tech à l'utilisateur de test !!
--
INSERT INTO INDIVIDU_ROLE(ID, INDIVIDU_ID, ROLE_ID)
select INDIVIDU_ROLE_ID_SEQ.nextval, i.ID, r.ID from INDIVIDU i, ROLE r
where i.SOURCE_CODE = 'INCONNU::00012345'
and r.SOURCE_CODE = 'ADMIN_TECH';

--
-- Accord des privilèges de gestion des privilèges au rôle ADMIN_TECH.
-- NB: cela débloque l'accès au menu "Droits d'accès" dans l'appli.
--
insert into ROLE_PRIVILEGE(ROLE_ID, PRIVILEGE_ID)
select r.id, p.id
from role r, privilege p, CATEGORIE_PRIVILEGE cp
where r.SOURCE_CODE = 'ADMIN_TECH'
  and cp.CODE = 'droit' -- gestion des droits
  and p.CATEGORIE_ID = cp.id;
