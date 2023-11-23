-- ------------------------------------------------------------------------------------------------------------------
-- Tests : individu
-- ------------------------------------------------------------------------------------------------------------------

--drop function test_substit_individu__set_up;
CREATE or replace FUNCTION test_substit_individu__set_up() returns void
    language plpgsql
as
$$begin
    alter table individu disable trigger individu_rech_update;
end$$;


--drop function test_substit_individu__tear_down;
CREATE or replace FUNCTION test_substit_individu__tear_down() returns void
    language plpgsql
as
$$begin
    delete from substit_log sl where type = 'individu' and exists (select id from individu s where sl.substitue_id = s.id and nom_usuel = 'test1234');
    delete from substit_log sl where type = 'individu' and exists (select id from individu s where sl.substituant_id = s.id and nom_usuel = 'test1234');

    alter table individu disable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit;

    delete from substit_fk_replacement where type = 'individu' and to_id in (select id from individu where nom_usuel = 'test1234');
    delete from individu_substit where from_id in (select id from individu where nom_usuel = 'test1234');
    delete from individu_substit where to_id in (select id from individu where nom_usuel = 'test1234');
    delete from individu where nom_usuel = 'test1234';

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit enable trigger substit_trigger_on_individu_substit;

    alter table individu enable trigger individu_rech_update;
end$$;


