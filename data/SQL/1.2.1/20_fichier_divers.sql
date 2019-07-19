--
-- Déplacement des privilèges de la catégorie 'fichier-divers' vers la catégorie 'these'
-- car ils concernent des fichiers liés à une thèse (ex: PV de soutenance).
--
-- La catégorie 'fichier-divers' désigne désormais les privilèges concernant des fichiers non liés
-- aux thèses (ex: fichiers déposés pour les pages d'informations).
--
update PRIVILEGE p
    set CATEGORIE_ID = (select id from CATEGORIE_PRIVILEGE where CODE = 'these'),
        CODE = 'fichier-divers-'||CODE
    where CATEGORIE_ID = (select id from CATEGORIE_PRIVILEGE where CODE = 'fichier-divers')
/

--
-- 2 nouveaux privilèges pour les opérations sur des fichiers divers, *non liées aux thèses* :
--   - fichier-divers/televerser
--   - fichier-divers/telecharger
--
insert into PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
    select PRIVILEGE_ID_SEQ.nextval, cp.id, 'televerser', 'Téléverser ou supprimer un fichier divers, non lié à une thèse', 100
    from CATEGORIE_PRIVILEGE cp
    where cp.CODE = 'fichier-divers'
/
insert into PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
    select PRIVILEGE_ID_SEQ.nextval, cp.id, 'telecharger', 'Télécharger un fichier divers, non lié à une thèse', 200
    from CATEGORIE_PRIVILEGE cp
    where cp.CODE = 'fichier-divers'
/

--
-- Supression du privilège faq/modifier-fichier, utilisé pour les fichiers des pages d'informations.
-- Il est remplacé par le privilège fichier-divers/televerser.
--
delete from PRIVILEGE
    where CATEGORIE_ID = (select id from CATEGORIE_PRIVILEGE where CODE = 'faq')
      and CODE = 'modifier-fichier'
/
-- Ajout des privilèges fichier-divers/* au profil 'ADMIN_TECH'
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
    with data(categ, priv) as (
        select 'fichier-divers',  'televerser'  from dual union
        select 'fichier-divers',  'telecharger' from dual
    )
    select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
    from data
    join PROFIL on profil.ROLE_ID in (/*'ADMIN_TECH',*/ 'BU', 'BDD')
    join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
    join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
/
-- Attribution (si pas déjà fait) des privilèges fichier-divers/* au rôle 'ADMIN_TECH' (grâce au profil 'ADMIN_TECH').
insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
    SELECT r.id as ROLE_ID, p.ID as PRIVILEGE_ID
    from PROFIL_PRIVILEGE pp
             join profil on PROFIL.ID = pp.PROFIL_ID and profil.ROLE_ID in (/*'ADMIN_TECH',*/ 'BU', 'BDD')
             join PRIVILEGE p on p.id = pp.PRIVILEGE_ID
             join CATEGORIE_PRIVILEGE cp on cp.id = p.CATEGORIE_ID and cp.CODE = 'fichier-divers'
             join role r on r.CODE = PROFIL.ROLE_ID
    where not exists (
        select * from ROLE_PRIVILEGE rp where rp.ROLE_ID = r.id and rp.PRIVILEGE_ID = p.id
    );
