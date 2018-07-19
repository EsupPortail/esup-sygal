
create table S_INDIVIDU as select * from sodoct.INDIVIDU@doctprod;
create table S_THESARD as select * from sodoct.THESARD@doctprod;
create table S_THESARD_COMPL as select * from sodoct.THESARD_COMPL@doctprod;


-- INDIVIDU ACTEUR

insert into INDIVIDU(
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
  from S_INDIVIDU i
/

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


-- INDIVIDU DOCTORANT

insert into INDIVIDU(
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
    INDIVIDU_ID_SEQ.nextval,
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
  from S_THESARD t
/

insert into DOCTORANT(
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
    (select e.id from ETABLISSEMENT e join structure s on s.id = e.STRUCTURE_ID where s.CODE = 'UCN'),
    (select id from INDIVIDU i where i.SOURCE_CODE = ('UCN::' || t.SOURCE_CODE) and i.TYPE = 'doctorant'),
    (select id from source where code = 'UCN::apogee') as SOURCE_ID,
    'UCN::' || t.SOURCE_CODE as SOURCE_CODE,
    HISTO_CREATEUR_ID,
    HISTO_CREATION,
    HISTO_DESTRUCTEUR_ID,
    HISTO_DESTRUCTION,
    HISTO_MODIFICATEUR_ID,
    HISTO_MODIFICATION
  from S_THESARD t
--where (select id from INDIVIDU i where i.SOURCE_CODE = ('UCN::' || t.SOURCE_CODE) and i.TYPE = 'doctorant') is not null
/

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


-- INDIVIDU_RECH : vérifier que le trigger l'a remplie automatiquement.
--
select count(*) from individu;
select count(*) from individu_rech;


-- DOCTORANT_COMPL

insert into DOCTORANT_COMPL(
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
  from S_THESARD_COMPL
/

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

-- verif
select i.NOM_USUEL, case when i.SOURCE_CODE = d.SOURCE_CODE then 'sources codes ok' else 'pb' end test, i.SOURCE_CODE, d.SOURCE_CODE, persopass, email_pro
  from DOCTORANT_COMPL dc
  join DOCTORANT d on d.id = dc.DOCTORANT_ID
  join INDIVIDU i on i.id = d.INDIVIDU_ID
;


--

drop table S_INDIVIDU ;
drop table S_THESARD ;
drop table S_THESARD_COMPL ;
