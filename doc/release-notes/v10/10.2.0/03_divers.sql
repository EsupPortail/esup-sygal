--
-- 10.2.0
--

alter table soutenance_justificatif
    drop constraint justificatif_fichier_these_fk;
alter table soutenance_justificatif
    add constraint justificatif_fichier_these_fk
        foreign key (fichier_these_id) references fichier_these
            on delete cascade;
