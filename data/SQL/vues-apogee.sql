--
-- Données APOGEE attendues.
--


----------------------------- SOURCE ------------------------------

create or replace view APOGEE.OBJECTH_SOURCE as
  select
    'apogee' as id,
    'apogee' as code,
    'Apogée' as libelle,
    1        as importable
  from dual
;


----------------------------- VARIABLE ------------------------------

create or replace view APOGEE.OBJECTH_VARIABLE AS
  select
    'apogee'            as source_id,       -- Id de la source
    cod_vap             as id,
    cod_vap,
    lib_vap,
    par_vap,
    to_date('2017-01-01', 'YYYY-MM-DD') as DATE_DEB_VALIDITE,
    to_date('9999-12-31', 'YYYY-MM-DD') as DATE_FIN_VALIDITE
  from variable_appli
  where cod_vap in (
    'ETB_LIB',
    'ETB_ART_ETB_LIB',
    'ETB_LIB_TIT_RESP',
    'ETB_LIB_NOM_RESP'
  )
  union all
  select
    'apogee' as source_id,
    'EMAIL_ASSISTANCE' as id,
    'EMAIL_ASSISTANCE' as cod_vap,
    'Adresse mail de l''assistance utilisateur' as lib_vap,
    'assistance-sygal@unicaen.fr' as par_vap,
    to_date('2017-01-01', 'YYYY-MM-DD') as DATE_DEB_VALIDITE,
    to_date('9999-12-31', 'YYYY-MM-DD') as DATE_FIN_VALIDITE
  from dual
  union all
  select
    'apogee' as source_id,
    'EMAIL_BU' as id,
    'EMAIL_BU' as cod_vap,
    'Adresse mail de contact de la BU' as lib_vap,
    'scd.theses@unicaen.fr' as par_vap,
    to_date('2017-01-01', 'YYYY-MM-DD') as DATE_DEB_VALIDITE,
    to_date('9999-12-31', 'YYYY-MM-DD') as DATE_FIN_VALIDITE
  from dual
  union all
  select
    'apogee' as source_id,
    'EMAIL_BDD' as id,
    'EMAIL_BDD' as cod_vap,
    'Adresse mail de contact du bureau des doctorats' as lib_vap,
    'recherche.doctorat@unicaen.fr' as par_vap,
    to_date('2017-01-01', 'YYYY-MM-DD') as DATE_DEB_VALIDITE,
    to_date('9999-12-31', 'YYYY-MM-DD') as DATE_FIN_VALIDITE
  from dual;


----------------------------- INDIVIDU ------------------------------

