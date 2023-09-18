--drop function test_substit_individu__tear_down;
CREATE or replace FUNCTION test_substit_individu__tear_down() returns void
    language plpgsql
as
$$begin
    delete from individu_substit where from_id in (select id from pre_individu where nom_usuel = 'test1234');
    delete from individu_substit where to_id in (select id from individu where nom_usuel = 'test1234');
    delete from individu where nom_usuel = 'test1234';
    truncate table substit_log;
    alter table pre_individu disable trigger individu_substit_trigger;
    delete from pre_individu where nom_usuel = 'test1234';
    alter table pre_individu enable trigger individu_substit_trigger;
end$$;


--drop function test_substit_individu__fetches_data_for_substituant;
CREATE or replace FUNCTION test_substit_individu__fetches_data_for_substituant() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
    v_pre_individu_3 pre_individu;
    v_data record;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Création d'un doublon : HOCHON PAULE bbbb@mail.fr
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Création d'un autre doublon : HÔCHON Paule cccc@mail.fr
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'Hochon', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    select * into v_data from substit_fetch_data_for_substituant_individu(v_npd_a);

    assert v_data.nom_patronymique = 'HOCHON',
        format('[TEST] Attendu : nom_patronymique (constituant du NPD) = %L car majoritaire (reçu %L)', 'HOCHON', v_data.nom_patronymique);
    assert v_data.prenom1 = 'PAULE',
        format('[TEST] Attendu : prenom1 (constituant du NPD) = %L car majoritaire (reçu %L)', 'PAULE', v_data.prenom1);
    assert v_data.date_naissance = '2000-01-01',
        format('[TEST] Attendu : date_naissance (constituant du NPD) = %L (reçu %L)', '2000-01-01', v_data.date_naissance);

    assert v_data.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : email = %L (reçu %L) car ordre alphabet', 'aaaa@mail.fr', v_data.email);

    --
    -- Modif du doublon 1 : PAULE => Paule
    --   => Seul changement attendu : prenom1 = 'Paule' car devient majoritaire.
    --
    update pre_individu set prenom1 = 'Paule' where id = v_pre_individu_1.id;

    select * into v_data from substit_fetch_data_for_substituant_individu(v_npd_a);

    assert v_data.nom_patronymique = 'HOCHON',
        format('[TEST] Attendu : nom_patronymique (constituant du NPD) = %L car majoritaire (reçu %L)', 'HOCHON', v_data.nom_patronymique);
    assert v_data.prenom1 = 'Paule',
        format('[TEST] Attendu : prenom1 (constituant du NPD) = %L car majoritaire (reçu %L)', 'Paule', v_data.prenom1);
    assert v_data.date_naissance = '2000-01-01',
        format('[TEST] Attendu : date_naissance (constituant du NPD) = %L (reçu %L)', '2000-01-01', v_data.date_naissance);

    assert v_data.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : email = %L (reçu %L) car ordre alphabet', 'aaaa@mail.fr', v_data.email);

    --
    -- Modif du doublon 1 : aaaa@mail.fr => cccc@mail.fr
    --   => Seul changement attendu : email = cccc@mail.fr car majoritaire.
    --
    update pre_individu set email = 'cccc@mail.fr' where id = v_pre_individu_1.id;

    select * into v_data from substit_fetch_data_for_substituant_individu(v_npd_a);

    assert v_data.nom_patronymique = 'HOCHON',
        format('[TEST] Attendu : nom_patronymique (constituant du NPD) = %L car majoritaire (reçu %L)', 'HOCHON', v_data.nom_patronymique);
    assert v_data.prenom1 = 'Paule',
        format('[TEST] Attendu : prenom1 (constituant du NPD) = %L car majoritaire (reçu %L)', 'Paule', v_data.prenom1);
    assert v_data.date_naissance = '2000-01-01',
        format('[TEST] Attendu : date_naissance (constituant du NPD) = %L (reçu %L)', '2000-01-01', v_data.date_naissance);

    assert v_data.email = 'cccc@mail.fr',
        format('[TEST] Attendu : email = %L (reçu %L) car ordre alphabet', 'cccc@mail.fr', v_data.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__creates_substit_2_doublons;
CREATE or replace FUNCTION test_substit_individu__creates_substit_2_doublons() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu pre_individu;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id;
    assert v_individu_substit.to_id is null,
        format('[TEST] Attendu : aucun individu_substit avec from_id = % ', v_pre_individu_1.id);

    --
    -- Test insertion d'un doublon : HOCHON PAULE aaaa@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_1.id, v_npd_a);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_2.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert not (v_pre_individu is null or v_pre_individu.email <> 'aaaa@mail.fr'),
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'aaaa@mail.fr', v_pre_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__creates_substit_3_doublons;
CREATE or replace FUNCTION test_substit_individu__creates_substit_3_doublons() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu pre_individu;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
    v_pre_individu_3 pre_individu;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id;
    assert v_individu_substit.to_id is null,
        format('[TEST] Attendu : aucun individu_substit avec from_id = % ', v_pre_individu_1.id);

    --
    -- Test insertion d'un doublon : HOCHON PAULE aaaa@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_1.id, v_npd_a);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_2.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert not (v_pre_individu is null or v_pre_individu.email <> 'aaaa@mail.fr'),
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'aaaa@mail.fr', v_pre_individu.email);

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule bbbb@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = bbbb@mail.fr car majoritaire)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_3.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_3.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert not (v_pre_individu is null or v_pre_individu.email <> 'bbbb@mail.fr'),
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'bbbb@mail.fr', v_pre_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__removes_from_substit_si_historise;
CREATE or replace FUNCTION test_substit_individu__removes_from_substit_si_historise() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu pre_individu;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
    v_pre_individu_3 pre_individu;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE aaaa@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule cccc@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Historisation d'un pre_individu : HOCHON PAULE aaaa@mail.fr
    --   - retrait pre_individu de la substitution existante 'hochon_paule_20000101' (historisation) : 2 doublons restants (mail substituant = bbbb@mail.fr car ordre alphabet)
    --
    update pre_individu set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_individu_2.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.histo_destruction is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L et histo_destruction not null', v_pre_individu_2.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert not (v_pre_individu is null or v_pre_individu.email <> 'bbbb@mail.fr'),
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'bbbb@mail.fr', v_pre_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__adds_to_substit_si_dehistorise;
CREATE or replace FUNCTION test_substit_individu__adds_to_substit_si_dehistorise() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu pre_individu;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
    v_pre_individu_3 pre_individu;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule cccc@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Historisation d'un pre_individu : HOCHON PAULE bbbb@mail.fr
    --
    update pre_individu set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_individu_2.id;

    --
    -- Restauration d'un pre_individu : HOCHON PAULE bbbb@mail.fr
    --   - ajout pre_individu dans la substitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alphabet)
    --
    update pre_individu set histo_destruction = null, histo_destructeur_id = null where id = v_pre_individu_2.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a and histo_destruction is null;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L et histo_destruction null', v_pre_individu_2.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert v_pre_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'aaaa@mail.fr', v_pre_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__removes_from_substit_si_source_app;
