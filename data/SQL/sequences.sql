--
-- Avance d'une sequence.
--
declare
  maxid integer;
  seqnextval integer;
begin
  select max(id) into maxid from individu;
  LOOP
    select INDIVIDU_ID_SEQ.nextval into seqnextval from dual;
    EXIT WHEN seqnextval >= maxid;
  END LOOP;
end;