create or replace view APOGEE.OBJECTH_INDIVIDU as
  -- doctorants
  select distinct
    'apogee'                                            as source_id,       -- Id de la source
    'doctorant'                                         as type,
    to_char(ind.cod_etu)                                as id,              -- Numero etudiant
    decode(ind.cod_civ, 1, 'M.', 'Mme')                 as civ,             -- Civilite etudiant
    ind.lib_nom_pat_ind                                 as lib_nom_pat_ind, -- Nom de famille etudiant
    coalesce(ind.lib_nom_usu_ind, ind.lib_nom_pat_ind)  as lib_nom_usu_ind, -- Nom usage etudiant
    initcap(coalesce(ind.lib_pr1_ind,'Aucun'))          as lib_pr1_ind,     -- Prenom 1 etudiant
    initcap(ind.lib_pr2_ind)                            as lib_pr2_ind,     -- Prenom 2 etudiant
    initcap(ind.lib_pr3_ind)                            as lib_pr3_ind,     -- Prenom 3 etudiant
    null as email,--ucbn_ldap.etu2mail ( ind.cod_etu )                  as email,           -- Mail etudiant
    --null                                                as num_tel_ind,     -- Téléphone
    ind.date_nai_ind                                    as date_nai_ind,    -- Date naissance etudiant
    ind.cod_pay_nat                                     as cod_pay_nat,     -- Code nationalite
    pay.lib_nat                                         as lib_nat          -- Libelle nationalite
  from these_hdr_sout ths
    join diplome        dip on dip.cod_dip     = ths.cod_dip
    join typ_diplome    tpd on tpd.cod_tpd_etb = dip.cod_tpd_etb
    join individu       ind on ind.cod_ind     = ths.cod_ind --and ind.cod_etu != 21009539 -- Exclusion du compte de test Aaron AAABA
    join pays           pay on pay.cod_pay     = ind.cod_pay_nat
  where ths.cod_ths_trv     =  '1'  --  Exclusion des travaux
        and dip.cod_tpd_etb     in ( '39', '40' )
        and tpd.eta_ths_hdr_drt =  'T'  -- Inscription en these
        and tpd.tem_sante       =  'N'  -- Exclusion des theses d exercice
        and ind.cod_etu is not null         -- oui, oui, ça arrive
  union
  -- acteurs
  select "SOURCE_ID","TYPE","ID","CIV","LIB_NOM_USU_IND","LIB_NOM_PAT_IND","LIB_PR1_IND","LIB_PR2_IND","LIB_PR3_IND","EMAIL","DATE_NAI_IND","COD_PAY_NAT","LIB_NAT" from (
    with acteur as (
      select
        ths.cod_ths,
        'D'              as cod_roj,
        ths.cod_per_dir  as cod_per,
        ths.cod_etb_dir  as cod_etb,
        ths.cod_cps_dir  as cod_cps,
        null             as tem_rap_recu,
        null             as cod_roj_compl
      from these_hdr_sout ths
      where ths.cod_ths_trv = '1' and ths.cod_per_dir is not null
      union
      select
        ths.cod_ths,
        'D'              as cod_roj,
        ths.cod_per_cdr  as cod_per,
        ths.cod_etb_cdr  as cod_etb,
        ths.cod_cps_cdr  as cod_cps,
        null             as tem_rap_recu,
        null             as cod_roj_compl
      from these_hdr_sout ths
      where ths.cod_ths_trv = '1' and ths.cod_per_cdr is not null
      union
      select
        ths.cod_ths,
        'D'              as cod_roj,
        ths.cod_per_cdr2 as cod_per,
        ths.cod_etb_cdr2 as cod_etb,
        ths.cod_cps_cdr2 as cod_cps,
        null             as tem_rap_recu,
        null             as cod_roj_compl
      from these_hdr_sout ths
      where ths.cod_ths_trv = '1' and ths.cod_per_cdr2 is not null
      union
      select
        trs.cod_ths,
        'R'              as cod_roj,
        trs.cod_per,
        null             as cod_etb,
        null             as cod_cps,
        trs.tem_rap_recu,
        null             as cod_roj_compl
      from ths_rap_sou trs
      union
      select
        tjp.cod_ths,
        'M'              as cod_roj,
        tjp.cod_per,
        tjp.cod_etb,
        tjp.cod_cps,
        null             as tem_rap_recu,
        case when tjp.cod_roj in ( 'P', 'B', 'A' ) then tjp.cod_roj else null end as cod_roj_compl
      from ths_jur_per tjp
    )
    select distinct
      'apogee'                                                                                                as source_id,
      'acteur'                                                                                                as type,
      coalesce(regexp_replace(per.num_dos_har_per,'[^0-9]',''), 'COD_PER_'||act.cod_per)                      as id,     -- Code Harpege ou Apogee de l acteur
      --act.cod_per                                                                                             as id,     -- Code Apogee de l acteur
      initcap(per.cod_civ_per)                                                                                as civ,             -- Civilite acteur
      --regexp_replace ( per.num_dos_har_per, '[^0-9]', '' )                                                    as uid_per,         -- uid de l acteur
      per.lib_nom_usu_per                                                                                     as lib_nom_usu_ind, -- Nom d'usage acteur
      per.LIB_NOM_PAT_PER                                                                                     as lib_nom_pat_ind, -- Nom de famille acteur
      per.lib_pr1_per                                                                                         as lib_pr1_ind,     -- Prenom 1 acteur
      null                                                                                                    as lib_pr2_ind,     -- Prenom 2 acteur
      null                                                                                                    as lib_pr3_ind,     -- Prenom 3 acteur
      null as email,--case when per.num_dos_har_per is null then null else ucbn_ldap.uid2mail ('p'||per.num_dos_har_per) end  as email,           -- Mail acteur
      --regexp_replace ( per.num_tel_per, '[^0-9]', '' )                                                        as num_tel_ind,      -- Telephone acteur
      per.dat_nai_per                                                                                         as date_nai_ind,    -- Date naissance acteur
      null                                                                                                    as cod_pay_nat,     -- Code nationalite
      null                                                                                                    as lib_nat          -- Libelle nationalite
    from acteur               act
      join role_jury            roj on roj.cod_roj = act.cod_roj
      join personnel            per on per.cod_per = act.cod_per
    --left join corps_per       cps on cps.cod_cps = nvl ( act.cod_cps, per.cod_cps )
    --left join etablissement   etb on etb.cod_etb = nvl ( act.cod_etb, per.cod_etb )
    --left join role_jury       rjc on rjc.cod_roj = act.cod_roj_compl
  )