--drop function test_substit_individu__fetches_data_for_substituant;
CREATE or replace FUNCTION test_substit_individu__fetches_data_for_substituant() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
    v_data record;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Création d'un doublon : HOCHON PAULE bbbb@mail.fr
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Création d'un autre doublon : HÔCHON Paule cccc@mail.fr
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'Hochon', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
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
    update individu set prenom1 = 'Paule' where id = v_pre_individu_1.id;

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
    update individu set email = 'cccc@mail.fr' where id = v_pre_individu_1.id;

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
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id;
    assert v_individu_substit.to_id is null,
        format('[TEST] Attendu : aucun individu_substit avec from_id = % ', v_pre_individu_1.id);

    --
    -- Test insertion d'un doublon : HOCHON PAULE aaaa@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_1.id, v_npd_a);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_2.id, v_npd_a);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert not (v_individu is null or v_individu.email <> 'aaaa@mail.fr'),
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'aaaa@mail.fr', v_individu.email);

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
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id;
    assert v_individu_substit.to_id is null,
        format('[TEST] Attendu : aucun individu_substit avec from_id = % ', v_pre_individu_1.id);

    --
    -- Test insertion d'un doublon : HOCHON PAULE aaaa@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_1.id, v_npd_a);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_2.id, v_npd_a);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert not (v_individu is null or v_individu.email <> 'aaaa@mail.fr'),
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'aaaa@mail.fr', v_individu.email);

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule bbbb@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = bbbb@mail.fr car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_3.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_3.id, v_npd_a);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert not (v_individu is null or v_individu.email <> 'bbbb@mail.fr'),
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'bbbb@mail.fr', v_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__creates_substit_2_doublons_insert_select;
CREATE or replace FUNCTION test_substit_individu__creates_substit_2_doublons_insert_select() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création *dans le même insert* de 3 individus, dont 2 doublons :
    --   - HOCHON PAULE (mail = bbbb@mail.fr)
    --   - HOCHON PAULE (mail = aaaa@mail.fr)
    --
    -- NB : l'ajout par INSERT...SELECT a dû faire l'objet d'une gestion particulière (cf. fonction "substit_trigger_fct").
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'TERIEUR', 'test1234', 'Alain', '2011-01-01'::date, 'abcd@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    union all
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01'::date, 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    union all
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01'::date, 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null;

    select * into v_pre_individu_1 from individu where nom_usuel = 'test1234' and email = 'bbbb@mail.fr';
    select * into v_pre_individu_2 from individu where nom_usuel = 'test1234' and email = 'aaaa@mail.fr';

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_1.id, v_npd_a);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_2.id, v_npd_a);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'aaaa@mail.fr', v_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__creates_substit_and_replaces_fk;
CREATE or replace FUNCTION test_substit_individu__creates_substit_and_replaces_fk() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_record record;
    v_table record;

    v_role role;
    v_individu_role_1 individu_role;
    v_individu_role_2 individu_role;
    v_utilisateur_1 utilisateur;
    v_utilisateur_2 utilisateur;

    v_individu_substit_1 individu_substit;
    v_individu_substit_2 individu_substit;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_individu_1 individu;
    v_individu_2 individu;
    v_individu_id bigint;
    v_fkr_1 substit_fk_replacement;
    v_fkr_2 substit_fk_replacement;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit enable trigger substit_trigger_on_individu_substit; -- AVEC remplacement des FK

    v_npd_a = 'hochon_paule_20000101';

    select * into v_role from role where code = 'ADMIN_TECH'; -- NB : il n'y a qu'un seul role avec ce code

    -- Création d'un 'individu' : HOCHON PAULE (mail = bbbb@mail.fr) :
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_individu_1;
    -- Création d'un 'utilisateur' pointant sur le 1er 'individu' :
    INSERT INTO utilisateur(id, username, password, individu_id) select nextval('utilisateur_id_seq'), 'login1234', 'xxxx', v_individu_1.id
    returning * into v_utilisateur_1;

    -- Insertion d'un 'individu' futur doublon : COCHON PAULE (mail = aaaa@mail.fr) :
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id)
    select nextval('individu_id_seq'), 'COCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_individu_2;
    -- Création d'un 'utilisateur' pointant sur le 1er 'individu' :
    INSERT INTO utilisateur(id, username, password, individu_id) select nextval('utilisateur_id_seq'), 'login5678', 'xxxx', v_individu_2.id
    returning * into v_utilisateur_2;

    -- Update du 2e 'individu' pour déclencher les substitutions
    update individu set nom_patronymique = 'Hochon' where id = v_individu_2.id;

    select * into v_individu_substit_1 from individu_substit where from_id = v_individu_1.id and npd = v_npd_a;
    select * into v_individu_substit_2 from individu_substit where from_id = v_individu_2.id and npd = v_npd_a;

    select * into v_utilisateur_1 from utilisateur where id = v_utilisateur_1.id; -- refetch
    select * into v_utilisateur_2 from utilisateur where id = v_utilisateur_2.id; -- refetch

    assert v_utilisateur_1.individu_id = v_individu_substit_1.to_id,
        format('[TEST] Attendu : FK UTILISATEUR.individu_id remplacée par %s (mais valeur = %s)',
               v_individu_substit_1.to_id, v_utilisateur_1.individu_id);

    assert v_utilisateur_2.individu_id = v_individu_substit_2.to_id,
        format('[TEST] Attendu : FK UTILISATEUR.individu_id remplacée par %s (mais valeur = %s)',
               v_individu_substit_2.to_id, v_utilisateur_2.individu_id);

    select * into v_fkr_1 from substit_fk_replacement
    where type = 'individu' and table_name = 'utilisateur' and column_name = 'individu_id'
      and record_id = v_utilisateur_1.id
      and from_id = v_individu_1.id
      and to_id = v_individu_substit_1.to_id;
    assert v_fkr_1.id is not null,
        format('[TEST] Attendu : un SUBSTIT_FK_REPLACEMENT pour %s => %s et la table "utilisateur"',
               v_individu_1.id, v_individu_substit_1.to_id, v_utilisateur_1.individu_id);

    select * into v_fkr_2 from substit_fk_replacement
    where type = 'individu' and table_name = 'utilisateur' and column_name = 'individu_id'
      and record_id = v_utilisateur_2.id
      and from_id = v_individu_2.id
      and to_id = v_individu_substit_2.to_id;
    assert v_fkr_2.id is not null,
        format('[TEST] Attendu : un SUBSTIT_FK_REPLACEMENT pour %s => %s et la table "utilisateur"',
               v_individu_2.id, v_individu_substit_2.to_id, v_utilisateur_2.individu_id);

    delete from utilisateur where id = v_utilisateur_1.id;
    delete from utilisateur where id = v_utilisateur_2.id;

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__creates_substit_can_fail_to_replace_fk;
CREATE or replace FUNCTION test_substit_individu__creates_substit_can_fail_to_replace_fk() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_role role;
    v_individu_role_1 individu_role;
    v_individu_role_2 individu_role;
    v_individu_substit individu_substit;
    v_individu_1 individu;
    v_individu_2 individu;
    v_individu_id bigint;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;

    v_count smallint;
