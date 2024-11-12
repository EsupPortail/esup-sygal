-- Modification de la requête présente dans 9.0.0/03_saisie_these.sql, afin d'associer le nouveau champ pays_id aux bons pays
UPDATE titre_acces t
SET pays_id = p.id FROM pays p
WHERE CAST (p.code_pays_apogee AS bigint) = CAST (t.code_pays_titre_acces AS BIGINT)
  AND t.code_pays_titre_acces IS NOT NULL
  AND EXISTS (
    SELECT 1 FROM pays p2
    WHERE CAST (p2.code_pays_apogee AS bigint) = CAST (t.code_pays_titre_acces AS BIGINT)
    );