;


----------------------------- ROLE ------------------------------

create or replace view APOGEE.OBJECTH_ROLE as
  select
    'apogee' as source_id, -- Id de la source
    COD_ROJ as id,
    LIB_ROJ,
    LIC_ROJ
  from role_jury;


----------------------------- DOCTORANT ------------------------------

create or replace view APOGEE.OBJECTH_DOCTORANT as
  select distinct
    'apogee' as source_id, -- Id de la source
    ind.cod_etu                                         as id,            -- Identifiant du doctorant
    ind.cod_etu                                         as individu_id    -- Identifiant de l'individu
  /*  decode(ind.cod_civ, 1, 'M.', 'Mme')                as civ_etu,         -- Civilite etudiant
    ind.lib_nom_pat_ind,                                                   -- Nom de famille etudiant
    coalesce(ind.lib_nom_usu_ind, ind.lib_nom_pat_ind) as lib_nom_usu_ind, -- Nom usage etudiant
    initcap(ind.lib_pr1_ind) lib_pr1_ind,                                  -- Prenom etudiant
    initcap(ind.lib_pr2_ind) lib_pr2_ind,                                  -- Prenom etudiant
    initcap(ind.lib_pr3_ind) lib_pr3_ind,                                  -- Prenom etudiant
    ucbn_ldap.etu2mail ( ind.cod_etu )                 as mail_etu,        -- Mail etudiant
    ind.date_nai_ind,                                                      -- Date naissance etudiant
    ind.cod_pay_nat,                                                       -- Code nationalite
    pay.lib_nat                                                            -- Libelle nationalite*/
  from these_hdr_sout ths
    join diplome        dip on dip.cod_dip     = ths.cod_dip
    join typ_diplome    tpd on tpd.cod_tpd_etb = dip.cod_tpd_etb
    join individu       ind on ind.cod_ind     = ths.cod_ind --and ind.cod_etu != 21009539 -- Exclusion du compte de test Aaron AAABA
    join pays           pay on pay.cod_pay     = ind.cod_pay_nat
  where ths.cod_ths_trv     =  '1'  --  Exclusion des travaux
        and dip.cod_tpd_etb     in ( '39', '40' )
        and tpd.eta_ths_hdr_drt =  'T'  -- Inscription en these
        and tpd.tem_sante       =  'N'  -- Exclusion des theses d exercice
        and cod_etu is not null         -- oui, oui, ça arrive
;


----------------------------- THESE ------------------------------