begin
    perform test_substit_individu__set_up();

    alter table individu disable trigger substit_trigger_individu; -- pour pouvoir créer des individu

    v_npd_a = 'hochon_paule_20000101';

    select * into v_role from role where code = 'ADMIN_TECH'; -- NB : il n'y a qu'un seul role avec ce code

    -- Création d'un 'individu' : HOCHON PAULE (mail = bbbb@mail.fr) :
    select nextval('individu_id_seq') into v_individu_id; -- 'individu' et 'individu' partagent les mêmes ids
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select v_individu_id, 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_individu_1;

    -- Création d'un 'individu' : TERGEIST PAULE (mail = aaaa@mail.fr)
    select nextval('individu_id_seq') into v_individu_id; -- 'individu' et 'individu' partagent les mêmes ids
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select v_individu_id, 'TERGEIST', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_individu_2;

    -- Création d'un 'individu_role' pointant sur le 1er 'individu' :
    INSERT INTO individu_role(id, role_id, individu_id) select nextval('individu_role_id_seq'), v_role.id, v_individu_1.id
    returning * into v_individu_role_1;
    -- Création d'un 'individu_role' pointant sur le 2e 'individu' ET SUR LE MÊME ROLE QUE LE 1ER :
    INSERT INTO individu_role(id, role_id, individu_id) select nextval('individu_role_id_seq'), v_role.id, v_individu_2.id
    returning * into v_individu_role_2;

    alter table individu enable trigger substit_trigger_individu; -- rétablissement du trigger
    alter table individu_substit enable trigger substit_trigger_on_individu_substit; -- AVEC remplacement des FK

    -- Update du 2e 'individu' pour déclencher les substitutions
    update individu set nom_patronymique = 'Hochon' where id = v_individu_2.id;

    -- Fetch de la substitution
    select * into v_individu_substit from individu_substit where from_id = v_individu_1.id and npd = v_npd_a;

    -- Attendu : 1 seul remplacement de FK sur les 2 nécessaires car la contrainte d'unicité (role_id, individu_id)
    -- bloque le 2e remplacement :
    select count(*) into v_count from individu_role where role_id = v_role.id and individu_id = v_individu_substit.to_id;
    assert v_count = 1, format('[TEST] Attendu : une FK INDIVIDU_ROLE.individu_id remplacée par %s', v_individu_substit.to_id);
    select count(*) into v_count from individu_role where role_id = v_role.id and individu_id in (v_individu_1.id, v_individu_2.id);
    assert v_count = 1, format('[TEST] Attendu : une FK INDIVIDU_ROLE.individu_id non remplacée');

    delete from individu_role where id in (v_individu_role_1.id, v_individu_role_2.id);

    perform test_substit_individu__tear_down();
end$$;

/*
--drop function test_substit_individu__removes_from_substit_si_historise;
CREATE or replace FUNCTION test_substit_individu__removes_from_substit_si_historise() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_structure_substit structure_substit;
    v_structure structure;
    v_pre_structure_1 structure;
    v_pre_structure_2 structure;
    v_pre_structure_3 structure;
begin
    perform test_substit_structure__set_up();

    -- TODO

    perform test_substit_structure__tear_down();
end$$;
*/