CREATE or replace FUNCTION test_substit_individu__removes_from_substit_si_source_app() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu pre_individu;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
    v_pre_individu_3 pre_individu;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule cccc@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Passage d'un pre_individu subsitué dans la source application : HOCHON PAULE aaaa@mail.fr
    --   - retrait pre_individu de la substitution existante : 2 doublons restants (mail substituant = bbbb@mail.fr car ordre alpha)
    --
    update pre_individu set source_id = 1 where id = v_pre_individu_1.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a and histo_destruction is not null;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L et histo_destruction not null', v_pre_individu_1.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert v_pre_individu.email = 'bbbb@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'bbbb@mail.fr', v_pre_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__removes_from_substit_si_plus_source_app;
CREATE or replace FUNCTION test_substit_individu__removes_from_substit_si_plus_source_app() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu pre_individu;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
    v_pre_individu_3 pre_individu;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule cccc@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Passage d'un pre_individu subsitué dans la source application : HOCHON PAULE aaaa@mail.fr
    --   - retrait pre_individu de la substitution existante : 2 doublons restants (mail substituant = bbbb@mail.fr car ordre alpha)
    --
    update pre_individu set source_id = 1 where id = v_pre_individu_1.id;

    --
    -- Retour d'un pre_individu dans la source INSA : HOCHON PAULE aaaa@mail.fr
    --   - ajout pre_individu dans la substitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alpha)
    --
    update pre_individu set source_id = v_source_id where id = v_pre_individu_1.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a and histo_destruction is not null;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L et histo_destruction not null', v_pre_individu_1.id, v_npd_a);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a and histo_destruction is null;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L et histo_destruction null', v_pre_individu_1.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert not (v_pre_individu is null or v_pre_individu.email <> 'aaaa@mail.fr'),
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'aaaa@mail.fr', v_pre_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__adds_to_substit_si_npd_force;
CREATE or replace FUNCTION test_substit_individu__adds_to_substit_si_npd_force() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu pre_individu;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion pre_individu avec NPD forcé : HOCHON Paulette aaaa@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'Paulette', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_a
    returning * into v_pre_individu_2;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_2.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert v_pre_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'aaaa@mail.fr', v_pre_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__updates_substits_si_modif_nom;
