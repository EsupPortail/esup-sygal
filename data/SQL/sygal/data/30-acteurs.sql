

/**
 * Génération des requêtes à partir de DOCTPROD :
 */
/*
SELECT
  'insert into ACTEUR(' ||
  'ID, ' ||
  'ETABLISSEMENT_ID, ' ||
  'INDIVIDU_ID, ' ||
  'THESE_ID, ' ||
  'ROLE_ID, ' ||
  'ETABLISSEMENT, ' ||
  'QUALITE, ' ||
  'LIB_ROLE_COMPL, ' ||
  'SOURCE_CODE, ' ||
  'SOURCE_ID, ' ||
  'HISTO_CREATEUR_ID, ' ||
  'HISTO_CREATION, ' ||
  'HISTO_MODIFICATEUR_ID, ' ||
  'HISTO_MODIFICATION, ' ||
  'HISTO_DESTRUCTEUR_ID, ' ||
  'HISTO_DESTRUCTION) values (' ||
  a.ID ||
  ', (select id from etablissement where code = ''UCN'')' ||
  ', ' || a.INDIVIDU_ID ||
  ', ' || a.THESE_ID ||
  ', (select id from role where source_code = ''UCN::' || r.SOURCE_CODE || ''')' ||
  ', ''' || decode(a.ETABLISSEMENT,null,'NULL',a.ETABLISSEMENT) || '''' ||
  ', ''' || decode(a.QUALITE,null,'NULL',QUALITE) || '''' ||
  ', ''' || decode(a.LIB_ROLE_COMPL,null,'NULL',a.LIB_ROLE_COMPL) || '''' ||
  ', ''UCN::' || a.SOURCE_CODE || '''' ||
  ', ' || a.SOURCE_ID ||
  ', ' || a.HISTO_CREATEUR_ID ||
  ', ' || dmf(a.HISTO_CREATION) ||
  ', ' || a.HISTO_MODIFICATEUR_ID ||
  ', ' || dmf(a.HISTO_MODIFICATION) ||
  ', ' || decode(a.HISTO_DESTRUCTEUR_ID,null,'NULL',a.HISTO_DESTRUCTEUR_ID) ||
  ', ' || dmf(a.HISTO_DESTRUCTION) ||
  ');' sql
FROM ACTEUR a
  join USER_ROLE r on r.ID = a.ROLE_ID
;
*/


/**
 * 2/ Exécution des requêtes générées :
 */

-- trop de lignes!


/**
 * 3/ Avancement de la sequence.
 */

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from ACTEUR;
  loop
    select ACTEUR_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/