--drop function test_substit_individu__substituant_update_enabled;
CREATE or replace FUNCTION test_substit_individu__substituant_update_enabled() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA

    v_npd_individu_a varchar(256);

    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
    v_individu individu;
    v_individu_substit individu_substit;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_individu_a = 'hochon_paule_20000101';

    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    -- Insertion d'un doublon : HOCHON PAULE aaaa@mail.fr
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    -- Fetch de la substitution et du substituant correspondant
    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_individu_a;
    select * into v_individu from individu i where id = v_individu_substit.to_id;

    -- Verif des valeurs des attributs mis à jour automatiquement à partir des substitués
    assert v_individu.email = 'aaaa@mail.fr' /* car ordre alpha */,
        format('[TEST] Attendu : 1 individu substituant avec email = %s (mais email = %s)', 'aaaa@mail.fr', v_individu.email);

    -- À présent, interdiction de mise à jour automatique des valeurs des attributs du substituant à partir des substitués
    update individu set est_substituant_modifiable = false where id = v_individu.id;

    -- Insertion d'un autre doublon : HÔCHON Paule bbbb@mail.fr
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    -- Vérif que les valeurs d'attributs du substituant n'ont pas changé
    assert v_individu.email = 'aaaa@mail.fr' /* alors que bbbb@mail.fr est majoritaire */,
        format('[TEST] Attendu : 1 individu substituant avec email = %s (mais email = %s)', 'aaaa@mail.fr', v_individu.email);

    perform test_substit_individu__tear_down();
END$$;


--drop function test_substit_individu__keeps_in_substit_si_historise;
CREATE or replace FUNCTION test_substit_individu__keeps_in_substit_si_historise() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE aaaa@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule cccc@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Historisation d'un individu : HOCHON PAULE aaaa@mail.fr
    --   - pas de retrait de l'enregistrement de la substitution existante 'hochon_paule_20000101'
    --
    update individu set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_individu_2.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_2.id, v_npd_a);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'aaaa@mail.fr', v_individu.email);

    perform test_substit_individu__tear_down();
end$$;


--drop function test_substit_individu__removes_from_substit_and_restores_fk;
CREATE or replace FUNCTION test_substit_individu__removes_from_substit_and_restores_fk() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_record record;
    v_table record;

    v_role role;
    v_individu_role individu_role;
    v_utilisateur utilisateur;

    v_individu_substit individu_substit;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
    v_individu_1 individu;
    v_individu_2 individu;
    v_individu_3 individu;
    v_individu_id bigint;
    v_fkr_1 substit_fk_replacement;
    v_fkr_2 substit_fk_replacement;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit enable trigger substit_trigger_on_individu_substit; -- AVEC remplacement des FK

    v_npd_a = 'hochon_paule_20000101';

    select * into v_role from role where code = 'ADMIN_TECH'; -- NB : il n'y a qu'un seul role avec ce code

    -- Création d'un 'individu' : HOCHON PAULE (mail = aaaa@mail.fr) :
    select nextval('individu_id_seq') into v_individu_id; -- 'individu' et 'individu' partagent les mêmes ids
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id)
    select v_individu_id, 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_individu_1;

    -- Création d'un 'individu_role' pointant sur le 1er 'individu' :
    INSERT INTO individu_role(id, role_id, individu_id) select nextval('individu_role_id_seq'), v_role.id, v_individu_1.id
    returning * into v_individu_role;
    -- Création d'un 'individu_compl' pointant sur le 1er 'individu' :
    INSERT INTO utilisateur(id, username, password, individu_id) select nextval('utilisateur_id_seq'), 'login1234', 'xxxx', v_individu_1.id
    returning * into v_utilisateur;

    -- Insertion d'un 'individu' en doublon : HOCHON PAULE bbbb@mail.fr
    select nextval('individu_id_seq') into v_individu_id; -- 'individu' et 'individu' partagent les mêmes ids
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id)
    select v_individu_id, 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_individu_2;

    select * into v_individu_substit from individu_substit where from_id = v_individu_1.id and npd = v_npd_a;

    -- Modif du NPD forcé pour sortir le 1er enregistrement de la substitution
    update individu set npd_force = 'ksldqhflksjdqhfl' where id = v_individu_1.id;

    select * into v_individu_role from individu_role where id = v_individu_role.id; -- refetch
    select * into v_utilisateur from utilisateur where id = v_utilisateur.id; -- refetch

    assert v_individu_role.individu_id = v_individu_1.id,
        format('[TEST] Attendu : FK INDIVIDU_ROLE.individu_id restaurée à %s (mais valeur = %s)',
               v_individu_1.id, v_individu_role.individu_id);
    assert v_utilisateur.individu_id = v_individu_1.id,
        format('[TEST] Attendu : FK UTILISATEUR.individu_id restaurée à %s (mais valeur = %s)',
               v_individu_1.id, v_utilisateur.individu_id);

    select * into v_fkr_1 from substit_fk_replacement
    where type = 'individu' and table_name = 'individu_role' and column_name = 'individu_id'
      and record_id = v_individu_role.id and from_id = v_individu_1.id and to_id = v_individu_substit.to_id;
    select * into v_fkr_2 from substit_fk_replacement
    where type = 'individu' and table_name = 'utilisateur' and column_name = 'individu_id'
      and record_id = v_utilisateur.id and from_id = v_individu_1.id and to_id = v_individu_substit.to_id;

    assert v_fkr_1.id is null,
        format('[TEST] Attendu : plus aucun SUBSTIT_FK_REPLACEMENT pour %s => %s dans INDIVIDU_ROLE',
               v_individu_1.id, v_individu_substit.to_id, v_individu_role.individu_id);
    assert v_fkr_2.id is null,
        format('[TEST] Attendu : plus aucun SUBSTIT_FK_REPLACEMENT pour %s => %s dans UTILISATEUR',
               v_individu_1.id, v_individu_substit.to_id, v_utilisateur.individu_id);

    delete from individu_role where id = v_individu_role.id;
    delete from utilisateur where id = v_utilisateur.id;

    perform test_substit_individu__tear_down();