CREATE or replace FUNCTION test_substit_individu__updates_substits_si_modif_nom() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);
    v_npd_b varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu pre_individu;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
    v_pre_individu_3 pre_individu;
    v_pre_individu_4 pre_individu;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule bbbb@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = bbbb@mail.fr car majoritaire)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Création d'un pre_individu : HOCHAN PAULE (mail = cccc@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHAN', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_4;

    v_npd_b = 'hochan_paule_20000101';

    --
    -- Test modif HÔCHON (bbbb@mail.fr) => HOCHAN (bbbb@mail.fr) :
    --   - retrait individu de la substitution existante 'hochon_paule_20000101' (historisation) : 3 doublons restants (mail substituant = aaaa@mail.fr car majoritaire)
    --   - création d'une nouvelle substitution 'hochan_paule_20000101' : 2 doublons (mail substituant = bbbb@mail.fr car 1 vs 1 mais 1er dans alphabet)
    --
    update pre_individu set nom_patronymique = 'HOCHAN' where id = v_pre_individu_3.id; -- 'HÔCHON' => 'HOCHAN'

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_3.id and npd = v_npd_a and histo_destruction is not null;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L et histo_destruction not null', v_pre_individu_3.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert v_pre_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = %s (mais email = %L)', 'aaaa@mail.fr', v_pre_individu.email);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_3.id and npd = v_npd_b;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_3.id, v_npd_b);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_4.id and npd = v_npd_b;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_4.id, v_npd_b);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert not (v_pre_individu is null or v_pre_individu.email <> 'bbbb@mail.fr'),
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'bbbb@mail.fr', v_pre_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__adds_to_substit_si_ajout_npd;
CREATE or replace FUNCTION test_substit_individu__adds_to_substit_si_ajout_npd() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);
    v_npd_b varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu pre_individu;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
    v_pre_individu_3 pre_individu;
    v_pre_individu_4 pre_individu;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule bbbb@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = bbbb@mail.fr car majoritaire)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Test insertion individu puis update du NPD forcé : COCHON Michel aaaa@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 4 doublons (mail substituant = aaaa@mail.fr car 2 contre 2 mais ordre alpha)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id)
    select nextval('pre_individu_id_seq'), 'COCHON', 'test1234', 'Michel', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_individu_4;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_4.id;
    assert v_individu_substit.to_id is null,
        format('[TEST] Attendu : aucun individu_substit avec from_id = % ', v_pre_individu_4.id);

    update pre_individu set npd_force = 'hochon_paule_20000101' where id = v_pre_individu_4.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_4.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_4.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert v_pre_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'aaaa@mail.fr', v_pre_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__adds_to_substit_si_suppr_npd;
