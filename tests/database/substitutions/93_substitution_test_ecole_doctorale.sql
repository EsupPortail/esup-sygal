-- ------------------------------------------------------------------------------------------------------------------
-- Tests : ecole_doct
-- ------------------------------------------------------------------------------------------------------------------

--drop function test_substit_ecole_doct__set_up;
CREATE or replace FUNCTION test_substit_ecole_doct__set_up() returns void
    language plpgsql
as
$$begin
    alter table substit_ecole_doct disable trigger substit_trigger_on_substit_ecole_doct;
    alter table substit_structure disable trigger substit_trigger_on_substit_structure;
end$$;


--drop function test_substit_ecole_doct__tear_down;
CREATE or replace FUNCTION test_substit_ecole_doct__tear_down() returns void
    language plpgsql
as
$$begin
    delete from substit_log sl where type = 'ecole_doct' and exists (select id from ecole_doct s where sl.substitue_id = s.id and structure_id in (select id from structure where sigle = 'test1234'));
    delete from substit_log sl where type = 'ecole_doct' and exists (select id from ecole_doct s where sl.substituant_id = s.id and structure_id in (select id from structure where sigle = 'test1234'));
    delete from substit_log sl where type = 'structure' and exists (select id from structure s where sl.substitue_id = s.id and sigle = 'test1234');
    delete from substit_log sl where type = 'structure' and exists (select id from structure s where sl.substituant_id = s.id and sigle = 'test1234');

    alter table structure disable trigger substit_trigger_structure;
    alter table substit_structure disable trigger substit_trigger_on_substit_structure;
    alter table ecole_doct disable trigger substit_trigger_ecole_doct;
    alter table substit_ecole_doct disable trigger substit_trigger_on_substit_ecole_doct;

    delete from substit_fk_replacement where type = 'ecole_doct' and to_id in (select d.id from ecole_doct d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from substit_ecole_doct where from_id in (select d.id from ecole_doct d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from substit_ecole_doct where to_id in (select d.id from ecole_doct d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from ecole_doct where structure_id in (select id from structure where sigle = 'test1234');

    delete from ecole_doct where structure_id in (select id from structure where sigle = 'test1234');

    delete from substit_structure where from_id in (select id from structure where sigle = 'test1234');
    delete from substit_structure where to_id in (select id from structure where sigle = 'test1234');
    delete from structure where sigle = 'test1234';

    delete from structure where sigle = 'test1234';

    alter table structure enable trigger substit_trigger_structure;
    alter table substit_structure enable trigger substit_trigger_on_substit_structure;
    alter table ecole_doct enable trigger substit_trigger_ecole_doct;
    alter table substit_ecole_doct enable trigger substit_trigger_on_substit_ecole_doct;
end$$;


--drop function test_substit_ecole_doct__fetches_data_for_substituant;
CREATE or replace FUNCTION test_substit_ecole_doct__fetches_data_for_substituant() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
    v_pre_ecole_doct_1 ecole_doct;
    v_pre_ecole_doct_2 ecole_doct;
    v_pre_ecole_doct_3 ecole_doct;
    v_data record;
begin
    --
    -- Pour l'instant, etablissement ne porte aucune colonne dont le contenu est importé donc test non pertinent.
    --      Ci-dessous, le code si 'theme' faisait partie des colonnes importées.
    --

    /*
    perform test_substit_ecole_doct__set_up();

    v_npd_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un structure et ecole_doct associé : theme = azerty.org
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO ecole_doct(id, structure_id, theme, source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, 'azerty.org', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création attendue d'une subsitution 'azerty.org' : 2 doublons (theme = azerty.fr car ordre alpha)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO ecole_doct(id, structure_id, theme, source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, 'azerty.fr', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons (theme = azerty.org car majoritaire)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO ecole_doct(id, structure_id, theme, source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, 'azerty.com', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    select * into v_data from substit_fetch_data_for_substituant_ecole_doct(v_npd_a);

    assert v_data.theme = 'azerty.com',
        format('[TEST] Attendu : theme = %L car ordre alpha (reçu %L)', 'azerty.com', v_data.theme);

    --
    -- Modif du doublon 3 : theme 'azerty.com'
    --   => Seul changement attendu : theme = 'azerty.com' car ordre alphabet
    --
    update ecole_doct set theme = 'azerty.com' where id = v_pre_ecole_doct_3.id;

    select * into v_data from substit_fetch_data_for_substituant_ecole_doct(v_npd_a);

    assert v_data.theme = 'azerty.com',
        format('[TEST] Attendu : theme = %L car ordre alpha (reçu %L)', 'azerty.com', v_data.theme);

    perform test_substit_ecole_doct__tear_down();
    */
end$$;


--drop function test_substit_ecole_doct__creates_substit_2_doublons;
CREATE or replace FUNCTION test_substit_ecole_doct__creates_substit_2_doublons() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_structure_a varchar(256);

    v_substit_structure substit_structure;
    v_pre_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;

    v_npd_ecole_doct_a varchar(256);

    v_substit_ecole_doct substit_ecole_doct;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct ecole_doct;
    v_pre_ecole_doct_1 ecole_doct;
    v_pre_ecole_doct_2 ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un structure et ecole_doct associé : theme = azerty.org
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.de',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_1.id;
    assert v_substit_ecole_doct.id is null,
        format('[TEST] Attendu : aucun substit_ecole_doct avec from_id = %L', v_pre_ecole_doct_1.id);

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création attendue d'une subsitution : 2 doublons (theme = azerty.al car ordre alpha)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a;
    assert v_substit_ecole_doct.to_id is not null,
        format('[TEST] Attendu : 1 substit_ecole_doct avec from_id = %L et npd = %L', v_pre_ecole_doct_1.id, v_npd_ecole_doct_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_2.id and npd = v_npd_ecole_doct_a;
    assert v_substit_ecole_doct.to_id is not null,
        format('[TEST] Attendu : 1 substit_ecole_doct avec from_id = %L et npd = %L', v_pre_ecole_doct_2.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct from ecole_doct i where id = v_substit_ecole_doct.to_id;
    /*assert v_ecole_doct.theme = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.al',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_ecole_doct.structure_id);

    perform test_substit_ecole_doct__tear_down();
END$$;

/*
--drop function test_substit_ecole_doct__removes_from_substit_si_historise;
CREATE or replace FUNCTION test_substit_ecole_doct__removes_from_substit_si_historise() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_structure_a varchar(256);

    v_substit_structure substit_structure;
    v_pre_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;

    v_npd_ecole_doct_a varchar(256);

    v_substit_ecole_doct substit_ecole_doct;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct ecole_doct;
    v_pre_ecole_doct_1 ecole_doct;
    v_pre_ecole_doct_2 ecole_doct;
    v_pre_ecole_doct_3 ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un structure et ecole_doct associé : theme = azerty.org
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    --
    -- Historisation d'un ecole_doct : celui avec ecole_doct = 56
    --   - retrait ecole_doct de la substitution existante : 2 doublons restants (ecole_doct = 2 car 2<5)
    --
    update ecole_doct set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_ecole_doct_3.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_3.id and npd = v_npd_ecole_doct_a;
    assert v_substit_ecole_doct.histo_destruction is not null,
        format('[TEST] Attendu : 1 substit_ecole_doct avec from_id = %L et npd = %L et histo_destruction not null', v_pre_ecole_doct_3.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct from ecole_doct i where id = v_substit_ecole_doct.to_id;
    /*assert v_ecole_doct.theme = 'azerty.fr'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', 2, /*'azerty.fr',*/ v_ecole_doct.theme);*/

    perform test_substit_ecole_doct__tear_down();
END$$;
*/
/*
--drop function test_substit_ecole_doct__adds_to_substit_si_dehistorise;
CREATE or replace FUNCTION test_substit_ecole_doct__adds_to_substit_si_dehistorise() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_structure_a varchar(256);

    v_substit_structure substit_structure;
    v_pre_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;

    v_npd_ecole_doct_a varchar(256);

    v_substit_ecole_doct substit_ecole_doct;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct ecole_doct;
    v_pre_ecole_doct_1 ecole_doct;
    v_pre_ecole_doct_2 ecole_doct;
    v_pre_ecole_doct_3 ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un structure et ecole_doct associé : theme = azerty.org
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    --
    -- Historisation d'un ecole_doct : celui avec ecole_doct = 56
    --   - retrait ecole_doct de la substitution existante : 2 doublons restants (ecole_doct = 2 car 2<5)
    --
    update ecole_doct set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_ecole_doct_3.id;

    --
    -- Restauration d'un ecole_doct : celui avec ecole_doct = 56
    --   - ajout ecole_doct à la substitution existante : 3 doublons (ecole_doct = 5 car majoritaire)
    --
    update ecole_doct set histo_destruction = null, histo_destructeur_id = null where id = v_pre_ecole_doct_3.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_3.id and npd = v_npd_ecole_doct_a and histo_destruction is not null;
    assert v_substit_ecole_doct.to_id is not null,
        format('[TEST] Attendu : 1 substit_ecole_doct avec from_id = %L et npd = %L et histo_destruction not null', v_pre_ecole_doct_3.id, v_npd_ecole_doct_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_3.id and npd = v_npd_ecole_doct_a and histo_destruction is null;
    assert v_substit_ecole_doct.to_id is not null,
        format('[TEST] Attendu : 1 substit_ecole_doct avec from_id = %L et npd = %L et histo_destruction null', v_pre_ecole_doct_3.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct from ecole_doct i where id = v_substit_ecole_doct.to_id;
    /*assert v_ecole_doct.theme = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.al',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_ecole_doct.structure_id);

    perform test_substit_ecole_doct__tear_down();
END$$;
*/

--drop function test_substit_ecole_doct__removes_from_substit_si_source_app;
CREATE or replace FUNCTION test_substit_ecole_doct__removes_from_substit_si_source_app() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_structure_a varchar(256);

    v_substit_structure substit_structure;
    v_pre_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;

    v_npd_ecole_doct_a varchar(256);

    v_substit_ecole_doct substit_ecole_doct;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct ecole_doct;
    v_pre_ecole_doct_1 ecole_doct;
    v_pre_ecole_doct_2 ecole_doct;
    v_pre_ecole_doct_3 ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un structure et ecole_doct associé : theme = azerty.org
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    --
    -- Passage d'un ecole_doct à la source application :
    --   - retrait ecole_doct de la substitution existante : 2 doublons
    --
    update ecole_doct set source_id = 1 where id = v_pre_ecole_doct_1.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a;
    assert v_substit_ecole_doct.to_id is null,
        format('[TEST] Attendu : 1 substit_ecole_doct supprimé avec from_id = %L et npd = %L et histo_destruction not null', v_pre_ecole_doct_1.id, v_npd_ecole_doct_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_3.id;
    select * into v_ecole_doct from ecole_doct i where id = v_substit_ecole_doct.to_id;
    /*assert v_ecole_doct.theme = 'azerty.fr'/*car azerty.al a changé de source*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.fr',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_substit_structure.to_id,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_ecole_doct.structure_id);

    perform test_substit_ecole_doct__tear_down();
END$$;


--drop function test_substit_ecole_doct__removes_from_substit_si_plus_source_ap;
CREATE or replace FUNCTION test_substit_ecole_doct__removes_from_substit_si_plus_source_ap() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_structure_a varchar(256);

    v_substit_structure substit_structure;
    v_pre_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;

    v_npd_ecole_doct_a varchar(256);

    v_substit_ecole_doct substit_ecole_doct;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct ecole_doct;
    v_pre_ecole_doct_1 ecole_doct;
    v_pre_ecole_doct_2 ecole_doct;
    v_pre_ecole_doct_3 ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un structure et ecole_doct associé : theme = azerty.org
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a;

    --
    -- Passage d'un ecole_doct à la source application :
    --   - la substitution de la structure liée perdure ;
    --   - retrait ecole_doct de la substitution existante : 2 doublons
    --
    update ecole_doct set source_id = 1 where id = v_pre_ecole_doct_1.id;

    --
    -- Retour d'un ecole_doct dans la source INSA :
    --   - ajout ecole_doct à la substitution existante : 3 doublons
    --
    update ecole_doct set source_id = v_source_id where id = v_pre_ecole_doct_1.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_1.id, v_npd_structure_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where id = v_substit_ecole_doct.id;
    assert v_substit_ecole_doct.id is null,
        format('[TEST] Attendu : 1 substit_ecole_doct supprimé avec from_id = %L et npd = %L', v_pre_ecole_doct_1.id, v_npd_ecole_doct_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a;
    assert v_substit_ecole_doct.to_id is not null,
        format('[TEST] Attendu : 1 substit_ecole_doct recréé avec from_id = %L et npd = %L', v_pre_ecole_doct_1.id, v_npd_ecole_doct_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_3.id;
    select * into v_ecole_doct from ecole_doct i where id = v_substit_ecole_doct.to_id;
    /*assert v_ecole_doct.theme = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.al',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_substit_structure.to_id,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_ecole_doct.structure_id);

    perform test_substit_ecole_doct__tear_down();
END$$;


--drop function test_substit_ecole_doct__adds_to_substit_si_npd_force;
CREATE or replace FUNCTION test_substit_ecole_doct__adds_to_substit_si_npd_force() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_structure_a varchar(256);

    v_substit_structure substit_structure;
    v_pre_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
    v_pre_structure_4 structure;

    v_npd_ecole_doct_a varchar(256);

    v_substit_ecole_doct substit_ecole_doct;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct ecole_doct;
    v_pre_ecole_doct_1 ecole_doct;
    v_pre_ecole_doct_2 ecole_doct;
    v_pre_ecole_doct_3 ecole_doct;
    v_pre_ecole_doct_4 ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un structure et ecole_doct associé : theme = azerty.org
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.com',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    --
    -- Test insertion ecole_doct avec NPD forcé mais STRUCTURE SANS NPD FORCÉ :
    --   - ajout à la subsitution existante : 4 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'PAS_ETABLE_HISMAN', 'Pas Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_4; -- NB : pas de NPD forcé donc la structure n'est pas en doublon

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_4.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_ecole_doct_a
    returning * into v_pre_ecole_doct_4; -- NB : NPD forcé

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_4.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is null,
        format('[TEST] Attendu : 0 substit_structure avec from_id = %s et npd = %L car la structure elle n''est pas en doublon', v_pre_structure_4.id, v_npd_structure_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_4.id and npd = v_npd_ecole_doct_a;
    assert v_substit_ecole_doct.to_id is not null,
        format('[TEST] Attendu : 1 substit_ecole_doct avec from_id = %s et npd = %L', v_pre_ecole_doct_4.id, v_npd_ecole_doct_a, v_pre_structure_4.id);

    select * into v_ecole_doct from ecole_doct i where id = v_substit_ecole_doct.to_id;
    /*assert v_ecole_doct.theme = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.al',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_ecole_doct.structure_id);

    perform test_substit_ecole_doct__tear_down();
END$$;


--drop function test_substit_ecole_doct__adds_to_substit_si_ajout_npd;
CREATE or replace FUNCTION test_substit_ecole_doct__adds_to_substit_si_ajout_npd() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_structure_a varchar(256);

    v_substit_structure substit_structure;
    v_pre_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;

    v_npd_ecole_doct_a varchar(256);

    v_substit_ecole_doct substit_ecole_doct;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct ecole_doct;
    v_pre_ecole_doct_1 ecole_doct;
    v_pre_ecole_doct_2 ecole_doct;
    v_pre_ecole_doct_3 ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un structure et ecole_doct associé : theme = azerty.org
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    --
    -- Test insertion ecole_doct puis update du NPD forcé : COCHON Michel cccc@mail.fr
    --   - ajout à la subsitution existante : 3 doublons (ecole_doct_id = 5 car majoritaire)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'PAS_ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_3.id;
    assert v_substit_ecole_doct.to_id is null,
        format('[TEST] Attendu : aucun substit_ecole_doct avec from_id = %L ', v_pre_ecole_doct_3.id);

    update ecole_doct set npd_force = v_npd_ecole_doct_a where id = v_pre_ecole_doct_3.id;

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_3.id and npd = v_npd_ecole_doct_a;
    assert v_substit_ecole_doct.to_id is not null,
        format('[TEST] Attendu : 1 substit_ecole_doct avec from_id = %L et npd = %L', v_pre_ecole_doct_3.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct from ecole_doct i where id = v_substit_ecole_doct.to_id;
    /*assert v_ecole_doct.theme = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.al',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_ecole_doct.structure_id);

    perform test_substit_ecole_doct__tear_down();
END$$;


--drop function test_substit_ecole_doct__deletes_substit_si_plus_doublon;
CREATE or replace FUNCTION test_substit_ecole_doct__deletes_substit_si_plus_doublon() returns void
    language plpgsql
as
$$declare
    v_count bigint;
    v_id bigint;
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_structure_a varchar(256);

    v_substit_structure substit_structure;
    v_pre_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;

    v_npd_ecole_doct_a varchar(256);

    v_substit_ecole_doct substit_ecole_doct;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct ecole_doct;
    v_pre_ecole_doct_1 ecole_doct;
    v_pre_ecole_doct_2 ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un structure et ecole_doct associé : theme = azerty.org
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a;
    assert v_substit_ecole_doct.to_id is not null,
        format('[TEST] Attendu : 1 substit_ecole_doct avec from_id = %L et npd = %L', v_pre_ecole_doct_1.id, v_npd_ecole_doct_a);

    -- Modif du NPD forcé pour sortir celui avec azerty.fr de la substitution :
    --   - la substitution de la structure liée perdure ;
    --   - retrait ED de la substitution existante : 1 substitué restant ;
    --   - suppression de la substitution car 0 doublon ;
    --   - suppression du substituant.
    update ecole_doct set npd_force = 'ksldqhflksjdqhfl' where id = v_pre_ecole_doct_2.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L non historise', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_ecole_doct from substit_ecole_doct where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a;
    select count(*) into v_count from substit_ecole_doct i where to_id = v_substit_ecole_doct.to_id;
    assert v_count = 0,
        format('[TEST] Attendu : 0 substit_ecole_doct avec substituant = %s', v_substit_ecole_doct.to_id);

    select * into v_ecole_doct from ecole_doct i where id = v_substit_ecole_doct.to_id;
    assert v_ecole_doct.id is null,
        format('[TEST] Attendu : 1 ecole_doct substituant supprimé : %s', v_ecole_doct.id);

    perform test_substit_ecole_doct__tear_down();
END$$;


select test_substit_ecole_doct__fetches_data_for_substituant();
select test_substit_ecole_doct__creates_substit_2_doublons();
-- select test_substit_ecole_doct__removes_from_substit_si_historise(); -- NA dans dernière version
-- select test_substit_ecole_doct__adds_to_substit_si_dehistorise(); -- NA dans dernière version
select test_substit_ecole_doct__removes_from_substit_si_source_app();
select test_substit_ecole_doct__removes_from_substit_si_plus_source_ap();
select test_substit_ecole_doct__adds_to_substit_si_npd_force(); -- NB : NPD forcé sur pre_ecole_doct seulement (pas sur pre_structure)
select test_substit_ecole_doct__adds_to_substit_si_ajout_npd();
select test_substit_ecole_doct__deletes_substit_si_plus_doublon();

rollback;
