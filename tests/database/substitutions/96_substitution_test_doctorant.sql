-- ------------------------------------------------------------------------------------------------------------------
-- Tests : doctorant
-- ------------------------------------------------------------------------------------------------------------------

--drop function test_substit_doctorant__set_up;
CREATE or replace FUNCTION test_substit_doctorant__set_up() returns void
    language plpgsql
as
$$begin

end$$;


--drop function test_substit_doctorant__tear_down;
CREATE or replace FUNCTION test_substit_doctorant__tear_down() returns void
    language plpgsql
as
$$begin
    delete from substit_log sl where type = 'doctorant' and exists (select id from doctorant s where sl.substitue_id = s.id and individu_id in (select id from individu where nom_usuel = 'test1234'));
    delete from substit_log sl where type = 'doctorant' and exists (select id from doctorant s where sl.substituant_id = s.id and individu_id in (select id from individu where nom_usuel = 'test1234'));
    delete from substit_log sl where type = 'individu' and exists (select id from individu s where sl.substitue_id = s.id and nom_usuel = 'test1234');
    delete from substit_log sl where type = 'individu' and exists (select id from individu s where sl.substituant_id = s.id and nom_usuel = 'test1234');

    alter table doctorant disable trigger substit_trigger_doctorant;
    alter table individu disable trigger substit_trigger_individu;

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    delete from substit_fk_replacement where type = 'doctorant' and to_id in (select d.id from doctorant d join individu i on d.individu_id = i.id where nom_usuel = 'test1234');
    delete from substit_doctorant where from_id in (select d.id from doctorant d join individu i on d.individu_id = i.id where nom_usuel = 'test1234');
    delete from substit_doctorant where to_id in (select d.id from doctorant d join individu i on d.individu_id = i.id where nom_usuel = 'test1234');
    delete from doctorant where individu_id in (select id from individu where nom_usuel = 'test1234');

    delete from doctorant where individu_id in (select id from individu where nom_usuel = 'test1234');

    delete from substit_individu where from_id in (select id from individu where nom_usuel = 'test1234');
    delete from substit_individu where to_id in (select id from individu where nom_usuel = 'test1234');
    delete from individu where nom_usuel = 'test1234';

    delete from individu where nom_usuel = 'test1234';

    alter table doctorant enable trigger substit_trigger_doctorant;
    alter table individu enable trigger substit_trigger_individu;

    alter table substit_individu enable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant enable trigger substit_trigger_on_substit_doctorant;
end$$;


