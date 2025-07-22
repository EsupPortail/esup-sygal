--
-- 10.3.0
--

update unicaen_privilege_privilege
set libelle = 'Supprimer un rapport n''ayant reçu aucune validation/avis, concernant toute thèse'
where categorie_id = (select id from unicaen_privilege_categorie where code = 'rapport-activite')
  and code = 'supprimer-tout';

update unicaen_privilege_privilege
set libelle = 'Supprimer un rapport n''ayant reçu aucune validation/avis, concernant ses thèses'
where categorie_id = (select id from unicaen_privilege_categorie where code = 'rapport-activite')
  and code = 'supprimer-sien';

INSERT INTO unicaen_privilege_privilege(CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'supprimer-tout-forcer', 'Supprimer un rapport ayant reçu des validations/avis, concernant toute thèse', 610 union all
    SELECT 'supprimer-sien-forcer', 'Supprimer un rapport ayant reçu des validations/avis, concernant ses thèses', 620
)
SELECT cp.id, d.code, d.lib, d.ordre
FROM d JOIN unicaen_privilege_categorie cp ON cp.CODE = 'rapport-activite';

select privilege__grant_privileges_to_profiles('rapport-activite', ARRAY['supprimer-tout-forcer'], ARRAY['ADMIN_TECH']);
select privilege__grant_privileges_to_profiles('rapport-activite', ARRAY['supprimer-sien-forcer'], ARRAY['BDD']);




