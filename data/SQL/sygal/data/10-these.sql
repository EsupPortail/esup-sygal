
create table S_THESE as select * from sodoct.THESE@doctprod;
create table S_METADONNEE_THESE as select * from sodoct.METADONNEE_THESE@doctprod;
create table S_RDV_BU as select * from sodoct.RDV_BU@doctprod;


-- THESE

insert into THESE (
  ID,
  BESOIN_EXPURGE,
  COD_UNIT_RECH,
  CORREC_AUTORISEE,
  DATE_AUTORIS_SOUTENANCE,
  DATE_FIN_CONFID,
  DATE_PREM_INSC,
  DATE_PREV_SOUTENANCE,
  DATE_SOUTENANCE,
  DOCTORANT_ID,
  ECOLE_DOCT_ID,
  ETABLISSEMENT_ID,
  ETAT_THESE,
  HISTO_CREATEUR_ID,
  HISTO_CREATION,
  HISTO_DESTRUCTEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_MODIFICATEUR_ID,
  HISTO_MODIFICATION,
  LIB_DISC,
  LIB_ETAB_COTUT,
  LIB_PAYS_COTUT,
  LIB_UNIT_RECH,
  RESULTAT,
  SOURCE_CODE,
  SOURCE_ID,
  SOUTENANCE_AUTORIS,
  TEM_AVENANT_COTUT,
  TITRE,
  UNITE_RECH_ID
)
  select
    ID,
    BESOIN_EXPURGE,
    COD_UNIT_RECH,
    CORREC_AUTORISEE,
    DATE_AUTORIS_SOUTENANCE,
    DATE_FIN_CONFID,
    DATE_PREM_INSC,
    DATE_PREV_SOUTENANCE,
    DATE_SOUTENANCE,
    THESARD_ID as DOCTORANT_ID,
    ECOLE_DOCT_ID,
    (select e.id from etablissement e join structure s on s.id = e.STRUCTURE_ID where s.CODE = 'UCN') as ETABLISSEMENT_ID,
    ETAT_THESE,
    HISTO_CREATEUR_ID,
    HISTO_CREATION,
    HISTO_DESTRUCTEUR_ID,
    HISTO_DESTRUCTION,
    HISTO_MODIFICATEUR_ID,
    HISTO_MODIFICATION,
    LIB_DISC,
    LIB_ETAB_COTUT,
    LIB_PAYS_COTUT,
    LIB_UNIT_RECH,
    RESULTAT,
    'UCN::' || SOURCE_CODE,
    (select id from source where code = 'UCN::apogee') as SOURCE_ID,
    SOUTENANCE_AUTORIS,
    TEM_AVENANT_COTUT,
    TITRE,
    UNITE_RECH_ID
  from S_THESE
;

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from THESE;
  loop
    select THESE_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/


update these set ECOLE_DOCT_ID = null, UNITE_RECH_ID = null;


-- Vérif

select * from THESE t
  join ETABLISSEMENT e on e.id = t.ETABLISSEMENT_ID
  join DOCTORANT d on d.id = t.DOCTORANT_ID
;



-- METADONNEE_THESE

insert into METADONNEE_THESE (
  ID,
  THESE_ID,
  TITRE,
  LANGUE,
  RESUME,
  RESUME_ANGLAIS,
  MOTS_CLES_LIBRES_FR,
  MOTS_CLES_RAMEAU,
  TITRE_AUTRE_LANGUE,
  MOTS_CLES_LIBRES_ANG
)
  select
    ID,
    THESE_ID,
    TITRE,
    LANGUE,
    RESUME,
    RESUME_ANGLAIS,
    MOTS_CLES_LIBRES_FR,
    MOTS_CLES_RAMEAU,
    TITRE_AUTRE_LANGUE,
    MOTS_CLES_LIBRES_ANG
  from S_METADONNEE_THESE
;

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from METADONNEE_THESE;
  loop
    select METADONNEE_THESE_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/


-- RDV_BU

insert into RDV_BU (
  ID,
  CONVENTION_MEL_SIGNEE,
  COORD_DOCTORANT,
  DISPO_DOCTORANT,
  DIVERS,
  EXEMPL_PAPIER_FOURNI,
  HISTO_CREATEUR_ID,
  HISTO_CREATION,
  HISTO_DESTRUCTEUR_ID,
  HISTO_DESTRUCTION,
  HISTO_MODIFICATEUR_ID,
  HISTO_MODIFICATION,
  MOTS_CLES_RAMEAU,
  THESE_ID,
  VERSION_ARCHIVABLE_FOURNIE
)
  select
    ID,
    CONVENTION_MEL_SIGNEE,
    COORD_DOCTORANT,
    DISPO_DOCTORANT,
    DIVERS,
    EXEMPL_PAPIER_FOURNI,
    HISTO_CREATEUR_ID,
    HISTO_CREATION,
    HISTO_DESTRUCTEUR_ID,
    HISTO_DESTRUCTION,
    HISTO_MODIFICATEUR_ID,
    HISTO_MODIFICATION,
    MOTS_CLES_RAMEAU,
    THESE_ID,
    VERSION_ARCHIVABLE_FOURNIE
  from S_RDV_BU
;

DECLARE
  maxid NUMBER;
  nextval NUMBER;
BEGIN
  select max(id) into maxid from RDV_BU;
  loop
    select RDV_BU_ID_SEQ.nextval into nextval from dual;
    EXIT WHEN maxid < nextval;
  end loop;
END;
/


--

drop table S_THESE ;
drop table S_METADONNEE_THESE ;
