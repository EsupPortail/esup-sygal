-- ------------------------------------------------------------------------------------------------------------------
-- Tests : unite_rech
-- ------------------------------------------------------------------------------------------------------------------

--drop function test_substit_unite_rech__set_up;
CREATE or replace FUNCTION test_substit_unite_rech__set_up() returns void
    language plpgsql
as
$$begin
    alter table substit_unite_rech disable trigger substit_trigger_on_substit_unite_rech;
    alter table substit_structure disable trigger substit_trigger_on_substit_structure;
end$$;


--drop function test_substit_unite_rech__tear_down;
CREATE or replace FUNCTION test_substit_unite_rech__tear_down() returns void
    language plpgsql
as
$$begin
    delete from substit_log sl where type = 'unite_rech' and exists (select id from unite_rech s where sl.substitue_id = s.id and structure_id in (select id from structure where sigle = 'test1234'));
    delete from substit_log sl where type = 'unite_rech' and exists (select id from unite_rech s where sl.substituant_id = s.id and structure_id in (select id from structure where sigle = 'test1234'));
    delete from substit_log sl where type = 'structure' and exists (select id from structure s where sl.substitue_id = s.id and sigle = 'test1234');
    delete from substit_log sl where type = 'structure' and exists (select id from structure s where sl.substituant_id = s.id and sigle = 'test1234');

    alter table structure disable trigger substit_trigger_structure;
    alter table substit_structure disable trigger substit_trigger_on_substit_structure;
    alter table unite_rech disable trigger substit_trigger_unite_rech;
    alter table substit_unite_rech disable trigger substit_trigger_on_substit_unite_rech;

    delete from substit_fk_replacement where type = 'unite_rech' and to_id in (select d.id from unite_rech d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from substit_unite_rech where from_id in (select d.id from unite_rech d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from substit_unite_rech where to_id in (select d.id from unite_rech d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from unite_rech where structure_id in (select id from structure where sigle = 'test1234');

    delete from unite_rech where structure_id in (select id from structure where sigle = 'test1234');

    delete from substit_structure where from_id in (select id from structure where sigle = 'test1234');
    delete from substit_structure where to_id in (select id from structure where sigle = 'test1234');
    delete from structure where sigle = 'test1234';

    delete from structure where sigle = 'test1234';

    alter table structure enable trigger substit_trigger_structure;
    alter table substit_structure enable trigger substit_trigger_on_substit_structure;
    alter table unite_rech enable trigger substit_trigger_unite_rech;
    alter table substit_unite_rech enable trigger substit_trigger_on_substit_unite_rech;
end$$;


--drop function test_substit_unite_rech__fetches_data_for_substituant;
CREATE or replace FUNCTION test_substit_unite_rech__fetches_data_for_substituant() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
    v_pre_unite_rech_1 unite_rech;
    v_pre_unite_rech_2 unite_rech;
    v_pre_unite_rech_3 unite_rech;
    v_data record;
begin
    --
    -- Pour l'instant, etablissement ne porte aucune colonne dont le contenu est importé donc test non pertinent.
    --      Ci-dessous, le code si 'domaine' faisait partie des colonnes importées.
    --

    /*
    perform test_substit_unite_rech__set_up();

    v_npd_a = 'unite-recherche,ETABLE_HISMAN';

    --
    -- Création d'un structure et unite_rech associé : etab_support = azerty.org
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO unite_rech(id, structure_id, etab_support, source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_1.id, 'azerty.org', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_1;

    --
    -- Test insertion d'un doublon de unite_rech : etab_support = azerty.fr
    --   - création attendue d'une subsitution 'azerty.org' : 2 doublons (etab_support = azerty.fr car ordre alpha)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO unite_rech(id, structure_id, etab_support, source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_2.id, 'azerty.fr', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_2;

    --
    -- Test insertion d'un autre doublon : etab_support = azerty.org
    --   - ajout à la subsitution existante 'unite-recherche,ETABLE_HISMAN' : 3 doublons (etab_support = azerty.org car majoritaire)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO unite_rech(id, structure_id, etab_support, source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_3.id, 'azerty.com', 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_3;

    select * into v_data from substit_fetch_data_for_substituant_unite_rech(v_npd_a);

    assert v_data.etab_support = 'azerty.com',
        format('[TEST] Attendu : etab_support = %L car ordre alpha (reçu %L)', 'azerty.com', v_data.etab_support);

    --
    -- Modif du doublon 3 : etab_support 'azerty.com'
    --   => Seul changement attendu : etab_support = 'azerty.com' car ordre alphabet
    --
    update unite_rech set etab_support = 'azerty.com' where id = v_pre_unite_rech_3.id;

    select * into v_data from substit_fetch_data_for_substituant_unite_rech(v_npd_a);

    assert v_data.etab_support = 'azerty.com',
        format('[TEST] Attendu : etab_support = %L car ordre alpha (reçu %L)', /*'azerty.com',*/ v_data.etab_support);

    perform test_substit_unite_rech__tear_down();
    */
end$$;


--drop function test_substit_unite_rech__creates_substit_2_doublons;
CREATE or replace FUNCTION test_substit_unite_rech__creates_substit_2_doublons() returns void
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

    v_npd_unite_rech_a varchar(256);

    v_substit_unite_rech substit_unite_rech;
    v_unite_rech unite_rech;
    v_pre_unite_rech unite_rech;
    v_pre_unite_rech_1 unite_rech;
    v_pre_unite_rech_2 unite_rech;
begin
    perform test_substit_unite_rech__set_up();

    v_npd_structure_a = 'unite-recherche,ETABLE_HISMAN';
    v_npd_unite_rech_a = 'unite-recherche,ETABLE_HISMAN';

    --
    -- Création d'un structure et unite_rech associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_1.id, /*'azerty.de',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_1;

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_1.id;
    assert v_substit_unite_rech.id is null,
        format('[TEST] Attendu : aucun substit_unite_rech avec from_id = %L', v_pre_unite_rech_1.id);

    --
    -- Test insertion d'un doublon de unite_rech :
    --   - création attendue d'une subsitution : 2 doublons (etab_support = azerty.al car ordre alpha)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_2.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_1.id and npd = v_npd_unite_rech_a;
    assert v_substit_unite_rech.to_id is not null,
        format('[TEST] Attendu : 1 substit_unite_rech avec from_id = %L et npd = %L', v_pre_unite_rech_1.id, v_npd_unite_rech_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_2.id and npd = v_npd_unite_rech_a;
    assert v_substit_unite_rech.to_id is not null,
        format('[TEST] Attendu : 1 substit_unite_rech avec from_id = %L et npd = %L', v_pre_unite_rech_2.id, v_npd_unite_rech_a);

    select * into v_unite_rech from unite_rech i where id = v_substit_unite_rech.to_id;
    /*assert v_unite_rech.etab_support = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec etab_support = %L (reçu %L)', /*'azerty.al',*/ v_unite_rech.etab_support);*/
    assert v_unite_rech.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_unite_rech.structure_id);

    perform test_substit_unite_rech__tear_down();
END$$;

/*
--drop function test_substit_unite_rech__removes_from_substit_si_historise;
CREATE or replace FUNCTION test_substit_unite_rech__removes_from_substit_si_historise() returns void
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

    v_npd_unite_rech_a varchar(256);

    v_substit_unite_rech substit_unite_rech;
    v_unite_rech unite_rech;
    v_pre_unite_rech unite_rech;
    v_pre_unite_rech_1 unite_rech;
    v_pre_unite_rech_2 unite_rech;
    v_pre_unite_rech_3 unite_rech;
begin
    perform test_substit_unite_rech__set_up();

    v_npd_structure_a = 'unite-recherche,ETABLE_HISMAN';
    v_npd_unite_rech_a = 'unite-recherche,ETABLE_HISMAN';

    --
    -- Création d'un structure et unite_rech associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_1;

    --
    -- Test insertion d'un doublon de unite_rech :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'unite-recherche,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_3;

    --
    -- Historisation d'un unite_rech : celui avec unite_rech = 56
    --   - retrait unite_rech de la substitution existante : 2 doublons restants (unite_rech = 2 car 2<5)
    --
    update unite_rech set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_unite_rech_3.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_3.id and npd = v_npd_unite_rech_a;
    assert v_substit_unite_rech.histo_destruction is not null,
        format('[TEST] Attendu : 1 substit_unite_rech avec from_id = %L et npd = %L et histo_destruction not null', v_pre_unite_rech_3.id, v_npd_unite_rech_a);

    select * into v_unite_rech from unite_rech i where id = v_substit_unite_rech.to_id;
    /*assert v_unite_rech.etab_support = 'azerty.fr'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec etab_support = %L (reçu %L)', 3, /*'azerty.fr',*/ v_unite_rech.etab_support);*/

    perform test_substit_unite_rech__tear_down();
