--
-- Corrections dans la table role.
--

update role set source_code = 'ADMIN_TECH' where source_code = 'UCN::ADMIN_TECH';
update role set source_code = 'OBSERV' where source_code = 'COMUE::OBSERV';
update role set source_code = 'OBSERVATOIRE' where source_code = 'SyGAL::OBSERVATOIRE';
update role set source_code = 'GEST_FORMATION' where source_code = 'SYGAL::GEST_FORMATION';
update role set source_code = 'FORMATEUR' where source_code = 'SYGAL::FORMATEUR';
update role set type_structure_dependant_id = (select id from type_structure where code = 'unite-recherche') where code in ('GEST_UR', 'RESP_UR');
update role set type_structure_dependant_id = (select id from type_structure where code = 'ecole-doctorale') where code in ('GEST_ED', 'RESP_ED');
update role set these_dep = true where code in ('A');
update role set type_structure_dependant_id = 1 where code in ('DOCTORANT', 'BU', 'BDD', 'ADMIN');
update role set libelle = 'Gestionnaire de formation' where code = 'GEST_FORM';

delete from role where code = 'GEST_FORMATION';
delete from role where code = 'P' and source_id = 1;

create sequence if not exists role_id_seq;
alter table role alter column id set default nextval('role_id_seq');
alter sequence role_id_seq owned by role.id;


--
-- Table soutenance_membre
--

alter table soutenance_membre alter column etablissement type varchar(512) using etablissement::varchar(512);
