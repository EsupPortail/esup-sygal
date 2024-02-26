-- ------------------------------------------------------------------------------------------------------------------
-- Tests : structure
-- ------------------------------------------------------------------------------------------------------------------

--drop function test_substit_structure__set_up;
CREATE or replace FUNCTION test_substit_structure__set_up() returns void
    language plpgsql
as
$$begin
    alter table substit_structure disable trigger substit_trigger_on_substit_structure;
end$$;


--drop function test_substit_structure__tear_down;
CREATE or replace FUNCTION test_substit_structure__tear_down() returns void
    language plpgsql
as
$$begin
--     delete from substit_log sl where type = 'structure' and exists (select id from structure s where sl.substitue_id = s.id and libelle = 'test1234');
--     delete from substit_log sl where type = 'structure' and exists (select id from structure s where sl.substituant_id = s.id and libelle = 'test1234');
    delete from substit_log sl where type = 'structure' and sl.substitue_id in (select id from structure s where libelle = 'test1234');
    delete from substit_log sl where type = 'structure' and sl.substituant_id in (select id from structure s where libelle = 'test1234');

    alter table structure disable trigger substit_trigger_structure;
    alter table substit_structure disable trigger substit_trigger_on_substit_structure;

    delete from substit_fk_replacement where type = 'structure' and to_id in (select id from structure where libelle = 'test1234');
    delete from substit_structure where from_id in (select id from structure where libelle = 'test1234');
    delete from substit_structure where to_id in (select id from structure where libelle = 'test1234');
    delete from structure where libelle = 'test1234';
    delete from structure where libelle = 'test1234';

    alter table structure enable trigger substit_trigger_structure;
    alter table substit_structure enable trigger substit_trigger_on_substit_structure;
end$$;


