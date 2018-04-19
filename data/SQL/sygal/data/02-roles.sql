
/**
 * 1/ Import des rôles importables.
 */

--> À faire via l'IHM.


/**
 * 2/ Création des rôles non importables.
 */

-- ROLE mutli établissement

INSERT INTO ROLE (
  ID,
  STRUCTURE_ID,
  TYPE_STRUCTURE_DEPENDANT_ID,
  LIBELLE,
  CODE,
  ROLE_ID,
  THESE_DEP,
  SOURCE_CODE,
  SOURCE_ID,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID)
with ds (LIBELLE, CODE, THESE_DEP) as (
  SELECT 'Administrateur technique',   'ADMIN_TECH' , 0 from dual union all
  SELECT 'Observateur',                'OBSERV'     , 0 from dual
)
SELECT
  ROLE_ID_SEQ.nextval,
  null as STRUCTURE_ID,
  null as TYPE_STRUCTURE_DEPENDANT_ID,
  ds.LIBELLE,
  ds.CODE,
  ds.LIBELLE,
  ds.THESE_DEP,
  'UCN::' || ds.CODE,
  src.id,
  u.id,
  u.id
FROM ds
join SOURCE src on src.CODE = 'COMUE::SYGAL'
join UTILISATEUR u on u.USERNAME = 'sygal-app';


-- ROLE mono établissement

INSERT INTO ROLE (
  --ID,
  STRUCTURE_ID,
  TYPE_STRUCTURE_DEPENDANT_ID,
  LIBELLE,
  CODE,
  ROLE_ID,
  THESE_DEP,
  SOURCE_CODE,
  SOURCE_ID,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID)
  with ds (LIBELLE, CODE, THESE_DEP) as (
    SELECT 'Administrateur',             'ADMIN'      , 0 from dual union all
    SELECT 'Bureau des doctorats',       'BDD'        , 0 from dual union all
    SELECT 'Bibliothèque universitaire', 'BU'         , 0 from dual union all
    SELECT 'Doctorant',                  'DOCTORANT'  , 1 from dual
  )
  SELECT
    --ROLE_ID_SEQ.nextval,
    s.ID,
    s.TYPE_STRUCTURE_ID,
    ds.LIBELLE,
    ds.CODE,
    ds.LIBELLE||' '||etab.CODE,
    ds.THESE_DEP,
    etab.CODE||'::'||ds.CODE,
    src.id,
    u.id,
    u.id
  FROM ds
    join ETABLISSEMENT etab on etab.DOMAINE is not null -- i.e. établissements COMUE
    join STRUCTURE s on s.id = etab.STRUCTURE_ID
    join SOURCE src on src.CODE = 'COMUE::SYGAL'
    join UTILISATEUR u on u.USERNAME = 'sygal-app'
;

update role set ATTRIB_AUTO = 1, THESE_DEP = 1, TYPE_STRUCTURE_DEPENDANT_ID = 0
where code in (
  'A',
  'B',
  'C',
  'D',
  'K',
  'M',
  'P',
  'R',
  'DOCTORANT'
);


-- ROLE ED

insert into role(
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
  TYPE_STRUCTURE_DEPENDANT_ID)
select
  ROLE_ID_SEQ.nextval,
  'ED',
  'École doctorale ' || nvl(s.sigle, '(aucun sigle trouvé)'),
  'COMUE::ED_' || ed.SOURCE_CODE,
  src.id,
  'École doctorale ' || nvl(s.sigle, '(aucun sigle trouvé)'),
  0,
  u.id,
  u.id,
  s.id,
  ts.id
from ECOLE_DOCT ed
  join STRUCTURE s on s.id = ed.STRUCTURE_ID
  join TYPE_STRUCTURE ts on ts.id = s.TYPE_STRUCTURE_ID
  join source src on src.CODE = 'COMUE::SYGAL'
  join UTILISATEUR u on u.USERNAME = 'sygal-app'
;


-- ROLE UR

insert into role(
  ID,
  CODE,
  LIBELLE,
  SOURCE_CODE,
  SOURCE_ID,
  ROLE_ID,
  IS_DEFAULT,
  LDAP_FILTER,
  ATTRIB_AUTO,
  THESE_DEP,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID,
  STRUCTURE_ID,
  TYPE_STRUCTURE_DEPENDANT_ID)
select
  ROLE_ID_SEQ.nextval,
  'UR',
  'Unité de recherche ' || nvl(s.sigle, '(aucun sigle trouvé)'),
  'COMUE::UR_' || ur.SOURCE_CODE,
  src.id,
  'Unité de recherche ' || nvl(s.sigle, '(aucun sigle trouvé)'),
  0,
  null,
  0,
  0,
  u.id,
  u.id,
  s.id,
  ts.id
from UNITE_RECH ur
  join STRUCTURE s on s.id = ur.STRUCTURE_ID
  join TYPE_STRUCTURE ts on ts.id = s.TYPE_STRUCTURE_ID
  join source src on src.CODE = 'COMUE::SYGAL'
  join UTILISATEUR u on u.USERNAME = 'sygal-app'
;


-- INDIVIDU_ROLE

insert into INDIVIDU_ROLE (
  --ID,
  INDIVIDU_ID,
  ROLE_ID
)
  with ds(email, code_role) as (
    select 'bertrand.gauthier@unicaen.fr',      'ADMIN_TECH' from dual union all
    select 'jean-philippe.metivier@unicaen.fr', 'ADMIN_TECH' from dual union all
    select 'bruno.bernard@unicaen.fr',          'ADMIN_TECH' from dual
  )
  select
    --INDIVIDU_ROLE_ID_SEQ.nextval,
    i.id,
    r.id
  from ds
    join INDIVIDU i on i.EMAIL = ds.email
    join ROLE r on r.CODE = ds.code_role
;


-- ROLE_MODELE

INSERT INTO ROLE_MODELE (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE) VALUES (1, 'Unité de recherche', 'UR', 3);
INSERT INTO ROLE_MODELE (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE) VALUES (2, 'École doctorale', 'ED', 2);
INSERT INTO ROLE_MODELE (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE) VALUES (3, 'Administrateur', 'ADMIN', 1);
INSERT INTO ROLE_MODELE (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE) VALUES (4, 'Bureau des doctorats', 'BDD', 1);
INSERT INTO ROLE_MODELE (ID, LIBELLE, ROLE_ID, STRUCTURE_TYPE) VALUES (5, 'Bibliothèque universitaire', 'BU', 1);
