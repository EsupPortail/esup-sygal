
CREATE or replace VIEW V_EXPORT_ALL AS
  with directeurs_these as (
      select distinct
        a.THESE_ID, listagg(i.NOM_USUEL || ' ' || i.PRENOM, ', ') WITHIN GROUP (ORDER BY i.NOM_USUEL) OVER (PARTITION BY a.THESE_ID) noms
      from ACTEUR a
        join USER_ROLE r on a.ROLE_ID = r.id
        join INDIVIDU i on a.INDIVIDU_ID = i.id
      where r.SOURCE_CODE = 'D'
  ), membres_jury as (
      select distinct
        a.THESE_ID, listagg(i.NOM_USUEL || ' ' || i.PRENOM, ', ') WITHIN GROUP (ORDER BY i.NOM_USUEL) OVER (PARTITION BY a.THESE_ID) noms
      from ACTEUR a
        join USER_ROLE r on a.ROLE_ID = r.id
        join INDIVIDU i on a.INDIVIDU_ID = i.id
      where r.SOURCE_CODE = 'M'
  )
  select
    d.CIVILITE                                        as CIVILITE,
    d.NOM_USUEL                                       as NOM_USUEL,
    d.PRENOM                                          as PRENOM,
    d.NOM_PATRONYMIQUE                                as NOM_PATRONYMIQUE,
    d.DATE_NAISSANCE                                  as DATE_NAISSANCE,
    d.TEL                                             as TEL,
    d.EMAIL                                           as EMAIL,
    d.SOURCE_CODE                                     as NUMERO_ETUDIANT,
    t.TITRE                                           as TITRE_APOGEE,
    t.SOURCE_CODE                                     as NUMERO_APOGEE,
    t.DATE_FIN_CONFID                                 as DATE_FIN_CONFID,
    t.DATE_PREM_INSC                                  as DATE_PREM_INSC,
    t.DATE_SOUTENANCE                                 as DATE_SOUTENANCE,
    t.ETAT_THESE                                      as ETAT_THESE,
    t.LIB_DISC                                        as LIB_DISC,
    t.RESULTAT                                        as RESULTAT,
    t.DATE_PREV_SOUTENANCE                            as DATE_PREV_SOUTENANCE,
    t.CORREC_AUTORISEE                                as CORREC_AUTORISEE,
    mt.TITRE                                          as TITRE,
    mt.LANGUE                                         as LANGUE,
    mt.RESUME                                         as RESUME,
    mt.RESUME_ANGLAIS                                 as RESUME_ANGLAIS,
    mt.MOTS_CLES_LIBRES_FR                            as MOTS_CLES_LIBRES_FR,
    mt.TITRE_AUTRE_LANGUE                             as TITRE_AUTRE_LANGUE,
    mt.MOTS_CLES_LIBRES_ANG                           as MOTS_CLES_LIBRES_ANG,
    diff.AUTORIS_MEL                                  as AUTORIS_MEL, --
    diff.AUTORIS_EMBARGO_DUREE                        as EMBARGO_DUREE, --
    rdv.COORD_DOCTORANT                               as COORD_DOCTORANT,
    rdv.DISPO_DOCTORANT                               as DISPO_DOCTORANT,
    rdv.MOTS_CLES_RAMEAU                              as MOTS_CLES_RAMEAU,
    rdv.CONVENTION_MEL_SIGNEE                         as CONVENTION_MEL_SIGNEE,
    rdv.EXEMPL_PAPIER_FOURNI                          as EXEMPL_PAPIER_FOURNI,
    dt.noms                                           as DIRECTEURS_THESE,
    mj.noms                                           as MEMBRES_JURY, --
    ed.LIBELLE                                        as LIBELLE_ED,
    ed.SIGLE                                          as SIGLE_ED,
    ed.SOURCE_CODE                                    as CODE_ED,
    ur.libelle                                        as LIBELLE_UR,
    ur.sigle                                          as SIGLE_UR,
    ur.etab_support                                   as ETAB_SUPPORT_UR,
    ur.autres_etab                                    as AUTRES_ETAB_SUPPORT_UR,
    ur.SOURCE_CODE                                    as CODE_UR,

    decode(avo.EST_VALIDE, 1, 'Oui', 0, 'Non')        as VERSION_ORIG_ARCHIVABLE,
    decode(ava.EST_VALIDE, 1, 'Oui', 0, 'Non')        as VERSION_ARCHI_ARCHIVABLE,
    ava.RETRAITEMENT                                  as VERSION_ARCHI_RETRAIT,
    decode(avoc.EST_VALIDE, 1, 'Oui', 0, 'Non')       as VERSION_ORIG_CORR_ARCHIVABLE,
    decode(avac.EST_VALIDE, 1, 'Oui', 0, 'Non')       as VERSION_ARCHI_CORR_ARCHIVABLE,
    avac.RETRAITEMENT                                 as VERSION_ARCHI_CORR_RETRAIT,

    decode(val_bu.VALIDE, 1, 'Oui', 'Non')            as VALIDATION_BU, --
    decode(dep_pv.FICHIER_ID, null, 'Non', 'Oui')     as PV_SOUTENANCE_DEPOSE, --
    decode(dep_rap.FICHIER_ID, null, 'Non', 'Oui')    as RAPPORT_SOUTENANCE_DEPOSE --

  from these t
    join thesard d on t.THESARD_ID = d.id and d.HISTO_DESTRUCTION is null
    left join METADONNEE_THESE mt on mt.THESE_ID = t.id
    left join DIFFUSION diff on diff.THESE_ID = t.id and diff.HISTO_DESTRUCTION is null
    left join RDV_BU rdv on rdv.THESE_ID = t.id and rdv.HISTO_DESTRUCTION is null
    left join directeurs_these dt on dt.THESE_ID = t.id
    left join membres_jury mj on mj.THESE_ID = t.id
    left join ECOLE_DOCT ed on t.ECOLE_DOCT_ID = ed.id and ed.HISTO_DESTRUCTION is null
    left join unite_rech ur on t.UNITE_RECH_ID = ur.id and ur.HISTO_DESTRUCTION is null
    left join V_SITU_ARCHIVAB_VO avo on avo.THESE_ID = t.id
    left join V_SITU_ARCHIVAB_VOC avoc on avoc.THESE_ID = t.id
    left join V_SITU_ARCHIVAB_VA ava on ava.THESE_ID = t.id
    left join V_SITU_ARCHIVAB_VAC avac on avac.THESE_ID = t.id
    left join V_SITU_RDV_BU_VALIDATION_BU val_bu on val_bu.THESE_ID = t.id
    left join V_SITU_DEPOT_PV_SOUT dep_pv on dep_pv.THESE_ID = t.id
    left join V_SITU_DEPOT_RAPPORT_SOUT dep_rap on dep_rap.THESE_ID = t.id
  where t.HISTO_DESTRUCTION is null
;



select * from v_export_all;