--drop function test_substit_structure__fetches_data_for_substituant;
CREATE or replace FUNCTION test_substit_structure__fetches_data_for_substituant() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
    v_data record;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Création d'un doublon : sigle = BBBB
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Création d'un autre doublon : sigle = CCCC
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    select * into v_data from substit_fetch_data_for_substituant_structure(v_npd_a);

    assert v_data.code = 'X123',
        format('[TEST] Attendu : code (constituant du NPD) = %L car seule valeur (reçu %L)', 'X123', v_data.code);

    assert v_data.sigle = 'AAAA',
        format('[TEST] Attendu : sigle = %L (reçu %L) car ordre alphabet', 'AAAA', v_data.sigle);

    assert v_data.libelle = 'test1234',
        format('[TEST] Attendu : libelle = %L (reçu %L)', 'test1234', v_data.libelle);

    --
    -- Modif du doublon 1 : sigle AAAA => CCCC
    --   => Seul changement attendu : sigle = 'CCCC' car devient majoritaire.
    --
    update structure set sigle = 'CCCC' where id = v_pre_structure_1.id;

    select * into v_data from substit_fetch_data_for_substituant_structure(v_npd_a);

    assert v_data.code = 'X123',
        format('[TEST] Attendu : code (constituant du NPD) = %L car majoritaire (reçu %L)', 'HOCHON', v_data.code);

    assert v_data.sigle = 'CCCC',
        format('[TEST] Attendu : sigle = %L (reçu %L) car ordre alphabet', 'CCCC', v_data.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__creates_substit_2_doublons;
CREATE or replace FUNCTION test_substit_structure__creates_substit_2_doublons() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'une structure : sigle = BBBB
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id;
    assert v_substit_structure.to_id is null,
        format('[TEST] Attendu : aucun substit_structure avec from_id = % ', v_pre_structure_1.id);

    --
    -- Test insertion d'un doublon : sigle = AAAA
    --   - création d'une subsitution 'X123' : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_1.id, v_npd_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_a);

    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert v_structure.sigle = 'AAAA',
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'AAAA', v_structure.sigle);

    assert v_structure.libelle = 'test1234',
        format('[TEST] Attendu : 1 structure substituant avec libelle = %L (mais libelle = %L)', 'test1234', v_structure.libelle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__creates_substit_3_doublons;
CREATE or replace FUNCTION test_substit_structure__creates_substit_3_doublons() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'une structure : sigle = BBBB
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_structure_1;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id;
    assert v_substit_structure.to_id is null,
        format('[TEST] Attendu : aucun substit_structure avec from_id = % ', v_pre_structure_1.id);

    --
    -- Test insertion d'un doublon : sigle = AAAA
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_structure_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = % et npd = %', v_pre_structure_1.id, v_npd_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = % et npd = %', v_pre_structure_2.id, v_npd_a);

    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert not (v_structure is null or v_structure.sigle <> 'AAAA'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = % (mais sigle = %)', 'AAAA', v_structure.sigle);

    --
    -- Test insertion d'un autre doublon HISTORISÉ : sigle = BBBB
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = BBBB car majoritaire)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, histo_destruction)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, current_timestamp
    returning * into v_pre_structure_3;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = % et npd = %', v_pre_structure_3.id, v_npd_a);

    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert not (v_structure is null or v_structure.sigle <> 'BBBB'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = % (mais sigle = %)', 'BBBB', v_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__creates_substit_2_doublons_insert_sel;
CREATE or replace FUNCTION test_substit_structure__creates_substit_2_doublons_insert_sel() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
begin
    perform test_substit_structure__set_up();

    alter table structure enable trigger substit_trigger_structure;
    alter table substit_structure disable trigger substit_trigger_on_substit_structure; -- SANS remplacement de FK

    v_npd_a = 'etablissement,X123';

    --
    -- Création *dans le même insert* de 3 enregistrements, dont 2 doublons :
    --   - X123 (sigle = BBBB)
    --   - X123 (sigle = AAAA)
    --
    -- NB : l'ajout par INSERT...SELECT a dû faire l'objet d'une gestion particulière (cf. fonction "substit_trigger_fct").
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    union all
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    union all
    select nextval('structure_id_seq'), 1, 'YYYY', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null;

    select * into v_pre_structure_1 from structure where libelle = 'test1234' and sigle = 'BBBB';
    select * into v_pre_structure_2 from structure where libelle = 'test1234' and sigle = 'AAAA';

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_1.id, v_npd_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_a);

    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert v_structure.sigle = 'AAAA',
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'AAAA', v_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__creates_substit_2_doublons_sync_cols;
CREATE or replace FUNCTION test_substit_structure__creates_substit_2_doublons_sync_cols() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
begin
    perform test_substit_structure__set_up();

    alter table structure enable trigger substit_trigger_structure;
    alter table substit_structure enable trigger substit_trigger_on_substit_structure;

    v_npd_a = 'etablissement,X123';

    --
    -- Création de 3 doublons :
    --   - sigle = BBBB
    --   - sigle = AAAA
    --   - sigle = BBBB
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_structure_1;
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_structure_2;
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_structure_3;

    select * into v_pre_structure_1 from structure where id = v_pre_structure_1.id; -- refetch requis
    select * into v_pre_structure_2 from structure where id = v_pre_structure_2.id; -- refetch requis
    select * into v_pre_structure_3 from structure where id = v_pre_structure_3.id; -- refetch requis

    assert v_pre_structure_1.synchro_undelete_enabled = false,
        format('[TEST] Attendu : substitué 1 avec synchro_undelete_enabled = %L (mais = %L)', false, v_pre_structure_1.synchro_undelete_enabled);
    assert v_pre_structure_2.synchro_undelete_enabled = false,
        format('[TEST] Attendu : substitué 2 avec synchro_undelete_enabled = %L (mais = %L)', false, v_pre_structure_2.synchro_undelete_enabled);
    assert v_pre_structure_3.synchro_undelete_enabled = false,
        format('[TEST] Attendu : substitué 3 avec synchro_undelete_enabled = %L (mais = %L)', false, v_pre_structure_3.synchro_undelete_enabled);

    assert v_pre_structure_1.synchro_update_on_deleted_enabled = true,
        format('[TEST] Attendu : substitué 1 avec synchro_update_on_deleted_enabled = %L (mais = %L)', true, v_pre_structure_1.synchro_update_on_deleted_enabled);
    assert v_pre_structure_2.synchro_update_on_deleted_enabled = true,
        format('[TEST] Attendu : substitué 2 avec synchro_update_on_deleted_enabled = %L (mais = %L)', true, v_pre_structure_2.synchro_update_on_deleted_enabled);
    assert v_pre_structure_3.synchro_update_on_deleted_enabled = true,
        format('[TEST] Attendu : substitué 3 avec synchro_update_on_deleted_enabled = %L (mais = %L)', true, v_pre_structure_3.synchro_update_on_deleted_enabled);

    -- sortie du 3 de la substitution
    update structure set code = 'Y456' where id = v_pre_structure_3.id;

    select * into v_pre_structure_1 from structure where id = v_pre_structure_1.id; -- refetch requis
    select * into v_pre_structure_2 from structure where id = v_pre_structure_2.id; -- refetch requis
    select * into v_pre_structure_3 from structure where id = v_pre_structure_3.id; -- refetch requis

    assert v_pre_structure_1.synchro_undelete_enabled = false,
        format('[TEST] Attendu : substitué 1 avec synchro_undelete_enabled = %L (mais = %L)', false, v_pre_structure_1.synchro_undelete_enabled);
    assert v_pre_structure_2.synchro_undelete_enabled = false,
        format('[TEST] Attendu : substitué 2 avec synchro_undelete_enabled = %L (mais = %L)', false, v_pre_structure_2.synchro_undelete_enabled);
    assert v_pre_structure_3.synchro_undelete_enabled = true,
        format('[TEST] Attendu : substitué 3 avec synchro_undelete_enabled = %L (mais = %L)', true, v_pre_structure_3.synchro_undelete_enabled);

    assert v_pre_structure_1.synchro_update_on_deleted_enabled = true,
        format('[TEST] Attendu : substitué 1 avec synchro_update_on_deleted_enabled = %L (mais = %L)', true, v_pre_structure_1.synchro_update_on_deleted_enabled);
    assert v_pre_structure_2.synchro_update_on_deleted_enabled = true,
        format('[TEST] Attendu : substitué 2 avec synchro_update_on_deleted_enabled = %L (mais = %L)', true, v_pre_structure_2.synchro_update_on_deleted_enabled);
    assert v_pre_structure_3.synchro_update_on_deleted_enabled = false,
        format('[TEST] Attendu : substitué 3 avec synchro_update_on_deleted_enabled = %L (mais = %L)', false, v_pre_structure_3.synchro_update_on_deleted_enabled);

    perform test_substit_structure__tear_down();
