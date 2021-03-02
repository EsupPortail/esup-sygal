--
-- Repeuplement de INDIVIDU_RECH
--
declare
    v_haystack clob;
begin
    delete from INDIVIDU_RECH;
    for rec in (
        select ID,
               NOM_USUEL,
               NOM_PATRONYMIQUE,
               PRENOM1,
               EMAIL,
               SOURCE_CODE
        from INDIVIDU
        ) loop
            v_haystack := individu_haystack(rec.NOM_USUEL, rec.NOM_PATRONYMIQUE, rec.PRENOM1, rec.EMAIL, rec.SOURCE_CODE);
            insert into INDIVIDU_RECH(ID, HAYSTACK) values (rec.ID, v_haystack);
        end loop;
end;

