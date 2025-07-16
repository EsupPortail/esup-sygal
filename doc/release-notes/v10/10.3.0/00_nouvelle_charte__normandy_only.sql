--
-- Nouvelle charte
--

--  
-- UNIQUEMENT POUR NOUS --  
-- Nouveau fichier : nouvelle charte de depot et diffusion
--
update fichier set permanent_id = 'CHARTE_DEPOT_DIFFUSION_THESE_2018' where permanent_id = 'CHARTE_DEPOT_DIFFUSION_THESE';
-- ATTENTION :
--   Pensez ensuite à aller dans "Administration > Fichiers communs" pour déposer le fichier de la nouvelle charte, 
--   puis de lui attribuer l'id permanent 'CHARTE_DEPOT_DIFFUSION_THESE' manuellement en bdd.