end$$;



--drop function test_substit_structure__creates_substit_and_replaces_fk;
CREATE or replace FUNCTION test_substit_structure__creates_substit_and_replaces_fk() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_role role;

    v_substit_structure substit_structure;
    v_structure_1 structure;
    v_structure_2 structure;
    v_fkr_1 substit_fk_replacement;
    v_fkr_2 substit_fk_replacement;
begin
    perform test_substit_structure__set_up();

    alter table structure enable trigger substit_trigger_structure;
    alter table substit_structure enable trigger substit_trigger_on_substit_structure;

    v_npd_a = 'etablissement,X123';

    -- Création d'une 'structure' :
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_structure_1;

    -- Création d'une 'role' pointant sur la 1ere 'structure' :
    INSERT INTO role(id, code, libelle, role_id, structure_id, source_id, source_code, histo_createur_id)
        select nextval('role_id_seq'), random(), random(), random(), v_structure_1.id, app_source_id(), 'INSA::'||trunc(10000000000*random()), 1;

    -- Insertion d'une 'structure' en doublon :
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_structure_2;

    select * into v_substit_structure from substit_structure where from_id = v_structure_1.id and npd = v_npd_a;
    select * into v_role from role where id = currval('role_id_seq');

    assert v_role.structure_id = v_substit_structure.to_id,
        format('[TEST] Attendu : FK INDIVIDU_ROLE.structure_id remplacée par %s (mais valeur = %s)',
               v_substit_structure.to_id, v_role.structure_id);

    select * into v_fkr_1 from substit_fk_replacement
    where type = 'structure' and table_name = 'role' and column_name = 'structure_id'
      and record_id = v_role.id
      and from_id = v_structure_1.id and to_id = v_substit_structure.to_id;

    assert v_fkr_1.id is not null,
        format('[TEST] Attendu : 1er SUBSTIT_FK_REPLACEMENT pour %s => %s dans INDIVIDU_ROLE',
               v_structure_1.id, v_substit_structure.to_id, v_role.structure_id);

    delete from role where id = v_role.id;

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_individu__creates_substit_can_fail_to_replace_fk;
CREATE or replace FUNCTION test_substit_individu__creates_substit_can_fail_to_replace_fk() returns void
    language plpgsql
as
$$declare

