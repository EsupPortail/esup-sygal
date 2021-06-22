--
-- DONNÉES DE TEST.
--

--
-- Création de l'individu/utilisateur de test
--
insert into individu (id, civilite, nom_usuel, nom_patronymique, prenom1, email, source_code, supann_id, source_id, histo_createur_id, histo_modificateur_id)
select nextval('individu_id_seq'),
       'M.',
       'Premier',
       'Premier',
       'François',
       'francois.premier@{ETAB_DOMAINE}',
       'INCONNU::00012345',
       '00012345',
       1, 1, 1
;
insert into utilisateur (id, username, email, display_name, password, individu_id)
select nextval('utilisateur_id_seq'),
       'premierf@{ETAB_DOMAINE}', -- du genre EPPN (si shibboleth activé) ou supannAliasLogin (si LDAP activé)
       'francois.premier@{ETAB_DOMAINE}',
       'François PREMIER',
       'shib', -- 'shib' (si authentification shibboleth), ou 'ldap' (si auth LDAP), ou mdp bcrypté (si auth BDD locale)
       i.id
from individu i
where i.source_code = 'INCONNU::00012345'
;

--
-- /!\ Attribution du rôle Admin tech à l'utilisateur de test !!
--
insert into individu_role(id, individu_id, role_id)
select nextval('individu_role_id_seq'), i.id, r.id
from individu i, role r
where i.source_code = 'INCONNU::00012345'
  and r.source_code = 'ADMIN_TECH'
;
