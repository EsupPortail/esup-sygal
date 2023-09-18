--drop function test_substit_structure__tear_down;
CREATE or replace FUNCTION test_substit_structure__tear_down() returns void
    language plpgsql
as
$$begin
    delete from structure_substit where from_id in (select id from pre_structure where libelle = 'test1234');
    delete from structure_substit where to_id in (select id from structure where libelle = 'test1234');
    delete from structure where libelle = 'test1234';
    truncate table substit_log;
    alter table pre_structure disable trigger structure_substit_trigger;
    delete from pre_structure where libelle = 'test1234';
    alter table pre_structure enable trigger structure_substit_trigger;
end$$;


--drop function test_substit_structure__fetches_data_for_substituant;
CREATE or replace FUNCTION test_substit_structure__fetches_data_for_substituant() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;
    v_data record;
begin
    v_npd_a = 'X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Création d'un doublon : sigle = BBBB
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Création d'un autre doublon : sigle = CCCC
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    select * into v_data from substit_fetch_data_for_substituant_structure(v_npd_a);

    assert v_data.code = 'X123',
        format('[TEST] Attendu : code (constituant du NPD) = %L car seule valeur (reçu %L)', 'X123', v_data.code);

    assert v_data.sigle = 'AAAA',
        format('[TEST] Attendu : sigle = %L (reçu %L) car ordre alphabet', 'AAAA', v_data.sigle);

    --
    -- Modif du doublon 1 : sigle AAAA => CCCC
    --   => Seul changement attendu : sigle = 'CCCC' car devient majoritaire.
    --
    update pre_structure set sigle = 'CCCC' where id = v_pre_structure_1.id;

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
begin
    v_npd_a = 'X123';

    --
    -- Création d'une structure : sigle = BBBB
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_1.id;
    assert v_structure_substit.to_id is null,
        format('[TEST] Attendu : aucun structure_substit avec from_id = % ', v_pre_structure_1.id);

    --
    -- Test insertion d'un doublon : sigle = AAAA
    --   - création d'une subsitution 'X123' : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_1.id and npd = v_npd_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L', v_pre_structure_1.id, v_npd_a);

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_a);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert not (v_pre_structure is null or v_pre_structure.sigle <> 'AAAA'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'AAAA', v_pre_structure.sigle);

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;
begin
    v_npd_a = 'X123';

    --
    -- Création d'une structure : sigle = BBBB
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_1.id;
    assert v_structure_substit.to_id is null,
        format('[TEST] Attendu : aucun structure_substit avec from_id = % ', v_pre_structure_1.id);

    --
    -- Test insertion d'un doublon : sigle = AAAA
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_1.id and npd = v_npd_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = % et npd = %', v_pre_structure_1.id, v_npd_a);

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = % et npd = %', v_pre_structure_2.id, v_npd_a);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert not (v_pre_structure is null or v_pre_structure.sigle <> 'AAAA'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = % (mais sigle = %)', 'AAAA', v_pre_structure.sigle);

    --
    -- Test insertion d'un autre doublon : sigle = BBBB
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = BBBB car majoritaire)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_3.id and npd = v_npd_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = % et npd = %', v_pre_structure_3.id, v_npd_a);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert not (v_pre_structure is null or v_pre_structure.sigle <> 'BBBB'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = % (mais sigle = %)', 'BBBB', v_pre_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__removes_from_substit_si_historise;
CREATE or replace FUNCTION test_substit_structure__removes_from_substit_si_historise() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;
begin
    v_npd_a = 'X123';

    --
    -- Création d'un structure : sigle = BBBB
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = AAAA
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = CCCC
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = AAAA car ordre alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Historisation d'un pre_structure : AAAA
    --   - retrait pre_structure de la substitution existante (historisation) : 2 doublons restants (sigle substituant = BBBB car ordre alphabet)
    --
    update pre_structure set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_structure_2.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_structure_substit.histo_destruction is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L et histo_destruction not null', v_pre_structure_2.id, v_npd_a);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert not (v_pre_structure is null or v_pre_structure.sigle <> 'BBBB'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'BBBB', v_pre_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__adds_to_substit_si_dehistorise;
