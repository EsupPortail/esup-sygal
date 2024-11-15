--
-- 9.1.0
--

--
-- Modification de la requête présente dans 9.0.0/03_saisie_these.sql, afin d'associer le nouveau champ pays_id aux bons pays
--

UPDATE titre_acces t
SET pays_id = p.id FROM pays p
WHERE CAST (p.code_pays_apogee AS bigint) = CAST (t.code_pays_titre_acces AS BIGINT)
  AND t.code_pays_titre_acces IS NOT NULL
  AND EXISTS (
    SELECT 1 FROM pays p2
    WHERE CAST (p2.code_pays_apogee AS bigint) = CAST (t.code_pays_titre_acces AS BIGINT)
);


--
-- Contrainte de référence modifiée récemment à tort (empêche la suppression d'un manuscrit déposé).
--

alter table validite_fichier drop constraint validite_fichier_ffk;
alter table validite_fichier add constraint validite_fichier_ffk foreign key (fichier_id) references fichier on delete cascade;


--
-- Contrainte de référence modifiée récemment à tort (empêche la suppression d'un rapport CSI).
--

alter table rapport drop constraint rapport_annuel_fichier_fk;
alter table rapport add constraint rapport_annuel_fichier_fk foreign key (fichier_id) references fichier on delete cascade;
