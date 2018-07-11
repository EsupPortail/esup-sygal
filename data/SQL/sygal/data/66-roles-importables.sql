--
-- Import des rôles importables.
--


-- 1/ Lancer l'import des données (WS + UnicaenImport).



-- 2/ Mettre à jour les flags :

update role set ATTRIB_AUTO = 0, THESE_DEP = 1, TYPE_STRUCTURE_DEPENDANT_ID = null
where code in (
  'A',
  'B',
  'C',
  'D',
  'K',
  'M',
  'P',
  'R'
);


-- -- ROLE ED
--
-- insert into role(
--   ID,
--   CODE,
--   LIBELLE,
--   SOURCE_CODE,
--   SOURCE_ID,
--   ROLE_ID,
--   THESE_DEP,
--   HISTO_CREATEUR_ID,
--   HISTO_MODIFICATEUR_ID,
--   STRUCTURE_ID,
--   TYPE_STRUCTURE_DEPENDANT_ID)
-- select
--   ROLE_ID_SEQ.nextval,
--   'ED',
--   'École doctorale ' || nvl(s.sigle, '(aucun sigle trouvé)'),
--   'COMUE::ED_' || ed.SOURCE_CODE,
--   src.id,
--   'École doctorale ' || nvl(s.sigle, '(aucun sigle trouvé)'),
--   0,
--   u.id,
--   u.id,
--   s.id,
--   ts.id
-- from ECOLE_DOCT ed
--   join STRUCTURE s on s.id = ed.STRUCTURE_ID
--   join TYPE_STRUCTURE ts on ts.id = s.TYPE_STRUCTURE_ID
--   join source src on src.CODE = 'SYGAL::sygal'
--   join UTILISATEUR u on u.USERNAME = 'sygal-app'
-- ;
--
--
-- -- ROLE UR
--
-- insert into role(
--   ID,
--   CODE,
--   LIBELLE,
--   SOURCE_CODE,
--   SOURCE_ID,
--   ROLE_ID,
--   IS_DEFAULT,
--   LDAP_FILTER,
--   ATTRIB_AUTO,
--   THESE_DEP,
--   HISTO_CREATEUR_ID,
--   HISTO_MODIFICATEUR_ID,
--   STRUCTURE_ID,
--   TYPE_STRUCTURE_DEPENDANT_ID)
-- select
--   ROLE_ID_SEQ.nextval,
--   'UR',
--   'Unité de recherche ' || nvl(s.sigle, '(aucun sigle trouvé)'),
--   'COMUE::UR_' || ur.SOURCE_CODE,
--   src.id,
--   'Unité de recherche ' || nvl(s.sigle, '(aucun sigle trouvé)'),
--   0,
--   null,
--   0,
--   0,
--   u.id,
--   u.id,
--   s.id,
--   ts.id
-- from UNITE_RECH ur
--   join STRUCTURE s on s.id = ur.STRUCTURE_ID
--   join TYPE_STRUCTURE ts on ts.id = s.TYPE_STRUCTURE_ID
--   join source src on src.CODE = 'SYGAL::sygal'
--   join UTILISATEUR u on u.USERNAME = 'sygal-app'
-- ;
