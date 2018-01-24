
--
-- Insertion de nouveaux individus SSI aucun individu n'existe avec le même mail.
--
-- 1) Vérifier le résultat du select :
with tmp as (
  select 'Francis'           p, 'EUSTACHE'            n, 'eustachef'       a from dual union
  select 'Myriam'            p, 'BERNAUDIN'           n, 'bernaudin'       a from dual union
  select 'Denis'             p, 'VIVIEN'              n, 'vivien'          a from dual union
  select 'Ludovic'           p, 'DICKEL'              n, 'dickel'          a from dual union
  select 'Damien'            p, 'DAVENNE'             n, 'davenne'         a from dual union
  select 'Alain'             p, 'MANRIQUE'            n, 'manrique'        a from dual union
  select 'Guy'               p, 'LAUNOY'              n, 'launoyg'         a from dual union
  select 'Laurent'           p, 'POULAIN'             n, 'poulainl'        a from dual union
  select 'Pierre-Jacques'    p, 'BONNAMY'             n, 'bonnamy'         a from dual union
  select 'Patrick'           p, 'DALLEMAGNE'          n, 'dallemagne'      a from dual union
  select 'Alain'             p, 'RINCE'               n, 'rince'           a from dual union
  select 'Karim'             p, 'BOUMEDIENE'          n, 'boumediene'      a from dual union
  select 'Marie'             p, 'PRUDHOMME'           n, 'prudhomme'       a from dual union
  select 'Pascal'            p, 'SOURDAINE'           n, 'sourdaine'       a from dual union
  select 'Jean-Claude'       p, 'DAUVIN'              n, 'dauvinj'         a from dual union
  select 'Jean-Luc'          p, 'GAILLARD'            n, 'gaillardj'       a from dual union
  select 'Christophe'        p, 'ALLEAUME'            n, 'alleaume'        a from dual union
  select 'Dominique'         p, 'CUSTOS'              n, 'custos'          a from dual union
  select 'Isabelle'          p, 'LEBON'               n, 'leboni'          a from dual union
  select 'Joël'              p, 'BRÉE'                n, 'bree'            a from dual union
  select 'Olivier'           p, 'MAQUAIRE'            n, 'maquaire'        a from dual union
  select 'Jean-Marc'         p, 'FOURNIER'            n, 'fournierj'       a from dual union
  select 'Thierry'           p, 'SAINT-GERAND'        n, 'saintgerand'     a from dual union
  select 'Dominique'         p, 'BEYNIER'             n, 'beynierd'        a from dual union
  select 'Michèle'           p, 'MOLINA'              n, 'molina'          a from dual union
  select 'Thierry'           p, 'PIOT'                n, 'piot'            a from dual union
  select 'Christophe'        p, 'DURAND'              n, 'durand'          a from dual union
  select 'Pierre'            p, 'BAUDUIN'             n, 'bauduin'         a from dual union
  select 'Jean-Louis'        p, 'LENHOF'              n, 'lenhof'          a from dual union
  select 'Pierre'            p, 'LARRIVEE'            n, 'larrivee'        a from dual union
  select 'Brigitte'          p, 'DIAZ'                n, 'diaz'            a from dual union
  select 'Anca'              p, 'CRISTOFOVICI'        n, 'cristofovici'    a from dual union
  select 'Eric'              p, 'LEROY DU CARDONNOY'  n, 'leroyducardo'    a from dual union
  select 'Gilles'            p, 'OLIVO'               n, 'olivog'          a from dual union
  select 'Dominique'         p, 'DURAND'              n, 'durandd'         a from dual union
  select 'Amine'             p, 'CASSIMI'             n, 'cassimi'         a from dual union
  select 'Antoine'           p, 'MAIGNAN'             n, 'maignana'        a from dual union
  select 'Christian'         p, 'FERNANDEZ'           n, 'fernandez'       a from dual union
  select 'Annie-Claude'      p, 'GAUMONT'             n, 'gaumont'         a from dual union
  select 'Hamid'             p, 'GUALOUS'             n, 'gualous'         a from dual union
  select 'Frédéric'          p, 'JURIE'               n, 'jurie'           a from dual union
  select 'Francesco'         p, 'AMOROSO'             n, 'amoroso'         a from dual
)
select /*INDIVIDU_id_seq.nextval,*/ u.id, u.id, s.id, /*INDIVIDU_id_seq.currval,*/ tmp.p, tmp.n, ucbn_ldap.alias2mail(tmp.a)
from UTILISATEUR u, source s, tmp
where u.USERNAME = 'sodoct-app' and s.CODE = 'App' and not exists (select * from individu where email = ucbn_ldap.alias2mail(tmp.a));
-- 2) Contrôler si les individus résultant ressortent en raison de leur email null (auquel cas il faut renseigner leur email) :
select * from individu where nom_usuel = 'CASSIMI';
-- 3) Mettre le select dans l'insert ci dessous, en décommentant les INDIVIDU_id_seq.* :
insert into INDIVIDU(ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, SOURCE_ID, SOURCE_CODE, PRENOM, NOM_USUEL, EMAIL)
-- with ...




