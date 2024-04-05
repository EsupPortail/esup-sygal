-- ------------------------------------------------------------------------------------------------------------------
-- Tests : etablissement
-- ------------------------------------------------------------------------------------------------------------------


--drop function test_substit_etab__set_up;
CREATE or replace FUNCTION test_substit_etab__set_up() returns void
    language plpgsql
as
$$begin
    alter table substit_etablissement disable trigger substit_trigger_on_substit_etablissement;
    alter table substit_structure disable trigger substit_trigger_on_substit_structure;
end$$;


--drop function test_substit_etab__tear_down;
CREATE or replace FUNCTION test_substit_etab__tear_down() returns void
    language plpgsql
as
$$begin
    delete from substit_log sl where type = 'etablissement' and exists (select id from etablissement s where sl.substitue_id = s.id and structure_id in (select id from structure where sigle = 'test1234'));
    delete from substit_log sl where type = 'etablissement' and exists (select id from etablissement s where sl.substituant_id = s.id and structure_id in (select id from structure where sigle = 'test1234'));
    delete from substit_log sl where type = 'structure' and exists (select id from structure s where sl.substitue_id = s.id and sigle = 'test1234');
    delete from substit_log sl where type = 'structure' and exists (select id from structure s where sl.substituant_id = s.id and sigle = 'test1234');

    alter table structure disable trigger substit_trigger_structure;
    alter table substit_structure disable trigger substit_trigger_on_substit_structure;
    alter table etablissement disable trigger substit_trigger_etablissement;
    alter table substit_etablissement disable trigger substit_trigger_on_substit_etablissement;

    delete from substit_fk_replacement where type = 'etablissement' and to_id in (select d.id from etablissement d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from substit_etablissement where from_id in (select d.id from etablissement d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from substit_etablissement where to_id in (select d.id from etablissement d join structure i on d.structure_id = i.id where sigle = 'test1234');
    delete from etablissement where structure_id in (select id from structure where sigle = 'test1234');

    delete from substit_structure where from_id in (select id from structure where sigle = 'test1234');
    delete from substit_structure where to_id in (select id from structure where sigle = 'test1234');
    delete from structure where sigle = 'test1234';

    alter table structure enable trigger substit_trigger_structure;
    alter table substit_structure enable trigger substit_trigger_on_substit_structure;
    alter table etablissement enable trigger substit_trigger_etablissement;
    alter table substit_etablissement enable trigger substit_trigger_on_substit_etablissement;
end$$;


--drop function test_substit_etab__fetches_data_for_substituant;
CREATE or replace FUNCTION test_substit_etab__fetches_data_for_substituant() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
    v_pre_etablissement_1 etablissement;
    v_pre_etablissement_2 etablissement;
    v_pre_etablissement_3 etablissement;
    v_data record;
begin
    --
    -- Pour l'instant, etablissement ne porte aucune colonne dont le contenu est importé donc test non pertinent.
    --      Ci-dessous, le code si 'domaine' faisait partie des colonnes importées.
    --

    /*
    perform test_substit_etab__set_up();

    v_npd_a = 'etablissement,ETABLE_HISMAN';

    --
    -- Création d'un structure et etablissement associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO etablissement(id, structure_id, source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_1.id, 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_1;

    --
    -- Test insertion d'un doublon de etablissement :
    --   - création attendue d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO etablissement(id, structure_id, source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_2.id, 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'etablissement,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO etablissement(id, structure_id, source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_3.id, 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_3;

    select * into v_data from substit_fetch_data_for_substituant_etablissement(v_npd_a);

    assert v_data.domaine = 'azerty.com',
        format('[TEST] Attendu : domaine = %L car ordre alpha (reçu %L)', 'azerty.com', v_data.domaine);

    --
    -- Modif du doublon 3 : domaine 'azerty.com'
    --   => Seul changement attendu : domaine = 'azerty.com' car ordre alphabet
    --
    update etablissement set domaine = 'azerty.com' where id = v_pre_etablissement_3.id;

    select * into v_data from substit_fetch_data_for_substituant_etablissement(v_npd_a);

    assert v_data.domaine = 'azerty.com',
        format('[TEST] Attendu : domaine = %L car ordre alpha (reçu %L)', 'azerty.com', v_data.domaine);

    perform test_substit_etab__tear_down();
    */
end$$;


--drop function test_substit_etab__creates_substit_2_doublons;
CREATE or replace FUNCTION test_substit_etab__creates_substit_2_doublons() returns void
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

    v_npd_etablissement_a varchar(256);

    v_substit_etablissement substit_etablissement;
    v_etablissement etablissement;
    v_pre_etablissement etablissement;
    v_pre_etablissement_1 etablissement;
    v_pre_etablissement_2 etablissement;
begin
    perform test_substit_etab__set_up();

    v_npd_structure_a = 'etablissement,ETABLE_HISMAN';
    v_npd_etablissement_a = 'etablissement,ETABLE_HISMAN';

    --
    -- Création d'un structure et etablissement associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_structure_1;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id)
    select nextval('etablissement_id_seq'), v_pre_structure_1.id, /*'azerty.de',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_etablissement_1;

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_1.id;
    assert v_substit_etablissement.id is null,
        format('[TEST] Attendu : aucun substit_etablissement avec from_id = %L', v_pre_etablissement_1.id);

    --
    -- Test insertion d'un doublon de etablissement HISTORISÉ :
    --   - création attendue d'une subsitution : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, histo_destruction)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, current_timestamp
    returning * into v_pre_structure_2;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, histo_destruction)
    select nextval('etablissement_id_seq'), v_pre_structure_2.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, current_timestamp
    returning * into v_pre_etablissement_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_1.id and npd = v_npd_etablissement_a;
    assert v_substit_etablissement.to_id is not null,
        format('[TEST] Attendu : 1 substit_etablissement avec from_id = %L et npd = %L', v_pre_etablissement_1.id, v_npd_etablissement_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_2.id and npd = v_npd_etablissement_a;
    assert v_substit_etablissement.to_id is not null,
        format('[TEST] Attendu : 1 substit_etablissement avec from_id = %L et npd = %L', v_pre_etablissement_2.id, v_npd_etablissement_a);

    select * into v_etablissement from etablissement i where id = v_substit_etablissement.to_id;
    /*assert v_etablissement.domaine = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 etablissement substituant avec domaine = %L (reçu %L)', /*'azerty.al',*/ v_etablissement.domaine);*/
    assert v_etablissement.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 etablissement substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_etablissement.structure_id);

    perform test_substit_etab__tear_down();
