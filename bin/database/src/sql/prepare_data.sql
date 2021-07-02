--
-- PREPARATION des données insérées.
--

-- Suppression des données dont l'utilisateur créateur/modificateur n'existera pas dans la nouvelle base :
delete from soutenance_etat    where histo_createur_id <> 1 or histo_modificateur_id <> 1; -- 1 = SyGAL
delete from soutenance_qualite where histo_createur_id <> 1 or histo_modificateur_id <> 1;
