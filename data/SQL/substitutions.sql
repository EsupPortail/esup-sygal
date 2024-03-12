--======================================================================================================================
--                  MODIFICATION DU FONCTIONNEMENT DE LA SUBSTITUTION D'INDIVIDU ET DE DOCTORANT
------------------------------------------------------------------------------------------------------------------------
-- On ne veut plus que soit possible la substitution de 2 individus dont les doctorants liés
-- ne sont pas eux-mêmes substitués.
--======================================================================================================================


-- !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
--                                     ! Idée abandonnée !
--
-- Ça a quand même du sens que les individus soient substitués indépendemment du fait qu'ils
-- soient des individus "purs" (acteur) ou des doctorants ou les 2. On peut même imaginer que
-- ça soit utile utile de savoir qu'un acteur donné a été doctorant.
-- Pour régler le "pb" des 2 doctorants non substitués (car INE différents) alors que leurs
-- individus respectifs le sont (car même nom,prénom,ddn) qui faisait couiner la recherche de
-- doctorant par individu_id (car 2 doctorants liés au même individu substituant), on ne lève
-- plus d'exception et on prend le doctorant le plus "récent". Sachant que :
--   - 1/ ce cas de doctroants non substitués alors que leurs individus respectifs le sont, est
--     aberrant donc improbable (et pour s'en sortir, on a forcé ne NPD d'un des doctorants).
--   - 2/ il faudrait s'arranger pour ne plus avoir à faire de recherche de doctorant par individu_id,
--     en s'assurant que l'on référence là où il faut un doctorant et pas un individu.
--
-- !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


--
-- Suppression de toutes les substitutions de doctorants, en forçant le NPD.
--
update doctorant d set npd_force = substit_npd_doctorant(d)||'-zzz-'||d.id
from substit_doctorant sub
where d.id = sub.from_id
  and npd_force is null
; ---------------> 12min

--
-- Verif absence de substitutions de doctorants.
--
select * from substit_doctorant order by npd ;

--
-- Modif manuelle du "NPD forcé" *s'il est null* pour retirer des substitutions d'individus les individus étant
-- des doctorants.
--
update individu i set npd_force = substit_npd_individu(i)||'-zzz-'||i.id
where npd_force IS NULL and id in (
    select i.id from individu i
                         join substit_individu sub on i.id = sub.from_id
    where type = 'doctorant'
); ---------------> 24min (sur bdd locale)

--
-- Verif absence de substitutions d'individus étant aussi des doctorants.
--
select * from substit_individu sub join individu i on sub.from_id = i.id and i.type = 'doctorant';

--
-- Impossibilités de supprimer des substituants car ils sont référencés dans d'autres tables (ex: validation, acteur) ?
--
select * from substit_log where type = 'doctorant' and operation = 'SUBSTITUANT_SUPPR_PROBLEM';
select * from substit_log where type = 'individu' and operation = 'SUBSTITUANT_SUPPR_PROBLEM';


--
-- recherche de cobayes : individus à la fois acteur et doctorant
--
select nom_patronymique, prenom1, sum(count_doctorant) as count_doctorant, sum(count_acteur) as count_acteur
from (
         select nom_patronymique, prenom1, type, 0 as count_doctorant, count(*) count_acteur from individu where type = 'acteur' and source_id <> 1 group by nom_patronymique, prenom1, type union
         select nom_patronymique, prenom1, type, count(*) count_doctorant, 0 as count_acteur from individu where type = 'doctorant' and source_id <> 1 group by nom_patronymique, prenom1, type
     ) tmp
group by nom_patronymique, prenom1
having sum(count_doctorant) > 0 and sum(count_acteur) > 0 and sum(count_doctorant) + sum(count_acteur) > 2;

--
-- cobaye 1
--
select substit_npd_individu(i)npd,id,type,nom_patronymique,prenom1,date_naissance,source_code,email,npd_force
    from individu i where nom_patronymique = 'XXXXX' and prenom1 ilike '%Yyyyy%' order by type;
alter table individu disable trigger substit_trigger_individu;
alter table substit_individu disable trigger substit_trigger_on_substit_individu;
update individu set npd_force = null where id = 1122;
update individu set npd_force = null where id = 1011; -------------------------------> ça fonctionne : les individu-doctrants 1122 et 1011 ne sont pas substitués
select * from v_individu_doublon where npd = 'bentahar_omar_19831007'; -- 37995
update individu set npd_force = 'lsdfjlmsdqjfmlqsdjfqsldkfj' where id in (1198707);
select * from v_individu_doublon where nom_patronymique = 'XXXXX';
alter table individu enable trigger substit_trigger_individu;
alter table substit_individu enable trigger substit_trigger_on_substit_individu;

--
-- cobaye 2
--
select substit_npd_individu(i)npd,id,type,nom_patronymique,prenom1,date_naissance,source_code,email,npd_force
    from individu i where nom_patronymique = 'XXXXX' and prenom1 ilike '%Yyyyyy%' order by type;
select * from individu where id = 1200286; -- substituait les individus 37995,18879
select * from acteur where individu_id = 1200286;
select * from acteur where individu_id in (37995,18879); -- substituait les individus 37995,18879

select i.* from individu i join substit_individu sub on i.id = sub.from_id where to_id = 1199128;

alter table individu disable trigger substit_trigger_individu;
alter table substit_individu disable trigger substit_trigger_on_substit_individu;
update individu set date_naissance = '1975-11-19 00:00:00.000000' where id = 1187822;
update individu set npd_force = null where id = 18879;
select * from v_individu_doublon where nom_patronymique = 'XXXXX'; -- 37995
update individu set npd_force = 'lsdfjlmsdqjfmlqsdjfqsldkfj' where id in (18879);
select * from v_individu_doublon where nom_patronymique = 'XXXXX';
alter table individu enable trigger substit_trigger_individu;
alter table substit_individu enable trigger substit_trigger_on_substit_individu;


select * from substit_individu sub join individu i on sub.from_id = i.id and i.type = 'doctorant';
