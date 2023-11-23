--drop function test_substit_ecole_doct__set_up;
CREATE or replace FUNCTION test_substit_ecole_doct__set_up() returns void
    language plpgsql
as
$$begin
    alter table ecole_doct_substit disable trigger substit_trigger_on_ecole_doct_substit;
    alter table structure_substit disable trigger substit_trigger_on_structure_substit;
end$$;


--drop function test_substit_ecole_doct__tear_down;
CREATE or replace FUNCTION test_substit_ecole_doct__tear_down() returns void
    language plpgsql
as
$$begin
    truncate table substit_log;

    alter table pre_structure disable trigger substit_trigger_pre_structure;
    alter table structure_substit disable trigger substit_trigger_on_structure_substit;
    alter table pre_ecole_doct disable trigger substit_trigger_pre_ecole_doct;
    alter table ecole_doct_substit disable trigger substit_trigger_on_ecole_doct_substit;

    delete from substit_fk_replacement where type = 'ecole_doct' and to_id in (select d.id from ecole_doct d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from ecole_doct_substit where from_id in (select d.id from pre_ecole_doct d join pre_structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from ecole_doct_substit where to_id in (select d.id from ecole_doct d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from ecole_doct where structure_id in (select id from structure where sigle = 'test1234');

    delete from pre_ecole_doct where structure_id in (select id from pre_structure where sigle = 'test1234');

    delete from structure_substit where from_id in (select id from pre_structure where sigle = 'test1234');
    delete from structure_substit where to_id in (select id from structure where sigle = 'test1234');
    delete from structure where sigle = 'test1234';

    delete from pre_structure where sigle = 'test1234';

    alter table pre_structure enable trigger substit_trigger_pre_structure;
    alter table structure_substit enable trigger substit_trigger_on_structure_substit;
    alter table pre_ecole_doct enable trigger substit_trigger_pre_ecole_doct;
    alter table ecole_doct_substit enable trigger substit_trigger_on_ecole_doct_substit;
end$$;


--drop function test_substit_ecole_doct__fetches_data_for_substituant;
CREATE or replace FUNCTION test_substit_ecole_doct__fetches_data_for_substituant() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;
    v_pre_ecole_doct_1 pre_ecole_doct;
    v_pre_ecole_doct_2 pre_ecole_doct;
    v_pre_ecole_doct_3 pre_ecole_doct;
    v_data record;
begin
    --
    -- Pour l'instant, pre_etablissement ne porte aucune colonne dont le contenu est importé donc test non pertinent.
    --      Ci-dessous, le code si 'theme' faisait partie des colonnes importées.
    --

    /*
    perform test_substit_ecole_doct__set_up();

    v_npd_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un pre_structure et pre_ecole_doct associé : theme = azerty.org
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO pre_ecole_doct(id, structure_id, theme, source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, 'azerty.org', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création attendue d'une subsitution 'azerty.org' : 2 doublons (theme = azerty.fr car ordre alpha)
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO pre_ecole_doct(id, structure_id, theme, source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, 'azerty.fr', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons (theme = azerty.org car majoritaire)
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO pre_ecole_doct(id, structure_id, theme, source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, 'azerty.com', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    select * into v_data from substit_fetch_data_for_substituant_ecole_doct(v_npd_a);

    assert v_data.theme = 'azerty.com',
        format('[TEST] Attendu : theme = %L car ordre alpha (reçu %L)', 'azerty.com', v_data.theme);

    --
    -- Modif du doublon 3 : theme 'azerty.com'
    --   => Seul changement attendu : theme = 'azerty.com' car ordre alphabet
    --
    update pre_ecole_doct set theme = 'azerty.com' where id = v_pre_ecole_doct_3.id;

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;

    v_npd_ecole_doct_a varchar(256);

    v_ecole_doct_substit ecole_doct_substit;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct pre_ecole_doct;
    v_pre_ecole_doct_1 pre_ecole_doct;
    v_pre_ecole_doct_2 pre_ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un pre_structure et pre_ecole_doct associé : theme = azerty.org
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.de',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_1.id;
    assert v_ecole_doct_substit.id is null,
        format('[TEST] Attendu : aucun ecole_doct_substit avec from_id = %L', v_pre_ecole_doct_1.id);

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création attendue d'une subsitution : 2 doublons (theme = azerty.al car ordre alpha)
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %L et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a;
    assert v_ecole_doct_substit.to_id is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %L et npd = %L', v_pre_ecole_doct_1.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_2.id and npd = v_npd_ecole_doct_a;
    assert v_ecole_doct_substit.to_id is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %L et npd = %L', v_pre_ecole_doct_2.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct from ecole_doct i where id = v_ecole_doct_substit.to_id;
    /*assert v_ecole_doct.theme = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.al',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_structure_substit.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_structure_substit.to_id, v_ecole_doct.structure_id);

    perform test_substit_ecole_doct__tear_down();
END$$;


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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;

    v_npd_ecole_doct_a varchar(256);

    v_ecole_doct_substit ecole_doct_substit;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct pre_ecole_doct;
    v_pre_ecole_doct_1 pre_ecole_doct;
    v_pre_ecole_doct_2 pre_ecole_doct;
    v_pre_ecole_doct_3 pre_ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un pre_structure et pre_ecole_doct associé : theme = azerty.org
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    --
    -- Historisation d'un pre_ecole_doct : celui avec ecole_doct = 56
    --   - retrait pre_ecole_doct de la substitution existante : 2 doublons restants (ecole_doct = 2 car 2<5)
    --
    update pre_ecole_doct set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_ecole_doct_3.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_3.id and npd = v_npd_ecole_doct_a;
    assert v_ecole_doct_substit.histo_destruction is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %L et npd = %L et histo_destruction not null', v_pre_ecole_doct_3.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct from ecole_doct i where id = v_ecole_doct_substit.to_id;
    /*assert v_ecole_doct.theme = 'azerty.fr'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', 2, /*'azerty.fr',*/ v_ecole_doct.theme);*/

    perform test_substit_ecole_doct__tear_down();
END$$;


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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;

    v_npd_ecole_doct_a varchar(256);

    v_ecole_doct_substit ecole_doct_substit;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct pre_ecole_doct;
    v_pre_ecole_doct_1 pre_ecole_doct;
    v_pre_ecole_doct_2 pre_ecole_doct;
    v_pre_ecole_doct_3 pre_ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un pre_structure et pre_ecole_doct associé : theme = azerty.org
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    --
    -- Historisation d'un pre_ecole_doct : celui avec ecole_doct = 56
    --   - retrait pre_ecole_doct de la substitution existante : 2 doublons restants (ecole_doct = 2 car 2<5)
    --
    update pre_ecole_doct set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_ecole_doct_3.id;

    --
    -- Restauration d'un pre_ecole_doct : celui avec ecole_doct = 56
    --   - ajout pre_ecole_doct à la substitution existante : 3 doublons (ecole_doct = 5 car majoritaire)
    --
    update pre_ecole_doct set histo_destruction = null, histo_destructeur_id = null where id = v_pre_ecole_doct_3.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_3.id and npd = v_npd_ecole_doct_a and histo_destruction is not null;
    assert v_ecole_doct_substit.to_id is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %L et npd = %L et histo_destruction not null', v_pre_ecole_doct_3.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_3.id and npd = v_npd_ecole_doct_a and histo_destruction is null;
    assert v_ecole_doct_substit.to_id is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %L et npd = %L et histo_destruction null', v_pre_ecole_doct_3.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct from ecole_doct i where id = v_ecole_doct_substit.to_id;
    /*assert v_ecole_doct.theme = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.al',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_structure_substit.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_structure_substit.to_id, v_ecole_doct.structure_id);

    perform test_substit_ecole_doct__tear_down();
END$$;


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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;

    v_npd_ecole_doct_a varchar(256);

    v_ecole_doct_substit ecole_doct_substit;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct pre_ecole_doct;
    v_pre_ecole_doct_1 pre_ecole_doct;
    v_pre_ecole_doct_2 pre_ecole_doct;
    v_pre_ecole_doct_3 pre_ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un pre_structure et pre_ecole_doct associé : theme = azerty.org
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    --
    -- Passage d'un pre_ecole_doct à la source application : HOCHON PAULE aaaa@mail.fr
    --   - retrait pre_ecole_doct de la substitution existante : 2 doublons
    --
    update pre_ecole_doct set source_id = 1 where id = v_pre_ecole_doct_1.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a and histo_destruction is not null;
    assert v_ecole_doct_substit.to_id is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %L et npd = %L et histo_destruction not null', v_pre_ecole_doct_1.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct from ecole_doct i where id = v_ecole_doct_substit.to_id;
    /*assert v_ecole_doct.theme = 'azerty.fr'/*car azerty.al a changé de source*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.fr',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_structure_substit.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_structure_substit.to_id, v_ecole_doct.structure_id);

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;

    v_npd_ecole_doct_a varchar(256);

    v_ecole_doct_substit ecole_doct_substit;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct pre_ecole_doct;
    v_pre_ecole_doct_1 pre_ecole_doct;
    v_pre_ecole_doct_2 pre_ecole_doct;
    v_pre_ecole_doct_3 pre_ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un pre_structure et pre_ecole_doct associé : theme = azerty.org
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    --
    -- Passage d'un pre_ecole_doct à la source application : HOCHON PAULE aaaa@mail.fr
    --   - retrait pre_ecole_doct de la substitution existante : 2 doublons
    --
    update pre_ecole_doct set source_id = 1 where id = v_pre_ecole_doct_1.id;

    --
    -- Retour d'un pre_ecole_doct dans la source INSA :
    --   - ajout pre_ecole_doct à la substitution existante : 3 doublons
    --
    update pre_ecole_doct set source_id = v_source_id where id = v_pre_ecole_doct_1.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_1.id and npd = v_npd_structure_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %L et npd = %L', v_pre_structure_1.id, v_npd_structure_a);

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a and histo_destruction is not null;
    assert v_ecole_doct_substit.to_id is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %L et npd = %L et histo_destruction not null', v_pre_ecole_doct_1.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a and histo_destruction is null;
    assert v_ecole_doct_substit.to_id is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %L et npd = %L et histo_destruction null', v_pre_ecole_doct_1.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct from ecole_doct i where id = v_ecole_doct_substit.to_id;
    /*assert v_ecole_doct.theme = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.al',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_structure_substit.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_structure_substit.to_id, v_ecole_doct.structure_id);

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;
    v_pre_structure_4 pre_structure;

    v_npd_ecole_doct_a varchar(256);

    v_ecole_doct_substit ecole_doct_substit;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct pre_ecole_doct;
    v_pre_ecole_doct_1 pre_ecole_doct;
    v_pre_ecole_doct_2 pre_ecole_doct;
    v_pre_ecole_doct_3 pre_ecole_doct;
    v_pre_ecole_doct_4 pre_ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un pre_structure et pre_ecole_doct associé : theme = azerty.org
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.com',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    --
    -- Test insertion d'un autre doublon : theme = azerty.org
    --   - ajout à la subsitution existante 'ecole-doctorale,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    --
    -- Test insertion pre_ecole_doct avec NPD forcé mais PRE_STRUCTURE SANS NPD FORCÉ :
    --   - ajout à la subsitution existante : 4 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'PAS_ETABLE_HISMAN', 'Pas Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_4; -- NB : pas de NPD forcé donc la structure n'est pas en doublon

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_4.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_ecole_doct_a
    returning * into v_pre_ecole_doct_4; -- NB : NPD forcé

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_4.id and npd = v_npd_structure_a;
    assert v_structure_substit.to_id is null,
        format('[TEST] Attendu : 0 structure_substit avec from_id = %s et npd = %L car la structure elle n''est pas en doublon', v_pre_structure_4.id, v_npd_structure_a);

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_4.id and npd = v_npd_ecole_doct_a;
    assert v_ecole_doct_substit.to_id is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %s et npd = %L', v_pre_ecole_doct_4.id, v_npd_ecole_doct_a, v_pre_structure_4.id);

    select * into v_ecole_doct from ecole_doct i where id = v_ecole_doct_substit.to_id;
    /*assert v_ecole_doct.theme = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.al',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_structure_substit.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_structure_substit.to_id, v_ecole_doct.structure_id);

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;
    v_pre_structure_3 pre_structure;

    v_npd_ecole_doct_a varchar(256);

    v_ecole_doct_substit ecole_doct_substit;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct pre_ecole_doct;
    v_pre_ecole_doct_1 pre_ecole_doct;
    v_pre_ecole_doct_2 pre_ecole_doct;
    v_pre_ecole_doct_3 pre_ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un pre_structure et pre_ecole_doct associé : theme = azerty.org
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    --
    -- Test insertion ecole_doct puis update du NPD forcé : COCHON Michel cccc@mail.fr
    --   - ajout à la subsitution existante : 3 doublons (ecole_doct_id = 5 car majoritaire)
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'PAS_ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_3;

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_3.id;
    assert v_ecole_doct_substit.to_id is null,
        format('[TEST] Attendu : aucun ecole_doct_substit avec from_id = %L ', v_pre_ecole_doct_3.id);

    update pre_ecole_doct set npd_force = v_npd_ecole_doct_a where id = v_pre_ecole_doct_3.id;

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_3.id and npd = v_npd_ecole_doct_a;
    assert v_ecole_doct_substit.to_id is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %L et npd = %L', v_pre_ecole_doct_4.id, v_npd_ecole_doct_a);

    select * into v_ecole_doct from ecole_doct i where id = v_ecole_doct_substit.to_id;
    /*assert v_ecole_doct.theme = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec theme = %L (reçu %L)', /*'azerty.al',*/ v_ecole_doct.theme);*/
    assert v_ecole_doct.structure_id = v_structure_substit.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 ecole_doct substituant avec structure_id = %s (reçu %s)', v_structure_substit.to_id, v_ecole_doct.structure_id);

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

    v_structure_substit structure_substit;
    v_pre_structure pre_structure;
    v_pre_structure_1 pre_structure;
    v_pre_structure_2 pre_structure;

    v_npd_ecole_doct_a varchar(256);

    v_ecole_doct_substit ecole_doct_substit;
    v_ecole_doct ecole_doct;
    v_pre_ecole_doct pre_ecole_doct;
    v_pre_ecole_doct_1 pre_ecole_doct;
    v_pre_ecole_doct_2 pre_ecole_doct;
begin
    perform test_substit_ecole_doct__set_up();

    v_npd_structure_a = 'ecole-doctorale,ETABLE_HISMAN';
    v_npd_ecole_doct_a = 'ecole-doctorale,ETABLE_HISMAN';

    --
    -- Création d'un pre_structure et pre_ecole_doct associé : theme = azerty.org
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_1;

    --
    -- Test insertion d'un doublon de ecole_doct : theme = azerty.fr
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO pre_structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 2, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO pre_ecole_doct(id, structure_id, /*theme,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('ecole_doct_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_ecole_doct_2;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_structure_substit.to_id is not null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %L et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_ecole_doct_substit from ecole_doct_substit where from_id = v_pre_ecole_doct_1.id and npd = v_npd_ecole_doct_a;
    assert v_ecole_doct_substit.to_id is not null,
        format('[TEST] Attendu : 1 ecole_doct_substit avec from_id = %L et npd = %L', v_pre_ecole_doct_1.id, v_npd_ecole_doct_a);

    --
    -- Historisation d'un pre_ecole_doct : celui avec azerty.fr
    --   - la subsitution de structure perdure
    --   - retrait pre_ecole_doct de la substitution existante et historisation de la substitution restante et du substituant.
    --
    update pre_ecole_doct set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_ecole_doct_2.id;

    select * into v_structure_substit from structure_substit where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_structure_substit.histo_destruction is null,
        format('[TEST] Attendu : 1 structure_substit avec from_id = %s et npd = %L non historise', v_pre_structure_2.id, v_npd_structure_a);

    select count(*) into v_count from ecole_doct_substit i where to_id = v_ecole_doct_substit.to_id and histo_destruction is null;
    assert v_count = 0,
        format('[TEST] Attendu : 0 ecole_doct_substit non historisé avec substituant = %s', v_ecole_doct_substit.to_id);

    select * into v_ecole_doct from ecole_doct i where id = v_ecole_doct_substit.to_id;
    assert v_ecole_doct.histo_destruction is not null,
        format('[TEST] Attendu : 1 ecole_doct substituant historisé : %', v_ecole_doct.id);

    perform test_substit_ecole_doct__tear_down();
END$$;
