
--
-- Vérif des persopass grâce au mail.
--
with tmp as (
  select 'durand'       u from dual union
  select 'laurentj'     u from dual union
  select 'saillant'     u from dual union
  select 'prellier'     u from dual union
  select 'fadili'       u from dual union
  select 'levigoureux'  u from dual union
  select 'ourry'        u from dual union
  select 'dauphin'      u from dual union
  select 'rouden'       u from dual
)
select ucbn_ldap.alias2mail(tmp.u) from tmp;


--
-- Insertion de nouveaux individus SSI aucun individu n'existe avec le même mail.
--
insert into INDIVIDU(ID, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, SOURCE_ID, SOURCE_CODE, PRENOM, NOM_USUEL, EMAIL)
  with tmp as (
    select 'Christophe' p, 'DURAND'         n, ucbn_ldap.alias2mail('durand'     ) m from dual union
    select 'Jérome'     p, 'LAURENT'        n, ucbn_ldap.alias2mail('laurentj'   ) m from dual union
    select 'Elodie'     p, 'SAILLANT'       n, ucbn_ldap.alias2mail('saillant'   ) m from dual union
    select 'Wilfrid'    p, 'PRELLIER'       n, ucbn_ldap.alias2mail('prellier'   ) m from dual union
    select 'Jalal'      p, 'FADILI'         n, ucbn_ldap.alias2mail('fadili'     ) m from dual union
    select 'Fabrice'    p, 'LE VIGOUREUX'   n, ucbn_ldap.alias2mail('levigoureux') m from dual union
    select 'Alain'      p, 'OURRY'          n, ucbn_ldap.alias2mail('ourry'      ) m from dual union
    select 'François'   p, 'DAUPHIN'        n, ucbn_ldap.alias2mail('dauphin'    ) m from dual union
    select 'Jacques'    p, 'ROUDEN'         n, ucbn_ldap.alias2mail('rouden'     ) m from dual union
    select 'Jérome'     p, 'LAURENT'        n, ucbn_ldap.alias2mail('laurentj'   ) m from dual union
    select 'Elodie'     p, 'SAILLANT'       n, ucbn_ldap.alias2mail('saillant'   ) m from dual union
    select 'Wilfrid'    p, 'PRELLIER'       n, ucbn_ldap.alias2mail('prellier'   ) m from dual union
    select 'Jalal'      p, 'FADILI'         n, ucbn_ldap.alias2mail('fadili'     ) m from dual union
    select 'Fabrice'    p, 'LE VIGOUREUX'   n, ucbn_ldap.alias2mail('levigoureux') m from dual union
    select 'Alain'      p, 'OURRY'          n, ucbn_ldap.alias2mail('ourry'      ) m from dual union
    select 'François'   p, 'DAUPHIN'        n, ucbn_ldap.alias2mail('dauphin'    ) m from dual union
    select 'Jacques'    p, 'ROUDEN'         n, ucbn_ldap.alias2mail('rouden'     ) m from dual
  )
  select INDIVIDU_id_seq.nextval, u.id, u.id, s.id, INDIVIDU_id_seq.currval, tmp.p, tmp.n, tmp.m
  from UTILISATEUR u, source s, tmp
  where u.USERNAME = 'sodoct-app' and s.CODE = 'App'
        and not exists (select * from individu i where email = tmp.m);

