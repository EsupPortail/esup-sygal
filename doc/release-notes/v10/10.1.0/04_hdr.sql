-- Suppression des paramètres inutilisés du module HDR

DELETE FROM unicaen_parametre_parametre
    USING unicaen_parametre_categorie
WHERE unicaen_parametre_parametre.categorie_id = unicaen_parametre_categorie.id
  AND unicaen_parametre_categorie.code = 'SOUTENANCE_HDR' and unicaen_parametre_parametre.code in ('DOC_CONFIDENTIALITE', 'DOC_DELEGATION_SIGNATURE', 'DOC_DELOCALISATION', 'DOC_REDACTION_ANGLAIS');
