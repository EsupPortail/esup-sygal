drop table Z_ETAPE;
drop table Z_ETAPE_DEP;

CREATE TABLE Z_ETAPE
(
  ID                 NUMBER(*, 0) NOT NULL,
  CODE               VARCHAR2(64 CHAR) NOT NULL,
  ORDRE              NUMBER(*, 0) DEFAULT 1 NOT NULL
);

CREATE TABLE Z_ETAPE_DEP
(
  ID            NUMBER(*, 0) NOT NULL,
  ETAPE_PREC_ID NUMBER(*, 0) NOT NULL,
  ETAPE_SUIV_ID NUMBER(*, 0) NOT NULL
);

ALTER TABLE Z_ETAPE_DEP ADD CONSTRAINT Z_ETAPE_DEP_PREC_FK FOREIGN KEY (ETAPE_PREC_ID) REFERENCES Z_ETAPE (ID) ON DELETE CASCADE ;
ALTER TABLE Z_ETAPE_DEP ADD CONSTRAINT Z_ETAPE_DEP_SUIV_FK FOREIGN KEY (ETAPE_SUIV_ID) REFERENCES Z_ETAPE (ID) ON DELETE CASCADE ;

drop sequence Z_ETAPE_id_seq;
drop sequence Z_ETAPE_DEP_id_seq;

create sequence Z_ETAPE_id_seq;
create sequence Z_ETAPE_DEP_id_seq;

--delete from Z_ETAPE;
insert into Z_ETAPE(ID, CODE, ORDRE)
  with ds(code, ordre) as (
    select 'Racine', 0 from dual union
    select 'A', 100 from dual union
    select 'B', 200 from dual union
    select 'C', 300 from dual union
    select 'D', 400 from dual union
    select 'E', 500 from dual union
    select 'F', 600 from dual union
    select 'G', 700 from dual union
    select 'H', 800 from dual union
    select 'I', 900 from dual
  )
  select Z_ETAPE_id_seq.nextval, code, ordre from ds;

--delete from Z_ETAPE_DEP;
INSERT INTO Z_ETAPE_DEP (ID, ETAPE_PREC_ID, ETAPE_SUIV_ID)
  WITH d AS (
    SELECT
      'Racine' prec,
      'A' suiv FROM dual UNION
    ----
    SELECT
      'A' prec,
      'B' suiv FROM dual UNION
    SELECT
      'B' prec,
      'C' suiv FROM dual UNION
    SELECT
      'C' prec,
      'D' suiv FROM dual UNION
    SELECT
      'D' prec,
      'E' suiv FROM dual UNION
    SELECT
      'E' prec,
      'F' suiv FROM dual UNION
    SELECT
      'F' prec,
      'G' suiv FROM dual UNION
    SELECT
      'G' prec,
      'H' suiv FROM dual UNION
    SELECT
      'H' prec,
      'I' suiv FROM dual UNION
    ----
    SELECT
      'A' prec,
      'E' suiv FROM dual UNION
    ----
    SELECT
      'B' prec,
      'D' suiv FROM dual UNION
    ----
    SELECT
      'C' prec,
      'G' suiv FROM dual
    ----
  )
  SELECT Z_ETAPE_DEP_id_seq.nextval, ep.id, es.id
  FROM Z_ETAPE ep, Z_ETAPE es, d
  where ep.CODE = d.prec and es.CODE = d.suiv
;

commit;


select rpad(prec.CODE, 1) || ' --> ' || suiv.CODE str
FROM Z_ETAPE_DEP d
  JOIN Z_ETAPE prec ON d.ETAPE_PREC_ID = prec.id
  JOIN Z_ETAPE suiv ON d.ETAPE_SUIV_ID = suiv.id
ORDER BY prec.ORDRE
;



create or replace view z_workflow(these_id, etape_id, code, ordre, franchie) as
  with ds(code, franchie) as (
    select 'Racine', 1 from dual union
    select 'A', 1 from dual union
    select 'B', 0 from dual union
    select 'C', 1 from dual union
    select 'D', 1 from dual union
    select 'E', 0 from dual union
    select 'F', 0 from dual union
    select 'G', 0 from dual union
    select 'H', 0 from dual union
    select 'I', 0 from dual
  )
  select 22155, e.id, ds.code, e.ordre, ds.franchie
  from ds
    join z_etape e on e.code = ds.code
;





SELECT
  wf.these_id,
  e.id etape_id,
  ep.id etape_prec_id,
  wf.franchie,
  level,
  rpad(ep.CODE, 2*level) lib,
  sys_connect_by_path(ep.CODE, '/') path,
  CONNECT_BY_ROOT e.code root,
  CONNECT_BY_ISLEAF Is_Leaf