end$$;

/*
--drop function test_substit_individu__adds_to_substit_si_dehistorise;
CREATE or replace FUNCTION test_substit_individu__adds_to_substit_si_dehistorise() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule cccc@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Historisation d'un individu : HOCHON PAULE bbbb@mail.fr
    --
    update individu set histo_destruction = current_timestamp, histo_destructeur_id = 1 where id = v_pre_individu_2.id;

    --
    -- Restauration d'un individu : HOCHON PAULE bbbb@mail.fr
    --   - ajout individu dans la substitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alphabet)
    --
    update individu set histo_destruction = null, histo_destructeur_id = null where id = v_pre_individu_2.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a and histo_destruction is null;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L et histo_destruction null', v_pre_individu_2.id, v_npd_a);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'aaaa@mail.fr', v_individu.email);

    perform test_substit_individu__tear_down();
end$$;
*/

--drop function test_substit_individu__removes_from_substit_si_source_app;
CREATE or replace FUNCTION test_substit_individu__removes_from_substit_si_source_app() returns void
    language plpgsql
as
$$declare
    v_app_user bigint = 1; -- pseudo-utilisateur SyGAL
    v_source_id bigint = 2; -- source INSA
    v_npd_a varchar(256);

    v_individu_substit individu_substit;
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule cccc@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Passage d'un individu subsitué dans la source application : HOCHON PAULE aaaa@mail.fr
    --   - retrait individu de la substitution existante : 2 doublons restants (mail substituant = bbbb@mail.fr car ordre alpha)
    --
    update individu set source_id = 1 where id = v_pre_individu_1.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a;
    assert v_individu_substit.to_id is null,
        format('[TEST] Attendu : 1 individu_substit supprimé avec from_id = %s et npd = %L', v_pre_individu_1.id, v_npd_a);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.email = 'bbbb@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'bbbb@mail.fr', v_individu.email);

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
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule cccc@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Passage d'un individu subsitué dans la source application : HOCHON PAULE aaaa@mail.fr
    --   - retrait individu de la substitution existante : 2 doublons restants (mail substituant = bbbb@mail.fr car ordre alpha)
    --
    update individu set source_id = 1 where id = v_pre_individu_1.id;

    --
    -- Retour d'un individu dans la source INSA : HOCHON PAULE aaaa@mail.fr
    --   - ajout individu dans la substitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = aaaa@mail.fr car ordre alpha)
    --
    update individu set source_id = v_source_id where id = v_pre_individu_1.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a and histo_destruction is null;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L et histo_destruction null', v_pre_individu_1.id, v_npd_a);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'aaaa@mail.fr', v_individu.email);

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
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion individu avec NPD forcé : HOCHON Paulette aaaa@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'Paulette', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_a
    returning * into v_pre_individu_2;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_2.id, v_npd_a);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'aaaa@mail.fr', v_individu.email);

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
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
    v_pre_individu_4 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule bbbb@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = bbbb@mail.fr car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Création d'un individu : HOCHAN PAULE (mail = cccc@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHAN', 'test1234', 'Paule', '2000-01-01', 'cccc@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_4;

    v_npd_b = 'hochan_paule_20000101';

    --
    -- Test modif HÔCHON (bbbb@mail.fr) => HOCHAN (bbbb@mail.fr) :
    --   - retrait individu de la substitution existante 'hochon_paule_20000101' (historisation) : 2 doublons restants (mail substituant = aaaa@mail.fr car majoritaire)
    --   - création d'une nouvelle substitution 'hochan_paule_20000101' : 2 doublons (mail substituant = bbbb@mail.fr car 1 vs 1 mais 1er dans alphabet)
    --
    update individu set nom_patronymique = 'HOCHAN' where id = v_pre_individu_3.id; -- 'HÔCHON' => 'HOCHAN'

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_3.id and npd = v_npd_a;
    assert v_individu_substit.to_id is null,
        format('[TEST] Attendu : 1 individu_substit supprimé avec from_id = %s et npd = %L', v_pre_individu_3.id, v_npd_a);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a;
    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = %s (mais email = %L)', 'aaaa@mail.fr', v_individu.email);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_3.id and npd = v_npd_b;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_3.id, v_npd_b);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_4.id and npd = v_npd_b;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = %s et npd = %L', v_pre_individu_4.id, v_npd_b);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert not (v_individu is null or v_individu.email <> 'bbbb@mail.fr'),
        format('[TEST] Attendu : 1 individu substituant avec email = %L (mais email = %L)', 'bbbb@mail.fr', v_individu.email);

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
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
    v_pre_individu_4 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule bbbb@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = bbbb@mail.fr car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Test insertion individu puis update du NPD forcé : COCHON Michel aaaa@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 4 doublons (mail substituant = aaaa@mail.fr car 2 contre 2 mais ordre alpha)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id)
    select nextval('individu_id_seq'), 'COCHON', 'test1234', 'Michel', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user
    returning * into v_pre_individu_4;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_4.id;
    assert v_individu_substit.to_id is null,
        format('[TEST] Attendu : aucun individu_substit avec from_id = % ', v_pre_individu_4.id);

    update individu set npd_force = 'hochon_paule_20000101' where id = v_pre_individu_4.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_4.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_4.id, v_npd_a);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'aaaa@mail.fr', v_individu.email);

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
    v_individu individu;
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_pre_individu_3 individu;
    v_pre_individu_4 individu;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = aaaa@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE bbbb@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    --
    -- Test insertion d'un autre doublon : HÔCHON Paule bbbb@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 3 doublons (mail substituant = bbbb@mail.fr car majoritaire)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HÔCHON', 'test1234', 'Paule', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_3;

    --
    -- Test insertion individu avec NPD forcé : COCHON Michel aaaa@mail.fr
    --   - ajout à la subsitution existante 'hochon_paule_20000101' : 4 doublons (mail substituant = aaaa@mail.fr car 2 contre 2 mais ordre alpha)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'COCHON', 'test1234', 'Michel', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, v_npd_a
    returning * into v_pre_individu_4;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_4.id and npd = v_npd_a;
    assert v_individu_substit.to_id is not null,
        format('[TEST] Attendu : 1 individu_substit avec from_id = % et npd = %', v_pre_individu_4.id, v_npd_a);

    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.email = 'aaaa@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = % (mais email = %)', 'aaaa@mail.fr', v_individu.email);

    --
    -- Effacement du NPD forcé.
    --
    update individu set npd_force = null where id = v_pre_individu_4.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_4.id and npd = v_npd_a;
    assert v_individu_substit.to_id is null,
        format('[TEST] Attendu : 1 individu_substit supprimé avec from_id = %s et npd = %L', v_pre_individu_4.id, v_npd_a);

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a;
    select * into v_individu from individu i where id = v_individu_substit.to_id;
    assert v_individu.email = 'bbbb@mail.fr',
        format('[TEST] Attendu : 1 individu substituant avec email = %s (mais email = %s)', 'bbbb@mail.fr', v_individu.email);

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
    v_pre_individu_1 individu;
    v_pre_individu_2 individu;
    v_individu individu;
    v_count smallint;
