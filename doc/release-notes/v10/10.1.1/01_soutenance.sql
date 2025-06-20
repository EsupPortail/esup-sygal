ALTER TABLE soutenance_avis
DROP CONSTRAINT soutenance_avis_fichier_these_id_fk;

ALTER TABLE soutenance_avis
    ADD CONSTRAINT soutenance_avis_fichier_these_id_fk
        FOREIGN KEY (fichierthese_id)
            REFERENCES fichier_these(id)
            ON DELETE CASCADE;

ALTER TABLE soutenance_avis
    ADD CONSTRAINT soutenance_avis_fichier_hdr_id_fk
        FOREIGN KEY (fichier_hdr_id)
            REFERENCES fichier_hdr(id)
            ON DELETE CASCADE;