--
-- 9.2.0
--

--
-- Passage à unicaen/renderer 7.0.0
--

-- nouvelle colonne pour le moteur de template
create type template_engine_enum as enum('default', 'laminas', 'twig');
alter table unicaen_renderer_template
    add engine template_engine_enum default 'default' not null;

-- macros utilitaires (pas d'injection de variable nécessaire)
insert into unicaen_renderer_macro (code, description, variable_name, methode_name)
values ('Page#SautDePage', 'Saut de page', '__page', 'getSautDePage');
insert into unicaen_renderer_macro (code, description, variable_name, methode_name)
values ('Log#Date', 'Date du jour', '__log', 'getDate');
insert into unicaen_renderer_macro (code, description, variable_name, methode_name)
values ('Log#DateEtHeure', 'Date et heure du jour', '__log', 'getDateEtHeure');