--drop function test_substit_doctorant__finds_doublon_ssi_doublon_individu;
CREATE or replace FUNCTION test_substit_doctorant__finds_doublon_ssi_doublon_individu() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_npd_individu_a varchar(256);
    v_npd_doctorant_a varchar(256);

    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;

    v_substit_individu substit_individu;
    v_substit_doctorant substit_doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    select * into v_pre_etab from etablissement limit 1;

    --
    -- Création d'un individu : prenom1 = 'PAUL' ;
    -- et doctorant associé : ine = '123abc'.
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAUL', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1; -- NB : prenom1 = 'PAUL'
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    --
    -- Création d'un individu : prenom1 = 'PIERRE' donc il n'est pas un doublon du 1er individu ;
    -- et doctorant associé : ine = '123abc' donc on pourrait s'attendre à ce qu'il soit détecté comme doublon du 1er doctorant.
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PIERRE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    -- Attendu : aucune substitution d'individu car les prénom1 diffèrent.

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_1.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is null,
        format('[TEST] Attendu : 0 substit_individu avec from_id = %L et npd = %L', v_pre_individu_1.id, v_npd_individu_a);

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_2.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is null,
        format('[TEST] Attendu : 0 substit_individu avec from_id = %L et npd = %L', v_pre_individu_2.id, v_npd_individu_a);

    -- Attendu : aucune substitution de doctorant créée car les individus liés respectifs ne sont pas substitués.

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is null,
        format('[TEST] Attendu : 0 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_1.id, v_npd_doctorant_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_2.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is null,
        format('[TEST] Attendu : 0 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_2.id, v_npd_doctorant_a);

    perform test_substit_doctorant__tear_down();
end$$;


--drop function test_substit_doctorant__fetches_data_for_substituant;
CREATE or replace FUNCTION test_substit_doctorant__fetches_data_for_substituant() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_pre_etab etablissement;

    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
    v_pre_doctorant_3 doctorant;
    v_data record;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    v_npd_a = 'hochon_paule_20000101,123abc';

    select * into v_pre_etab from etablissement limit 1;

    --
    -- Création d'un individu et doctorant associé : ine = 123abc, code_apprenant_in_source = PEG123
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    --
    -- Test insertion d'un doublon de doctorant : ine = 123abc, code_apprenant_in_source = PEG456
    --   - création d'une subsitution '123abc' : 2 doublons (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    --
    -- Test insertion d'un autre doublon : ine = 123abc, code_apprenant_in_source = PEG456
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (code_apprenant_in_source = PEG456 car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_3.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_3;

    select * into v_data from substit_fetch_data_for_substituant_doctorant(v_npd_a);

    assert v_data.ine = '123abc',
        format('[TEST] Attendu : ine (constituant du NPD) = %L car seule valeur (reçu %L)', '123abc', v_data.ine);

    assert v_data.code_apprenant_in_source = 'PEG456',
        format('[TEST] Attendu : code_apprenant_in_source = %L (reçu %L) car majoritaire', 'PEG456', v_data.email);

    --
    -- Modif du doublon 3 : code_apprenant_in_source 'PEG456' => 'PEG111'
    --   => Seul changement attendu : code_apprenant_in_source = 'PEG111' car ordre alphabet
    --
    update doctorant set code_apprenant_in_source = 'PEG111' where id = v_pre_doctorant_3.id;

    select * into v_data from substit_fetch_data_for_substituant_doctorant(v_npd_a);

    assert v_data.ine = '123abc',
        format('[TEST] Attendu : ine (constituant du NPD) = %L car seule valeur (reçu %L)', '123abc', v_data.ine);

    assert v_data.code_apprenant_in_source = 'PEG111',
        format('[TEST] Attendu : code_apprenant_in_source = %L (reçu %L) car ordre alphabet', 'PEG111', v_data.email);

    perform test_substit_doctorant__tear_down();
end$$;


--drop function test_substit_doctorant__creates_substit_2_doublons;
CREATE or replace FUNCTION test_substit_doctorant__creates_substit_2_doublons() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;

    v_npd_doctorant_a varchar(256);

    v_pre_etab etablissement;

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_doctorant_1 doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    select * into v_pre_etab from etablissement limit 1;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    -- Création de 2 'individu' en doublon.
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    -- Insertion des 2 doublons 'doctorant' associés :
    --   - ine = 123abc, code_apprenant_in_source = PEG123
    --   - ine = 123abc, code_apprenant_in_source = PEG456
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_2.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is not null,
        format('[TEST] Attendu : 1 substit_individu avec from_id = %L et npd = %L', v_pre_individu_2.id, v_npd_individu_a);

    select * into v_individu from individu where id = v_substit_individu.to_id;

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_1.id, v_npd_doctorant_a);
    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_2.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_2.id, v_npd_doctorant_a);

    select * into v_doctorant from doctorant i where id = v_substit_doctorant.to_id;
    assert v_doctorant.code_apprenant_in_source = 'PEG123'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)', 'PEG123', v_doctorant.code_apprenant_in_source);
    assert v_doctorant.individu_id = v_substit_individu.to_id/*id de l'individu substituant*/,
        format('[TEST] Attendu : 1 doctorant substituant avec individu_id = %s (reçu %s)',
            v_substit_individu.to_id, v_doctorant.individu_id);

    perform test_substit_doctorant__tear_down();
END$$;


