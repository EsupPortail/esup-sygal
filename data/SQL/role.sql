--
-- Nouveau rôle.
--
insert into role (
  ID,
  CODE,
  LIBELLE,
  SOURCE_CODE,
  SOURCE_ID,
  ROLE_ID,
  STRUCTURE_ID,
  TYPE_STRUCTURE_DEPENDANT_ID,
  THESE_DEP,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID
) SELECT
  ROLE_ID_SEQ.nextval,
  'OBSERV',
  'Observateur',
  'OBSERV',
  src.id,
  'Observateur COMUE',
  null,
  null,
  0,
  u.id,
  u.id
  from source src, UTILISATEUR u
  where
    src.CODE = 'SYGAL::sygal' and
    u.USERNAME = 'sygal-app'
;
