
-- SOURCE

-- INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (, 'UCN:apogee-sodoct', 'Ex Apogée Sodoct (à supprimer après reprise des données)', 0);
-- INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (, 'UCN:autre', 'Autre source UCN', 1);
-- INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (, 'fOO::apogee', 'Apogée fOO', 0);
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (1, 'COMUE::SYGAL', 'SyGAL', 0);
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (2, 'INSA::physalis', 'Physalis de l''INSA de Rouen', 1);
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (3, 'UCN::apogee', 'Apogée UCN', 1);
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (4, 'ULHN::apogee', 'Apogée Université de Le Havre Normandie ', 1);
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (5, 'URN::apogee', 'Apogée Université de Rouen Normandie', 1);


-- FAQ

INSERT INTO FAQ (ID, QUESTION, REPONSE, ORDRE) VALUES (1, 'Qu''est-ce qu''une question ?', 'C''est une phrase appelant une réponse.', 10);

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from FAQ;
  loop
    select FAQ_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/
