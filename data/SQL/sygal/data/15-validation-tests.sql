-- for tests
drop table      VALIDATION_XX ;
drop sequence   VALIDATION_XX_id_seq;
create table    VALIDATION_XX as select * from VALIDATION where 1=0;
create sequence VALIDATION_XX_id_seq;
-- en réel, remplacer ci-dessous '_XX' par ''.


create table S_VALIDATION as select * from sodoct.VALIDATION@doctprod;



-- TYPE_VALIDATION

INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (1, 'RDV_BU', 'Validation suite au rendez-vous avec le doctorant');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (2, 'DEPOT_THESE_CORRIGEE', 'Validation automatique du dépôt de la thèse corrigée');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (3, 'CORRECTION_THESE', 'Validation par le(s) directeur(s) de thèse des corrections de la thèse');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (4, 'VERSION_PAPIER_CORRIGEE', 'Confirmation dépot de la version papier corrigée');


-- VALIDATION
 
insert into VALIDATION_XX (
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
  select max(id) into maxid from VALIDATION_XX;
  loop
    select VALIDATION_XX_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/
