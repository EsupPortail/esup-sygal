--
-- Module Doctorant
--

select privilege__grant_privileges_to_profiles('doctorant', 'consulter-sien', 'DOCTORANT');
select privilege__grant_privileges_to_profiles('doctorant', 'consulter-sien', 'DOCTORANT');
select privilege__grant_privileges_to_profiles('doctorant', ARRAY['lister-tout', 'consulter-tout'], ARRAY['ADMIN_TECH','BDD','BU']);
select privilege__grant_privileges_to_profiles('doctorant', ARRAY['lister-sien', 'consulter-sien'], ARRAY['D','K','GEST_ED','GEST_UR','RESP_ED','RESP_UR']);
