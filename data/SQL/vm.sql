--
-- Création d'une vue matérialisée.
--

--drop materialized view MV_VARIABLE;
CREATE MATERIALIZED VIEW MV_VARIABLE
REFRESH COMPLETE ON DEMAND USING TRUSTED CONSTRAINTS AS
  select
    v.lib_vap as description,
    v.par_vap as valeur,
    s.id source_id,
    v.cod_vap as source_code
  from
    sodoct_variable@APOPROD v,
    source s
  where s.code = 'Apogee'
;



--
-- En cas d'erreur "ORA-01775: bouclage de chaînes de synonymes" sur le refresh d'une VM,
-- il s'agit sans doute d'un problème côté Apogée :
--

drop public SYNONYM sodoct_these;
drop public SYNONYM sodoct_thesard;
drop public SYNONYM sodoct_acteur;

create public SYNONYM sodoct_these for apogee.sodoct_these;
create public SYNONYM sodoct_thesard for apogee.sodoct_thesard;
create public SYNONYM sodoct_acteur for apogee.sodoct_acteur;

grant select on sodoct_these to ucbn_sodoct;
grant select on sodoct_thesard to ucbn_sodoct;
grant select on sodoct_acteur to ucbn_sodoct;