--drop function test_substit_doctorant__creates_substit_3_doublons;
CREATE or replace FUNCTION test_substit_doctorant__creates_substit_3_doublons() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu_1 individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;

    v_npd_doctorant_a varchar(256);

    v_pre_etab etablissement;

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_doctorant_1 doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
    v_pre_doctorant_3 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    select * into v_pre_etab from etablissement limit 1;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    --
    -- Création de 2 'individu' en doublon.
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Insertion des 3 doublons 'doctorant' associés :
    --   - ine = 123abc, code_apprenant_in_source = PEG123
    --   - ine = 123abc, code_apprenant_in_source = PEG456
    --   - ine = 123abc, code_apprenant_in_source = PEG456
    --
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_3.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_3;

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_3.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is not null,
        format('[TEST] Attendu : 1 substit_individu avec from_id = %L et npd = %L', v_pre_individu_3.id, v_npd_individu_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_1.id, v_npd_doctorant_a);
    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_2.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_2.id, v_npd_doctorant_a);
    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_3.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_3.id, v_npd_doctorant_a);

    select * into v_doctorant from doctorant i where id = v_substit_doctorant.to_id;
    assert v_doctorant.code_apprenant_in_source = 'PEG456'/*car majoritaire*/,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)',
            'PEG456', v_doctorant.code_apprenant_in_source);
    assert v_doctorant.individu_id = v_substit_individu.to_id/*id de l'individu substituant*/,
        format('[TEST] Attendu : 1 doctorant substituant avec individu_id = %s (reçu %s)',
            v_substit_individu.to_id, v_doctorant.individu_id);

    perform test_substit_doctorant__tear_down();
END$$;


--drop function test_substit_doctorant__creates_substit_and_replaces_fk;
CREATE or replace FUNCTION test_substit_doctorant__creates_substit_and_replaces_fk() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;

    v_npd_doctorant_a varchar(256);

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table doctorant enable trigger substit_trigger_doctorant;
    alter table substit_doctorant enable trigger substit_trigger_on_substit_doctorant; -- AVEC remplacement des FK

    select * into v_pre_etab from etablissement limit 1;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    -- Création de 2 'individu' en doublon.
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    -- Insertion des 2 doublons 'doctorant' associés :
    --   - ine = 123abc, code_apprenant_in_source = PEG123
    --   - ine = 123abc, code_apprenant_in_source = PEG456
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_2.id and npd = v_npd_individu_a;
    select * into v_individu from individu where id = v_substit_individu.to_id;

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    select * into v_doctorant from doctorant where id = v_substit_doctorant.to_id;

    assert v_doctorant.individu_id = v_individu.id,
        format('[TEST] Attendu : FK DOCTORANT.individu_id remplacée par %s (mais valeur = %s)',
               v_individu.id, v_doctorant.individu_id);

    perform test_substit_doctorant__tear_down();
end$$;


--drop function test_substit_doctorant__substituant_update_enabled;
CREATE or replace FUNCTION test_substit_doctorant__substituant_update_enabled() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;

    v_npd_doctorant_a varchar(256);

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
    v_pre_doctorant_3 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    select * into v_pre_etab from etablissement limit 1;

    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    -- Création d'un individu et doctorant associé : ine = 123abc, code_apprenant_in_source = PEG123
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    -- Insertion d'un doublon : ine = 123abc, code_apprenant_in_source = PEG456
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    -- Fetch de la substitution et du substituant correspondant
    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    select * into v_doctorant from doctorant i where id = v_substit_doctorant.to_id;

    -- Verif des valeurs des attributs mis à jour automatiquement à partir des substitués
    assert v_doctorant.code_apprenant_in_source = 'PEG123'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)',
            'PEG123', v_doctorant.code_apprenant_in_source);

    -- À présent, interdiction de mise à jour automatique des valeurs des attributs du substituant à partir des substitués
    update doctorant set est_substituant_modifiable = false where id = v_doctorant.id;

    -- Insertion d'un autre doublon : ine = 123abc, code_apprenant_in_source = PEG456
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_3.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_3;

    -- Vérif que les valeurs d'attributs du substituant n'ont pas changé
    assert v_doctorant.code_apprenant_in_source = 'PEG123' /* alors que PEG456 est majoritaire */,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)',
            'PEG123', v_doctorant.code_apprenant_in_source);

    perform test_substit_doctorant__tear_down();
END$$;

