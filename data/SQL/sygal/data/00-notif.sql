
-- IMPORT_NOTIF

INSERT INTO IMPORT_NOTIF (ID, TABLE_NAME, COLUMN_NAME, OPERATION, TO_VALUE, DESCRIPTION, URL) VALUES (1, 'THESE', 'RESULTAT', 'UPDATE', '1', 'Notification lorsque le résultat de la thèse passe à 1 (admis)', 'https://test.unicaen.fr/sodoct/import/receive-notification');


DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from IMPORT_NOTIF;
  loop
    select IMPORT_NOTIF_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/


-- IMPORT_OBSERV

INSERT INTO IMPORT_OBSERV (ID, CODE, TABLE_NAME, COLUMN_NAME, OPERATION, TO_VALUE, DESCRIPTION, ENABLED) VALUES (6, 'RESULTAT_PASSE_A_ADMIS', 'THESE', 'RESULTAT', 'UPDATE', '1', 'Le résultat de la thèse passe à 1 (admis)', 1);
INSERT INTO IMPORT_OBSERV (ID, CODE, TABLE_NAME, COLUMN_NAME, OPERATION, TO_VALUE, DESCRIPTION, ENABLED) VALUES (7, 'CORRECTION_PASSE_A_MINEURE', 'THESE', 'CORREC_AUTORISEE', 'UPDATE', 'mineure', 'Correction attendue passe à mineure', 1);
INSERT INTO IMPORT_OBSERV (ID, CODE, TABLE_NAME, COLUMN_NAME, OPERATION, TO_VALUE, DESCRIPTION, ENABLED) VALUES (8, 'CORRECTION_PASSE_A_MAJEURE', 'THESE', 'CORREC_AUTORISEE', 'UPDATE', 'majeure', 'Correction attendue passe à majeure', 1);

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from IMPORT_OBSERV;
  loop
    select IMPORT_OBSERV_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/


-- IMPORT_OBSERV_RESULT

INSERT INTO IMPORT_OBSERV_RESULT (ID, IMPORT_OBSERV_ID, DATE_CREATION, SOURCE_CODE, RESULTAT, DATE_NOTIF)
  select ID, IMPORT_OBSERV_ID, DATE_CREATION, SOURCE_CODE, RESULTAT, DATE_NOTIF
  from sodoct.IMPORT_OBSERV_RESULT@doctprod;

update IMPORT_OBSERV_RESULT set SOURCE_CODE = 'UCN::'||SOURCE_CODE;

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from IMPORT_OBSERV_RESULT;
  loop
    select IMPORT_OBSERV_RESULT_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/


-- IMPORT_OBS_NOTIF : vide


-- IMPORT_OBS_RESULT_NOTIF : vide


-- NOTIF : vide


-- NOTIF_RESULT : vide