begin
    perform test_substit_individu__set_up();

    alter table individu enable trigger substit_trigger_individu;
    alter table individu_substit disable trigger substit_trigger_on_individu_substit; -- SANS remplacement de FK

    v_npd_a = 'hochon_paule_20000101';

    --
    -- Création d'un individu : HOCHON PAULE (mail = bbbb@mail.fr)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'bbbb@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_1;

    --
    -- Test insertion d'un doublon : HOCHON PAULE aaaa@mail.fr
    --   - création d'une subsitution 'hochon_paule_20000101' : 2 doublons (mail substituant = aaaa@mail.fr car 1er dans alphabet)
    --
    INSERT INTO individu(id, nom_patronymique, nom_usuel, prenom1, date_naissance, email, source_code, source_id, histo_createur_id, npd_force)
    select nextval('individu_id_seq'), 'HOCHON', 'test1234', 'PAULE', '2000-01-01', 'aaaa@mail.fr', 'INSA::'||trunc(10000000000*random()), v_source_id, v_app_user, null
    returning * into v_pre_individu_2;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_1.id and npd = v_npd_a;
    select * into v_individu from individu i where id = v_individu_substit.to_id;

    -- Modif du NPD forcé pour sortir HOCHON PAULE aaaa@mail.fr de la substitution
    --   - retrait individu de la substitution existante : 1 doublons restant (mail substituant = bbbb@mail.fr car ordre alphabet)
    --   - historisation du substituant existant car 0 doublon restant.
    update individu set npd_force = 'ksldqhflksjdqhfl' where id = v_pre_individu_2.id;

    select * into v_individu_substit from individu_substit where from_id = v_pre_individu_2.id and npd = v_npd_a;
    assert v_individu_substit.to_id is null,
        format('[TEST] Attendu : 1 individu_substit supprimé avec from_id = %s et npd = %L', v_pre_individu_2.id, v_npd_a);

    select count(*) into v_count from individu_substit i where to_id = v_individu.id;
    assert v_count = 0,
        format('[TEST] Attendu : 0 individu_substit avec substituant = %s', v_individu_substit.to_id);

    select * into v_individu from individu where id = v_individu.id;
    assert v_individu.id is null,
        format('[TEST] Attendu : individu substituant supprimé : %s', v_individu.id);

    perform test_substit_individu__tear_down();
