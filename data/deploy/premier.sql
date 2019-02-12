set define off;

--
-- Pré-requis :
--
--   - avoir choisi un code pour son établissement, ex: 'UCN'.
--   - avoir choisi un code pour sa COMUE éventuelle, ex: 'COMUE'.
--

--
-- Sources de données.
--
--INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (1, 'SYGAL::sygal', 'SyGAL', 0);
INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (2, 'UCN::apogee', 'Apogée UCN', 1);

--
-- Etablissements/Structures.
--
INSERT INTO STRUCTURE (ID, SIGLE, LIBELLE, TYPE_STRUCTURE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, SOURCE_ID, SOURCE_CODE, CODE)
VALUES (1, 'NU', 'Normandie Université', null, 1, 1, 1, 'COMUE', 'COMUE');
INSERT INTO STRUCTURE (ID, SIGLE, LIBELLE, TYPE_STRUCTURE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, SOURCE_ID, SOURCE_CODE, CODE)
VALUES (2, 'UCN', 'Université de Caen Normandie', null, 1, 1, 1, 'UCN', 'UCN');

INSERT INTO ETABLISSEMENT (ID, STRUCTURE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, DOMAINE, SOURCE_ID, SOURCE_CODE, EST_COMUE, EST_MEMBRE)
VALUES (1, 1, 1, 1, 'normandie-univ.fr', 1, 'COMUE', 1, 0);
INSERT INTO ETABLISSEMENT (ID, STRUCTURE_ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, DOMAINE, SOURCE_ID, SOURCE_CODE, EST_COMUE, EST_MEMBRE)
VALUES (2, 2, 1, 1, 'unicaen.fr', 1, 'UCN', 0, 1);

--
-- Sources de données : compléments.
--
update SOURCE set ETABLISSEMENT_ID = (select id from etablissement where SOURCE_CODE = 'UNILIM') where CODE like 'UNILIM'||'::%';

--
-- Pseudo-utilisateur Application.
--
INSERT INTO UTILISATEUR (ID, USERNAME, EMAIL, DISPLAY_NAME, PASSWORD) VALUES (1, 'sygal-app', 'noreply@mail.fr', 'Application SyGAL', 'ldap');

--
-- Premier utilisateur.
--
INSERT INTO UTILISATEUR (ID,
                         USERNAME,
                         EMAIL,
                         DISPLAY_NAME,
                         PASSWORD,
                         STATE,
                         LAST_ROLE_ID,
                         INDIVIDU_ID,
                         PASSWORD_RESET_TOKEN)
VALUES (2,
        'francois.premier@sygal.fr',
        'francois.premier@sygal.fr',
        'François PREMIER',
        'ldap',
        1,
        1,
        null,
        null);

--
-- Rôles.
--

INSERT INTO ROLE (ID,
                  CODE,
                  LIBELLE,
                  SOURCE_CODE,
                  SOURCE_ID,
                  ROLE_ID,
                  IS_DEFAULT,
                  LDAP_FILTER,
                  ATTRIB_AUTO,
                  THESE_DEP,
                  HISTO_CREATEUR_ID,
                  HISTO_CREATION,
                  HISTO_MODIFICATEUR_ID,
                  HISTO_MODIFICATION,
                  HISTO_DESTRUCTEUR_ID,
                  HISTO_DESTRUCTION,
                  STRUCTURE_ID,
                  TYPE_STRUCTURE_DEPENDANT_ID,
                  ORDRE_AFFICHAGE)
VALUES (1,
        'ADMIN_TECH',
        'Administrateur technique',
        '$prefixEtab' || '::ADMIN_TECH',
        1,
        'Administrateur technique',
        0,
        NULL,
        0,
        0,
        1,
        sysdate,
        1,
        sysdate,
        NULL,
        NULL,
        NULL,
        NULL,
        'zzz');
INSERT INTO ROLE (ID,
                  CODE,
                  LIBELLE,
                  SOURCE_CODE,
                  SOURCE_ID,
                  ROLE_ID,
                  IS_DEFAULT,
                  LDAP_FILTER,
                  ATTRIB_AUTO,
                  THESE_DEP,
                  HISTO_CREATEUR_ID,
                  HISTO_CREATION,
                  HISTO_MODIFICATEUR_ID,
                  HISTO_MODIFICATION,
                  HISTO_DESTRUCTEUR_ID,
                  HISTO_DESTRUCTION,
                  STRUCTURE_ID,
                  TYPE_STRUCTURE_DEPENDANT_ID,
                  ORDRE_AFFICHAGE)
VALUES (241,
        'OBSERV',
        'Observateur',
        'OBSERV',
        1,
        'Observateur multi-établissement',
        0,
        NULL,
        0,
        0,
        1,
        sysdate,
        1,
        sysdate,
        NULL,
        NULL,
        NULL,
        NULL,
        'yy');