/*
--drop function test_substit_doctorant__removes_from_substit_si_historise;
CREATE or replace FUNCTION test_substit_doctorant__removes_from_substit_si_historise() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu_1 individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;

    v_npd_doctorant_a varchar(256);

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_doctorant_1 doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
    v_pre_doctorant_3 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    select * into v_pre_etab from etablissement limit 1;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    --
    -- Création d'un individu et doctorant associé : ine = 123abc, code_apprenant_in_source = PEG123
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    --
    -- Test insertion d'un doublon de doctorant : ine = 123abc, code_apprenant_in_source = PEG456
    --   - création d'une subsitution '123abc' : 2 doublons (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    --
    -- Test insertion d'un autre doublon : ine = 123abc, code_apprenant_in_source = PEG456
    --   - ajout à la subsitution existante 'hochon_paule_20000101,123abc' : 3 doublons (code_apprenant_in_source = PEG456 car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_3.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_3;

    --
    -- Historisation d'un doctorant : celui avec code_apprenant_in_source = PEG456
    --   - retrait doctorant de la substitution existante : 2 doublons restants (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    update doctorant set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_doctorant_3.id;

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_3.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is not null,
        format('[TEST] Attendu : 1 substit_individu avec from_id = %L et npd = %L', v_pre_individu_3.id, v_npd_individu_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_3.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.histo_destruction is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L et histo_destruction not null', v_pre_doctorant_3.id, v_npd_doctorant_a);

    select * into v_doctorant from doctorant i where id = v_substit_doctorant.to_id;
    assert v_doctorant.code_apprenant_in_source = 'PEG123'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)',
            'PEG123', v_doctorant.code_apprenant_in_source);
    assert v_doctorant.individu_id = v_substit_individu.to_id/*id de l'individu substituant*/,
        format('[TEST] Attendu : 1 doctorant substituant avec individu_id = %s (reçu %s)',
            v_substit_individu.to_id, v_doctorant.individu_id);

    perform test_substit_doctorant__tear_down();
END$$;
*/
/*
--drop function test_substit_doctorant__adds_to_substit_si_dehistorise;
CREATE or replace FUNCTION test_substit_doctorant__adds_to_substit_si_dehistorise() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu_1 individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;

    v_npd_doctorant_a varchar(256);

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_doctorant_1 doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
    v_pre_doctorant_3 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    select * into v_pre_etab from etablissement limit 1;

    --
    -- Création d'un individu et doctorant associé : ine = 123abc, code_apprenant_in_source = PEG123
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    --
    -- Test insertion d'un doublon de doctorant : ine = 123abc, code_apprenant_in_source = PEG456
    --   - création d'une subsitution '123abc' : 2 doublons (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    --
    -- Test insertion d'un autre doublon : ine = 123abc, code_apprenant_in_source = PEG456
    --   - ajout à la subsitution existante 'hochon_paule_20000101,123abc' : 3 doublons (code_apprenant_in_source = PEG456 car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_3.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_3;

    --
    -- Historisation d'un doctorant : celui avec code_apprenant_in_source = PEG456
    --   - retrait doctorant de la substitution existante : 2 doublons restants (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    update doctorant set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_doctorant_3.id;

    --
    -- Restauration d'un doctorant : celui avec code_apprenant_in_source = PEG456
    --   - ajout doctorant à la substitution existante : 3 doublons (code_apprenant_in_source = PEG456 car majoritaire)
    --
    update doctorant set histo_destruction = null, histo_destructeur_id = null where id = v_pre_doctorant_3.id;

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_3.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is not null,
        format('[TEST] Attendu : 1 substit_individu avec from_id = %L et npd = %L', v_pre_individu_3.id, v_npd_individu_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_3.id and npd = v_npd_doctorant_a and histo_destruction is not null;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L et histo_destruction not null', v_pre_doctorant_3.id, v_npd_doctorant_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_3.id and npd = v_npd_doctorant_a and histo_destruction is null;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L et histo_destruction null', v_pre_doctorant_3.id, v_npd_doctorant_a);

    select * into v_doctorant from doctorant i where id = v_substit_doctorant.to_id;
    assert v_doctorant.code_apprenant_in_source = 'PEG456'/*car majoritaire*/,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)',
            'PEG456', v_doctorant.code_apprenant_in_source);
    assert v_doctorant.individu_id = v_substit_individu.to_id/*id de l'individu substituant*/,
        format('[TEST] Attendu : 1 doctorant substituant avec individu_id = %s (reçu %s)',
            v_substit_individu.to_id, v_doctorant.individu_id);

    perform test_substit_doctorant__tear_down();
END$$;
*/

