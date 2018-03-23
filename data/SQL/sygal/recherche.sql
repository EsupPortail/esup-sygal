-------------------------------------------
-- Table, trigger et fonction de recherche --
-------------------------------------------


-- table de recherche
create table INDIVIDU_RECH (
  ID NUMBER not null constraint INDIVIDU_RECH_PK primary key,
  HAYSTACK CLOB
);
-- contenu initial
insert into INDIVIDU_RECH(ID, HAYSTACK)
  select id, individu_haystack(NOM_USUEL, NOM_PATRONYMIQUE, PRENOM1, EMAIL, SOURCE_CODE)
  from INDIVIDU;
-- ou màj contenu
update INDIVIDU_RECH ir set HAYSTACK = (
  SELECT individu_haystack(NOM_USUEL, NOM_PATRONYMIQUE, PRENOM1, EMAIL, SOURCE_CODE)
  from individu i
  where i.id = ir.id
);

-- fonction haystack
create or replace function individu_haystack(
  NOM_USUEL varchar2,
  NOM_PATRONYMIQUE varchar2,
  PRENOM1 varchar2,
  EMAIL varchar2,
  SOURCE_CODE varchar2) RETURN VARCHAR2
AS
  BEGIN
    return trim(UNICAEN_ORACLE.str_reduce(
      NOM_USUEL || ' ' || PRENOM1 || ' ' || NOM_PATRONYMIQUE || ' ' || PRENOM1 || ' ' ||
      PRENOM1 || ' ' || NOM_USUEL || ' ' || PRENOM1 || ' ' || NOM_PATRONYMIQUE || ' ' ||
      EMAIL || ' ' ||
      SOURCE_CODE
    ));
  END;


-- trigger de màj de la table de recherche
--drop trigger INDIVIDU_RECH_UPDATE;
CREATE TRIGGER INDIVIDU_RECH_UPDATE
  AFTER DELETE OR INSERT OR UPDATE OF NOM_USUEL, NOM_PATRONYMIQUE, PRENOM1, PRENOM2, PRENOM3, SOURCE_CODE ON INDIVIDU
  FOR EACH ROW
  DECLARE
    v_haystack CLOB := individu_haystack(:new.NOM_USUEL, :new.NOM_PATRONYMIQUE, :new.PRENOM1, :new.EMAIL, :new.SOURCE_CODE);
  BEGIN
    IF INSERTING THEN
      insert into INDIVIDU_RECH(ID, HAYSTACK) values (:new.ID, v_haystack);
    END IF;
    IF UPDATING THEN
      UPDATE INDIVIDU_RECH SET HAYSTACK = v_haystack where ID = :new.ID;
    END IF;
    IF DELETING THEN
      delete from INDIVIDU_RECH where id = :old.ID;
    END IF;
  END;


-- test
update INDIVIDU set source_code = source_code||' x' where id = 102443;
-- verif
select * from INDIVIDU where  id = 102443;
select * from INDIVIDU_RECH where  id = 102443;