begin

    --
    -- Pas de contrainte d'unicité à tester pour les structures.
    --

end$$;

/*
--drop function test_substit_structure__removes_from_substit_si_historise;
CREATE or replace FUNCTION test_substit_structure__removes_from_substit_si_historise() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'un structure : sigle = BBBB
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = AAAA
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = CCCC
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = AAAA car ordre alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Historisation d'un structure : AAAA
    --   - retrait structure de la substitution existante (historisation) : 2 doublons restants (sigle substituant = BBBB car ordre alphabet)
    --
    update structure set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_structure_2.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_substit_structure.histo_destruction is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L et histo_destruction not null', v_pre_structure_2.id, v_npd_a);

    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert not (v_structure is null or v_structure.sigle <> 'BBBB'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'BBBB', v_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;
*/

--drop function test_substit_structure__substituant_update_enabled;
CREATE or replace FUNCTION test_substit_structure__substituant_update_enabled() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_structure_a varchar(256);

    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
    v_structure structure;
    v_substit_structure substit_structure;
begin
    perform test_substit_structure__set_up();

    alter table structure enable trigger substit_trigger_structure;
    alter table substit_structure disable trigger substit_trigger_on_substit_structure; -- SANS remplacement de FK

    v_npd_structure_a = 'etablissement,X123';

    --
    -- Création d'une structure : sigle = BBBB
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = AAAA
    --   - création d'une subsitution 'X123' : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    -- Fetch de la substitution et du substituant correspondant
    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_structure_a;
    select * into v_structure from structure where id = v_substit_structure.to_id;

    -- Verif des valeurs des attributs mis à jour automatiquement à partir des substitués
    assert v_structure.sigle = 'AAAA' /* car ordre alpha */,
        format('[TEST] Attendu : 1 structure substituant avec sigle = %s (mais sigle = %s)', 'AAAA', v_structure.sigle);

    -- À présent, interdiction de mise à jour automatique des valeurs des attributs du substituant à partir des substitués
    update structure set est_substituant_modifiable = false where id = v_structure.id;

    -- Insertion d'un autre doublon : sigle = BBBB
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    -- Vérif que les valeurs d'attributs du substituant n'ont pas changé
    assert v_structure.sigle = 'AAAA' /* alors que BBBB est majoritaire */,
        format('[TEST] Attendu : 1 structure substituant avec sigle = %s (mais sigle = %s)', 'AAAA', v_structure.sigle);

    perform test_substit_structure__tear_down();
END$$;


--drop function test_substit_structure__keeps_in_substit_si_historise;
CREATE or replace FUNCTION test_substit_structure__keeps_in_substit_si_historise() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
begin
    perform test_substit_structure__set_up();

    alter table structure enable trigger substit_trigger_structure;
    alter table substit_structure disable trigger substit_trigger_on_substit_structure; -- SANS remplacement de FK

    v_npd_a = 'etablissement,X123';

    --
    -- Création de 3 doublons :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Historisation de la 3e structure (BBBB)
    --   - pas de retrait de l'enregistrement de la substitution existante
    --
    update structure set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_structure_3.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_3.id, v_npd_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_a;
    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert v_structure.sigle = 'BBBB', -- car BBBB majoritaire
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'BBBB', v_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__removes_from_substit_and_restores_fk;
CREATE or replace FUNCTION test_substit_structure__removes_from_substit_and_restores_fk() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_record record;
    v_table record;

    v_role_1 role;
    v_role_2 role;
    v_structure structure;
    v_utilisateur utilisateur;

    v_substit_structure_1 substit_structure;
    v_substit_structure_2 substit_structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
    v_structure_1 structure;
    v_structure_2 structure;
    v_structure_3 structure;
    v_structure_id bigint;
    v_fkr_1 substit_fk_replacement;
    v_fkr_2 substit_fk_replacement;