create or replace view APOGEE.OBJECTH_THESE as
  with inscription_administrative as (
      select
        iae.cod_ind,
        iae.cod_dip,
        iae.cod_vrs_vdi,
        dip.lib_dip,
        max ( iae.cod_anu ) cod_anu_der_iae
      from ins_adm_etp iae
        join diplome     dip on dip.cod_dip     = iae.cod_dip
        join typ_diplome tpd on tpd.cod_tpd_etb = dip.cod_tpd_etb
      where iae.eta_iae         =  'E'  -- Inscription administrative non annulee
            and iae.eta_pmt_iae     =  'P'  -- Inscription administrative payee
            and dip.cod_tpd_etb     in ( '39', '40' )
            and tpd.eta_ths_hdr_drt =  'T'  -- Inscription en these
            and tpd.tem_sante       =  'N'  -- Exclusion des theses d exercice
      group by
        iae.cod_ind,
        iae.cod_dip,
        iae.cod_vrs_vdi,
        dip.lib_dip
  ),
      hierarchie_structures as (
        select
          cod_cmp_inf,
          cod_cmp_sup
        from cmp_cmp
        where connect_by_isleaf = 1
        connect by prior cod_cmp_sup = cod_cmp_inf
    )
  select
    'apogee' as source_id, -- Id de la source
    --
    -- -------- Enregistrement de la these --------
    --
    ths.cod_ths as id,                       -- Identifiant de la these
    case when ths.eta_ths = 'S' and nvl ( ths.dat_sou_ths, sysdate + 1 ) > sysdate
      then 'E' else ths.eta_ths end eta_ths,          -- Etat de la these ( E=En cours / A=Abandonnee / S=Soutenue / U=Transferee )
    ind.cod_etu                     doctorant_id,     -- Identifiant du doctorant
    --iae.cod_dip,                                      -- Code diplome
    --iae.cod_vrs_vdi,                                  -- Version de diplome
    --nvl ( vdi.lib_web_vdi, iae.lib_dip ) lib_web_vdi, -- Libelle version de diplome
    ths.cod_dis,                                      -- Code discipline
    dis.lib_int1_dis,                                 -- Libellé discipline
    ths.lib_ths,                                      -- Titre de la these
    ths.cod_lng,                                      -- Code langue etrangere du titre
    --lng.lib_lng,                                      -- Libelle langue etrangere du titre
    --lng.lib_nls_lng,                                  -- Parametre Oracle NLS_LANG
    --ths.lib_ths_lng,                                  -- Titre de la these dans la langue etrangere
    ths.dat_deb_ths,                                  -- Date de 1ere inscription
    --iae.cod_anu_der_iae,                              -- Code annee de derniere inscription
    --ths.daa_fin_ths,                                  -- Code annee previsionnelle de soutenance
    --ans.lib_anu lib_anu_fin_ths,                      -- Libelle annee previsionnelle de soutenance
    --ths.cod_edo,                                      -- Code ecole doctorale
    edo.cod_nat_edo,                                  -- Identifiant national ecole doctorale
    --edo.lib_edo,                                      -- Denomination ecole doctorale
    --ths.cod_ser,                                      -- Code secteur de recherche principal
    --ser.lib_ser,                                      -- Denomination secteur de recherche principal
    ths.cod_eqr,                                      -- Code unite de recherche principale
    --eqr.lib_eqr,                                      -- Denomination unite de recherche principale
    --ths.lib_cmt_ths,                                  -- Informations complementaires sur la these
    --
    -- ----------------------------- Cotutelle -----------------------------
    --
    --ths.tem_cot_ths,                                  -- Cotutelle (O/N)
    --ths.lib_cmt_cot_ths,                              -- Descriptif cotutelle
    --ths.cod_pay,                                      -- Code pays de cotutelle
    pay.lib_pay,                                      -- Denomination pays de cotutelle
    --ths.cod_etb cod_etb_cot,                          -- Code etablissement de cotutelle
    nvl ( etb.lib_web_etb, etb.lib_etb ) lib_etb_cot, -- Denomination etablissement de cotutelle
    --ths.dat_sign_cnv,                                 -- Date de signature de la convention de cotutelle
    ths.tem_avenant,                                  -- Avenant a la convention de cotutelle (O/N)
    --ths.tem_etb_sou,                                  -- Soutenance dans l etablissement d inscription (V) ou dans l etablissement de cotutelle (E)
    --ths.lib_cmt_compl,                                -- Info complementaire sur cotutelle
    --
    -- -------- Abandon ou transferts --------
    --
    --ths.dat_abandon,                                  -- Date d abandon de la these
    --ths.dat_transfert_dep,                            -- Date de transfert depart
    --ths.tem_transfert_arr,                            -- Transfert arrivee (O/N)
    --ths.dat_deb_ths_ori,                              -- Date de debut de la these dans l etablissement d origine
    --ths.cod_etb_origine,                              -- Code etablissement d origine
    --nvl ( ori.lib_web_etb, ori.lib_etb ) lib_etb_origine, -- Denomination etablissement d origine
    --
    -- -------- Expertise des rapporteurs --------
    --
    --ths.dat_des_rap_ths,                              -- Date de designation des rapporteurs
    --
    -- -------- Organisation de la soutenance --------
    --
    --ths.duree_ths,                                    -- Duree de la these en mois
    --ths.eta_duree_ths,                                -- Etat de la duree de la these ( M=Modifiee? / C=Calculee? )
    ths.dat_prev_sou,                                 -- Date previsionnelle de soutenance
    ths.tem_sou_aut_ths,                              -- Soutenance autorisee (O/N/null)
    ths.dat_aut_sou_ths,                              -- Date d autorisation de soutenance
    --ths.lib_cmt_sou_aut_ths,                          -- Commentaire associe a la non autorisation de soutenance
    --ths.lib_cmt_leu_sou_ths,                          -- Lieu de la soutenance
    --ths.cod_etb_sou,                                  -- Code etablissement du lieu de soutenance
    --nvl ( sou.lib_web_etb, sou.lib_etb ) lib_etb_sou, -- Denomination etablissement du lieu de soutenance
    ths.dat_sou_ths,                                  -- Date de soutenance de la these
    --ths.hh_sou_ths,                                   -- Heure de soutenance (hh)
    --ths.mm_sou_ths,                                   -- Heure de soutenance (mi)
    --cmp.cod_cmp,                                      -- Code composante
    --cmp.lib_web_cmp,                                  -- Libelle composante
    --ths.tem_aut_etb_sou_ths,                          -- Soutenance dans autre etablissement si cotutelle (O/N)
    --
    -- -------- Confidentialite --------
    --
    --ths.tem_pub_sou_ths,                              -- Soutenance publique (O/N)
    --ths.lib_cmt_pub_sou_ths,                          -- Commentaire associe a la confidentialite de la these
    ths.dat_fin_cfd_ths,                              -- Date de fin de confidentialite de la these
    --
    -- -------- Jury et resultats --------
    --
    --ths.dat_des_jur_ths,                              -- Date de designation du jury
    -- rvi.cod_anu cod_anu_rvi,                          -- Code annee universitaire du resultat
    --anr.lib_anu lib_anu_rvi,                          -- Libelle annee universitaire du resultat
    tre.cod_neg_tre,                                    -- Resultat positif (1) ou non (0)
    --rvi.cod_tre,                                      -- Code resultat
    --tre.lib_tre,                                      -- Libelle resultat
    --rvi.cod_men,                                      -- Code mention
    --men.lib_men,                                      -- Libelle mention
    --ths.tem_lab_eur_ths,                              -- Label europeen (O/N)
    ths.eta_rpd_ths,                                  -- Reproduction de la these ( O=Oui / C=Oui avec corrections / N=Non )
    decode(ths.eta_rpd_ths, 'N', 'majeure', 'C', 'mineure', null) as correction_possible
  --ths.tem_cor_ths,                                  -- Corrections effectuees (O/N)
  --ths.tem_pv_transmis,                              -- PV de soutenance transmis (O/N)
  --ths.tem_rap_transmis,                             -- Rapport de soutenance transmis (O/N)
  --ths.tem_stop_mvt_abes                             -- Aucun mouvement ne doit etre genere vers l ABES (O/N)
  from inscription_administrative iae
    join individu                   ind on ind.cod_ind = iae.cod_ind
    join version_diplome            vdi on vdi.cod_dip = iae.cod_dip and vdi.cod_vrs_vdi = iae.cod_vrs_vdi
    join these_hdr_sout             ths on ths.cod_ind = iae.cod_ind and ths.cod_dip = iae.cod_dip and ths.cod_vrs_vdi = iae.cod_vrs_vdi
    left join annee_uni             ans on ans.cod_anu = ths.daa_fin_ths
    left join ecole_doctorale       edo on edo.cod_edo = ths.cod_edo
    left join secteur_rch           ser on ser.cod_ser = ths.cod_ser
    left join equipe_rch            eqr on eqr.cod_eqr = ths.cod_eqr
    left join resultat_vdi          rvi on rvi.cod_ind = iae.cod_ind and rvi.cod_dip = iae.cod_dip and rvi.cod_vrs_vdi = iae.cod_vrs_vdi and rvi.cod_ses = '0' and rvi.cod_adm = '1' and rvi.cod_tre is not null
    left join annee_uni             anr on anr.cod_anu = rvi.cod_anu
    left join typ_resultat          tre on tre.cod_tre = rvi.cod_tre
    left join mention               men on men.cod_men = rvi.cod_men
    left join hierarchie_structures ccm on ccm.cod_cmp_inf = ths.cod_cmp
    left join composante            cmp on cmp.cod_cmp = nvl ( ccm.cod_cmp_sup, ths.cod_cmp )
    left join diplome_sise          dis on dis.cod_dis = ths.cod_dis
    left join etablissement         etb on etb.cod_etb = ths.cod_etb
    left join pays                  pay on pay.cod_pay = ths.cod_pay
    left join etablissement         sou on sou.cod_etb = ths.cod_etb_sou
    left join etablissement         ori on ori.cod_etb = ths.cod_etb_origine
    left join langue                lng on lng.cod_lng = ths.cod_lng
  where ths.cod_ths_trv = '1'     --  Exclusion des travaux
