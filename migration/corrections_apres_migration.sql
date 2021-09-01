
create or replace function trigger_fct_individu_rech_update() returns trigger
    security definer
    language plpgsql
as
    $$
DECLARE
BEGIN
    IF TG_OP = 'INSERT' THEN
        insert into INDIVIDU_RECH(ID, HAYSTACK)
        values (NEW.ID, individu_haystack(NEW.NOM_USUEL, NEW.NOM_PATRONYMIQUE, NEW.PRENOM1, NEW.EMAIL, NEW.SOURCE_CODE));
    END IF;
    IF TG_OP = 'UPDATE' THEN
        UPDATE INDIVIDU_RECH
        SET HAYSTACK = individu_haystack(NEW.NOM_USUEL, NEW.NOM_PATRONYMIQUE, NEW.PRENOM1, NEW.EMAIL, NEW.SOURCE_CODE)
        where ID = NEW.ID;
    END IF;
    IF TG_OP = 'DELETE' THEN
        delete from INDIVIDU_RECH where id = OLD.ID;
    END IF;
    IF TG_OP = 'DELETE' THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;

END
$$;