END$$;

/*
--drop function test_substit_etab__removes_from_substit_si_historise;
CREATE or replace FUNCTION test_substit_etab__removes_from_substit_si_historise() returns void
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

    v_npd_etablissement_a varchar(256);

    v_substit_etablissement substit_etablissement;
    v_etablissement etablissement;
    v_pre_etablissement etablissement;
    v_pre_etablissement_1 etablissement;
    v_pre_etablissement_2 etablissement;
    v_pre_etablissement_3 etablissement;
begin
    perform test_substit_etab__set_up();

    v_npd_structure_a = 'etablissement,ETABLE_HISMAN';
    v_npd_etablissement_a = 'etablissement,ETABLE_HISMAN';

    --
    -- Création d'un structure et etablissement associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_1;

    --
    -- Test insertion d'un doublon de etablissement :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'etablissement,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_3;

    --
    -- Historisation d'un etablissement : celui avec etablissement = 56
    --   - retrait etablissement de la substitution existante : 2 doublons restants (etablissement = 2 car 2<5)
    --
    update etablissement set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_etablissement_3.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_3.id and npd = v_npd_etablissement_a;
    assert v_substit_etablissement.histo_destruction is not null,
        format('[TEST] Attendu : 1 substit_etablissement avec from_id = %L et npd = %L et histo_destruction not null', v_pre_etablissement_3.id, v_npd_etablissement_a);

    /*select * into v_etablissement from etablissement i where id = v_substit_etablissement.to_id;
    assert v_etablissement.domaine = 'azerty.fr'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 etablissement substituant avec domaine = %L (reçu %L)', 2, /*'azerty.fr',*/ v_etablissement.domaine);*/

    perform test_substit_etab__tear_down();
