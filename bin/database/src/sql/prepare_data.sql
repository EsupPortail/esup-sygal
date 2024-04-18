--
-- PREPARATION des données insérées.
--

--
-- Dans certaines tables, on supprime les données dont l'auteur n'existe pas dans une bdd neuve.
--

delete from soutenance_etat                 where histo_createur_id <> 1 or histo_modificateur_id <> 1; -- 1 = pseudo-utilisateur SyGAL
delete from soutenance_qualite              where histo_createur_id <> 1 or histo_modificateur_id <> 1;
delete from soutenance_qualite_sup          where histo_createur_id <> 1 or histo_modificateur_id <> 1;
delete from soutenance_qualite_sup          where not exists (select * from soutenance_qualite q where qualite_id = q.id);


--
-- Dans certaines tables, on écrase l'auteur avec celui par défaut.
--

update soutenance_etat                      set histo_createur_id = 1, histo_creation = current_timestamp, histo_modificateur_id = null, histo_modification = null;
update soutenance_qualite                   set histo_createur_id = 1, histo_creation = current_timestamp, histo_modificateur_id = null, histo_modification = null;
update soutenance_qualite_sup               set histo_createur_id = 1, histo_creation = current_timestamp, histo_modificateur_id = null, histo_modification = null;
update formation_enquete_categorie          set histo_createur_id = 1, histo_creation = current_timestamp, histo_modificateur_id = null, histo_modification = null;
update formation_enquete_question           set histo_createur_id = 1, histo_creation = current_timestamp, histo_modificateur_id = null, histo_modification = null;
update pays                                 set histo_createur_id = 1, histo_creation = current_timestamp, histo_modificateur_id = null, histo_modification = null;
