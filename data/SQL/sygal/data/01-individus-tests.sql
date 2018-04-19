-- for tests
drop table INDIVIDU_XX ;
drop table DOCTORANT_XX ;
drop table DOCTORANT_COMPL_XX ;
drop sequence INDIVIDU_XX_id_seq;
drop sequence DOCTORANT_XX_id_seq;
drop sequence DOCTORANT_COMPL_XX_id_seq;
create table INDIVIDU_XX as select * from INDIVIDU where 1=0;
create table DOCTORANT_XX as select * from DOCTORANT where 1=0;
create table DOCTORANT_COMPL_XX as select * from DOCTORANT_COMPL where 1=0;
create sequence INDIVIDU_XX_id_seq;
create sequence DOCTORANT_XX_id_seq;
create sequence DOCTORANT_COMPL_XX_id_seq;
-- en réel, remplacer ci-dessous '_XX' par ''.




create table S_INDIVIDU as select * from sodoct.INDIVIDU@doctprod;
create table S_THESARD as select * from sodoct.THESARD@doctprod;
create table S_THESARD_COMPL as select * from sodoct.THESARD_COMPL@doctprod;



-- INDIVIDU ACTEUR

insert into INDIVIDU_XX(
  ID,
  TYPE,
  NOM_USUEL,
  NOM_PATRONYMIQUE,
  PRENOM1,
  PRENOM2,
  PRENOM3,
  EMAIL,
  NATIONALITE,
  SOURCE_ID,
  SOURCE_CODE,
  HISTO_CREATEUR_ID,
  HISTO_CREATION,
  HISTO_DESTRUCTEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_MODIFICATEUR_ID,
  HISTO_MODIFICATION
)
select
  ID, -- NB: même id que SODOCT
  'acteur' as TYPE,
  NOM_USUEL,
  null as NOM_PATRONYMIQUE,
  PRENOM as PRENOM1,
  null as PRENOM2,
  null as PRENOM3,
  EMAIL,
  null as NATIONALITE,
  (select id from source where code = 'UCN::apogee') as SOURCE_ID,
  'UCN::' || i.SOURCE_CODE as SOURCE_CODE,
  HISTO_CREATEUR_ID,
  HISTO_CREATION,
  HISTO_DESTRUCTEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_MODIFICATEUR_ID,
  HISTO_MODIFICATION
from oth.S_INDIVIDU i
/

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from INDIVIDU_XX;
  loop
    select INDIVIDU_XX_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/

-- INDIVIDU DOCTORANT

insert into INDIVIDU_XX(
  ID,
  TYPE,
  NOM_USUEL,
  NOM_PATRONYMIQUE,
  PRENOM1,
  PRENOM2,
  PRENOM3,
  EMAIL,
  NATIONALITE,
  SOURCE_ID,
  SOURCE_CODE,
  HISTO_CREATEUR_ID,
  HISTO_CREATION,
  HISTO_DESTRUCTEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_MODIFICATEUR_ID,
  HISTO_MODIFICATION
)
  select
    INDIVIDU_XX_ID_SEQ.nextval,
    'doctorant' as TYPE,
    NOM_USUEL,
    NOM_PATRONYMIQUE,
    nvl(PRENOM,'Aucun') as PRENOM1,
    null as PRENOM2,
    null as PRENOM3,
    EMAIL,
    NATIONALITE,
    (select id from source where code = 'UCN::apogee') as SOURCE_ID,
    'UCN::' || t.SOURCE_CODE as SOURCE_CODE,
    HISTO_CREATEUR_ID,
    HISTO_CREATION,
    HISTO_DESTRUCTEUR_ID,
    HISTO_DESTRUCTION,
    HISTO_MODIFICATEUR_ID,
    HISTO_MODIFICATION
  from oth.S_THESARD t
/

insert into DOCTORANT_XX(
  ID,
  ETABLISSEMENT_ID,
  INDIVIDU_ID,
  SOURCE_ID,
  SOURCE_CODE,
  HISTO_CREATEUR_ID,
  HISTO_CREATION,
  HISTO_DESTRUCTEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_MODIFICATEUR_ID,
  HISTO_MODIFICATION
)
  select
    ID, -- NB: même id que SODOCT
    (select id from ETABLISSEMENT where CODE = 'UCN'),
    (select id from INDIVIDU_XX i where i.SOURCE_CODE = ('UCN::' || t.SOURCE_CODE) and i.TYPE = 'doctorant'),
    (select id from source where code = 'UCN::apogee') as SOURCE_ID,
    'UCN::' || t.SOURCE_CODE as SOURCE_CODE,
    HISTO_CREATEUR_ID,
    HISTO_CREATION,
    HISTO_DESTRUCTEUR_ID,
    HISTO_DESTRUCTION,
    HISTO_MODIFICATEUR_ID,
    HISTO_MODIFICATION
  from oth.S_THESARD t
  --where (select id from INDIVIDU i where i.SOURCE_CODE = ('UCN::' || t.SOURCE_CODE) and i.TYPE = 'doctorant') is not null
/

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from DOCTORANT_XX;
  loop
    select DOCTORANT_XX_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/


-- INDIVIDU_RECH : vérifier que le trigger l'a remplie automatiquement.


-- DOCTORANT_COMPL

insert into DOCTORANT_COMPL_XX(
  ID,
  DOCTORANT_ID,
  PERSOPASS,
  EMAIL_PRO,
  HISTO_CREATION,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATION,
  HISTO_MODIFICATEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_DESTRUCTEUR_ID
)
select
  ID,
  THESARD_ID,
  PERSOPASS,
  EMAIL_PRO,
  HISTO_CREATION,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATION,
  HISTO_MODIFICATEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_DESTRUCTEUR_ID
from oth.S_THESARD_COMPL
/

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from DOCTORANT_COMPL_XX;
  loop
    select DOCTORANT_COMPL_XX_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/

/*
-- verif
select i.NOM_USUEL, i.SOURCE_CODE, d.SOURCE_CODE, persopass, email_pro
  from DOCTORANT_COMPL_XX dc
  join DOCTORANT_XX d on d.id = dc.DOCTORANT_ID
  join INDIVIDU_XX i on i.id = d.INDIVIDU_ID
;
*/