--drop function test_substit_doctorant__removes_from_substit_si_source_app;
CREATE or replace FUNCTION test_substit_doctorant__removes_from_substit_si_source_app() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu_1 individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;

    v_npd_doctorant_a varchar(256);

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_doctorant_1 doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
    v_pre_doctorant_3 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    select * into v_pre_etab from etablissement limit 1;

    --
    -- Création d'un individu et doctorant associé : ine = 123abc, code_apprenant_in_source = PEG123
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    --
    -- Test insertion d'un doublon de doctorant : ine = 123abc, code_apprenant_in_source = PEG456
    --   - création d'une subsitution '123abc' : 2 doublons (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    --
    -- Test insertion d'un autre doublon : ine = 123abc, code_apprenant_in_source = PEG456
    --   - ajout à la subsitution existante 'hochon_paule_20000101,123abc' : 3 doublons (code_apprenant_in_source = PEG456 car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_3.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_3;

    --
    -- Passage d'un doctorant à la source application : HOCHON PAULE aaaa@mail.fr
    --   - retrait doctorant de la substitution existante : 2 doublons (code_apprenant_in_source = PEG456 car seule valeur)
    --
    update doctorant set source_id = 1 where id = v_pre_doctorant_1.id;

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_3.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is not null,
        format('[TEST] Attendu : 1 substit_individu avec from_id = %L et npd = %L', v_pre_individu_3.id, v_npd_individu_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is null,
        format('[TEST] Attendu : 1 substit_doctorant supprimé avec from_id = %L et npd = %L', v_pre_doctorant_1.id, v_npd_doctorant_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_2.id and npd = v_npd_doctorant_a;
    select * into v_doctorant from doctorant i where id = v_substit_doctorant.to_id;
    assert v_doctorant.code_apprenant_in_source = 'PEG456'/*car seule valeur*/,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)', 2,
            'PEG456', v_doctorant.code_apprenant_in_source);
    assert v_doctorant.individu_id = v_substit_individu.to_id/*id de l'individu substituant*/,
        format('[TEST] Attendu : 1 doctorant substituant avec individu_id = %s (reçu %s)',
            v_substit_individu.to_id, v_doctorant.individu_id);

    perform test_substit_doctorant__tear_down();
END$$;


--drop function test_substit_doctorant__removes_from_substit_si_plus_source_app;
CREATE or replace FUNCTION test_substit_doctorant__removes_from_substit_si_plus_source_app() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu_1 individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;

    v_npd_doctorant_a varchar(256);

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_doctorant_1 doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
    v_pre_doctorant_3 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    select * into v_pre_etab from etablissement limit 1;

    --
    -- Création d'un individu et doctorant associé : ine = 123abc, code_apprenant_in_source = PEG123
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    --
    -- Test insertion d'un doublon de doctorant : ine = 123abc, code_apprenant_in_source = PEG456
    --   - création d'une subsitution '123abc' : 2 doublons (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    --
    -- Test insertion d'un autre doublon : ine = 123abc, code_apprenant_in_source = PEG456
    --   - ajout à la subsitution existante 'hochon_paule_20000101,123abc' : 3 doublons (code_apprenant_in_source = PEG456 car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_3.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_3;

    --
    -- Passage d'un doctorant à la source application : HOCHON PAULE aaaa@mail.fr
    --   - retrait doctorant de la substitution existante : 2 doublons (code_apprenant_in_source = PEG456 car seule valeur)
    --
    update doctorant set source_id = 1 where id = v_pre_doctorant_1.id;

    --
    -- Retour d'un doctorant dans la source INSA :
    --   - ajout doctorant à la substitution existante : 3 doublons (code_apprenant_in_source = PEG456 car majoritaire)
    --
    update doctorant set source_id = v_source_id where id = v_pre_doctorant_1.id;

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_1.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is not null,
        format('[TEST] Attendu : 1 substit_individu avec from_id = %L et npd = %L', v_pre_individu_1.id, v_npd_individu_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_1.id, v_npd_doctorant_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_2.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_1.id, v_npd_doctorant_a);

    select * into v_doctorant from doctorant i where id = v_substit_doctorant.to_id;
    assert v_doctorant.code_apprenant_in_source = 'PEG456'/*car majoritaire*/,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)',
            'PEG456', v_doctorant.code_apprenant_in_source);
    assert v_doctorant.individu_id = v_substit_individu.to_id/*id de l'individu substituant*/,
        format('[TEST] Attendu : 1 doctorant substituant avec individu_id = %s (reçu %s)',
            v_substit_individu.to_id, v_doctorant.individu_id);

    perform test_substit_doctorant__tear_down();
END$$;