END$$;
*/
/*
--drop function test_substit_etab__adds_to_substit_si_dehistorise;
CREATE or replace FUNCTION test_substit_etab__adds_to_substit_si_dehistorise() returns void
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

    v_npd_etablissement_a varchar(256);

    v_substit_etablissement substit_etablissement;
    v_etablissement etablissement;
    v_pre_etablissement etablissement;
    v_pre_etablissement_1 etablissement;
    v_pre_etablissement_2 etablissement;
    v_pre_etablissement_3 etablissement;
begin
    perform test_substit_etab__set_up();

    v_npd_structure_a = 'etablissement,ETABLE_HISMAN';
    v_npd_etablissement_a = 'etablissement,ETABLE_HISMAN';

    --
    -- Création d'un structure et etablissement associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_1;

    --
    -- Test insertion d'un doublon de etablissement :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'etablissement,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_3;

    --
    -- Historisation d'un etablissement : celui avec etablissement = 56
    --   - retrait etablissement de la substitution existante : 2 doublons restants (etablissement = 2 car 2<5)
    --
    update etablissement set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_etablissement_3.id;

    --
    -- Restauration d'un etablissement : celui avec etablissement = 56
    --   - ajout etablissement à la substitution existante : 3 doublons (etablissement = 5 car majoritaire)
    --
    update etablissement set histo_destruction = null, histo_destructeur_id = null where id = v_pre_etablissement_3.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_3.id and npd = v_npd_etablissement_a and histo_destruction is not null;
    assert v_substit_etablissement.to_id is not null,
        format('[TEST] Attendu : 1 substit_etablissement avec from_id = %L et npd = %L et histo_destruction not null', v_pre_etablissement_3.id, v_npd_etablissement_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_3.id and npd = v_npd_etablissement_a and histo_destruction is null;
    assert v_substit_etablissement.to_id is not null,
        format('[TEST] Attendu : 1 substit_etablissement avec from_id = %L et npd = %L et histo_destruction null', v_pre_etablissement_3.id, v_npd_etablissement_a);

    select * into v_etablissement from etablissement i where id = v_substit_etablissement.to_id;
    /*assert v_etablissement.domaine = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 etablissement substituant avec domaine = %L (reçu %L)', /*'azerty.al',*/ v_etablissement.domaine);*/
    assert v_etablissement.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 etablissement substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_etablissement.structure_id);

    perform test_substit_etab__tear_down();
END$$;
*/

--drop function test_substit_etab__removes_from_substit_si_source_app;
CREATE or replace FUNCTION test_substit_etab__removes_from_substit_si_source_app() returns void
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

    v_npd_etablissement_a varchar(256);

    v_substit_etablissement substit_etablissement;
    v_etablissement etablissement;
    v_pre_etablissement etablissement;
    v_pre_etablissement_1 etablissement;
    v_pre_etablissement_2 etablissement;
    v_pre_etablissement_3 etablissement;
