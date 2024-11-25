--
-- 9.2.0
--

--
-- Nouvelle version de unicaen/parametre.
--

alter table unicaen_parametre_parametre add column affichable boolean not null default true;
alter table unicaen_parametre_parametre add column modifiable boolean not null default true;

INSERT INTO unicaen_privilege_privilege(CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'parametre_afficher_masquer', 'Affichage de les paramètres masqués', 15
)
SELECT cp.id, d.code, d.lib, d.ordre
FROM d JOIN unicaen_privilege_categorie cp ON cp.CODE = 'parametre';

select privilege__grant_privilege_to_profile('parametre', 'parametre_afficher_masquer', 'ADMIN_TECH');
