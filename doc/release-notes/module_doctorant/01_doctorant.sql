--
-- Module Doctorant
--

select privilege__grant_privileges_to_profiles('doctorant', ARRAY['consulter-sien'], ARRAY['DOCTORANT']);
select privilege__grant_privileges_to_profiles('doctorant', ARRAY['lister-tout', 'consulter-tout'], ARRAY['ADMIN_TECH','BDD','BU','GEST_FORM']);
select privilege__grant_privileges_to_profiles('doctorant', ARRAY['lister-sien', 'consulter-sien'], ARRAY['GEST_ED','GEST_UR','RESP_ED','RESP_UR']);
select privilege__grant_privileges_to_profiles('doctorant', ARRAY['lister-sien', 'consulter-sien'], ARRAY['D','K']);
-- select privilege__revoke_privileges_to_profiles('doctorant', ARRAY['lister-sien', 'consulter-sien'], ARRAY['D','K']);

update unicaen_privilege_privilege
    set code = 'modifier-email-contact-et-consentement',
        libelle = 'Modifier email de contact et consentement du doctorant'
    where categorie_id = (select id from unicaen_privilege_categorie where code = 'doctorant') and code = 'modifier-email-contact'
