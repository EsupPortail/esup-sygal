--
-- Base de données SYGAL.
--
-- Données.
--


---------------------- SOURCE ---------------------

INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (1, 'APOGEE-UCN',  'Apogée - Université de Caen Normandie', 1);
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (2, 'SYGAL',       'SYGAL',                                 0);


---------------------- ETABLISSEMENT ---------------------

INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (1, 'COMUE', 'Normandie Université');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (2, 'UCN',   'Université de Caen Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (3, 'URN',   'Université de Rouen Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (4, 'ULHN',  'Université Le Havre Normandie');
INSERT INTO ETABLISSEMENT (ID, CODE, LIBELLE) VALUES (5, 'INSA',  'INSA de Rouen');


---------------------- UTILISATEUR ---------------------

--truncate table UTILISATEUR;
INSERT INTO UTILISATEUR(ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD)
    with ds (USERNAME, EMAIL, DISPLAY_NAME) as (
      select 'sygal-app', 'contact.sygal@unicaen.fr',          'SYGAL' from dual union all
      select 'bernardb',  'bruno.bernard@unicaen.fr',          'BB'    from dual union all
      select 'gauthierb', 'bertrand.gauthier@unicaen.fr',      'BG'    from dual union all
      select 'metivier',  'jean-philippe.metivier@unicaen.fr', 'JPM'   from dual
    )
  select UTILISATEUR_ID_SEQ.nextval, ds.USERNAME,  ds.EMAIL, ds.DISPLAY_NAME, 'ldap' from ds;


--------------------------- ROLE -----------------------

--delete from ROLE;
INSERT INTO ROLE(ID, ETABLISSEMENT_ID, LIBELLE_LONG, LIBELLE_COURT, SOURCE_CODE, SOURCE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
  with ds (LIBELLE_LONG, LIBELLE_COURT) as (
    SELECT 'Administrateur technique',   'Admin tech' from dual union all
    SELECT 'Administrateur',             'Admin'      from dual union all
    SELECT 'Bureau des doctorats',       'BdD'        from dual union all
    SELECT 'Bibliothèque universitaire', 'BU'         from dual
  )
    SELECT ROLE_ID_SEQ.nextval, etab.id, ds.LIBELLE_LONG, ds.LIBELLE_COURT, ds.LIBELLE_LONG, src.id, u.id, u.id
    FROM ds
      join ETABLISSEMENT etab on etab.CODE <> 'COMUE'
      join SOURCE src on src.CODE = 'SYGAL'
      join UTILISATEUR u on u.USERNAME = 'sygal-app'
;
