--
-- Fichiers communs.
--
-- Nouvelle catégorie.
insert into CATEGORIE_PRIVILEGE(ID, CODE, LIBELLE, ORDRE)
values (47, 'fichier-commun', 'Dépôt de fichier commun (non lié à une thèse)', 50)
/
insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
select privilege_id_seq.nextval, cp.id, 'televerser', 'Téléversement de fichier commun non lié à une thèse particulière (ex: PV de soutenance)', 10
from CATEGORIE_PRIVILEGE cp where cp.CODE = 'fichier-commun'
/
insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
select privilege_id_seq.nextval, cp.id, 'telecharger', 'Téléchargement de fichier commun non lié à une thèse particulière (ex: PV de soutenance)', 20
from CATEGORIE_PRIVILEGE cp where cp.CODE = 'fichier-commun'
/
-- Ajout des privilèges fichier-commun/* au profil 'ADMIN_TECH'
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'fichier-commun',  'televerser'  from dual union
    select 'fichier-commun',  'telecharger' from dual
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH', 'BU', 'BDD')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
/
-- Attribution (si pas déjà fait) des privilèges fichier-divers/* au rôle 'ADMIN_TECH' (grâce au profil 'ADMIN_TECH').
insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
SELECT r.id as ROLE_ID, p.ID as PRIVILEGE_ID
from PROFIL_PRIVILEGE pp
         join profil on PROFIL.ID = pp.PROFIL_ID and profil.ROLE_ID in ('ADMIN_TECH', 'BU', 'BDD')
         join PRIVILEGE p on p.id = pp.PRIVILEGE_ID
         join CATEGORIE_PRIVILEGE cp on cp.id = p.CATEGORIE_ID and cp.CODE = 'fichier-commun'
         join role r on r.CODE = PROFIL.ROLE_ID
where not exists (
        select * from ROLE_PRIVILEGE rp where rp.ROLE_ID = r.id and rp.PRIVILEGE_ID = p.id
    );


--
-- Nouvelle NATURE_FICHIER : 'COMMUNS'
--
insert into NATURE_FICHIER (ID, CODE, LIBELLE)
values (NATURE_FICHIER_ID_SEQ.nextval, 'COMMUNS', 'Fichier commun (ex: modèle d''avenant à la convention de MEL)')
/



select * from PRIVILEGE p
join CATEGORIE_PRIVILEGE cp on cp.id = p.CATEGORIE_ID and cp.CODE = 'fichier-divers';
