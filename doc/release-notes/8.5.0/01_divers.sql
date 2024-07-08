--
-- Corrections dans la table role.
--

update role set type_structure_dependant_id = (select id from type_structure where code = 'unite-recherche')
            where code in ('GEST_UR', 'RESP_UR');
update role set type_structure_dependant_id = (select id from type_structure where code = 'ecole-doctorale')
            where code in ('GEST_ED', 'RESP_ED');
update role set these_dep = true
            where code in ('A');
update role set type_structure_dependant_id = 1
            where code in ('DOCTORANT', 'BU', 'BDD', 'ADMIN');
update role set libelle = 'Gestionnaire de formation'
            where code = 'GEST_FORM';

delete from role
       where code = 'GEST_FORMATION';
delete from role
       where code = 'P' and source_id = 1;

create sequence if not exists role_id_seq;
alter table role alter column id set default nextval('role_id_seq');
alter sequence role_id_seq owned by role.id;
