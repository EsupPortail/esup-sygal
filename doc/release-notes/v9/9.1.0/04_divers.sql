--
-- 9.1.0
--

--
-- Contrainte de référence ajoutée récemment à tort (empêche la suppression d'un manuscrit déposé).
--

alter table validite_fichier drop constraint validite_fichier_ffk;