begin
    perform test_substit_structure__set_up();

    alter table structure enable trigger substit_trigger_structure;
    alter table substit_structure enable trigger substit_trigger_on_substit_structure;

    v_npd_a = 'etablissement,X123';

    -- Création d'une 'structure' :
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_structure_1;
    -- Création d'un 1er 'role' pointant sur la 1ere 'structure' :
    INSERT INTO role(id, code, libelle, role_id, structure_id, source_id, source_code, histo_createur_id)
    select nextval('role_id_seq'), random(), random(), random(), v_structure_1.id, app_source_id(), 'INSA::'||trunc(10000000000*random()), 1
    returning * into v_role_1;

    -- Insertion d'une 'structure' pas encore en doublon (code Y456) pour pas déclencher sa substituion (sinon on peut pas tester la restauration de FK) :
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'Y456', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_structure_2;
    -- Création d'un 2e 'role' pointant sur la 2e 'structure' :
    INSERT INTO role(id, code, libelle, role_id, structure_id, source_id, source_code, histo_createur_id)
    select nextval('role_id_seq'), random(), random(), random(), v_structure_2.id, app_source_id(), 'INSA::'||trunc(10000000000*random()), 1
    returning * into v_role_2;

    -- Modif du code de la 2e structure pour qu'elle soit substituée :
    update structure set code = 'X123' where id = v_structure_2.id;

    select * into v_substit_structure_1 from substit_structure where from_id = v_structure_1.id and npd = v_npd_a;
    select * into v_substit_structure_2 from substit_structure where from_id = v_structure_2.id and npd = v_npd_a;

    select * into v_fkr_1 from substit_fk_replacement
        where type = 'structure' and table_name = 'role' and column_name = 'structure_id'
          and record_id = v_role_1.id
          and from_id = v_structure_1.id
          and to_id = v_substit_structure_1.to_id;
    assert v_fkr_1.id is not null,
        format('[TEST] Attendu : un SUBSTIT_FK_REPLACEMENT pour %s => %s et la table "role"',
               v_structure_1.id, v_substit_structure_1.to_id, v_role_1.structure_id);

    select * into v_fkr_2 from substit_fk_replacement
        where type = 'structure' and table_name = 'role' and column_name = 'structure_id'
          and record_id = v_role_2.id
          and from_id = v_structure_2.id
          and to_id = v_substit_structure_2.to_id;
    assert v_fkr_2.id is not null,
        format('[TEST] Attendu : un SUBSTIT_FK_REPLACEMENT pour %s => %s et la table "role"',
               v_structure_2.id, v_substit_structure_2.to_id, v_role_2.structure_id);

    -- Modif du NPD forcé pour sortir la 1ere structure de la substitution (cela supprimera la substitution puisqu'il ne reste qu'un substitué)
    update structure set npd_force = 'ksldqhflksjdqhfl' where id = v_structure_1.id;

    --
    -- 1ere structure
    --
    select * into v_role_1 from role where id = v_role_1.id; -- refetch
    assert v_role_1.structure_id = v_structure_1.id,
        format('[TEST] Attendu : FK INDIVIDU_ROLE.structure_id restaurée à %s (mais valeur = %s)',
               v_structure_1.id, v_role_1.structure_id);

    select * into v_fkr_1 from substit_fk_replacement
        where type = 'structure' and table_name = 'role' and column_name = 'structure_id'
          and record_id = v_role_1.id
          and from_id = v_structure_1.id
          and to_id = v_substit_structure_1.to_id;
    assert v_fkr_1.id is null,
        format('[TEST] Attendu : plus aucun SUBSTIT_FK_REPLACEMENT pour %s => %s et la table "role"',
               v_structure_1.id, v_substit_structure_1.to_id, v_role_1.structure_id);

    --
    -- 2e structure
    --
    select * into v_role_2 from role where id = v_role_2.id; -- refetch
    assert v_role_2.structure_id = v_structure_2.id,
        format('[TEST] Attendu : FK INDIVIDU_ROLE.structure_id restaurée à %s (mais valeur = %s)',
               v_structure_2.id, v_role_2.structure_id);

    select * into v_fkr_2 from substit_fk_replacement
        where type = 'structure' and table_name = 'role' and column_name = 'structure_id'
          and record_id = v_role_2.id
          and from_id = v_structure_2.id
          and to_id = v_substit_structure_2.to_id;
    assert v_fkr_2.id is null,
        format('[TEST] Attendu : plus aucun SUBSTIT_FK_REPLACEMENT pour %s => %s et la table "role"',
               v_structure_2.id, v_substit_structure_2.to_id, v_role_2.structure_id);

    delete from role where id = v_role_1.id;
    delete from role where id = v_role_2.id;

    perform test_substit_structure__tear_down();
end$$;

