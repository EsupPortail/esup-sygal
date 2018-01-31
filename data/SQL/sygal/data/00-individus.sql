
--delete from individu;

------------------------- INDIVIDU ACTEUR -----------------------------

/*
-- génération des requêtes à partir de DOCTPROD :
SELECT 'INSERT INTO INDIVIDU (' ||
  'ID, ' ||
  'TYPE, ' ||
  'CIVILITE, ' ||
  'NOM_USUEL, ' ||
  'NOM_PATRONYMIQUE, ' ||
  'PRENOM1, ' ||
  'PRENOM2, ' ||
  'PRENOM3, ' ||
  'EMAIL, ' ||
  'DATE_NAISSANCE, ' ||
  'NATIONALITE, ' ||
  'SOURCE_CODE, ' ||
  'SOURCE_ID, ' ||
  'HISTO_CREATEUR_ID, ' ||
  'HISTO_CREATION, ' ||
  'HISTO_MODIFICATEUR_ID, ' ||
  'HISTO_MODIFICATION, ' ||
  'HISTO_DESTRUCTEUR_ID, ' ||
  'HISTO_DESTRUCTION) values (' ||
       ID ||
       ', ''acteur''' ||
       ', ''' || CIVILITE || '''' ||
       ', ''' || replace(NOM_USUEL,'''','''''') || '''' ||
       ', ''' || replace(NOM_USUEL,'''','''''') || '''' ||
       ', ''' || replace(PRENOM,'''','''''') || '''' ||
       ', null' ||
       ', null' ||
       ', ''' || EMAIL || '''' ||
       ', null' ||
       ', null' ||
       ', ''UCN::' || SOURCE_CODE || '''' ||
       ', (select id from source where code = ''UCN::apogee'')' ||
       ', ' || HISTO_CREATEUR_ID ||
       ', ' || dmf(HISTO_CREATION) ||
       ', ' || HISTO_MODIFICATEUR_ID ||
       ', ' || dmf(HISTO_MODIFICATION) ||
       ', ' || decode(HISTO_DESTRUCTEUR_ID,null,'NULL',HISTO_DESTRUCTEUR_ID) ||
       ', ' || dmf(HISTO_DESTRUCTION) ||
       ');'
FROM INDIVIDU i;
*/

--
-- ..............exécution des requêtes générées...
--
--                Cf. 00-individus-acteurs.sql
--

-- avancement de la sequence INDIVIDU_ID_SEQ
DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from INDIVIDU;
  loop
    select INDIVIDU_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/

------------------------- INDIVIDU DOCTORANT -----------------------------

/*
-- génération des requêtes à partir de DOCTPROD :
SELECT 'INSERT INTO INDIVIDU (' ||
       'ID, ' ||
       'TYPE, ' ||
       'CIVILITE, ' ||
       'NOM_USUEL, ' ||
       'NOM_PATRONYMIQUE, ' ||
       'PRENOM1, ' ||
       'PRENOM2, ' ||
       'PRENOM3, ' ||
       'EMAIL, ' ||
       'DATE_NAISSANCE, ' ||
       'NATIONALITE, ' ||
       'SOURCE_CODE, ' ||
       'SOURCE_ID, ' ||
       'HISTO_CREATEUR_ID, ' ||
       'HISTO_CREATION, ' ||
       'HISTO_MODIFICATEUR_ID, ' ||
       'HISTO_MODIFICATION, ' ||
       'HISTO_DESTRUCTEUR_ID, ' ||
       'HISTO_DESTRUCTION) values (' ||
       'INDIVIDU_ID_SEQ.nextval' ||
       ', ''doctorant''' ||
       ', ''' || CIVILITE || '''' ||
       ', ''' || replace(NOM_USUEL,'''','''''') || '''' ||
       ', ''' || replace(NOM_PATRONYMIQUE,'''','''''') || '''' ||
       ', ''' || replace(nvl(PRENOM,'Aucun'),'''','''''') || '''' ||
       ', null' ||
       ', null' ||
       ', ''' || EMAIL || '''' ||
       ', ' || dmf(DATE_NAISSANCE) ||
       ', ''' || NATIONALITE || '''' ||
       ', ''UCN::' || SOURCE_CODE || '''' ||
       ', (select id from source where code = ''UCN::apogee'')' ||
       ', ' || HISTO_CREATEUR_ID ||
       ', ' || dmf(HISTO_CREATION) ||
       ', ' || HISTO_MODIFICATEUR_ID ||
       ', ' || dmf(HISTO_MODIFICATION) ||
       ', ' || decode(HISTO_DESTRUCTEUR_ID,null,'NULL',HISTO_DESTRUCTEUR_ID) ||
       ', ' || dmf(HISTO_DESTRUCTION) ||
       ');
       INSERT INTO DOCTORANT(' ||
          'ID, ' ||
          'ETABLISSEMENT_ID, ' ||
          'INDIVIDU_ID, ' ||
          'SOURCE_CODE, ' ||
          'SOURCE_ID, ' ||
          'HISTO_CREATEUR_ID, ' ||
          'HISTO_CREATION, ' ||
          'HISTO_MODIFICATEUR_ID, ' ||
          'HISTO_MODIFICATION, ' ||
          'HISTO_DESTRUCTEUR_ID, ' ||
          'HISTO_DESTRUCTION) values (' ||
        ID ||
        ', (select id from ETABLISSEMENT where CODE = ''UCN'')' ||
        ', INDIVIDU_ID_SEQ.currval' ||
        ', ''UCN::' || SOURCE_CODE || '''' ||
        ', (select id from source where code = ''UCN::apogee'')' ||
        ', ' || HISTO_CREATEUR_ID ||
        ', ' || dmf(HISTO_CREATION) ||
        ', ' || HISTO_MODIFICATEUR_ID ||
        ', ' || dmf(HISTO_MODIFICATION) ||
        ', ' || decode(HISTO_DESTRUCTEUR_ID,null,'NULL',HISTO_DESTRUCTEUR_ID) ||
        ', ' || dmf(HISTO_DESTRUCTION) ||
        ');'
FROM THESARD d;
*/

