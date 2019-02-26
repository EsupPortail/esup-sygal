--
-- Avec ou sans COMUE ?
--

--
-- Pour faire simple... si le logo de votre COMUE doit figurer sur la page de couverture des thèses
-- (page générée par l'appli), alors vous devez lancer les 2 inserts ci-dessous.
--

INSERT INTO STRUCTURE (ID, SOURCE_CODE, SIGLE, LIBELLE, TYPE_STRUCTURE_ID, SOURCE_ID, CODE, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
select STRUCTURE_ID_SEQ.nextval, 'COMUE', 'NU', 'Normandie Université', null, 1, 'COMUE', 1, 1 from dual;
INSERT INTO ETABLISSEMENT (ID, STRUCTURE_ID, DOMAINE, SOURCE_ID, SOURCE_CODE, EST_COMUE, EST_MEMBRE, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
select ETABLISSEMENT_ID_SEQ.nextval, s.ID, 'normandie-univ.fr', 1, 'COMUE', 1, 0, 1, 1 from STRUCTURE s where s.SOURCE_CODE = 'COMUE';