begin
    perform test_substit_etab__set_up();

    v_npd_structure_a = 'etablissement,ETABLE_HISMAN';
    v_npd_etablissement_a = 'etablissement,ETABLE_HISMAN';

    --
    -- Création d'un structure et etablissement associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_1.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_1;

    --
    -- Test insertion d'un doublon de etablissement :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'etablissement,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_3;

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_1.id and npd = v_npd_etablissement_a;

    --
    -- Passage d'un etablissement à la source application :
    --   - retrait etablissement de la substitution existante : 2 doublons
    --
    update etablissement set source_id = 1 where id = v_pre_etablissement_1.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_etablissement from substit_etablissement where id = v_substit_etablissement.id;
    assert v_substit_etablissement.id is null,
        format('[TEST] Attendu : 1 substit_etablissement supprimé avec from_id = %L et npd = %L', v_pre_etablissement_1.id, v_npd_etablissement_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_3.id;
    select * into v_etablissement from etablissement i where id = v_substit_etablissement.to_id;
    /*assert v_etablissement.domaine = 'azerty.fr'/*car azerty.al a changé de source*/,
        format('[TEST] Attendu : 1 etablissement substituant avec domaine = %L (reçu %L)', /*'azerty.fr',*/ v_etablissement.domaine);*/
    assert v_etablissement.structure_id = v_substit_structure.to_id,
        format('[TEST] Attendu : 1 etablissement substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_etablissement.structure_id);

    perform test_substit_etab__tear_down();
END$$;


--drop function test_substit_etab__removes_from_substit_si_plus_source_app;
CREATE or replace FUNCTION test_substit_etab__removes_from_substit_si_plus_source_app() returns void
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

    v_npd_etablissement_a varchar(256);

    v_substit_etablissement substit_etablissement;
    v_etablissement etablissement;
    v_pre_etablissement etablissement;
    v_pre_etablissement_1 etablissement;
    v_pre_etablissement_2 etablissement;
    v_pre_etablissement_3 etablissement;
begin
    perform test_substit_etab__set_up();

    v_npd_structure_a = 'etablissement,ETABLE_HISMAN';
    v_npd_etablissement_a = 'etablissement,ETABLE_HISMAN';

    --
    -- Création d'un structure et etablissement associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_1.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_1;

    --
    -- Test insertion d'un doublon de etablissement :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'etablissement,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_3;

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_1.id and npd = v_npd_etablissement_a;

    --
    -- Passage d'un etablissement à la source application :
    --   - la substitution de la structure liée perdure ;
    --   - retrait etablissement de la substitution existante : 2 doublons.
    --
    update etablissement set source_id = 1 where id = v_pre_etablissement_1.id;

    --
    -- Retour d'un etablissement dans la source INSA :
    --   - ajout etablissement à la substitution existante : 3 doublons
    --
    update etablissement set source_id = v_source_id where id = v_pre_etablissement_1.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_1.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_1.id, v_npd_structure_a);

    select * into v_substit_etablissement from substit_etablissement where id = v_substit_etablissement.id;
    assert v_substit_etablissement.id is null,
        format('[TEST] Attendu : 1 substit_etablissement supprimé avec from_id = %L et npd = %L', v_pre_etablissement_1.id, v_npd_etablissement_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_1.id and npd = v_npd_etablissement_a;
    assert v_substit_etablissement.to_id is not null,
        format('[TEST] Attendu : 1 substit_etablissement recréé avec from_id = %L et npd = %L et histo_destruction null', v_pre_etablissement_1.id, v_npd_etablissement_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_3.id;
    select * into v_etablissement from etablissement i where id = v_substit_etablissement.to_id;
    /*assert v_etablissement.domaine = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 etablissement substituant avec domaine = %L (reçu %L)', /*'azerty.al',*/ v_etablissement.domaine);*/
    assert v_etablissement.structure_id = v_substit_structure.to_id,
        format('[TEST] Attendu : 1 etablissement substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_etablissement.structure_id);

    perform test_substit_etab__tear_down();
END$$;


--drop function test_substit_etab__adds_to_substit_si_npd_force;
CREATE or replace FUNCTION test_substit_etab__adds_to_substit_si_npd_force() returns void
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

    v_npd_etablissement_a varchar(256);

    v_substit_etablissement substit_etablissement;
    v_etablissement etablissement;
    v_pre_etablissement etablissement;
    v_pre_etablissement_1 etablissement;
    v_pre_etablissement_2 etablissement;
    v_pre_etablissement_3 etablissement;
    v_pre_etablissement_4 etablissement;
begin
    perform test_substit_etab__set_up();

    v_npd_structure_a = 'etablissement,ETABLE_HISMAN';
    v_npd_etablissement_a = 'etablissement,ETABLE_HISMAN';

    --
    -- Création d'un structure et etablissement associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_1.id, /*'azerty.com',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_1;

    --
    -- Test insertion d'un doublon de etablissement :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_2;

    --
    -- Test insertion d'un autre doublon :
    --   - ajout à la subsitution existante 'etablissement,ETABLE_HISMAN' : 3 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_3.id, /*'azerty.org',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_3;

    --
    -- Test insertion etablissement avec NPD forcé mais STRUCTURE SANS NPD FORCÉ :
    --   - ajout à la subsitution existante : 4 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'PAS_ETABLE_HISMAN', 'Pas Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_4; -- NB : pas de NPD forcé donc la structure n'est pas en doublon

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_4.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_etablissement_a
    returning * into v_pre_etablissement_4; -- NB : NPD forcé

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_4.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is null,
        format('[TEST] Attendu : 0 substit_structure avec from_id = %s et npd = %L car la structure elle n''est pas en doublon', v_pre_structure_4.id, v_npd_structure_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_3.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_3.id, v_npd_structure_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_4.id and npd = v_npd_etablissement_a;
    assert v_substit_etablissement.to_id is not null,
        format('[TEST] Attendu : 1 substit_etablissement avec from_id = %s et npd = %L', v_pre_etablissement_4.id, v_npd_etablissement_a, v_pre_structure_4.id);

    select * into v_etablissement from etablissement i where id = v_substit_etablissement.to_id;
    /*assert v_etablissement.domaine = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 etablissement substituant avec domaine = %L (reçu %L)', /*'azerty.al',*/ v_etablissement.domaine);*/
    assert v_etablissement.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 etablissement substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_etablissement.structure_id);

    perform test_substit_etab__tear_down();
END$$;


--drop function test_substit_etab__adds_to_substit_si_ajout_npd;
CREATE or replace FUNCTION test_substit_etab__adds_to_substit_si_ajout_npd() returns void
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

    v_npd_etablissement_a varchar(256);

    v_substit_etablissement substit_etablissement;
    v_etablissement etablissement;
    v_pre_etablissement etablissement;
    v_pre_etablissement_1 etablissement;
    v_pre_etablissement_2 etablissement;
    v_pre_etablissement_3 etablissement;
begin
    perform test_substit_etab__set_up();

    v_npd_structure_a = 'etablissement,ETABLE_HISMAN';
    v_npd_etablissement_a = 'etablissement,ETABLE_HISMAN';

    --
    -- Création d'un structure et etablissement associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_1;

    --
    -- Test insertion d'un doublon de etablissement :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    --
    -- Test insertion etablissement puis update du NPD forcé : COCHON Michel cccc@mail.fr
    --   - ajout à la subsitution existante : 3 doublons (etablissement_id = 5 car majoritaire)
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'PAS_ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_3;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_3.id, /*'azerty.al',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_3;

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_3.id;
    assert v_substit_etablissement.to_id is null,
        format('[TEST] Attendu : aucun substit_etablissement avec from_id = %L ', v_pre_etablissement_3.id);

    update etablissement set npd_force = v_npd_etablissement_a where id = v_pre_etablissement_3.id;

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_3.id and npd = v_npd_etablissement_a;
    assert v_substit_etablissement.to_id is not null,
        format('[TEST] Attendu : 1 substit_etablissement avec from_id = %L et npd = %L', v_pre_etablissement_3.id, v_npd_etablissement_a);

    select * into v_etablissement from etablissement i where id = v_substit_etablissement.to_id;
    /*assert v_etablissement.domaine = 'azerty.al'/*car ordre alpha*/,
        format('[TEST] Attendu : 1 etablissement substituant avec domaine = %L (reçu %L)', /*'azerty.al',*/ v_etablissement.domaine);*/
    assert v_etablissement.structure_id = v_substit_structure.to_id/*id de l'structure substituant*/,
        format('[TEST] Attendu : 1 etablissement substituant avec structure_id = %s (reçu %s)', v_substit_structure.to_id, v_etablissement.structure_id);

    perform test_substit_etab__tear_down();
END$$;


--drop function test_substit_etab__deletes_substit_si_plus_doublon;
CREATE or replace FUNCTION test_substit_etab__deletes_substit_si_plus_doublon() returns void
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

    v_npd_etablissement_a varchar(256);

    v_substit_etablissement substit_etablissement;
    v_etablissement etablissement;
    v_pre_etablissement etablissement;
    v_pre_etablissement_1 etablissement;
    v_pre_etablissement_2 etablissement;
begin
    perform test_substit_etab__set_up();

    v_npd_structure_a = 'etablissement,ETABLE_HISMAN';
    v_npd_etablissement_a = 'etablissement,ETABLE_HISMAN';

    --
    -- Création d'un structure et etablissement associé :
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hisman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_1;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_1.id, /*'azerty.org',*/ 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_1;

    --
    -- Test insertion d'un doublon de etablissement :
    --   - création d'une subsitution 'azerty.org' : 2 doublons
    --
    INSERT INTO structure(id, type_structure_id, code, libelle, sigle, source_code, source_id, histo_createur_id, npd_force)
    select nextval('structure_id_seq'), 1, 'ETABLE_HISMAN', 'Etable Hissman', 'test1234', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_structure_2;

    INSERT INTO etablissement(id, structure_id, /*domaine,*/ source_code, source_id, histo_createur_id, npd_force)
    select nextval('etablissement_id_seq'), v_pre_structure_2.id, /*'azerty.fr',*/ 'UCN::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_etablissement_2;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.to_id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %L et npd = %L', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_1.id and npd = v_npd_etablissement_a;
    assert v_substit_etablissement.to_id is not null,
        format('[TEST] Attendu : 1 substit_etablissement avec from_id = %L et npd = %L', v_pre_etablissement_1.id, v_npd_etablissement_a);

    -- Modif du NPD forcé pour sortir celui avec azerty.fr de la substitution :
    --   - la substitution de la structure liée perdure ;
    --   - retrait etablissement de la substitution existante : 1 substitué restant ;
    --   - suppression de la substitution car 0 doublon ;
    --   - suppression du substituant.
    update etablissement set npd_force = 'ksldqhflksjdqhfl' where id = v_pre_etablissement_2.id;

    select * into v_substit_structure from substit_structure where from_id = v_pre_structure_2.id and npd = v_npd_structure_a;
    assert v_substit_structure.id is not null,
        format('[TEST] Attendu : 1 substit_structure avec from_id = %s et npd = %L non historise', v_pre_structure_2.id, v_npd_structure_a);

    select * into v_substit_etablissement from substit_etablissement where from_id = v_pre_etablissement_1.id and npd = v_npd_etablissement_a;
    select count(*) into v_count from substit_etablissement i where to_id = v_substit_etablissement.to_id;
    assert v_count = 0,
        format('[TEST] Attendu : 0 substit_etablissement avec substituant = %s', v_substit_etablissement.to_id);

    select * into v_etablissement from etablissement i where id = v_substit_etablissement.to_id;
    assert v_etablissement.id is null,
        format('[TEST] Attendu : 1 etablissement substituant supprimé : %s', v_etablissement.id);

    perform test_substit_etab__tear_down();
END$$;


select test_substit_etab__fetches_data_for_substituant();
select test_substit_etab__creates_substit_2_doublons();
-- select test_substit_etab__adds_to_substit_si_dehistorise(); -- NA dans dernière version
-- select test_substit_etab__removes_from_substit_si_historise(); -- NA dans dernière version
select test_substit_etab__removes_from_substit_si_source_app();
select test_substit_etab__removes_from_substit_si_plus_source_app();
select test_substit_etab__adds_to_substit_si_npd_force(); -- NB : NPD forcé sur pre_etablissement seulement (pas sur pre_structure)
select test_substit_etab__adds_to_substit_si_ajout_npd();
select test_substit_etab__deletes_substit_si_plus_doublon();
/*
select test_substit_etab__creates_substit_2_doublons();
select substit_npd_structure(pre.*) from pre_structure pre where code='ETABLE_HISMAN';
select substit_npd_structure(pre.*) from pre_structure pre order by id desc;
select substit_npd_etablissement(pre.*) from pre_etablissement pre order by id desc;
select * from pre_structure order by id desc;
select * from pre_etablissement order by id desc;
select * from v_structure_doublon order by id desc;
select * from substit_structure order by id desc;
select * from v_etablissement_doublon order by id desc;
select * from substit_etablissement order by id desc;
select * from structure order by id desc;
select * from etablissement order by id desc;
select * from substit_fetch_data_for_substituant_etablissement('etablissement,ETABLE_HISMAN');
*/

rollback;