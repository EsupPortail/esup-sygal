
create table S_VALIDATION as select * from sodoct.VALIDATION@doctprod;



-- TYPE_VALIDATION

INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (1, 'RDV_BU', 'Validation suite au rendez-vous avec le doctorant');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (2, 'DEPOT_THESE_CORRIGEE', 'Validation automatique du dépôt de la thèse corrigée');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (3, 'CORRECTION_THESE', 'Validation par le(s) directeur(s) de thèse des corrections de la thèse');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (4, 'VERSION_PAPIER_CORRIGEE', 'Confirmation dépot de la version papier corrigée');

insert into TYPE_VALIDATION(ID, CODE, LIBELLE) values
  (5, 'PAGE_DE_COUVERTURE', 'Validation de la page de couverture');


-- VALIDATION

insert into VALIDATION (
  ID,
  TYPE_VALIDATION_ID,
  THESE_ID,
  INDIVIDU_ID,
  HISTO_CREATION,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATION,
  HISTO_MODIFICATEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_DESTRUCTEUR_ID
)
select
  ID,
  TYPE_VALIDATION_ID,
  THESE_ID,
  INDIVIDU_ID,
  HISTO_CREATION,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATION,
  HISTO_MODIFICATEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_DESTRUCTEUR_ID
from S_VALIDATION
;

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from VALIDATION;
  loop
    select VALIDATION_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/



insert into validation(
  ID,
  TYPE_VALIDATION_ID,
  THESE_ID,
  INDIVIDU_ID,
  HISTO_CREATION,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATION,
  HISTO_MODIFICATEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_DESTRUCTEUR_ID)
  select
    VALIDATION_ID_SEQ.nextval,
    5,
    THESE_ID,
    null,
    HISTO_CREATION,
    HISTO_CREATEUR_ID,
    HISTO_MODIFICATION,
    HISTO_MODIFICATEUR_ID,
    HISTO_DESTRUCTION,
    HISTO_DESTRUCTEUR_ID
  from S_RDV_BU
  where PAGE_TITRE_CONFORME = 1
;

drop table S_RDV_BU ;


--

drop table S_VALIDATION;
