--------------------------------------------------------------------------------------
--         Migration des tables impliquées dans l'observation de l'import.
--------------------------------------------------------------------------------------

--
-- IMPORT_OBSERV_ETAB
--
create table IMPORT_OBSERV_ETAB (
    ID NUMBER not null constraint IMPORT_OBSERV_ETAB_PK primary key,
    IMPORT_OBSERV_ID NUMBER not null constraint IMPORT_OBSERV_ETAB_OBSERV_FK references IMPORT_OBSERV on delete cascade,
    ETABLISSEMENT_ID NUMBER not null constraint IMPORT_OBSERV_ETAB_ETAB_FK references IMPORT_OBSERV_ETAB on delete cascade,
    ENABLED NUMBER(1) default 0 not null,
    constraint IMPORT_OBSERV_ETAB_UN unique (IMPORT_OBSERV_ID, ETABLISSEMENT_ID)
);
INSERT INTO IMPORT_OBSERV_ETAB (ID, IMPORT_OBSERV_ID, ETABLISSEMENT_ID, ENABLED)
select IMPORT_OBSERV_ETAB_ID_SEQ.nextval, io.id, e.id, io.ENABLED
from IMPORT_OBSERV io,
     ETABLISSEMENT e
where e.EST_MEMBRE = 1;

--
-- IMPORT_OBSERV
--
alter table IMPORT_OBSERV drop column ENABLED;

--
-- IMPORT_OBSERV_RESULT
--
alter table IMPORT_OBSERV_RESULT add IMPORT_OBSERV_ETAB_ID NUMBER /*not null ajouté plus bas*/;
update IMPORT_OBSERV_RESULT ioer set IMPORT_OBSERV_ETAB_ID = (
    select ioe.id
    from IMPORT_OBSERV_ETAB ioe,
         ETABLISSEMENT e
    where e.SOURCE_CODE = substr(ioer.SOURCE_CODE, 1, instr(ioer.SOURCE_CODE, '::') - 1)
      and ioe.ETABLISSEMENT_ID = e.id
      and ioe.IMPORT_OBSERV_ID = ioer.IMPORT_OBSERV_ID
);
alter table IMPORT_OBSERV_RESULT add TOO_OLD NUMBER(1) default 0 not null;
alter table IMPORT_OBSERV_RESULT modify IMPORT_OBSERV_ETAB_ID not null;
alter table IMPORT_OBSERV_RESULT drop column IMPORT_OBSERV_ID;
rename IMPORT_OBSERV_RESULT to IMPORT_OBSERV_ETAB_RESULT ;
alter table IMPORT_OBSERV_ETAB_RESULT
    add constraint IMPORT_OBSERV_ETAB_RESULT_IOE_FK
        foreign key (IMPORT_OBSERV_ETAB_ID) references IMPORT_OBSERV_ETAB (ID) on delete cascade;
create index IMPORT_OBSERV_ETAB_RES_IOE_IDX on IMPORT_OBSERV_ETAB_RESULT (IMPORT_OBSERV_ETAB_ID);

--
--
--
create or replace PACKAGE "APP_IMPORT" IS
    PROCEDURE REFRESH_MV( mview_name VARCHAR2 );
    PROCEDURE SYNC_TABLES;
    PROCEDURE SYNCHRONISATION;
    PROCEDURE STORE_OBSERV_RESULTS;
END APP_IMPORT;

