--
-- 9.2.0
--

--
-- Nouvelle version de unicaen/parametre.
--

alter table unicaen_parametre_parametre add column affichable boolean not null default true;
alter table unicaen_parametre_parametre add column modifiable boolean not null default true;