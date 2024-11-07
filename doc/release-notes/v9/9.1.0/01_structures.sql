--
-- 9.1.0
--

--
-- Suppression de la colonne etablissement.est_membre
--   redondante avec est_etab_inscription et non pertinente si aucune COMUE.
--

alter table etablissement drop column est_membre;


--
-- Renommage oublié du profil "Bureau des doctorats".
--

update profil set libelle = 'Maison du doctorat' where role_id = 'BDD';
