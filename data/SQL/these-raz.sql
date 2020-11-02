--
-- Remise à zéro des saisies concernant une thèse.
--

declare
  theseid NUMBER := 27403;
begin
  delete from ATTESTATION where THESE_ID = theseid;
  delete from DIFFUSION where these_id = theseid;
  delete from FICHIER where these_id = theseid; -- delete cascade VALIDITE_FICHIER
  delete from METADONNEE_THESE where these_id = theseid;
  delete from RDV_BU where these_id = theseid;
  delete from VALIDATION where these_id = theseid;
  delete from DOCTORANT_COMPL where DOCTORANT_ID = (
    select DOCTORANT_ID from THESE where id = theseid
  );
  delete from MAIL_CONFIRMATION where INDIVIDU_ID = (
    select d.INDIVIDU_ID
    from THESE t
    join DOCTORANT d on d.id = t.DOCTORANT_ID
    where t.id = theseid
  );
end;
/