FROM Z_ETAPE_DEP d
  join Z_ETAPE e on e.code = 'E'
  JOIN Z_ETAPE ep ON d.ETAPE_PREC_ID = ep.id
  JOIN Z_ETAPE es ON d.ETAPE_SUIV_ID = es.id
  JOIN z_workflow wf on wf.code = ep.code
--WHERE CONNECT_BY_ISLEAF = 1
CONNECT BY ETAPE_SUIV_ID = PRIOR ETAPE_PREC_ID START WITH es.id = e.id
ORDER SIBLINGS BY ep.ordre
;





/*
--
-- Version Z_*
--
create or replace function atteignable(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC
AS
  v_path CLOB;
  v_franchie integer;
BEGIN
  -- parcours de tous les chemins possibles
  for v_path_row in (
    -- tous les chemins possibles (l'astuce est WHERE CONNECT_BY_ISLEAF = 1)
    SELECT
      level depth,
      sys_connect_by_path(ep.CODE, '/') path -- ex: '/D/C/B/A'
    FROM Z_ETAPE_DEP d
      JOIN Z_ETAPE ep ON d.ETAPE_PREC_ID = ep.id
      JOIN Z_ETAPE es ON d.ETAPE_SUIV_ID = es.id
    WHERE CONNECT_BY_ISLEAF = 1
    CONNECT BY ETAPE_SUIV_ID = PRIOR ETAPE_PREC_ID START WITH es.id = p_etape_id
    ORDER SIBLINGS BY ep.ordre
  ) LOOP

    DBMS_OUTPUT.PUT_LINE ('Parcours de '||v_path_row.path||' (');

    -- parcours des étapes du chemin
    for v_etape_row in (
      -- astuce pour faire un explode() des codes étapes
      WITH tmp(str) AS (
          SELECT substr(v_path_row.path, 2) FROM dual -- ex: 'D/C/B/A'
      )
      SELECT trim(regexp_substr(str, '[^/]+', 1, LEVEL)) code
      FROM tmp
      CONNECT BY instr(str, '/', 1, LEVEL - 1) > 0
    ) LOOP

      select franchie into v_franchie from z_workflow wf
        join Z_ETAPE e on e.code = v_etape_row.code
      where wf.these_id = p_these_id and wf.etape_id = e.id;

      DBMS_OUTPUT.PUT_LINE ('  - Etape '||v_etape_row.code||': franchie = '||v_franchie);

      -- à la moindre étape non franchie dans le chemin, stop, on passe au chemin suivant.
      EXIT WHEN v_franchie = 0;

    END LOOP;

    DBMS_OUTPUT.PUT_LINE (') franchie = '||v_franchie);

    -- si toutes les étapes du chemin franchies, hourra!
    EXIT WHEN v_franchie = 1;

  END LOOP;

  return v_franchie;

END atteignable;
/
*/

update Z_ETAPE set code = 'A' where code = 'AA'; commit;
update Z_ETAPE set code = 'AA' where code = 'A'; commit;