end$$;


select test_substit_individu__fetches_data_for_substituant();
select test_substit_individu__creates_substit_2_doublons();
select test_substit_individu__creates_substit_3_doublons();
select test_substit_individu__creates_substit_2_doublons_insert_select();
select test_substit_individu__creates_substit_and_replaces_fk();
select test_substit_individu__creates_substit_can_fail_to_replace_fk();
select test_substit_individu__updates_substits_si_modif_nom();
select test_substit_individu__adds_to_substit_si_npd_force();
--select test_substit_individu__removes_from_substit_si_historise(); -- NA dans dernière version
--select test_substit_individu__adds_to_substit_si_dehistorise(); -- NA dans dernière version
select test_substit_individu__adds_to_substit_si_ajout_npd();
select test_substit_individu__adds_to_substit_si_suppr_npd();
select test_substit_individu__substituant_update_enabled();
select test_substit_individu__keeps_in_substit_si_historise();
select test_substit_individu__removes_from_substit_si_source_app();
select test_substit_individu__removes_from_substit_si_plus_source_app();
select test_substit_individu__removes_from_substit_and_restores_fk();
select test_substit_individu__deletes_substit_si_plus_doublon();
/*
select * from substit_log;
select * from structure_substit order by to_id, id;
select * from etablissement_substit order by to_id, id;
select * from pre_etablissement where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select * from src_etablissement where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select * from etablissement where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select i.id, v.* from v_diff_etablissement v join pre_etablissement i on v.source_code = i.source_code;

select substit_create_all_substitutions_etablissement(20); -- totalité : 23-24 min (avec ou sans les raise)
*/
