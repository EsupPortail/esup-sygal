
---------------------- UTILISATEUR ---------------------

INSERT INTO UTILISATEUR (ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD, STATE, LAST_ROLE_ID, INDIVIDU_ID) VALUES
  (1, 'sygal-app', 'ne_pas_repondre@normandie-univ.fr', 'Application SyGAL', 'ldap', 1, null, null);

insert into UTILISATEUR(
  ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD
)
SELECT
  ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD
from sodoct.utilisateur@doctprod st
where not exists ( select * from UTILISATEUR u where u.id = st.ID )
;


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