create or replace PACKAGE BODY "APP_IMPORT"
IS
    PROCEDURE REFRESH_MV( mview_name VARCHAR2 ) IS
    BEGIN
        DBMS_MVIEW.REFRESH(mview_name, 'C');
    EXCEPTION WHEN OTHERS THEN
        UNICAEN_IMPORT.SYNC_LOG( SQLERRM, mview_name );
    END;

    PROCEDURE SYNC_TABLES
        IS
    BEGIN
        -- mise à jour des tables à partir des vues sources
        -- NB: l'ordre importe !
        UNICAEN_IMPORT.MAJ_STRUCTURE();
        UNICAEN_IMPORT.MAJ_ETABLISSEMENT();
        UNICAEN_IMPORT.MAJ_ECOLE_DOCT();
        UNICAEN_IMPORT.MAJ_UNITE_RECH();
        UNICAEN_IMPORT.MAJ_INDIVIDU();
        UNICAEN_IMPORT.MAJ_DOCTORANT();
        UNICAEN_IMPORT.MAJ_THESE();
        UNICAEN_IMPORT.MAJ_THESE_ANNEE_UNIV();
        UNICAEN_IMPORT.MAJ_ROLE();
        UNICAEN_IMPORT.MAJ_ACTEUR();
        UNICAEN_IMPORT.MAJ_VARIABLE();
        UNICAEN_IMPORT.MAJ_FINANCEMENT();
        UNICAEN_IMPORT.MAJ_TITRE_ACCES();
        REFRESH_MV('MV_RECHERCHE_THESE'); -- NB: à faire en dernier
    END;

    --
    -- Recherche des changements de type UPDATE concernant la colonne de table observée et
    -- enregistrement de ces changements dans une table.
    --
    PROCEDURE STORE_UPDATE_OBSERV_RESULT(observEtab IMPORT_OBSERV_ETAB%ROWTYPE)
        IS
        u_col_name VARCHAR2(50);
        where_to_value CLOB;
        i_query CLOB;
        observ IMPORT_OBSERV%ROWTYPE;

        TYPE r_cursor is REF CURSOR;
        rc r_cursor;
        l_id CLOB;
        l_detail CLOB;
    BEGIN
        select * into observ from IMPORT_OBSERV where id = observEtab.IMPORT_OBSERV_ID;

        -- Construction du nom de la colonne de la vue V_DIFF_X indiquant un changement de valeur dans la table X.
        -- Ex: 'U_RESULTAT' (dans la vue V_DIFF_THESE, indiquant que la colonne THESE.RESULTAT a changé).
        u_col_name := 'U_' || observ.COLUMN_NAME;

        -- Construction de la clause permettant de ne prendre en compte que la prise de valeur qui nous intéresse.
        -- Ex: "v.COLONNE = 'VALEUR'" ou "v.COLONNE IS NULL".
        where_to_value := 'v.' || observ.COLUMN_NAME ||
                          case when observ.TO_VALUE is null then ' is null' else ' = ''' || observ.TO_VALUE || '''' end;

        -- Construction de la requête recherchant dans la vue V_DIFF_X les lignes correspondant à :
        -- une prise de valeur particulière spécifiée par IMPORT_OBSERV.TO_VALUE,
        -- de la colonne spécifiée par IMPORT_OBSERV.COLUMN_NAME,
        -- dans la table spécifiée par IMPORT_OBSERV.TABLE_NAME.
        i_query := 'select v.source_code, t.' || observ.COLUMN_NAME || ' || ''>'' || v.' || observ.COLUMN_NAME || ' detail ' ||
                   'from V_DIFF_' || observ.TABLE_NAME || ' v ' ||
                   'join ' || observ.TABLE_NAME || ' t on t.source_code = v.source_code ' ||
                   'where ' || u_col_name || ' = 1 and ' || where_to_value || ' ' ||
                   'order by v.source_code';

        --DBMS_OUTPUT.PUT_LINE(i_query);
        OPEN rc FOR i_query;
        LOOP
            FETCH rc INTO l_id, l_detail;
            EXIT WHEN rc%NOTFOUND;
            --DBMS_OUTPUT.PUT_LINE(l_id); DBMS_OUTPUT.PUT_LINE(l_detail);
            insert into IMPORT_OBSERV_ETAB_RESULT(ID, IMPORT_OBSERV_ETAB_ID, DATE_CREATION, SOURCE_CODE, RESULTAT, TOO_OLD)
            values (IMPORT_OBSERV_RESULT_ID_SEQ.nextval, observEtab.ID, sysdate, l_id, l_detail, 0);
        END LOOP;
    END;

    PROCEDURE STORE_OBSERV_RESULTS
        IS
    BEGIN
        -- Parcours des IMPORT_OBSERV_ETAB de type UPDATE et non désactivés.
        for observEtab in (
            select ioe.*
            from IMPORT_OBSERV_ETAB ioe
                     join IMPORT_OBSERV io on ioe.IMPORT_OBSERV_ID = io.ID and io.OPERATION = 'UPDATE'
            where ioe.ENABLED = 1
            ) loop
                STORE_UPDATE_OBSERV_RESULT(observEtab);
            end loop;
    END;

    PROCEDURE SYNCHRONISATION
        IS
    BEGIN
        STORE_OBSERV_RESULTS;
        SYNC_TABLES;
    END;

END APP_IMPORT;