;


----------------------------- ACTEUR ------------------------------

create or replace view APOGEE.OBJECTH_ACTEUR as
  with acteur as (
    select
      ths.cod_ths,
      'D'              as cod_roj,
      ths.cod_per_dir  as cod_per,
      ths.cod_etb_dir  as cod_etb,
      ths.cod_cps_dir  as cod_cps,
      null             as tem_rap_recu,
      null             as cod_roj_compl
    from these_hdr_sout ths
    where ths.cod_ths_trv = '1' and ths.cod_per_dir is not null
    union
    select
      ths.cod_ths,
      'D'              as cod_roj,
      ths.cod_per_cdr  as cod_per,
      ths.cod_etb_cdr  as cod_etb,
      ths.cod_cps_cdr  as cod_cps,
      null             as tem_rap_recu,
      null             as cod_roj_compl
    from these_hdr_sout ths
    where ths.cod_ths_trv = '1' and ths.cod_per_cdr is not null
    union
    select
      ths.cod_ths,
      'D'              as cod_roj,
      ths.cod_per_cdr2 as cod_per,
      ths.cod_etb_cdr2 as cod_etb,
      ths.cod_cps_cdr2 as cod_cps,
      null             as tem_rap_recu,
      null             as cod_roj_compl
    from these_hdr_sout ths
    where ths.cod_ths_trv = '1' and ths.cod_per_cdr2 is not null
    union
    select
      trs.cod_ths,
      'R'              as cod_roj,
      trs.cod_per,
      null             as cod_etb,
      null             as cod_cps,
      trs.tem_rap_recu,
      null             as cod_roj_compl
    from ths_rap_sou trs
    union
    select
      tjp.cod_ths,
      'M'              as cod_roj,
      tjp.cod_per,
      tjp.cod_etb,
      tjp.cod_cps,
      null             as tem_rap_recu,
      case when tjp.cod_roj in ( 'P', 'B', 'A' ) then tjp.cod_roj else null end as cod_roj_compl
    from ths_jur_per tjp
  )
  select distinct
    rownum                                                                        as id,
    'apogee'                                                                      as source_id,     -- Id de la source
    act.cod_ths                                                                   as these_id,      -- Identifiant de la these
    roj.cod_roj                                                                   as role_id,       -- Identifiant du rôle
    cast(act.cod_roj_compl as varchar2(1 char))                                   as cod_roj_compl, -- Code du complement sur le role dans le jury
    rjc.lib_roj                                                                   as lib_roj_compl, -- Libelle du complement sur le role dans le jury
    coalesce(regexp_replace(per.num_dos_har_per,'[^0-9]',''), 'COD_PER_'||act.cod_per) as individu_id, -- Code Harpege ou Apogee de l acteur
    act.cod_etb,                                                                                    -- Code etablissement
    etb.lib_etb,                                                                                    -- Libelle etablissement
    cps.cod_cps,                                                                                    -- Code du corps d'appartenance
    cps.lib_cps,                                                                                    -- Libelle du corps d'appartenance
    per.tem_hab_rch_per,                                                                            -- HDR (O/N)
    act.tem_rap_recu                                                                                -- Rapport recu (O/N)
  from acteur               act
    join role_jury            roj on roj.cod_roj = act.cod_roj
    join personnel            per on per.cod_per = act.cod_per
    left join corps_per       cps on cps.cod_cps = nvl ( act.cod_cps, per.cod_cps )
    left join etablissement   etb on etb.cod_etb = nvl ( act.cod_etb, per.cod_etb )
    left join role_jury       rjc on rjc.cod_roj = act.cod_roj_compl
;