END$$;
*/
/*
--drop function test_substit_unite_rech__adds_to_substit_si_dehistorise;
CREATE or replace FUNCTION test_substit_unite_rech__adds_to_substit_si_dehistorise() returns void
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

    v_npd_unite_rech_a varchar(256);

    v_substit_unite_rech substit_unite_rech;
    v_unite_rech unite_rech;
    v_pre_unite_rech unite_rech;
    v_pre_unite_rech_1 unite_rech;
    v_pre_unite_rech_2 unite_rech;
    v_pre_unite_rech_3 unite_rech;
begin
    perform test_substit_unite_rech__set_up();

    v_npd_structure_a = 'unite-recherche,ETABLE_HISMAN';
    v_npd_unite_rech_a = 'unite-recherche,ETABLE_HISMAN';

    --
    -- Création d'un structure et unite_rech associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_1;

    --
    -- Test insertion d'un doublon de unite_rech :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'unite-recherche,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_3;

    --
    -- Historisation d'un unite_rech : celui avec unite_rech = 56
    --   - retrait unite_rech de la substitution existante : 2 doublons restants (unite_rech = 2 car 2<5)
    --
    update unite_rech set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_unite_rech_3.id;

    --
    -- Restauration d'un unite_rech : celui avec unite_rech = 56
    --   - ajout unite_rech à la substitution existante : 3 doublons (unite_rech = 5 car majoritaire)
    --
    update unite_rech set histo_destruction = null, histo_destructeur_id = null where id = v_pre_unite_rech_3.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_3.id and npd = v_npd_unite_rech_a and histo_destruction is not null;
    assert v_substit_unite_rech.to_id is not null,
        format('[TEST] Attendu : 1 substit_unite_rech avec from_id = %L et npd = %L et histo_destruction not null', v_pre_unite_rech_3.id, v_npd_unite_rech_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_3.id and npd = v_npd_unite_rech_a and histo_destruction is null;
    assert v_substit_unite_rech.to_id is not null,
        format('[TEST] Attendu : 1 substit_unite_rech avec from_id = %L et npd = %L et histo_destruction null', v_pre_unite_rech_3.id, v_npd_unite_rech_a);

    select * into v_unite_rech from unite_rech i where id = v_substit_unite_rech.to_id;
    /*assert v_unite_rech.etab_support = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec etab_support = %L (reçu %L)', /*'azerty.al',*/ v_unite_rech.etab_support);*/
    assert v_unite_rech.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_unite_rech.structure_id);

    perform test_substit_unite_rech__tear_down();
