
---------------------- UTILISATEUR ---------------------

insert into UTILISATEUR(
  ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD
)
SELECT
  ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD
from sodoct.utilisateur@doctprod st
where not exists ( select * from UTILISATEUR u where u.id = st.ID )
;

update UTILISATEUR set id = 1, username = 'sygal-app', DISPLAY_NAME = 'Application SYGAL' where username = 'sodoct-app';


DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from UTILISATEUR;
  loop
    select UTILISATEUR_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/
