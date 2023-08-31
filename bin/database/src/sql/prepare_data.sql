--
-- PREPARATION des données insérées.
--

--
-- Dans certaines tables, on supprime les données dont l'auteur n'existe pas dans une bdd neuve.
--

delete from soutenance_etat                 where histo_createur_id <> 1 or histo_modificateur_id <> 1; -- 1 = pseudo-utilisateur SyGAL
delete from soutenance_qualite              where histo_createur_id <> 1 or histo_modificateur_id <> 1;


--
-- Dans certaines tables, on écrase l'auteur avec celui par défaut.
--

update soutenance_etat                      set histo_createur_id = 1, histo_creation = default, histo_modificateur_id = null, histo_modification = null;
update soutenance_qualite                   set histo_createur_id = 1, histo_creation = default, histo_modificateur_id = null, histo_modification = null;
update formation_enquete_categorie          set histo_createur_id = 1, histo_creation = default, histo_modificateur_id = null, histo_modification = null;
update formation_enquete_question           set histo_createur_id = 1, histo_creation = default, histo_modificateur_id = null, histo_modification = null;
update pays                                 set histo_createur_id = 1, histo_creation = default, histo_modificateur_id = null, histo_modification = null;