INSERT INTO ROLE (ID,
                  CODE,
                  LIBELLE,
                  SOURCE_CODE,
                  SOURCE_ID,
                  ROLE_ID,
                  IS_DEFAULT,
                  LDAP_FILTER,
                  ATTRIB_AUTO,
                  THESE_DEP,
                  HISTO_CREATEUR_ID,
                  HISTO_CREATION,
                  HISTO_MODIFICATEUR_ID,
                  HISTO_MODIFICATION,
                  HISTO_DESTRUCTEUR_ID,
                  HISTO_DESTRUCTION,
                  STRUCTURE_ID,
                  TYPE_STRUCTURE_DEPENDANT_ID,
                  ORDRE_AFFICHAGE)
VALUES (2,
        'ADMIN',
        'Administrateur',
        '$prefixEtab' || '::ADMIN',
        1,
        'Administrateur UCN',
        0,
        NULL,
        0,
        0,
        1,
        sysdate,
        1,
        sysdate,
        NULL,
        NULL,
        2,
        1,
        'zz');
INSERT INTO ROLE (ID,
                  CODE,
                  LIBELLE,
                  SOURCE_CODE,
                  SOURCE_ID,
                  ROLE_ID,
                  IS_DEFAULT,
                  LDAP_FILTER,
                  ATTRIB_AUTO,
                  THESE_DEP,
                  HISTO_CREATEUR_ID,
                  HISTO_CREATION,
                  HISTO_MODIFICATEUR_ID,
                  HISTO_MODIFICATION,
                  HISTO_DESTRUCTEUR_ID,
                  HISTO_DESTRUCTION,
                  STRUCTURE_ID,
                  TYPE_STRUCTURE_DEPENDANT_ID,
                  ORDRE_AFFICHAGE)
VALUES (6,
        'BDD',
        'Bureau des doctorats',
        '$prefixEtab' || '::BDD',
        1,
        'Bureau des doctorats UCN',
        0,
        NULL,
        0,
        0,
        1,
        sysdate,
        1,
        sysdate,
        NULL,
        NULL,
        2,
        1,
        'zzz');
INSERT INTO ROLE (ID,
                  CODE,
                  LIBELLE,
                  SOURCE_CODE,
                  SOURCE_ID,
                  ROLE_ID,
                  IS_DEFAULT,
                  LDAP_FILTER,
                  ATTRIB_AUTO,
                  THESE_DEP,
                  HISTO_CREATEUR_ID,
                  HISTO_CREATION,
                  HISTO_MODIFICATEUR_ID,
                  HISTO_MODIFICATION,
                  HISTO_DESTRUCTEUR_ID,
                  HISTO_DESTRUCTION,
                  STRUCTURE_ID,
                  TYPE_STRUCTURE_DEPENDANT_ID,
                  ORDRE_AFFICHAGE)
VALUES (10,
        'BU',
        'Bibliothèque universitaire',
        '$prefixEtab' || '::BU',
        1,
        'Bibliothèque universitaire UCN',
        0,
        NULL,
        0,
        0,
        1,
        sysdate,
        1,
        sysdate,
        NULL,
        NULL,
        2,
        1,
        'zzz');
INSERT INTO ROLE (ID,
                  CODE,
                  LIBELLE,
                  SOURCE_CODE,
                  SOURCE_ID,
                  ROLE_ID,
                  IS_DEFAULT,
                  LDAP_FILTER,
                  ATTRIB_AUTO,
                  THESE_DEP,
                  HISTO_CREATEUR_ID,
                  HISTO_CREATION,
                  HISTO_MODIFICATEUR_ID,
                  HISTO_MODIFICATION,
                  HISTO_DESTRUCTEUR_ID,
                  HISTO_DESTRUCTION,
                  STRUCTURE_ID,
                  TYPE_STRUCTURE_DEPENDANT_ID,
                  ORDRE_AFFICHAGE)
VALUES (14,
        'DOCTORANT',
        'Doctorant',
        '$prefixEtab' || '::DOCTORANT',
        1,
        'Doctorant UCN',
        0,
        NULL,
        1,
        1,
        1,
        sysdate,
        1,
        sysdate,
        NULL,
        NULL,
        2,
        NULL,
        'zzz');


--
-- Structure/Etablissement inconnu.
--

insert into STRUCTURE(
  ID,
  LIBELLE,
  TYPE_STRUCTURE_ID,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID,
  SOURCE_ID,
  SOURCE_CODE,
  CODE
)
select STRUCTURE_ID_SEQ.nextval,
       'Établissement inconnu',
       1, -- type etab
       1,
       1,
       1, -- src sygal
       'ETAB_INCONNU', -- source code
       'INCONNU' -- code
from dual;

insert into ETABLISSEMENT(
  ID,
  STRUCTURE_ID,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID,
  DOMAINE,
  SOURCE_ID,
  SOURCE_CODE
)
select ETABLISSEMENT_ID_SEQ.nextval,
       STRUCTURE_ID_SEQ.currval,
       1,
       1,
       null, -- domaine
       1, -- src sygal
       'ETAB_INCONNU' -- source code, idem structure
from dual;


