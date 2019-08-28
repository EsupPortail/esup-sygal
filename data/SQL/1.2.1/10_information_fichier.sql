
--
-- Nouvelle NATURE_FICHIER : 'Fichier divers'
--
insert into NATURE_FICHIER (ID, CODE, LIBELLE) values (NATURE_FICHIER_ID_SEQ.nextval, 'DIVERS', 'Fichier divers')
/
update NATURE_FICHIER set LIBELLE = 'Fichier non PDF joint à une thèse (ex: vidéo)' where CODE = 'FICHIER_NON_PDF'
/


--
-- FICHIER : élargissement de la colonne UUID
--
alter table FICHIER modify UUID VARCHAR2(60)
/


--
-- INFORMATION_FICHIER => FICHIER
--
insert into FICHIER (ID,
                     UUID,
                     NOM,
                     NOM_ORIGINAL,
                     TYPE_MIME,
                     TAILLE,
                     DESCRIPTION,
                     NATURE_ID,
                     VERSION_FICHIER_ID,
                     HISTO_CREATION,
                     HISTO_CREATEUR_ID,
                     HISTO_MODIFICATION,
                     HISTO_MODIFICATEUR_ID)
select FICHIER_ID_SEQ.nextval,
       if.FILENAME as UUID,
       if.FILENAME as NOM,         -- TODO: normaliser ultérieurement pour garantir l'unicité
       if.NOM as NOM_ORIGINAL,
       'application/octet-stream', -- TODO: corriger ultérieurement
       0,                          -- TODO: corriger ultérieurement
       if.NOM as DESCRIPTION,
       nf.ID,
       vf.ID,
       if.CREATION,
       if.CREATEUR,
       if.CREATION,
       if.CREATEUR
from INFORMATION_FICHIER if
         join VERSION_FICHIER vf on vf.CODE = 'VO'
         join NATURE_FICHIER nf on nf.CODE = 'DIVERS'
/
alter table INFORMATION_FICHIER rename to INFORMATION_FICHIER_SAV
/


--
-- INFORMATION : correction des ID de fichiers dans les URL.
--
CREATE OR REPLACE Function new_contenu(information_id in number)
    RETURN clob
    is
    vcontenu clob;
    cursor cur is
        select inf.id as from_id, f.id as to_id
        from INFORMATION_FICHIER_SAV inf
                 join fichier f on f.UUID = inf.FILENAME;
begin
    select contenu into vcontenu from INFORMATION where id = information_id;
    FOR row in cur
        LOOP
            vcontenu := replace(vcontenu, '/fichiers/telecharger/'||row.from_id, '/fichiers/telecharger/'||row.to_id);
        END LOOP;
    return vcontenu;
end;
/
create table information_new as
    select id, contenu, new_contenu(id) new_contenu
    from INFORMATION
/
declare
    cursor cur is select * from information_new;
begin
    FOR r in cur
        LOOP
            update INFORMATION set contenu = r.new_contenu where id = r.id;
        END LOOP;
end;
/
drop table information_new
/