END$$;
*/

--drop function test_substit_unite_rech__removes_from_substit_si_source_app;
CREATE or replace FUNCTION test_substit_unite_rech__removes_from_substit_si_source_app() returns void
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

    v_npd_unite_rech_a varchar(256);

    v_substit_unite_rech substit_unite_rech;
    v_unite_rech unite_rech;
    v_pre_unite_rech unite_rech;
    v_pre_unite_rech_1 unite_rech;
    v_pre_unite_rech_2 unite_rech;
    v_pre_unite_rech_3 unite_rech;
begin
    perform test_substit_unite_rech__set_up();

    v_npd_structure_a = 'unite-recherche,ETABLE_HISMAN';
    v_npd_unite_rech_a = 'unite-recherche,ETABLE_HISMAN';

    --
    -- Création d'un structure et unite_rech associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_1.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_1;

    --
    -- Test insertion d'un doublon de unite_rech :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'unite-recherche,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_3;

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_1.id and npd = v_npd_unite_rech_a;

    --
    -- Passage d'un unite_rech à la source application : HOCHON PAULE aaaa@mail.fr
    --   - retrait unite_rech de la substitution existante : 2 doublons
    --
    update unite_rech set source_id = 1 where id = v_pre_unite_rech_1.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_unite_rech from substit_unite_rech where id = v_substit_unite_rech.id;
    assert v_substit_unite_rech.id is null,
        format('[TEST] Attendu : 1 substit_unite_rech supprimé avec from_id = %L et npd = %L', v_pre_unite_rech_1.id, v_npd_unite_rech_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_3.id;
    select * into v_unite_rech from unite_rech i where id = v_substit_unite_rech.to_id;
    /*assert v_unite_rech.domaine = 'azerty.fr'/*car azerty.al a changé de source*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec domaine = %L (reçu %L)', /*'azerty.fr',*/ v_unite_rech.domaine);*/
    assert v_unite_rech.structure_id = v_substit_structure.to_id,
        format('[TEST] Attendu : 1 unite_rech substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_unite_rech.structure_id);

    perform test_substit_unite_rech__tear_down();
END$$;


--drop function test_substit_unite_rech__removes_from_substit_si_plus_source_ap;
CREATE or replace FUNCTION test_substit_unite_rech__removes_from_substit_si_plus_source_ap() returns void
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

    v_npd_unite_rech_a varchar(256);

    v_substit_unite_rech substit_unite_rech;
    v_unite_rech unite_rech;
    v_pre_unite_rech unite_rech;
    v_pre_unite_rech_1 unite_rech;
    v_pre_unite_rech_2 unite_rech;
    v_pre_unite_rech_3 unite_rech;
begin
    perform test_substit_unite_rech__set_up();

    v_npd_structure_a = 'unite-recherche,ETABLE_HISMAN';
    v_npd_unite_rech_a = 'unite-recherche,ETABLE_HISMAN';

    --
    -- Création d'un structure et unite_rech associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_1.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_1;

    --
    -- Test insertion d'un doublon de unite_rech :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'unite-recherche,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_3;

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_1.id and npd = v_npd_unite_rech_a;

    --
    -- Passage d'un unite_rech à la source application : HOCHON PAULE aaaa@mail.fr
    --   - retrait unite_rech de la substitution existante : 2 doublons
    --
    update unite_rech set source_id = 1 where id = v_pre_unite_rech_1.id;

    --
    -- Retour d'un unite_rech dans la source INSA :
    --   - ajout unite_rech à la substitution existante : 3 doublons
    --
    update unite_rech set source_id = v_source_id where id = v_pre_unite_rech_1.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_1.id, v_npd_structure_a);

    select * into v_substit_unite_rech from substit_unite_rech where id = v_substit_unite_rech.id;
    assert v_substit_unite_rech.id is null,
        format('[TEST] Attendu : 1 substit_unite_rech supprimé avec from_id = %L et npd = %L', v_pre_unite_rech_1.id, v_npd_unite_rech_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_1.id and npd = v_npd_unite_rech_a;
    assert v_substit_unite_rech.to_id is not null,
        format('[TEST] Attendu : 1 substit_unite_rech recréé avec from_id = %L et npd = %L et histo_destruction null', v_pre_unite_rech_1.id, v_npd_unite_rech_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_3.id;
    select * into v_unite_rech from unite_rech i where id = v_substit_unite_rech.to_id;
    /*assert v_unite_rech.domaine = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec domaine = %L (reçu %L)', /*'azerty.al',*/ v_unite_rech.domaine);*/
    assert v_unite_rech.structure_id = v_substit_structure.to_id,
        format('[TEST] Attendu : 1 unite_rech substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_unite_rech.structure_id);

    perform test_substit_unite_rech__tear_down();
END$$;


--drop function test_substit_unite_rech__adds_to_substit_si_npd_force;
CREATE or replace FUNCTION test_substit_unite_rech__adds_to_substit_si_npd_force() returns void
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

    v_npd_unite_rech_a varchar(256);

    v_substit_unite_rech substit_unite_rech;
    v_unite_rech unite_rech;
    v_pre_unite_rech unite_rech;
    v_pre_unite_rech_1 unite_rech;
    v_pre_unite_rech_2 unite_rech;
    v_pre_unite_rech_3 unite_rech;
    v_pre_unite_rech_4 unite_rech;
begin
    perform test_substit_unite_rech__set_up();

    v_npd_structure_a = 'unite-recherche,ETABLE_HISMAN';
    v_npd_unite_rech_a = 'unite-recherche,ETABLE_HISMAN';

    --
    -- Création d'un structure et unite_rech associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_1.id, /*'azerty.com',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_1;

    --
    -- Test insertion d'un doublon de unite_rech :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'unite-recherche,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_3;

    --
    -- Test insertion unite_rech avec NPD forcé mais STRUCTURE SANS NPD FORCÉ :
    --   - ajout à la subsitution existante : 4 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'PAS_ETABLE_HISMAN', 'Pas Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_4; -- NB : pas de NPD forcé donc la structure n'est pas en doublon

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_4.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_unite_rech_a
    returning * into v_pre_unite_rech_4; -- NB : NPD forcé

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_4.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is null,
        format('[TEST] Attendu : 0 substit_structure avec from_id = %s et npd = %L car la structure elle n''est pas en doublon', v_pre_structure_4.id, v_npd_structure_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_4.id and npd = v_npd_unite_rech_a;
    assert v_substit_unite_rech.to_id is not null,
        format('[TEST] Attendu : 1 substit_unite_rech avec from_id = %s et npd = %L', v_pre_unite_rech_4.id, v_npd_unite_rech_a, v_pre_structure_4.id);

    select * into v_unite_rech from unite_rech i where id = v_substit_unite_rech.to_id;
    /*assert v_unite_rech.etab_support = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec etab_support = %L (reçu %L)', /*'azerty.al',*/ v_unite_rech.etab_support);*/
    assert v_unite_rech.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_unite_rech.structure_id);

    perform test_substit_unite_rech__tear_down();
END$$;


--drop function test_substit_unite_rech__adds_to_substit_si_ajout_npd;
CREATE or replace FUNCTION test_substit_unite_rech__adds_to_substit_si_ajout_npd() returns void
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

    v_npd_unite_rech_a varchar(256);

    v_substit_unite_rech substit_unite_rech;
    v_unite_rech unite_rech;
    v_pre_unite_rech unite_rech;
    v_pre_unite_rech_1 unite_rech;
    v_pre_unite_rech_2 unite_rech;
    v_pre_unite_rech_3 unite_rech;
begin
    perform test_substit_unite_rech__set_up();

    v_npd_structure_a = 'unite-recherche,ETABLE_HISMAN';
    v_npd_unite_rech_a = 'unite-recherche,ETABLE_HISMAN';

    --
    -- Création d'un structure et unite_rech associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_1;

    --
    -- Test insertion d'un doublon de unite_rech :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    --
    -- Test insertion unite_rech puis update du NPD forcé : COCHON Michel cccc@mail.fr
    --   - ajout à la subsitution existante : 3 doublons (unite_rech_id = 5 car majoritaire)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'PAS_ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_3;

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_3.id;
    assert v_substit_unite_rech.to_id is null,
        format('[TEST] Attendu : aucun substit_unite_rech avec from_id = %L ', v_pre_unite_rech_3.id);

    update unite_rech set npd_force = v_npd_unite_rech_a where id = v_pre_unite_rech_3.id;

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_3.id and npd = v_npd_unite_rech_a;
    assert v_substit_unite_rech.to_id is not null,
        format('[TEST] Attendu : 1 substit_unite_rech avec from_id = %L et npd = %L', v_pre_unite_rech_3.id, v_npd_unite_rech_a);

    select * into v_unite_rech from unite_rech i where id = v_substit_unite_rech.to_id;
    /*assert v_unite_rech.etab_support = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec etab_support = %L (reçu %L)', /*'azerty.al',*/ v_unite_rech.etab_support);*/
    assert v_unite_rech.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 unite_rech substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_unite_rech.structure_id);

    perform test_substit_unite_rech__tear_down();
