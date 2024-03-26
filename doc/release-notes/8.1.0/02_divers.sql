
--
-- Divers
--

create unique index role_code_structure_id_uindex on role (code, structure_id);

update role set ordre_affichage = 'aaa' where code = 'D';
update role set ordre_affichage = 'bbb' where code = 'K';
update role set ordre_affichage = 'ccc' where code = 'B';
update role set ordre_affichage = 'ddd' where code = 'M';
update role set ordre_affichage = 'eee' where code = 'R';
update role set ordre_affichage = 'fff' where code = 'RESP_ED';
update role set ordre_affichage = 'ggg' where code = 'RESP_UR';
update role set ordre_affichage = 'hhh' where code = 'GEST_ED';
update role set ordre_affichage = 'iii' where code = 'GEST_UR';
