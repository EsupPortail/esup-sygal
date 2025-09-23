--
-- Module Doctorant
--

INSERT INTO unicaen_privilege_privilege (categorie_id, code, libelle, ordre) 
select cp.id, 'afficher-email-contact', 'Visualiser l''email de contact du doctorant', 20 
from unicaen_privilege_categorie cp where cp.code = 'doctorant' on conflict do nothing;

INSERT INTO unicaen_privilege_privilege (categorie_id, code, libelle, ordre) 
select cp.id, 'lister-tout', 'Lister tous·tes les doctorant·es', 30 
from unicaen_privilege_categorie cp where cp.code = 'doctorant' on conflict do nothing;

INSERT INTO unicaen_privilege_privilege (categorie_id, code, libelle, ordre) 
select cp.id, 'lister-sien', 'Lister ses doctorant·es', 31 
from unicaen_privilege_categorie cp where cp.code = 'doctorant' on conflict do nothing;

INSERT INTO unicaen_privilege_privilege (categorie_id, code, libelle, ordre) 
select cp.id, 'consulter-tout', 'Consulter la fiche détaillée de n''importe quel·le doctorant·e', 40 
from unicaen_privilege_categorie cp where cp.code = 'doctorant' on conflict do nothing;

INSERT INTO unicaen_privilege_privilege (categorie_id, code, libelle, ordre) 
select cp.id, 'consulter-sien', 'Consulter la fiche détaillée d''un·e de ses doctorant·es', 41 
from unicaen_privilege_categorie cp where cp.code = 'doctorant' on conflict do nothing;

INSERT INTO unicaen_privilege_privilege (categorie_id, code, libelle, ordre) 
select cp.id, 'modifier-email-contact-et-consentement', 'Modifier email de contact et consentement du doctorant', 10 
from unicaen_privilege_categorie cp where cp.code = 'doctorant' on conflict do nothing;

update unicaen_privilege_privilege set code = 'lister-tous' where code = 'lister-tout' and categorie_id = (
    select id from unicaen_privilege_categorie cp where cp.code = 'doctorant'
);
update unicaen_privilege_privilege set code = 'consulter-tous' where code = 'consulter-tout' and categorie_id = (
    select id from unicaen_privilege_categorie cp where cp.code = 'doctorant'
);

INSERT INTO unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
select cp.id, 'modifier-tous', 'Modifier la fiche de n''importe quel·le doctorant·e', 40
from unicaen_privilege_categorie cp where cp.code = 'doctorant' on conflict do nothing;

INSERT INTO unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
select cp.id, 'modifier-sien', 'Modifier la fiche d''un·e de ses doctorant·es', 41
from unicaen_privilege_categorie cp where cp.code = 'doctorant' on conflict do nothing;


select privilege__grant_privileges_to_profiles('doctorant', ARRAY['consulter-sien'], ARRAY['DOCTORANT']);
select privilege__grant_privileges_to_profiles('doctorant', ARRAY['lister-tous', 'consulter-tous'], ARRAY['ADMIN_TECH','BDD','BU','GEST_FORM']);
select privilege__grant_privileges_to_profiles('doctorant', ARRAY['lister-sien', 'consulter-sien'], ARRAY['GEST_ED','GEST_UR','RESP_ED','RESP_UR']);
select privilege__grant_privileges_to_profiles('doctorant', ARRAY['lister-sien', 'consulter-sien'], ARRAY['D','K']);
-- select privilege__revoke_privileges_to_profiles('doctorant', ARRAY['lister-sien', 'consulter-sien'], ARRAY['D','K']);
select privilege__grant_privileges_to_profiles('doctorant', ARRAY['modifier-tous'], ARRAY['ADMIN_TECH','BDD','BU']);
select privilege__grant_privileges_to_profiles('doctorant', ARRAY['modifier-sien'], ARRAY['GEST_ED','RESP_ED']);

update unicaen_privilege_privilege
    set code = 'modifier-email-contact-et-consentement',
        libelle = 'Modifier email de contact et consentement du doctorant'
    where categorie_id = (select id from unicaen_privilege_categorie where code = 'doctorant') and code = 'modifier-email-contact';

-- Ajout de l'ORCID pour un doctorant
alter table individu add orcid varchar(200);

-- Transfert des données de la table diffusion vers le champ nouvellement crée dans individu
UPDATE individu i
SET orcid = d.orcid
    FROM diffusion d
         JOIN these t ON d.these_id = t.id
    JOIN doctorant doc ON t.doctorant_id = doc.id
WHERE doc.individu_id = i.id and d.orcid is not null and trim(d.orcid) != '';

alter table diffusion drop column orcid;