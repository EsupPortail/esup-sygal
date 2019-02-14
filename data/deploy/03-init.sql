--
-- INIT
--

--
-- COMUE éventuelle
-- ----------------
--
-- Contraintes :
--   SOURCE_CODE: Utiliser 'COMUE'.
--
INSERT INTO STRUCTURE (ID, SOURCE_CODE, SIGLE, LIBELLE, TYPE_STRUCTURE_ID, SOURCE_ID, CODE, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
  VALUES (1, 'COMUE', 'NU', 'Normandie Université', null, 1, 'COMUE', 1, 1);
INSERT INTO ETABLISSEMENT (ID, STRUCTURE_ID, DOMAINE, SOURCE_ID, SOURCE_CODE, EST_COMUE, EST_MEMBRE, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
  VALUES (1, 1, 'normandie-univ.fr', 1, 'COMUE', 1, 0, 1, 1);

--
-- Etablissements
-- --------------
--
-- :codeEtablissement: choisir un code court et unique, ex: 'UCN', 'UNILIM'.
--
INSERT INTO STRUCTURE (ID, SOURCE_CODE, SIGLE, LIBELLE, TYPE_STRUCTURE_ID, SOURCE_ID, CODE, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
  VALUES (2, :codeEtablissement, 'Unicaen', 'Université de Caen Normandie', 1, 1, :codeEtablissement, 1, 1);
INSERT INTO ETABLISSEMENT (ID, STRUCTURE_ID, DOMAINE, SOURCE_ID, SOURCE_CODE, EST_COMUE, EST_MEMBRE, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
  VALUES (2, 2, 'unicaen.fr', 1, :codeEtablissement, 0, 1, 1, 1);

--
-- Sources de données importables.
--
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE, ETABLISSEMENT_ID)
  SELECT 2, SOURCE_CODE||'::apogee', 'Apogée '||SOURCE_CODE, 1, ID from STRUCTURE where SOURCE_CODE = :codeEtablissement;
-- CODE : SOURCE_CODE de l'établissement + '::' + 'apogee'

--
-- Premier utilisateur.
--
INSERT INTO UTILISATEUR (ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD)
  VALUES (2,
          'premierf@sygal.fr', -- EPPN (si shibboleth activé) ou supannAliasLogin (si LDAP activé)
          'francois.premier@sygal.fr',
          'François PREMIER',
          'shib' -- 'shib' (auth shibboleth), ou 'ldap' (auth LDAP), ou mdp bcrypté (auth locale)
         );

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