--
-- .............exécution des requêtes générées...
--
--              cf. 00-individus-doctorants.sql
--

-- avancement de la sequence DOCTORANT_ID_SEQ
DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from DOCTORANT;
  loop
    select DOCTORANT_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/

------------------------- DOCTORANT_COMPL -----------------------------

/*
-- génération des requêtes à partir de DOCTPROD :
SELECT
'INSERT INTO DOCTORANT_COMPL(' ||
  'ID, ' ||
  'DOCTORANT_ID, ' ||
  'PERSOPASS, ' ||
  'EMAIL_PRO, ' ||
  'HISTO_CREATEUR_ID, ' ||
  'HISTO_CREATION, ' ||
  'HISTO_MODIFICATEUR_ID, ' ||
  'HISTO_MODIFICATION, ' ||
  'HISTO_DESTRUCTEUR_ID, ' ||
  'HISTO_DESTRUCTION) values (' ||
dc.ID ||
', ' || dc.THESARD_ID ||
', ''' || dc.PERSOPASS || '''' ||
', ''' || dc.EMAIL_PRO || '''' ||
', ' || dc.HISTO_CREATEUR_ID ||
', ' || dmf(dc.HISTO_CREATION) ||
', ' || dc.HISTO_MODIFICATEUR_ID ||
', ' || dmf(dc.HISTO_MODIFICATION) ||
', ' || decode(dc.HISTO_DESTRUCTEUR_ID,null,'NULL',dc.HISTO_DESTRUCTEUR_ID) ||
', ' || dmf(dc.HISTO_DESTRUCTION) ||
');'
FROM THESARD d
join THESARD_COMPL dc on dc.THESARD_ID = d.id
;
 */

--
-- .............exécution des requêtes générées...
--
--              cf. 00-individus-doctorants-compl.sql
--

-- avancement de la sequence DOCTORANT_COMPL_ID_SEQ
DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from DOCTORANT_COMPL;
  loop
    select DOCTORANT_COMPL_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/




/*
--delete from INDIVIDU;
insert into INDIVIDU (
  ID,
  TYPE,
  CIVILITE,
  NOM_USUEL,
  NOM_PATRONYMIQUE,
  PRENOM1,
  EMAIL,
  DATE_NAISSANCE,
  NATIONALITE,
  SOURCE_CODE,
  SOURCE_ID,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID
)
with ds(TYPE, CIVILITE, NOM_USUEL, PRENOM1, EMAIL, NATIONALITE, SOURCE_CODE) as (
  select 'autre', 'M.', 'Gauthier', 'Bertrand',      'bertrand.gauthier@unicaen.fr',      'Français(e)', '?' from dual union all
  select 'autre', 'M.', 'Métivier', 'Jean-Philippe', 'jean-philippe.metivier@unicaen.fr', 'Français(e)', '#' from dual union all
  select 'autre', 'M.', 'Bernard',  'Bruno',         'bruno.bernard@unicaen.fr',          'Français(e)', '@' from dual
)
select
  INDIVIDU_ID_SEQ.nextval,
  ds.TYPE,
  ds.CIVILITE,
  ds.NOM_USUEL,
  ds.NOM_USUEL,
  ds.PRENOM1,
  ds.EMAIL,
  sysdate,
  ds.NATIONALITE,
  ds.SOURCE_CODE,
  s.id,
  u.id,
  u.id
from ds
  join OTH.SOURCE s on s.CODE = 'SYGAL'
  join OTH.UTILISATEUR u on u.username = 'sygal-app'
;
*/
