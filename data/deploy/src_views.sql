--
-- Les vues SRC_* ont été modifiées :
--   - le source_code des structures UCN, URN, etc ne sont plus préfixés par 'COMUE::'.
--


create or replace view SRC_ACTEUR as
SELECT
  NULL                                     AS id,
  tmp.SOURCE_CODE,
  src.ID                                   AS SOURCE_ID,
  i.id                                     AS INDIVIDU_ID,
  t.id                                     AS THESE_ID,
  r.id                                     AS ROLE_ID,
  coalesce(etab_substit.id, eact.id)       AS ACTEUR_ETABLISSEMENT_ID,
  tmp.LIB_CPS                              AS QUALITE,
  tmp.LIB_ROJ_COMPL                        AS LIB_ROLE_COMPL
FROM TMP_ACTEUR tmp
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
       JOIN INDIVIDU i ON i.SOURCE_CODE = tmp.INDIVIDU_ID
       JOIN THESE t ON t.SOURCE_CODE = tmp.THESE_ID
       JOIN ROLE r ON r.SOURCE_CODE = tmp.ROLE_ID
       LEFT JOIN ETABLISSEMENT eact ON eact.SOURCE_CODE = tmp.ACTEUR_ETABLISSEMENT_ID
       LEFT JOIN STRUCTURE_SUBSTIT ss_ed on ss_ed.FROM_STRUCTURE_ID = eact.STRUCTURE_ID
       LEFT JOIN ETABLISSEMENT etab_substit on etab_substit.STRUCTURE_ID = ss_ed.TO_STRUCTURE_ID
/

create or replace view SRC_DOCTORANT as
SELECT
  NULL                                     AS id,
  tmp.SOURCE_CODE,
  src.id                                   AS source_id,
  i.id                                     AS individu_id,
  e.id                                     AS etablissement_id
FROM TMP_DOCTORANT tmp
       JOIN STRUCTURE s ON s.SOURCE_CODE = /*'COMUE::'||*/tmp.ETABLISSEMENT_ID
       JOIN ETABLISSEMENT e ON e.STRUCTURE_ID = s.ID
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
       JOIN INDIVIDU i ON i.SOURCE_CODE = tmp.INDIVIDU_ID
/

create or replace view SRC_ECOLE_DOCT as
SELECT
  NULL              AS id,
  tmp.SOURCE_CODE   as SOURCE_CODE,
  src.id            AS SOURCE_ID,
  s.ID              as STRUCTURE_ID
FROM TMP_ECOLE_DOCT tmp
       JOIN STRUCTURE s on s.SOURCE_CODE = tmp.STRUCTURE_ID
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create or replace view SRC_ETABLISSEMENT as
SELECT
  NULL              AS id,
  tmp.SOURCE_CODE   as SOURCE_CODE,
  src.id            AS SOURCE_ID,
  s.ID              as STRUCTURE_ID
FROM TMP_ETABLISSEMENT tmp
       JOIN STRUCTURE s on s.SOURCE_CODE = tmp.STRUCTURE_ID
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create or replace view SRC_FINANCEMENT as
SELECT
  NULL                  AS id,
  tmp.SOURCE_CODE       AS SOURCE_CODE,
  src.ID                AS source_id,
  --e.id                  AS etablissement_id,
  t.id                  AS THESE_ID,
  to_number(tmp.ANNEE)  AS ANNEE,
  ofi.id                AS ORIGINE_FINANCEMENT_ID,
  tmp.COMPLEMENT_FINANCEMENT,
  tmp.QUOTITE_FINANCEMENT,
  tmp.DATE_DEBUT_FINANCEMENT as DATE_DEBUT,
  tmp.DATE_FIN_FINANCEMENT as DATE_FIN
FROM TMP_FINANCEMENT tmp
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
       JOIN THESE t on t.SOURCE_CODE = tmp.THESE_ID
       JOIN ORIGINE_FINANCEMENT ofi on substr(ofi.SOURCE_CODE,6/*sans 'UCN::'*/) = substr(tmp.ORIGINE_FINANCEMENT_ID,8/*sans 'COMUE::'*/)
/

create or replace view SRC_INDIVIDU as
SELECT
  NULL                                     AS id,
  tmp.SOURCE_CODE,
  src.id                                   AS SOURCE_ID,
  TYPE,
  SUPANN_ID,
  civ                                      AS CIVILITE,
  lib_nom_usu_ind                          AS NOM_USUEL,
  lib_nom_pat_ind                          AS NOM_PATRONYMIQUE,
  lib_pr1_ind                              AS PRENOM1,
  lib_pr2_ind                              AS PRENOM2,
  lib_pr3_ind                              AS PRENOM3,
  EMAIL,
  dat_nai_per                              AS DATE_NAISSANCE,
  lib_nat                                  AS NATIONALITE
FROM TMP_INDIVIDU tmp
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create or replace view SRC_ROLE as
SELECT
  NULL                       AS id,
  tmp.SOURCE_CODE            AS SOURCE_CODE,
  src.ID                     AS source_id,
  --e.id                       AS etablissement_id,
  tmp.LIB_ROJ                AS libelle,
  to_char(tmp.id)            AS code,
  tmp.LIB_ROJ||' '||s.CODE   AS role_id,
  1                          AS these_dep,
  s.ID                       AS STRUCTURE_ID,
  NULL                       AS TYPE_STRUCTURE_DEPENDANT_ID
