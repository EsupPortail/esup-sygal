
create table S_VALIDITE_FICHIER as select * from sodoct.VALIDITE_FICHIER@doctprod;


create table S_FICHIER as
  select
    ID,
    NOM,
    TYPE_MIME,
    TAILLE,
    -- CONTENU, sans le contenu!
    DESCRIPTION,
    THESE_ID,
    VERSION_FICHIER_ID,
    HISTO_CREATION,
    HISTO_CREATEUR_ID,
    HISTO_MODIFICATION,
    HISTO_MODIFICATEUR_ID,
    HISTO_DESTRUCTION,
    HISTO_DESTRUCTEUR_ID,
    EST_ANNEXE,
    NOM_ORIGINAL,
    EST_CONVERTI,
    EST_EXPURGE,
    EST_CONFORME,
    RETRAITEMENT,
    NATURE_ID
  from sodoct.FICHIER@doctprod;



-- NATURE_FICHIER

INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (1, 'THESE_PDF', 'Thèse au format PDF');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (2, 'FICHIER_NON_PDF', 'Fichier non PDF');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (3, 'PV_SOUTENANCE', 'PV de soutenance');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (4, 'RAPPORT_SOUTENANCE', 'Rapport de soutenance');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (5, 'DEMANDE_CONFIDENT', 'Demande de confidentialité');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (6, 'PROLONG_CONFIDENT', 'Demande de prolongation de confidentialité');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (7, 'PRE_RAPPORT_SOUTENANCE', 'Pré-rapport de soutenance');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (8, 'CONV_MISE_EN_LIGNE', 'Convention de mise en ligne');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (9, 'AVENANT_CONV_MISE_EN_LIGNE', 'Avenant à la convention de mise en ligne');


-- VERSION_FICHIER

INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (1, 'VA', 'Version d''archivage');
INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (2, 'VD', 'Version de diffusion');
INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (3, 'VO', 'Version originale');
INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (4, 'VAC', 'Version d''archivage corrigée');
INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (5, 'VDC', 'Version de diffusion corrigée');
INSERT INTO VERSION_FICHIER (ID, CODE, LIBELLE) VALUES (6, 'VOC', 'Version originale corrigée');


-- FICHIER

insert into FICHIER (
  ID,
  DESCRIPTION,
  EST_ANNEXE,
  EST_CONFORME,
  EST_EXPURGE,
  EST_PARTIEL,
  HISTO_CREATEUR_ID,
  HISTO_CREATION,
  HISTO_DESTRUCTEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_MODIFICATEUR_ID,
  HISTO_MODIFICATION,
  NATURE_ID,
  NOM,
  NOM_ORIGINAL,
  RETRAITEMENT,
  TAILLE,
  THESE_ID,
  TYPE_MIME,
  VERSION_FICHIER_ID
)
  select
    ID,
    DESCRIPTION,
    EST_ANNEXE,
    EST_CONFORME,
    EST_EXPURGE,
    0 as EST_PARTIEL,
    HISTO_CREATEUR_ID,
    HISTO_CREATION,
    HISTO_DESTRUCTEUR_ID,
    HISTO_DESTRUCTION,
    HISTO_MODIFICATEUR_ID,
    HISTO_MODIFICATION,
    NATURE_ID,
    NOM,
    NOM_ORIGINAL,
    RETRAITEMENT,
    TAILLE,
    THESE_ID,
    TYPE_MIME,
    VERSION_FICHIER_ID
  from S_FICHIER
;


-- VALIDITE_FICHIER

insert into VALIDITE_FICHIER (
  ID,
  EST_VALIDE,
  FICHIER_ID,
  HISTO_CREATEUR_ID,
  HISTO_CREATION,
  HISTO_DESTRUCTEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_MODIFICATEUR_ID,
  HISTO_MODIFICATION,
  LOG,
  MESSAGE
)
  select
    ID,
    EST_VALIDE,
    FICHIER_ID,
    HISTO_CREATEUR_ID,
    HISTO_CREATION,
    HISTO_DESTRUCTEUR_ID,
    HISTO_DESTRUCTION,
    HISTO_MODIFICATEUR_ID,
    HISTO_MODIFICATION,
    LOG,
    MESSAGE
  from s_VALIDITE_FICHIER
;


DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from VALIDITE_FICHIER;
  loop
    select VALIDITE_FICHIER_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/



--
-- Une fois connecté à Sodoct, lancer 'check-nommage-fichier' (barre ZendDevTools) pour vérifier le nommage des fichiers en bdd.
-- Lancer si besoin 'update-nommage-fichiers' pour renommer les manquants.
-- Lancer 'create-files-from-contenu-fichiers' pour récupérer les CONTENU_FICHIER sous forme de fichiers sur disque.
-- Copier les fichiers sur le serveur d'appli.
--
