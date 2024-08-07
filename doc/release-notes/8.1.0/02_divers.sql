
--
-- Divers
--

create unique index if not exists role_code_structure_id_uindex on role (code, structure_id);

update role set ordre_affichage = 'aaa' where code = 'D';
update role set ordre_affichage = 'bbb' where code = 'K';
update role set ordre_affichage = 'ccc' where code = 'B';
update role set ordre_affichage = 'ddd' where code = 'M';
update role set ordre_affichage = 'eee' where code = 'R';
update role set ordre_affichage = 'fff' where code = 'RESP_ED';
update role set ordre_affichage = 'ggg' where code = 'RESP_UR';
update role set ordre_affichage = 'hhh' where code = 'GEST_ED';
update role set ordre_affichage = 'iii' where code = 'GEST_UR';

update utilisateur u set last_role_id = null where last_role_id is not null and not exists (
    select * from role where id = u.last_role_id
);
alter table utilisateur
    add constraint utilisateur_role_id_fk
        foreign key (last_role_id) references role;

alter table unite_domaine_linker
    add constraint unite_domaine_linker_unite_rech_id_fk
        foreign key (unite_id) references unite_rech;
alter table unite_domaine_linker
    drop constraint unite_domaine_linker_pkey;
alter table unite_domaine_linker
    add id bigserial;
alter table unite_domaine_linker
    add constraint unite_domaine_linker_pk
        primary key (id);
alter table unite_domaine_linker
    add constraint unite_domaine_linker_pk_2
        unique (unite_id, domaine_id);
