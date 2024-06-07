--
-- Suppression de individu.z_etablissement_id
--

drop view if exists v_individu_insa_double;

drop trigger if exists substit_trigger_individu on individu;
create trigger substit_trigger_individu
    after insert
        or delete
        or update of
        nom_patronymique, prenom1, date_naissance, -- pour entrer ou sortir d'une substitution éventuelle (NPD)
        --
        type, civilite, nom_usuel, prenom2, prenom3, email, nationalite,
        supann_id, pays_id_nationalite, -- pour mettre à jour le substituant éventuel
        --
        npd_force, -- pour réagir à une demande de substitution forcée
        histo_destruction, -- pour réagir à l'historisation/restauration d'un enregsitrement
        source_id -- pour réagir au changement de source (source application => source compatible, et vice-versa)
    on individu
    for each row
    when (pg_trigger_depth() < 1)
execute procedure substit_trigger_fct('individu');

alter table individu drop column z_etablissement_id;


--
-- Suppression de individu.source_code_sav
--

alter table individu drop column source_code_sav;


--
-- Suppression de rapport_avis.commentaires
--

alter table rapport_avis drop column commentaires;


--
-- Suppression de substit_doctorant.npd_sav
--

alter table substit_doctorant drop column npd_sav;


--
-- Suppression de z_doctorant_compl
--

drop table if exists z_doctorant_compl;


--
-- Création d'une PK correcte dans soutenance_horodatage
--

alter table soutenance_horodatage add id bigserial not null ;
alter table soutenance_horodatage drop constraint soutenance_horodatage_pk;
alter table soutenance_horodatage add constraint soutenance_horodatage_pk primary key (id);