--drop function test_substit_doctorant__adds_to_substit_si_npd_force;
CREATE or replace FUNCTION test_substit_doctorant__adds_to_substit_si_npd_force() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu_1 individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
    v_pre_individu_4 individu;

    v_npd_doctorant_a varchar(256);

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_doctorant_1 doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
    v_pre_doctorant_3 doctorant;
    v_pre_doctorant_4 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    select * into v_pre_etab from etablissement limit 1;

    --
    -- Création d'un individu et doctorant associé : ine = 123abc, code_apprenant_in_source = PEG123
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    --
    -- Test insertion d'un doublon de doctorant : ine = 123abc, code_apprenant_in_source = PEG456
    --   - création d'une subsitution '123abc' : 2 doublons (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    --
    -- Test insertion d'un autre doublon : ine = 123abc, code_apprenant_in_source = PEG456
    --   - ajout à la subsitution existante 'hochon_paule_20000101,123abc' : 3 doublons (code_apprenant_in_source = PEG456 car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_3.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_3;

    --
    -- Test insertion doctorant avec NPD forcé : HOCHON Paulette aaaa@mail.fr
    --   - ajout à la subsitution existante : 4 doublons (code_apprenant_in_source = PEG456 car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'Paulette', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_4; -- NB : pas de NPD forcé donc l'individu n'est pas en doublon

    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_4.id, 'PEUIMPORTE', v_pre_etab.id, 'PEG456', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_doctorant_a
    returning * into v_pre_doctorant_4; -- NB : NPD forcé

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_4.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is null,
        format('[TEST] Attendu : 0 substit_individu avec from_id = %s et npd = %L car l''individu lui n''est pas en doublon', v_pre_individu_4.id, v_npd_individu_a);

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_3.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is not null,
        format('[TEST] Attendu : 1 substit_individu avec from_id = %s et npd = %L', v_pre_individu_3.id, v_npd_individu_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_4.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %s et npd = %L', v_pre_doctorant_4.id, v_npd_doctorant_a, v_pre_individu_4.id);

    select * into v_doctorant from doctorant i where id = v_substit_doctorant.to_id;
    assert v_doctorant.code_apprenant_in_source = 'PEG456'/*car majoritaire*/,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)',
            'PEG456', v_doctorant.code_apprenant_in_source);
    assert v_doctorant.individu_id = v_substit_individu.to_id/*id de l'individu substituant*/,
        format('[TEST] Attendu : 1 doctorant substituant avec individu_id = %s (reçu %s)',
            v_substit_individu.to_id, v_doctorant.individu_id);

    perform test_substit_doctorant__tear_down();
END$$;


--drop function test_substit_doctorant__updates_substits_si_modif_ine;
CREATE or replace FUNCTION test_substit_doctorant__updates_substits_si_modif_ine() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu_1 individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
    v_pre_individu_4 individu;

    v_npd_doctorant_a varchar(256);
    v_npd_doctorant_b varchar(256);

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_doctorant_1 doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
    v_pre_doctorant_3 doctorant;
    v_pre_doctorant_4 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    select * into v_pre_etab from etablissement limit 1;

    --
    -- Création d'un individu et doctorant associé : ine = 123abc, code_apprenant_in_source = PEG123
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    --
    -- Test insertion d'un doublon de doctorant : ine = 123abc, code_apprenant_in_source = PEG456
    --   - création d'une subsitution '123abc' : 2 doublons (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    --
    -- Test insertion d'un autre doublon : ine = 123abc, code_apprenant_in_source = PEG456
    --   - ajout à la substitution existante 'hochon_paule_20000101,123abc' : 3 doublons (code_apprenant_in_source = PEG456 car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_3.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_3;

    v_npd_doctorant_b = 'hochon_paule_20000101,666ABC';

    --
    -- Création doctorant avec ine = 666ABC, code_apprenant_in_source = PEG123
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHAN', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_4;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_4.id, '666ABC', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_4;

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_4.id;
    assert v_substit_doctorant.to_id is null,
        format('[TEST] Attendu : aucun substit_doctorant avec from_id = %s', v_pre_doctorant_4.id);

    --
    -- Test modif INE doctorant déjà présent dans une substitution : ine 123abc => 666ABC
    --   - pas de substitution de doctorant possible car l'individu lié HOCHAN n'a pas de doublon (cf. calcul NPD doctorant).
    --   - retrait doctorant de la substitution existante 123abc : 3 doublons (code_apprenant_in_source = PEG123 car ordre alpha)
    --
    update doctorant set ine = '666ABC' where id = v_pre_doctorant_2.id; -- 123abc => 666ABC

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_2.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is null,
        format('[TEST] Attendu : 1 substit_doctorant supprimé avec from_id = %L et npd = %L', v_pre_doctorant_2.id, v_npd_doctorant_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    select * into v_doctorant from doctorant i where id = v_substit_doctorant.to_id;
    assert v_doctorant.code_apprenant_in_source = 'PEG123'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)',
            'PEG456', v_doctorant.code_apprenant_in_source);

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_4.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is null,
        format('[TEST] Attendu : aucun substit_individu avec from_id = %s et npd = %L', v_pre_individu_4.id, v_npd_individu_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_2.id and npd = v_npd_doctorant_b;
    assert v_substit_doctorant.to_id is null,
        format('[TEST] Attendu : aucun substit_doctorant avec from_id = %s et npd = %L', v_pre_doctorant_2.id, v_npd_doctorant_b);

    perform test_substit_doctorant__tear_down();
END$$;


--drop function test_substit_doctorant__adds_to_substit_si_ajout_npd;
CREATE or replace FUNCTION test_substit_doctorant__adds_to_substit_si_ajout_npd() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu_1 individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;

    v_npd_doctorant_a varchar(256);

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_doctorant_1 doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
    v_pre_doctorant_3 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    select * into v_pre_etab from etablissement limit 1;

    --
    -- Création d'un individu et doctorant associé : ine = 123abc, code_apprenant_in_source = PEG123
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    --
    -- Test insertion d'un doublon de doctorant : ine = 123abc, code_apprenant_in_source = PEG456
    --   - création d'une subsitution '123abc' : 2 doublons (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_2.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is not null,
        format('[TEST] Attendu : 1 substit_individu avec from_id = %s et npd = %L', v_pre_individu_2.id, v_npd_individu_a);

    --
    -- Test insertion doctorant puis update du NPD forcé : COCHON Michel cccc@mail.fr
    --   - ajout à la subsitution existante : 3 doublons (etablissement_id = 5 car majoritaire, code_apprenant_in_source = PEG123 car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'COCHON', 'test1234', 'Michel', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_3.id, 'PEUIMPORTE', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_3;

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_3.id;
    assert v_substit_doctorant.to_id is null,
        format('[TEST] Attendu : aucun substit_doctorant avec from_id = %L ', v_pre_doctorant_3.id);

    update doctorant set npd_force = v_npd_doctorant_a where id = v_pre_doctorant_3.id;

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_3.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_3.id, v_npd_doctorant_a);

    select * into v_doctorant from doctorant i where id = v_substit_doctorant.to_id;
    assert v_doctorant.code_apprenant_in_source = 'PEG123'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 doctorant substituant avec code_apprenant_in_source = %L (reçu %L)',
            'PEG123', v_doctorant.code_apprenant_in_source);
    assert v_doctorant.individu_id = v_substit_individu.to_id/*id de l'individu substituant*/,
        format('[TEST] Attendu : 1 doctorant substituant avec individu_id = %s (reçu %s)',
            v_substit_individu.to_id, v_doctorant.individu_id);

    perform test_substit_doctorant__tear_down();
END$$;


--drop function test_substit_doctorant__deletes_substit_si_plus_doublon;
CREATE or replace FUNCTION test_substit_doctorant__deletes_substit_si_plus_doublon() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_pre_etab etablissement;

    v_npd_individu_a varchar(256);

    v_substit_individu substit_individu;
    v_individu_1 individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;

    v_npd_doctorant_a varchar(256);

    v_substit_doctorant substit_doctorant;
    v_doctorant doctorant;
    v_doctorant_1 doctorant;
    v_pre_doctorant_1 doctorant;
    v_pre_doctorant_2 doctorant;
begin
    perform test_substit_doctorant__set_up();

    alter table substit_individu disable trigger substit_trigger_on_substit_individu;
    alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;

    v_npd_individu_a = 'hochon_paule_20000101';
    v_npd_doctorant_a = 'hochon_paule_20000101,123abc';

    select * into v_pre_etab from etablissement limit 1;

    --
    -- Création d'un individu et doctorant associé : ine = 123abc, code_apprenant_in_source = PEG123
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;
    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_1.id, '123abc', v_pre_etab.id, 'PEG123', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_1;

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id;
    assert v_substit_doctorant.id is null,
        format('[TEST] Attendu : aucun substit_doctorant avec from_id = %L', v_pre_doctorant_1.id);

    --
    -- Test insertion d'un doublon de doctorant : ine = 123abc, code_apprenant_in_source = PEG456
    --   - création d'une subsitution '123abc' : 2 doublons (code_apprenant_in_source = PEG123 car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    INSERT INTO doctorant(id, individu_id, ine, etablissement_id, code_apprenant_in_source, source_code, source_id, histo_createur_id, npd_force)
    select nextval('doctorant_id_seq'), v_pre_individu_2.id, '123abc', v_pre_etab.id, 'PEG456', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_doctorant_2;

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_2.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is not null,
        format('[TEST] Attendu : 1 substit_individu avec from_id = %L et npd = %L', v_pre_individu_2.id, v_npd_individu_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    assert v_substit_doctorant.to_id is not null,
        format('[TEST] Attendu : 1 substit_doctorant avec from_id = %L et npd = %L', v_pre_doctorant_1.id, v_npd_doctorant_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    select * into v_doctorant from doctorant where id = v_substit_doctorant.to_id;

    -- Modif du NPD forcé pour sortir celui avec code_apprenant_in_source PEG456 de la substitution :
    --   - la substitution de l'individu lié perdure ;
    --   - retrait doctorant de la substitution existante : 1 substitué restant (code_apprenant_in_source = PEG123 car ordre alphabet) ;
    --   - suppression de la substitution car 0 doublon ;
    --   - suppression du substituant.
    update doctorant set npd_force = 'ksldqhflksjdqhfl' where id = v_pre_doctorant_2.id;

    select * into v_substit_individu from substit_individu where from_id = v_pre_individu_2.id and npd = v_npd_individu_a;
    assert v_substit_individu.to_id is not null,
        format('[TEST] Attendu : 1 substit_individu conservé avec from_id = %s et npd = %L', v_pre_individu_2.id, v_npd_individu_a);

    select * into v_substit_doctorant from substit_doctorant where from_id = v_pre_doctorant_1.id and npd = v_npd_doctorant_a;
    select count(*) into v_count from substit_doctorant where to_id = v_substit_doctorant.to_id;
    assert v_count = 0,
        format('[TEST] Attendu : 0 substit_doctorant non historisé avec substituant = %s', v_substit_doctorant.to_id);

    select * into v_doctorant from doctorant i where id = v_doctorant.id;
    assert v_doctorant.id is null,
        format('[TEST] Attendu : 1 doctorant substituant supprimé : %', v_doctorant.id);

    perform test_substit_doctorant__tear_down();
END$$;


-- ------------------------------------------------------------------------------------------------------------------
-- Tests : doctorant
-- ------------------------------------------------------------------------------------------------------------------

select test_substit_doctorant__fetches_data_for_substituant();
select test_substit_doctorant__finds_doublon_ssi_doublon_individu();
select test_substit_doctorant__creates_substit_2_doublons();
select test_substit_doctorant__creates_substit_3_doublons();
select test_substit_doctorant__creates_substit_and_replaces_fk();
-- select test_substit_doctorant__adds_to_substit_si_dehistorise(); -- NA dans dernière version
--select test_substit_doctorant__removes_from_substit_si_historise(); -- NA dans dernière version
select test_substit_doctorant__adds_to_substit_si_npd_force();
select test_substit_doctorant__updates_substits_si_modif_ine();
select test_substit_doctorant__adds_to_substit_si_ajout_npd();
select test_substit_doctorant__substituant_update_enabled();
select test_substit_doctorant__removes_from_substit_si_source_app();
select test_substit_doctorant__removes_from_substit_si_plus_source_app();
select test_substit_doctorant__deletes_substit_si_plus_doublon();
/*
select * from substit_log;
select * from substit_individu order by to_id, id;
select * from substit_doctorant order by to_id, id;
select * from pre_doctorant where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select * from src_doctorant where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select * from doctorant where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select i.id, v.* from v_diff_doctorant v join pre_doctorant i on v.source_code = i.source_code;

select substit_create_all_substitutions_doctorant(20); -- totalité : 23-24 min (avec ou sans les raise)
*/
