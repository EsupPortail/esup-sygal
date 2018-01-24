--
-- Recherche de fichiers.
--
select f.* from fichier f
  join NATURE_FICHIER nf on nf.id = f.NATURE_ID
  join VERSION_FICHIER vf on vf.id = f.VERSION_FICHIER_ID and vf.CODE = 'VOC'
where f.THESE_ID = 27851;

--
-- Recherche de résultat de test d'archivabilité.
--
select v.* from VALIDITE_FICHIER v
  join fichier f on f.id = v.fichier_id
  join NATURE_FICHIER nf on nf.id = f.NATURE_ID
  join VERSION_FICHIER vf on vf.id = f.VERSION_FICHIER_ID and vf.CODE = 'VOC'
where f.THESE_ID = 27851;

--
-- Création d'un fichier de test.
--
INSERT INTO FICHIER (
  ID,
  THESE_ID,
  VERSION_FICHIER_ID,
  NOM,
  TYPE,
  TAILLE,
  CONTENU,
  DESCRIPTION,
  EST_ANNEXE,
  EST_DEUXIEME_DEPOT,
  HISTO_CREATEUR_ID,
  HISTO_MODIFICATEUR_ID
)
select fichier_id_seq.nextval,
  1,
  ver.id,
  'these.pdf',
  'application/pdf',
  3212141,
  utl_raw.cast_to_raw('hello, this is the first review'),
  'Le PDF de ma thèse!',
  0,
  0,
  u.id,
  u.id
from VERSION_FICHIER ver, UTILISATEUR u
where ver.code = 'VA'
and U.USERNAME = 'sodoct';