--
-- Ajout d'un directeur d'UR recherché dans la table individu par son mail unicaen :
--
insert into UNITE_RECH_IND(ID, UNITE_RECH_ID, INDIVIDU_ID, ROLE_ID)
  with tmp as (
    select 'UMRS1077'    u, 'eustachef'       a from dual union
    select 'UMR6301'     u, 'bernaudin'       a from dual union
    select 'UMRS919'     u, 'vivien'          a from dual union
    select 'EA4259'      u, 'dickel'          a from dual union
    select 'UMRS1075'    u, 'davenne'         a from dual union
    select 'EA4650'      u, 'manrique'        a from dual union
    select 'UMRS1086'    u, 'launoyg'         a from dual union
    select 'UMRS1199'    u, 'poulainl'        a from dual union
    select 'EA2608'      u, 'bonnamy'         a from dual union
    select 'EA4258'      u, 'dallemagne'      a from dual union
    select 'EA4655'      u, 'rince'           a from dual union
    select 'EA4652'      u, 'boumediene'      a from dual union
    select 'UMRA950'     u, 'prudhomme'       a from dual union
    select 'UMR7208'     u, 'sourdaine'       a from dual union
    select 'UMR6143'     u, 'dauvinj'         a from dual union
    select 'EA4651'      u, 'gaillardj'       a from dual union
    select 'EA967'       u, 'alleaume'        a from dual union
    select 'EA2132'      u, 'custos'          a from dual union
    select 'UMR6211'     u, 'leboni'          a from dual union
    select 'EA969'       u, 'bree'            a from dual union
    select 'UMR6554'     u, 'maquaire'        a from dual union
    select 'UMR6590'     u, 'fournierj'       a from dual union
    select 'UMR6266'     u, 'saintgerand'     a from dual union
    select 'EA3918'      u, 'beynierd'        a from dual union
    select 'EA4649'      u, 'molina'          a from dual union
    select 'EA965'       u, 'piot'            a from dual union
    select 'EA4260'      u, 'durand'          a from dual union
    select 'UMR6273'     u, 'bauduin'         a from dual union
    select 'UMR6583'     u, 'lenhof'          a from dual union
    select 'EA4255'      u, 'larrivee'        a from dual union
    select 'EA4256'      u, 'diaz'            a from dual union
    select 'EA2610'      u, 'cristofovici'    a from dual union
    select 'EA4254'      u, 'leroyducardo'    a from dual union
    select 'EA2129'      u, 'olivog'          a from dual union
    select 'UMR6534'     u, 'durandd'         a from dual union
    select 'UMR6252'     u, 'cassimi'         a from dual union
    select 'UMR6508'     u, 'maignana'        a from dual union
    select 'UMR6506'     u, 'fernandez'       a from dual union
    select 'UMR6507'     u, 'gaumont'         a from dual union
    select 'EA4253'      u, 'gualous'         a from dual union
    select 'UMR6072'     u, 'jurie'           a from dual union
    select 'UMR6139'     u, 'amoroso'         a from dual
  )
  select UNITE_RECH_IND_id_seq.nextval, ur.id, i.id, r.id
  from UNITE_RECH ur, INDIVIDU i, USER_ROLE r, tmp
  where r.ROLE_ID = 'Directeur d''unité de recherche' and  ur.SOURCE_CODE = tmp.u and i.EMAIL = ucbn_ldap.alias2mail(tmp.a)
;



-- vérif
select ur.SOURCE_CODE, i.id, i.nom_usuel, i.prenom, r.role_id
from UNITE_RECH_IND uri
  join UNITE_RECH ur on uri.UNITE_RECH_ID = ur.id
  join individu i on uri.individu_id = i.id
  join user_role r on uri.role_id = r.id;