CREATE or replace FUNCTION test_substit_structure__adds_to_substit_si_dehistorise() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;
begin
    v_npd_a = 'X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = BBBB
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = CCCC
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = AAAA car ordre alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Historisation d'un pre_structure : HOCHON PAULE BBBB
    --
    update pre_structure set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_structure_2.id;

    --
    -- Restauration d'un pre_structure : HOCHON PAULE BBBB
    --   - ajout pre_structure dans la substitution existante 'hochon_paule_20000101' : 3 doublons (sigle substituant = AAAA car ordre alphabet)
    --
    update pre_structure set histo_destruction = null, histo_destructeur_id = null where id = v_pre_structure_2.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_a and histo_destruction is null;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L et histo_destruction null', v_pre_structure_2.id, v_npd_a);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert v_pre_structure.sigle = 'AAAA',
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'AAAA', v_pre_structure.sigle);

    perform test_substit_structure__tear_down();
end$$;


--drop function test_substit_structure__removes_from_substit_si_source_app;
CREATE or replace FUNCTION test_substit_structure__removes_from_substit_si_source_app() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;
begin
    v_npd_a = 'X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = BBBB
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = CCCC
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = AAAA car ordre alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Passage d'un pre_structure subsitué dans la source application : HOCHON PAULE AAAA
    --   - retrait pre_structure de la substitution existante : 2 doublons restants (sigle substituant = BBBB car ordre alpha)
    --
    update pre_structure set source_id = 1 where id = v_pre_structure_1.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_1.id and npd = v_npd_a and histo_destruction is not null;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L et histo_destruction not null', v_pre_structure_1.id, v_npd_a);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert v_pre_structure.sigle = 'BBBB',
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'BBBB', v_pre_structure.sigle);

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;
begin
    v_npd_a = 'X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = BBBB
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = CCCC
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = AAAA car ordre alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Passage d'un pre_structure subsitué dans la source application : HOCHON PAULE AAAA
    --   - retrait pre_structure de la substitution existante : 2 doublons restants (sigle substituant = BBBB car ordre alpha)
    --
    update pre_structure set source_id = 1 where id = v_pre_structure_1.id;

    --
    -- Retour d'un pre_structure dans la source INSA : HOCHON PAULE AAAA
    --   - ajout pre_structure dans la substitution existante 'hochon_paule_20000101' : 3 doublons (sigle substituant = AAAA car ordre alpha)
    --
    update pre_structure set source_id = v_source_id where id = v_pre_structure_1.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_1.id and npd = v_npd_a and histo_destruction is not null;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L et histo_destruction not null', v_pre_structure_1.id, v_npd_a);

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_1.id and npd = v_npd_a and histo_destruction is null;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L et histo_destruction null', v_pre_structure_1.id, v_npd_a);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert not (v_pre_structure is null or v_pre_structure.sigle <> 'AAAA'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'AAAA', v_pre_structure.sigle);

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
begin
    v_npd_a = 'X123';

    --
    -- Création d'un structure : sigle = BBBB
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion pre_structure avec NPD forcé : AAAA
    --   - ajout à la subsitution existante : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'PEUIMPORTE', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_a
    returning * into v_pre_structure_2;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = % et npd = %', v_pre_structure_2.id, v_npd_a);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert v_pre_structure.sigle = 'AAAA',
        format('[TEST] Attendu : 1 structure substituant avec sigle = % (mais sigle = %)', 'AAAA', v_pre_structure.sigle);

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;
    v_pre_structure_4 pre_structure;
begin
    v_npd_a = 'X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = BBBB
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = BBBB
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = BBBB car majoritaire)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    v_npd_b = 'Z666';

    --
    -- Création d'un pre_structure : code = 'Z666'
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), v_npd_b, 'test1234', 'CCCC', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_4;

    --
    -- Test modif individu 3 : code X123 => Z666
    --   - retrait structure de la substitution existante 'X123' (historisation) : 3 doublons restants (sigle substituant = AAAA car majoritaire)
    --   - création d'une nouvelle substitution 'Z666' : 2 doublons (sigle substituant = BBBB car 1 vs 1 mais 1er dans alphabet)
    --
    update pre_structure set code = 'Z666' where id = v_pre_structure_3.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_3.id and npd = v_npd_a and histo_destruction is not null;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L et histo_destruction not null', v_pre_structure_3.id, v_npd_a);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert v_pre_structure.sigle = 'AAAA',
        format('[TEST] Attendu : 1 structure substituant avec sigle = %s (mais sigle = %L)', 'AAAA', v_pre_structure.sigle);

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_3.id and npd = v_npd_b;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L', v_pre_structure_3.id, v_npd_b);

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_4.id and npd = v_npd_b;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L', v_pre_structure_4.id, v_npd_b);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert not (v_pre_structure is null or v_pre_structure.sigle <> 'BBBB'),
        format('[TEST] Attendu : 1 structure substituant avec sigle = %L (mais sigle = %L)', 'BBBB', v_pre_structure.sigle);

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;
    v_pre_structure_4 pre_structure;