/*
--drop function test_substit_structure__adds_to_substit_si_dehistorise;
CREATE or replace FUNCTION test_substit_structure__adds_to_substit_si_dehistorise() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = BBBB
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = CCCC
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = AAAA car ordre alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Historisation d'un structure : HOCHON PAULE BBBB
    --
    update structure set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_structure_2.id;

    --
    -- Restauration d'un structure : HOCHON PAULE BBBB
    --   - ajout structure dans la substitution existante 'hochon_paule_20000101' : 3 doublons (sigle substituant = AAAA car ordre alphabet)
    --
    update structure set histo_destruction = null, histo_destructeur_id = null where id = v_pre_structure_2.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_a and histo_destruction is null;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L et histo_destruction null', v_pre_structure_2.id, v_npd_a);

    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert v_structure.sigle = 'AAAA',
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'AAAA', v_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;
*/

--drop function test_substit_structure__removes_from_substit_si_source_app;
CREATE or replace FUNCTION test_substit_structure__removes_from_substit_si_source_app() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = BBBB
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = CCCC
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = AAAA car ordre alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Passage d'un structure subsitué dans la source application : HOCHON PAULE AAAA
    --   - retrait structure de la substitution existante : 2 doublons restants (sigle substituant = BBBB car ordre alpha)
    --
    update structure set source_id = 1 where id = v_pre_structure_1.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_a;
    assert v_substit_structure.to_id is null,
        format('[TEST] Attendu : 1 substit_structure supprimé avec from_id = %s et npd = %L ', v_pre_structure_1.id, v_npd_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_a;
    select * into v_structure from structure where id = v_substit_structure.to_id;
    assert v_structure.sigle = 'BBBB',
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'BBBB', v_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__removes_from_substit_si_plus_source_app;
CREATE or replace FUNCTION test_substit_structure__removes_from_substit_si_plus_source_app() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = BBBB
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = CCCC
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = AAAA car ordre alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Passage d'un structure subsitué dans la source application : HOCHON PAULE AAAA
    --   - retrait structure de la substitution existante : 2 doublons restants (sigle substituant = BBBB car ordre alpha)
    --
    update structure set source_id = 1 where id = v_pre_structure_1.id;

    --
    -- Retour d'un structure dans la source INSA : HOCHON PAULE AAAA
    --   - ajout structure dans la substitution existante 'hochon_paule_20000101' : 3 doublons (sigle substituant = AAAA car ordre alpha)
    --
    update structure set source_id = v_source_id where id = v_pre_structure_1.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_1.id, v_npd_a);

    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert not (v_structure is null or v_structure.sigle <> 'AAAA'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'AAAA', v_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__adds_to_substit_si_npd_force;
CREATE or replace FUNCTION test_substit_structure__adds_to_substit_si_npd_force() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'un structure : sigle = BBBB
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion structure avec NPD forcé : AAAA
    --   - ajout à la subsitution existante : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'PEUIMPORTE', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_a
    returning * into v_pre_structure_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = % et npd = %', v_pre_structure_2.id, v_npd_a);

    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert v_structure.sigle = 'AAAA',
        format('[TEST] Attendu : 1 structure substituant avec sigle = % (mais sigle = %)', 'AAAA', v_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__updates_substits_si_modif_code;
CREATE or replace FUNCTION test_substit_structure__updates_substits_si_modif_code() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);
    v_npd_b varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
    v_pre_structure_4 structure;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = BBBB
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = BBBB
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = BBBB car majoritaire)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    v_npd_b = 'etablissement,Z666';

    --
    -- Création d'un structure : code = 'Z666'
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'Z666', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_4;

    --
    -- Test modif individu 3 : code X123 => Z666
    --   - retrait structure de la substitution existante 'X123' (historisation) : 3 doublons restants (sigle substituant = AAAA car majoritaire)
    --   - création d'une nouvelle substitution 'Z666' : 2 doublons (sigle substituant = BBBB car 1 vs 1 mais 1er dans alphabet)
    --
    update structure set code = 'Z666' where id = v_pre_structure_3.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_a;
    assert v_substit_structure.to_id is null,
        format('[TEST] Attendu : 1 substit_structure supprimé avec from_id = %s et npd = %L', v_pre_structure_3.id, v_npd_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_a;
    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert v_structure.sigle = 'AAAA',
        format('[TEST] Attendu : 1 structure substituant avec sigle = %s (mais sigle = %L)', 'AAAA', v_structure.sigle);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_b;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_3.id, v_npd_b);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_4.id and npd = v_npd_b;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_4.id, v_npd_b);

    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert not (v_structure is null or v_structure.sigle <> 'BBBB'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'BBBB', v_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__adds_to_substit_si_ajout_npd;
CREATE or replace FUNCTION test_substit_structure__adds_to_substit_si_ajout_npd() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);
    v_npd_b varchar(256);

    v_substit_structure substit_structure;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
    v_pre_structure_4 structure;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = BBBB
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = BBBB
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = BBBB car majoritaire)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Test insertion structure puis update du NPD forcé : sigle = AAAA
    --   - ajout à la subsitution existante : 4 doublons (sigle substituant = AAAA car 2 contre 2 mais ordre alpha)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'X444', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_structure_4;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_4.id;
    assert v_substit_structure.to_id is null,
        format('[TEST] Attendu : aucun substit_structure avec from_id = % ', v_pre_structure_4.id);

    update structure set npd_force = v_npd_a where id = v_pre_structure_4.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_4.id and npd = v_npd_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = % et npd = %', v_pre_structure_4.id, v_npd_a);

    select * into v_structure from structure i where id = v_substit_structure.to_id;
    assert v_structure.sigle = 'AAAA',
        format('[TEST] Attendu : 1 structure substituant avec sigle = % (mais sigle = %)', 'AAAA', v_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__deletes_substit_si_plus_doublon;
CREATE or replace FUNCTION test_substit_structure__deletes_substit_si_plus_doublon() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);
    v_npd_b varchar(256);

    v_substit_structure substit_structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_structure structure;
    v_count smallint;