--drop materialized view Z_CHEMINS;
create materialized view Z_CHEMINS as
  SELECT -- tous les chemins possibles (l'astuce est WHERE CONNECT_BY_ISLEAF = 1)
    es.CODE,
    level depth,
    CONNECT_BY_ISLEAF,
    sys_connect_by_path(es.CODE, '/') path -- ex: '/D/C/B/A'.
  FROM Z_ETAPE_DEP d
    JOIN Z_ETAPE ep ON d.ETAPE_PREC_ID = ep.id
    JOIN Z_ETAPE es ON d.ETAPE_SUIV_ID = es.id
    JOIN Z_ETAPE er ON er.CODE = 'Racine'
  WHERE CONNECT_BY_ISLEAF = 1
  CONNECT BY PRIOR ETAPE_SUIV_ID = ETAPE_PREC_ID
  START WITH ep.id = er.id
  ORDER SIBLINGS BY es.ordre
;


-- requête de sioux faisant un explode() des codes étapes
with chemins as (
  select rownum, path from Z_CHEMINS
)
select * from (
  SELECT
    ch.rownum,
    ch.path,
    trim(regexp_substr(ch.path, '[^/]+', 1, LEVEL)) code
  FROM chemins ch
  CONNECT BY instr(ch.path, '/', 1, LEVEL - 1) > 0
) t
  join z_etape e on e.code = t.code
order by path, e.ORDRE
;

select * from Z_CHEMINS;



SELECT -- tous les chemins possibles (l'astuce est WHERE CONNECT_BY_ISLEAF = 1)
  level depth,
  sys_connect_by_path(es.CODE, '/') path -- ex: '/D/C/B/A'.
FROM WF_ETAPE_DEP d
  JOIN WF_ETAPE ep ON d.ETAPE_PREC_ID = ep.id
  JOIN WF_ETAPE es ON d.ETAPE_SUIV_ID = es.id
  JOIN WF_ETAPE er ON er.CODE = 'DEPOT_VERSION_ORIGINALE'
WHERE CONNECT_BY_ISLEAF = 1
CONNECT BY PRIOR ETAPE_SUIV_ID = ETAPE_PREC_ID START WITH ep.id = er.id
ORDER SIBLINGS BY es.ordre
;


drop function atteignable;
create or replace function atteignable(p_etape_id NUMERIC, p_these_id NUMERIC/*, p_est_derniere NUMERIC*/) return NUMERIC
AS
  v_path CLOB;
  v_franchie NUMERIC := 1; -- NB: pour retourner 1 si l'étape testée n'est précédée d'aucune étape.
  v_atteignable NUMERIC;
BEGIN
  -- Parcours de tous les chemins possibles.
  -- On part de l'étape testée et on remonte dans les étapes précédentes.
  -- NB: l'étape testée NE figure PAS dans le chemin.
  for v_path_row in (
    SELECT -- tous les chemins possibles (l'astuce est WHERE CONNECT_BY_ISLEAF = 1)
      level depth,
      sys_connect_by_path(ep.CODE, '/') path -- ex: '/D/C/B/A'.
    FROM WF_ETAPE_DEP d
      JOIN WF_ETAPE ep ON d.ETAPE_PREC_ID = ep.id
      JOIN WF_ETAPE es ON d.ETAPE_SUIV_ID = es.id
    WHERE CONNECT_BY_ISLEAF = 1
    CONNECT BY ETAPE_SUIV_ID = PRIOR ETAPE_PREC_ID START WITH es.id = p_etape_id
    ORDER SIBLINGS BY ep.ordre
  )
  LOOP
    --DBMS_OUTPUT.PUT_LINE ('Parcours de '||v_path_row.path||' (');

    -- parcours des étapes du chemin
    for v_etape_row in (
      WITH tmp(str) AS (
        SELECT substr(v_path_row.path, 2) FROM dual -- suppression du 1er '/', ex: 'D/C/B/A'.
      ) -- requête de sioux faisant un explode() des codes étapes
      SELECT trim(regexp_substr(str, '[^/]+', 1, LEVEL)) code FROM tmp
      CONNECT BY instr(str, '/', 1, LEVEL - 1) > 0
    )
    LOOP
      select franchie into v_franchie from V_WORKFLOW wf
        join WF_ETAPE e on e.code = v_etape_row.code
      where wf.these_id = p_these_id and wf.etape_id = e.id;

      --DBMS_OUTPUT.PUT_LINE ('  - Etape '||v_etape_row.code||': franchie = '||v_franchie);

      -- à la moindre étape non franchie dans le chemin, stop, on passe au chemin suivant.
      EXIT WHEN v_franchie = 0;
    END LOOP;

    --DBMS_OUTPUT.PUT_LINE (') franchie = '||v_franchie);

    -- si toutes les étapes du chemin sont franchies, c'est bon, pas besoin de tester les autres chemins.
    EXIT WHEN v_franchie = 1;
  END LOOP;

  v_atteignable := v_franchie;

--   -- Si "p_est_derniere" est à 1, on recherche si l'étape testée est la dernière atteignable
--   -- (i.e. si les étapes suivantes immédiates sont non franchies).
--   if v_atteignable = 1 and p_est_derniere = 1 then
--     for v_suiv_row in (
--       SELECT wf.FRANCHIE
--       FROM WF_ETAPE_DEP d
--         JOIN V_WORKFLOW wf on wf.these_id = p_these_id and wf.etape_id = d.ETAPE_SUIV_ID
--       WHERE d.ETAPE_PREC_ID = p_etape_id
--     )
--     LOOP
--
--     END LOOP;
--   END IF;

  return v_atteignable;
END atteignable;
/


select e.CODE, atteignable(v.ETAPE_ID, t.id) atteignable, t.id, th.nom_usuel
from these t
  join thesard th on th.id = t.THESARD_ID
  join MV_WF_ETAPE_PERTIN v on v.THESE_ID = t.id
  join wf_etape e on e.id = v.ETAPE_ID
where t.ETAT_THESE = 'E' and e.CODE = 'RDV_BU_VALIDATION_BU' and atteignable(v.ETAPE_ID, t.id) = 1
order by e.ORDRE, th.nom_usuel
;


select atteignable(11, 26856) atteignable from dual;



select * from V_WF_ETAPE_PERTIN;


SELECT
  level depth,
  sys_connect_by_path(ep.id, '/') path -- ex: '/D/C/B/A'
FROM WF_ETAPE_DEP d
  JOIN WF_ETAPE ep ON d.ETAPE_PREC_ID = ep.id
  JOIN WF_ETAPE es ON d.ETAPE_SUIV_ID = es.id
WHERE CONNECT_BY_ISLEAF = 1
CONNECT BY ETAPE_SUIV_ID = PRIOR ETAPE_PREC_ID START WITH es.id = 8
ORDER SIBLINGS BY ep.ordre
;





