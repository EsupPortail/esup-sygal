--
-- DONNÉES DE TEST.
--

--
-- Création de l'individu/utilisateur de test
--
insert into individu (id, nom_usuel, nom_patronymique, prenom1, email, source_code, supann_id, source_id, histo_createur_id)
select nextval('individu_id_seq'),
       $${TEST_USER_NOM_PATRONYMIQUE}$$,
       $${TEST_USER_NOM_PATRONYMIQUE}$$,
       $${TEST_USER_PRENOM}$$,
       '{TEST_USER_EMAIL}',
       '{ETAB_CODE}::00012345',
       '00012345',
       1,
       1
;
insert into utilisateur (id, individu_id, username, email, display_name, password, password_reset_token, nom, prenom)
select nextval('utilisateur_id_seq'),
       i.id,
       '{TEST_USER_EMAIL}', -- EPPN (si shibboleth), ou supannAliasLogin (si LDAP) ou email (si local)
       '{TEST_USER_EMAIL}',
       $${TEST_USER_PRENOM} {TEST_USER_NOM_PATRONYMIQUE}$$,
       '????', -- 'shib' (si authentification shibboleth), ou 'ldap' (si auth LDAP), ou mdp bcrypté (si local)
       '{TEST_USER_PASSWORD_RESET_TOKEN}',
       $${TEST_USER_NOM_PATRONYMIQUE}$$,
       $${TEST_USER_PRENOM}$$
from individu i
where i.source_code = '{ETAB_CODE}::00012345'
;

--
-- /!\ Attribution du rôle Admin tech à l'utilisateur de test !!
--
insert into individu_role(id, individu_id, role_id)
select nextval('individu_role_id_seq'), i.id, r.id
from individu i, role r
where i.source_code = '{ETAB_CODE}::00012345'
  and r.source_code = 'ADMIN_TECH'
;
