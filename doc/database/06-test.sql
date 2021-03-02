--
-- Données de test.
--
--


--
-- Création de l'individu/utilisateur de test
--
INSERT INTO INDIVIDU (ID, CIVILITE, NOM_USUEL, NOM_PATRONYMIQUE, PRENOM1, EMAIL, SOURCE_CODE, SUPANN_ID, SOURCE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID)
select INDIVIDU_ID_SEQ.nextval,
       'M.',
       'Premier',
       'Premier',
       'François',
       'francois.premier@univ.fr',
       'INCONNU::00012345',
       '00012345',
       1, 1, 1
from dual
/
INSERT INTO UTILISATEUR (ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD, INDIVIDU_ID)
select UTILISATEUR_ID_SEQ.nextval,
       'premierf@univ.fr', -- EPPN (si shibboleth activé) ou supannAliasLogin (si LDAP activé)
       'francois.premier@univ.fr',
       'François PREMIER',
       'shib', -- 'shib' (auth shibboleth), ou 'ldap' (auth LDAP), ou mdp bcrypté (auth locale)
       i.ID
from INDIVIDU i
where i.SOURCE_CODE = 'INCONNU::00012345'
/

--
-- /!\ Attribution du rôle Admin tech à l'utilisateur de test !!
--
INSERT INTO INDIVIDU_ROLE(ID, INDIVIDU_ID, ROLE_ID)
select INDIVIDU_ROLE_ID_SEQ.nextval, i.ID, r.ID from INDIVIDU i, ROLE r
where i.SOURCE_CODE = 'INCONNU::00012345'
  and r.SOURCE_CODE = 'ADMIN_TECH'
/