END$$;


--drop function test_substit_unite_rech__deletes_substit_si_plus_doublon;
CREATE or replace FUNCTION test_substit_unite_rech__deletes_substit_si_plus_doublon() returns void
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

    v_npd_unite_rech_a varchar(256);

    v_substit_unite_rech substit_unite_rech;
    v_unite_rech unite_rech;
    v_pre_unite_rech unite_rech;
    v_pre_unite_rech_1 unite_rech;
    v_pre_unite_rech_2 unite_rech;
begin
    perform test_substit_unite_rech__set_up();

    v_npd_structure_a = 'unite-recherche,ETABLE_HISMAN';
    v_npd_unite_rech_a = 'unite-recherche,ETABLE_HISMAN';

    --
    -- Création d'un structure et unite_rech associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_1;

    --
    -- Test insertion d'un doublon de unite_rech :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 3, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO unite_rech(id, structure_id, /*etab_support,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('unite_rech_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_unite_rech_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_1.id and npd = v_npd_unite_rech_a;
    assert v_substit_unite_rech.to_id is not null,
        format('[TEST] Attendu : 1 substit_unite_rech avec from_id = %L et npd = %L', v_pre_unite_rech_1.id, v_npd_unite_rech_a);

    -- Modif du NPD forcé pour sortir celui avec azerty.fr de la substitution :
    --   - la substitution de la structure liée perdure ;
    --   - retrait unite_rech de la substitution existante : 1 substitué restant ;
    --   - suppression de la substitution car 0 doublon ;
    --   - suppression du substituant.
    update unite_rech set npd_force = 'ksldqhflksjdqhfl' where id = v_pre_unite_rech_2.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L non historise', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_unite_rech from substit_unite_rech where from_id = v_pre_unite_rech_1.id and npd = v_npd_unite_rech_a;
    select count(*) into v_count from substit_unite_rech i where to_id = v_substit_unite_rech.to_id;
    assert v_count = 0,
        format('[TEST] Attendu : 0 substit_unite_rech avec substituant = %s', v_substit_unite_rech.to_id);

    select * into v_unite_rech from unite_rech i where id = v_substit_unite_rech.to_id;
    assert v_unite_rech.id is null,
        format('[TEST] Attendu : 1 unite_rech substituant supprimé : %s', v_unite_rech.id);

    perform test_substit_unite_rech__tear_down();
END$$;


select test_substit_unite_rech__fetches_data_for_substituant();
select test_substit_unite_rech__creates_substit_2_doublons();
-- select test_substit_unite_rech__removes_from_substit_si_historise(); -- NA dans dernière version
-- select test_substit_unite_rech__adds_to_substit_si_dehistorise(); -- NA dans dernière version
select test_substit_unite_rech__removes_from_substit_si_source_app();
select test_substit_unite_rech__removes_from_substit_si_plus_source_ap();
select test_substit_unite_rech__adds_to_substit_si_npd_force(); -- NB : NPD forcé sur pre_unite_rech seulement (pas sur pre_structure)
select test_substit_unite_rech__adds_to_substit_si_ajout_npd();
select test_substit_unite_rech__deletes_substit_si_plus_doublon();

