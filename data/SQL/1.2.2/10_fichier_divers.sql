--
-- Insertions oubliées dans le script SQL de la version 1.2.1.
--

-- Ajout des privilèges fichier-divers/* au profil 'ADMIN_TECH'
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
    with data(categ, priv) as (
        select 'fichier-divers',  'televerser'  from dual union
        select 'fichier-divers',  'telecharger' from dual
    )
    select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
    from data
    join PROFIL on profil.ROLE_ID in ('ADMIN_TECH')
    join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
    join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
/
-- Attribution (si pas déjà fait) des privilèges fichier-divers/* au rôle 'ADMIN_TECH' (grâce au profil).
insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
    SELECT r.id as ROLE_ID, p.ID as PRIVILEGE_ID
    from PROFIL_PRIVILEGE pp
             join profil on PROFIL.ID = pp.PROFIL_ID and profil.ROLE_ID in ('ADMIN_TECH')
             join PRIVILEGE p on p.id = pp.PRIVILEGE_ID
             join CATEGORIE_PRIVILEGE cp on cp.id = p.CATEGORIE_ID and cp.CODE = 'fichier-divers'
             join role r on r.CODE = PROFIL.ROLE_ID
    where not exists (
        select * from ROLE_PRIVILEGE rp where rp.ROLE_ID = r.id and rp.PRIVILEGE_ID = p.id
    );