FROM TMP_ROLE tmp
       JOIN STRUCTURE s ON s.SOURCE_CODE = /*'COMUE::'||*/tmp.ETABLISSEMENT_ID
       JOIN ETABLISSEMENT e ON e.STRUCTURE_ID = s.ID
       JOIN STRUCTURE s ON s.id = e.STRUCTURE_ID
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create or replace view SRC_STRUCTURE as
SELECT
  NULL              AS id,
  tmp.SOURCE_CODE   as SOURCE_CODE,
  tmp.id            as CODE,
  src.id            AS SOURCE_ID,
  ts.id             as TYPE_STRUCTURE_ID,
  tmp.SIGLE,
  tmp.LIBELLE,
  tmp.CODE_PAYS,
  tmp.LIBELLE_PAYS
FROM TMP_STRUCTURE tmp
       JOIN TYPE_STRUCTURE ts on ts.CODE = tmp.TYPE_STRUCTURE_ID
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create or replace view SRC_THESE as
select
  null                            as id,
  tmp.source_code                 as source_code,
  src.id                          as source_id,
  e.id                            as etablissement_id,
  d.id                            as doctorant_id,
  coalesce(ed_substit.id, ed.id)  as ecole_doct_id,
  coalesce(ur_substit.id, ur.id)  as unite_rech_id,
  ed.id                           as ecole_doct_id_orig,
  ur.id                           as unite_rech_id_orig,
  tmp.lib_ths                     as titre,
  tmp.eta_ths                     as etat_these,
  to_number(tmp.cod_neg_tre)      as resultat,
  tmp.lib_int1_dis                as lib_disc,
  tmp.dat_deb_ths                 as date_prem_insc,
  tmp.annee_univ_1ere_insc        as annee_univ_1ere_insc,
  tmp.dat_prev_sou                as date_prev_soutenance,
  tmp.dat_sou_ths                 as date_soutenance,
  tmp.dat_fin_cfd_ths             as date_fin_confid,
  tmp.lib_etab_cotut              as lib_etab_cotut,
  tmp.lib_pays_cotut              as lib_pays_cotut,
  tmp.correction_possible         as correc_autorisee,
  tem_sou_aut_ths                 as soutenance_autoris,
  dat_aut_sou_ths                 as date_autoris_soutenance,
  tem_avenant_cotut               as tem_avenant_cotut
from tmp_these tmp
       JOIN STRUCTURE s ON s.SOURCE_CODE = /*'COMUE::'||*/tmp.ETABLISSEMENT_ID
       join etablissement e on e.structure_id = s.id
       join source src on src.code = tmp.source_id
       join doctorant d on d.source_code = tmp.doctorant_id

       left join ecole_doct ed on ed.source_code = tmp.ecole_doct_id
       left join unite_rech ur on ur.source_code = tmp.unite_rech_id

       left join structure_substit ss_ed on ss_ed.from_structure_id = ed.structure_id
       left join ecole_doct ed_substit on ed_substit.structure_id = ss_ed.to_structure_id

       left join structure_substit ss_ur on ss_ur.from_structure_id = ur.structure_id
       left join unite_rech ur_substit on ur_substit.structure_id = ss_ur.to_structure_id
/

create or replace view SRC_THESE_ANNEE_UNIV as
SELECT
  NULL                 AS id,
  tmp.SOURCE_CODE   AS SOURCE_CODE,
  src.ID            AS source_id,
  --e.id              AS etablissement_id,
  t.id              AS these_id,
  tmp.ANNEE_UNIV
FROM TMP_THESE_ANNEE_UNIV tmp
       JOIN STRUCTURE s ON s.SOURCE_CODE = /*'COMUE::'||*/tmp.ETABLISSEMENT_ID
       JOIN ETABLISSEMENT e ON e.STRUCTURE_ID = s.ID
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
       JOIN THESE t ON t.SOURCE_CODE = tmp.THESE_ID
/

create or replace view SRC_TITRE_ACCES as
SELECT
  NULL                 AS id,
  tmp.SOURCE_CODE   AS SOURCE_CODE,
  src.ID            AS source_id,
  --e.id              AS etablissement_id,
  t.id              AS these_id,
  tmp.TITRE_ACCES_INTERNE_EXTERNE,
  tmp.LIBELLE_TITRE_ACCES,
  tmp.TYPE_ETB_TITRE_ACCES,
  tmp.LIBELLE_ETB_TITRE_ACCES,
  tmp.CODE_DEPT_TITRE_ACCES,
  tmp.CODE_PAYS_TITRE_ACCES
FROM TMP_TITRE_ACCES tmp
       JOIN STRUCTURE s ON s.SOURCE_CODE = /*'COMUE::'||*/tmp.ETABLISSEMENT_ID
       JOIN ETABLISSEMENT e ON e.STRUCTURE_ID = s.ID
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
       JOIN THESE t ON t.SOURCE_CODE = tmp.THESE_ID
/

create or replace view SRC_UNITE_RECH as
SELECT
  NULL              AS id,
  tmp.SOURCE_CODE   as SOURCE_CODE,
  src.id            AS SOURCE_ID,
  s.ID              as STRUCTURE_ID
FROM TMP_UNITE_RECH tmp
       JOIN STRUCTURE s on s.SOURCE_CODE = tmp.STRUCTURE_ID
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

create or replace view SRC_VARIABLE as
SELECT
  NULL                   AS id,
  tmp.SOURCE_CODE,
  src.ID                 AS SOURCE_ID,
  e.id                   AS ETABLISSEMENT_ID,
  tmp.COD_VAP            AS CODE,
  tmp.lib_vap            AS DESCRIPTION,
  tmp.par_vap            AS VALEUR,
  tmp.DATE_DEB_VALIDITE,
  tmp.DATE_FIN_VALIDITE
FROM TMP_VARIABLE tmp
       JOIN STRUCTURE s ON s.SOURCE_CODE = /*'COMUE::'||*/tmp.ETABLISSEMENT_ID
       JOIN ETABLISSEMENT e ON e.STRUCTURE_ID = s.ID
       JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
/