begin
    perform test_substit_structure__set_up();

    v_npd_a = 'etablissement,X123';

    --
    -- Création d'un structure : sigle = BBBB
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = AAAA
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_a;
    select * into v_structure from structure where id = v_substit_structure.to_id;

    -- Modif du NPD forcé pour sortir AAAA de la substitution
    --   - retrait structure de la substitution existante : 1 doublons restant (mail substituant = bbbb@mail.fr car ordre alphabet)
    --   - suppression du substituant existant car 0 doublon restant.
    update structure set npd_force = 'ksldqhflksjdqhfl' where id = v_pre_structure_2.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_substit_structure.id is null,
        format('[TEST] Attendu : 1 substit_structure supprimé avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_a);

    select count(*) into v_count from substit_structure i where to_id = v_structure.id;
    assert v_count = 0,
        format('[TEST] Attendu : 0 substit_structure avec substituant = %s', v_substit_structure.to_id);

    select * into v_structure from structure where id = v_structure.id;
    assert v_structure.id is null,
        format('[TEST] Attendu : 1 structure substituant supprimé : %s', v_structure.id);

    perform test_substit_structure__tear_down();
end$$;


select test_substit_structure__fetches_data_for_substituant();
select test_substit_structure__creates_substit_2_doublons();
select test_substit_structure__creates_substit_3_doublons();
select test_substit_structure__creates_substit_2_doublons_insert_sel();
select test_substit_structure__creates_substit_2_doublons_sync_cols();
select test_substit_structure__creates_substit_and_replaces_fk();
--select test_substit_structure__removes_from_substit_si_historise();
select test_substit_structure__substituant_update_enabled();
select test_substit_structure__keeps_in_substit_si_historise();
select test_substit_structure__removes_from_substit_and_restores_fk();
--select test_substit_structure__adds_to_substit_si_dehistorise();
select test_substit_structure__removes_from_substit_si_source_app();
select test_substit_structure__removes_from_substit_si_plus_source_app();
select test_substit_structure__adds_to_substit_si_npd_force();
select test_substit_structure__updates_substits_si_modif_code();
select test_substit_structure__adds_to_substit_si_ajout_npd();
select test_substit_structure__deletes_substit_si_plus_doublon();
/*
select * from substit_log;
select * from substit_structure order by to_id desc, id desc;

select substit_create_all_substitutions_structure(20); -- totalité : 23-24 min (avec ou sans les raise)

select * from v_structure_doublon
where nom_patronymique in ('HOCHAN', 'VIEILLE', 'BERNAUDIN', 'BRANDLE DE MOTTA', 'DEMOULIN', 'DURET')
order by nom_patronymique;
*/