CREATE or replace FUNCTION test_substit_individu__adds_to_substit_si_suppr_npd() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);
    v_npd_b varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu pre_individu;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
    v_pre_individu_3 pre_individu;
    v_pre_individu_4 pre_individu;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule bbbb@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = bbbb@mail.fr car majoritaire)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Test insertion individu avec NPD forcé : COCHON Michel aaaa@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 4 doublons (mail substituant = aaaa@mail.fr car 2 contre 2 mais ordre alpha)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'COCHON', 'test1234', 'Michel', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_a
    returning * into v_pre_individu_4;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_4.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_4.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert v_pre_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'aaaa@mail.fr', v_pre_individu.email);

    --
    -- Effacement du NPD forcé.
    --
    update pre_individu set npd_force = null where id = v_pre_individu_4.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_4.id and npd = v_npd_a;
    assert v_individu_substit.histo_destruction is not null,
        format('[TEST] Attendu : 1 individu_substit historisé avec from_id = % et npd = %', v_pre_individu_4.id, v_npd_a);

    select * into v_pre_individu from individu i where id = v_individu_substit.to_id;
    assert v_pre_individu.email = 'bbbb@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'bbbb@mail.fr', v_pre_individu.email);


    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__deletes_substit_si_plus_doublon;
CREATE or replace FUNCTION test_substit_individu__deletes_substit_si_plus_doublon() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);
    v_npd_b varchar(256);

    v_individu_substit individu_substit;
    v_pre_individu_1 pre_individu;
    v_pre_individu_2 pre_individu;
    v_individu individu;
    v_count smallint;
begin
    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE aaaa@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO pre_individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('pre_individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Historisation d'un pre_individu : HOCHON PAULE aaaa@mail.fr
    --   - retrait pre_individu de la substitution existante (historisation) : 1 doublons restant (mail substituant = bbbb@mail.fr car ordre alphabet)
    --   - historisation du substituant existant car 0 doublon restant.
    --
    update pre_individu set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_individu_2.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.histo_destruction is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L et histo_destruction not null', v_pre_individu_2.id, v_npd_a);

    select count(*) into v_count from individu_substit i where to_id = v_individu_substit.to_id and histo_destruction is null;
    assert v_count = 0,
        format('[TEST] Attendu : 0 individu_substit non historisé avec substituant = %s', v_individu_substit.to_id);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.histo_destruction is not null,
        format('[TEST] Attendu : 1 individu substituant historisé : %', v_individu.id);

    perform test_substit_individu__tear_down();
end$$;


alter table individu disable trigger individu_rech_update;
select test_substit_individu__fetches_data_for_substituant();
select test_substit_individu__creates_substit_2_doublons();
select test_substit_individu__creates_substit_3_doublons();
select test_substit_individu__removes_from_substit_si_historise();
select test_substit_individu__adds_to_substit_si_dehistorise();
select test_substit_individu__removes_from_substit_si_source_app();
select test_substit_individu__removes_from_substit_si_plus_source_app();
select test_substit_individu__adds_to_substit_si_npd_force();
select test_substit_individu__updates_substits_si_modif_nom();
select test_substit_individu__adds_to_substit_si_ajout_npd();
select test_substit_individu__adds_to_substit_si_suppr_npd();
select test_substit_individu__deletes_substit_si_plus_doublon();
alter table individu enable trigger individu_rech_update;

-- ménage : perform test_substit_individu__tear_down();