begin
    v_npd_a = 'X123';

    --
    -- Création d'un structure : sigle = AAAA
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = BBBB
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Test insertion d'un autre doublon : sigle = BBBB
    --   - ajout à la subsitution existante : 3 doublons (sigle substituant = BBBB car majoritaire)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    --
    -- Test insertion structure puis update du NPD forcé : sigle = AAAA
    --   - ajout à la subsitution existante : 4 doublons (sigle substituant = AAAA car 2 contre 2 mais ordre alpha)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('pre_structure_id_seq'), 'X444', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_structure_4;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_4.id;
    assert v_structure_substit.to_id is null,
        format('[TEST] Attendu : aucun structure_substit avec from_id = % ', v_pre_structure_4.id);

    update pre_structure set npd_force = v_npd_a where id = v_pre_structure_4.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_4.id and npd = v_npd_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = % et npd = %', v_pre_structure_4.id, v_npd_a);

    select * into v_pre_structure from structure i where id = v_structure_substit.to_id;
    assert v_pre_structure.sigle = 'AAAA',
        format('[TEST] Attendu : 1 structure substituant avec sigle = % (mais sigle = %)', 'AAAA', v_pre_structure.sigle);

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

    v_structure_substit structure_substit;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_structure structure;
    v_count smallint;
begin
    v_npd_a = 'X123';

    --
    -- Création d'un structure : sigle = BBBB
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'BBBB', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    --
    -- Test insertion d'un doublon : sigle = AAAA
    --   - création d'une subsitution : 2 doublons (sigle substituant = AAAA car 1er dans alphabet)
    --
    INSERT INTO pre_structure(id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_structure_id_seq'), 'X123', 'test1234', 'AAAA', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    --
    -- Historisation d'un pre_structure : HOCHON PAULE AAAA
    --   - retrait pre_structure de la substitution existante (historisation) : 1 doublons restant (sigle substituant = BBBB car ordre alphabet)
    --   - historisation du substituant existant car 0 doublon restant.
    --
    update pre_structure set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_structure_2.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_a;
    assert v_structure_substit.histo_destruction is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L et histo_destruction not null', v_pre_structure_2.id, v_npd_a);

    select count(*) into v_count from structure_substit i where to_id = v_structure_substit.to_id and histo_destruction is null;
    assert v_count = 0,
        format('[TEST] Attendu : 0 structure_substit non historisé avec substituant = %s', v_structure_substit.to_id);

    select * into v_structure from structure i where id = v_structure_substit.to_id;
    assert v_structure.histo_destruction is not null,
        format('[TEST] Attendu : 1 structure substituant historisé : %', v_structure.id);

    perform test_substit_structure__tear_down();
end$$;


select test_substit_structure__fetches_data_for_substituant();
select test_substit_structure__creates_substit_2_doublons();
select test_substit_structure__creates_substit_3_doublons();
select test_substit_structure__removes_from_substit_si_historise();
select test_substit_structure__adds_to_substit_si_dehistorise();
select test_substit_structure__removes_from_substit_si_source_app();
select test_substit_structure__removes_from_substit_si_plus_source_app();
select test_substit_structure__adds_to_substit_si_npd_force();
select test_substit_structure__updates_substits_si_modif_code();
select test_substit_structure__adds_to_substit_si_ajout_npd();
select test_substit_structure__deletes_substit_si_plus_doublon();

-- ménage : select test_substit_structure__tear_down();

select * from substit_log;
select * from structure_substit order by to_id, id;

select substit_create_all_substitutions_structure(20); -- totalité : 23-24 min (avec ou sans les raise)

select * from v_structure_doublon
where nom_patronymique in ('HOCHAN', 'VIEILLE', 'BERNAUDIN', 'BRANDLE DE MOTTA', 'DEMOULIN', 'DURET')
order by nom_patronymique;


