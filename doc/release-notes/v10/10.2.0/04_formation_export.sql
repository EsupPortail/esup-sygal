--
-- 10.2.0
--

--
-- paramètres
--
INSERT INTO unicaen_parametre_parametre (categorie_id, code, libelle, description, valeur, ordre)
select cp.id,
       'SPEC_ANNEE_UNIV_DATE_DEBUT_FORMATION',
       'Spécification de la date de début d''année universitaire pour le module Formation',
       'Spécification de la date de début d''une année universitaire, *fonction de la date de bascule*, pour le module Formation',
       '01/09/%s 00:00:00',
       30
from unicaen_parametre_categorie cp
where cp.code = 'FORMATION';
INSERT INTO unicaen_parametre_parametre (categorie_id, code, libelle, description, valeur, ordre)
select cp.id,
       'SPEC_ANNEE_UNIV_DATE_FIN_FORMATION',
       'Spécification de la date de fin d''année universitaire pour le module Formation',
       'Spécification de la date de fin d''une année universitaire, *fonction de la date de bascule*, pour le module Formation',
       '31/08/%s 23:59:59',
       31
from unicaen_parametre_categorie cp
where cp.code = 'FORMATION';
INSERT INTO public.unicaen_parametre_parametre (categorie_id, code, libelle, description, valeur, ordre)
select cp.id,
       'SPEC_ANNEE_UNIV_DATE_BASCULE_FORMATION',
       'Spécification pour la date de bascule d''année universitaire pour le module Formation',
       'Spécification pour calculer la date de bascule d''une année universitaire sur la suivante, pour le module Formation',
       '-7 months',
       32
from unicaen_parametre_categorie cp
where cp.code = 'FORMATION';



--
-- procédure
--

drop function if exists unicaen_parametre_fetch_parametre(varchar, varchar);
create or replace function unicaen_parametre_fetch_parametre(categorie_code varchar, param_code varchar)
    returns unicaen_parametre_parametre
    language plpgsql
as
$$declare
    param unicaen_parametre_parametre;
begin
    select p.* into param
    from unicaen_parametre_parametre p
             join unicaen_parametre_categorie cp on p.categorie_id = cp.id and cp.code = categorie_code
    where p.code = param_code;
    if param is null then
        raise exception 'Anomalie : paramètre % introuvable (catégorie %) !', param_code, categorie_code;
    end if;
    ----raise notice 'unicaen_parametre_fetch_parametre() : %', param;

    return param;
end
$$;


drop function if exists annee_univ_compute_premiere_annee_from_date(timestamptz);
create or replace function annee_univ_compute_premiere_annee_from_date(date timestamptz) returns smallint
    language plpgsql
as
$$declare
    specDate unicaen_parametre_parametre;
begin
    -- fetch du paramètre SPEC_DATE_BASCULE, ex : '-10 months' (ce qui revient à basculer le 01/11)
    specDate = unicaen_parametre_fetch_parametre('ANNEE_UNIV', 'SPEC_DATE_BASCULE');
    --raise notice 'annee_univ_compute_premiere_annee_from_date() : %', specDate.valeur;

    return date_part('year', (date + specDate.valeur::interval)); -- ex : 2022
end
$$;


drop view v_extract_formation_presence;
drop view v_extract_formation;
drop function if exists  annee_univ_compute_from_date(timestamptz);
create or replace function annee_univ_compute_from_date(date timestamptz) returns varchar
    language plpgsql
as
$$declare
    premiereAnnee smallint;
begin
    premiereAnnee = annee_univ_compute_premiere_annee_from_date(date);
    --raise notice 'annee_univ_compute_from_date() : %', premiereAnnee;

    return premiereAnnee || '/' || (premiereAnnee::smallint + 1); -- ex : '2022/2023'
end
$$;


drop function if exists annee_univ_compute_date_debut(smallint);
create or replace function annee_univ_compute_date_debut(anneeUnivPremiereAnnee smallint) returns timestamptz
    language plpgsql
as
$$declare
    specDate unicaen_parametre_parametre;
begin
    -- fetch du paramètre SPEC_ANNEE_UNIV_DATE_DEBUT, ex : '01/11/%s 00:00:00'
    specDate = unicaen_parametre_fetch_parametre('ANNEE_UNIV'::varchar, 'SPEC_ANNEE_UNIV_DATE_DEBUT'::varchar);
    --raise notice 'annee_univ_compute_date_debut() : %', specDate.valeur;

    return to_timestamp(replace(specDate.valeur, '%s', anneeUnivPremiereAnnee::varchar), 'DD/MM/YYYY HH24:MI:SS');
end
$$;


drop function if exists annee_univ_compute_date_fin(smallint);
create or replace function annee_univ_compute_date_fin(anneeUnivPremiereAnnee smallint) returns timestamptz
    language plpgsql
as
$$declare
    specDate unicaen_parametre_parametre;
begin
    -- fetch du paramètre SPEC_ANNEE_UNIV_DATE_FIN, ex : '31/10/%s 23:59:59'
    specDate = unicaen_parametre_fetch_parametre('ANNEE_UNIV', 'SPEC_ANNEE_UNIV_DATE_FIN');
    --raise notice 'annee_univ_compute_date_fin() : %', specDate.valeur;
    --raise notice 'annee_univ_compute_date_fin() : %', replace(specDate.valeur, '%s', anneeUnivPremiereAnnee::varchar);

    return to_timestamp(replace(specDate.valeur, '%s', anneeUnivPremiereAnnee::varchar), 'DD/MM/YYYY HH24:MI:SS');
end
$$;




--
-- privilèges
--

WITH d(code, lib, ordre) AS (
    SELECT 'exporter-xls', 'Exporter les formations/sessions/séances au format Excel', 1000
)
INSERT
INTO unicaen_privilege_privilege(CATEGORIE_ID, CODE, LIBELLE, ORDRE)
SELECT cp.id, d.code, d.lib, d.ordre
FROM d
         JOIN unicaen_privilege_categorie cp ON cp.CODE = 'formation_formation'
ON CONFLICT (CATEGORIE_ID, CODE) DO UPDATE SET LIBELLE = excluded.LIBELLE,
                                               ORDRE = excluded.ORDRE;

select privilege__grant_privileges_to_profiles('formation_formation',
                                               ARRAY['exporter-xls'],
                                               ARRAY['ADMIN_TECH', 'BDD']
       );




--
-- vues
--

drop view if exists v_extract_formation;

create or replace view v_extract_formation as
SELECT m.libelle                                                                    AS module_libelle,
       f.id                                                                         AS formation_id,
       f.libelle                                                                    AS formation_titre,
       sess.id                                                                      AS session_id,
       (SELECT string_agg((if.nom_patronymique::text || ' '::text) || if.prenom1::text,
                          ','::text || chr(10)) AS string_agg
        FROM formation_formateur ff
                 JOIN individu if ON if.id = ff.individu_id
        WHERE ff.histo_destruction IS NULL
          AND sess.id = ff.session_id)                                              AS session_intervenants,
       sess.modalite                                                                AS session_modalite,
       case sess.type
           when 'T' then 'Transversale'
           else 'Spécifique' end                                                    AS session_type,
       (SELECT string_agg(COALESCE(fsvs.sigle, fsvs.code)::text, ','::text || chr(10)) AS string_agg
        FROM formation_session_structure_valide fsv
                 JOIN structure fsvs ON fsv.structure_id = fsvs.id
        WHERE sess.id = fsv.session_id
          AND fsv.histo_destruction IS NULL)                                        AS session_structures_valides,
       ssr.sigle                                                                    AS session_etab_orga,
       COALESCE((sir.nom_patronymique::text || ' '::text) || sir.prenom1::text,
                (fir.nom_patronymique::text || ' '::text) || fir.prenom1::text)     AS session_responsable,
       (SELECT count(fi.id) AS count
        FROM formation_inscription fi
        WHERE sess.id = fi.session_id
          AND fi.histo_destruction IS NULL
          AND fi.liste IS NULL)                                                     AS session_nb_nonclasses,

       (SELECT count(fi.id) AS count
        FROM formation_inscription fi
        WHERE sess.id = fi.session_id
          AND fi.histo_destruction IS NULL
          AND fi.liste::text = 'P'::text)                                           AS session_nb_liste_princ,

       (SELECT string_agg(tmp.str, ', '::text) AS string_agg
        FROM (SELECT (de.source_code::text || ':'::text) || count(*) AS str
              FROM formation_inscription fi
               JOIN doctorant d ON fi.doctorant_id = d.id
               JOIN etablissement de ON d.etablissement_id = de.id
              WHERE sess.id = fi.session_id
                AND fi.histo_destruction IS NULL
                AND fi.liste::text = 'P'::text
              GROUP BY de.source_code
              ORDER BY de.source_code) tmp)                                           AS session_nb_liste_princ_etab,

       (SELECT count(fi.id) AS count
        FROM formation_inscription fi
        WHERE sess.id = fi.session_id
          AND fi.histo_destruction IS NULL
          AND fi.liste::text = 'C'::text)                                           AS session_nb_liste_compl,
       sea.id                                                                       AS seance_id,
       EXTRACT(year FROM sea.debut)                                                 AS seance_annee_debut,
       to_char(sea.debut, 'DD/MM/YYYY'::text)                                       AS seance_debut,
       to_char(sea.fin, 'DD/MM/YYYY'::text)                                         AS seance_fin,
       annee_univ_compute_from_date(sea.debut)                                      as seance_debut_annee_univ,
       sea.debut                                                                    AS seance_debut_ts,
       sea.fin                                                                      AS seance_fin_ts,
       extract(epoch from (sea.fin - sea.debut)) / 3600                             AS seance_nb_h,
       (SELECT count(fp.id) AS count
        FROM formation_presence fp
        WHERE fp.seance_id = sea.id
          AND fp.histo_destruction IS NULL
          AND fp.temoin::text = 'O'::text)                                          AS seance_nb_presents,
       ((SELECT count(fi.id) AS count
         FROM formation_inscription fi
         WHERE sess.id = fi.session_id
           AND fi.histo_destruction IS NULL
           AND fi.liste::text = 'P'::text)) - ((SELECT count(fp.id) AS count
                                                FROM formation_presence fp
                                                WHERE sea.id = fp.seance_id
                                                  AND fp.histo_destruction IS NULL
                                                  AND fp.temoin::text = 'O'::text)) AS seance_nb_absents,
       (SELECT string_agg(tmp.str, ', '::text) AS string_agg
        FROM (SELECT (de.source_code::text || ':'::text) || count(*) AS str
              FROM formation_presence fp
                       JOIN formation_inscription fi ON fp.inscription_id = fi.id AND fi.histo_destruction IS NULL
                       JOIN doctorant d ON fi.doctorant_id = d.id
                       JOIN etablissement de ON d.etablissement_id = de.id
              WHERE fp.temoin::text = 'O'::text
                AND fp.seance_id = sea.id
              GROUP BY de.source_code
              ORDER BY de.source_code) tmp)                                              AS seance_nb_presents_etab
FROM formation_seance sea
         JOIN formation_session sess ON sea.session_id = sess.id AND sess.histo_destruction IS NULL
         JOIN formation_formation f ON sess.formation_id = f.id AND f.histo_destruction IS NULL
         JOIN formation_module m ON m.id = f.module_id AND m.histo_destruction IS NULL
         JOIN structure ssr ON ssr.id = sess.site_id
         LEFT JOIN individu fir ON fir.id = f.responsable_id
         LEFT JOIN individu sir ON sir.id = sess.responsable_id
WHERE sea.histo_destruction IS NULL;


-- drop view if exists v_extract_formation_enquete;
--
-- create or replace view v_extract_formation_enquete as
-- SELECT fs.formation_id,
--        ff.libelle                                                                      AS formation_libelle,
--        fs.id                                                                           AS session_id,
--        ec.libelle                                                                      AS question_categorie,
--        eq.libelle                                                                      AS question_libelle,
--        er.niveau                                                                       AS reponse_niveau,
--        er.description                                                                  AS reponse_commentaire,
--        to_char(COALESCE(er.histo_modification, er.histo_creation), 'DD/MM/YYYY'::text) AS reponse_date
-- FROM formation_enquete_reponse er
--          JOIN formation_inscription fi ON er.inscription_id = fi.id AND fi.histo_destruction IS NULL
--          JOIN formation_session fs ON fi.session_id = fs.id AND fs.histo_destruction IS NULL
--          JOIN formation_formation ff ON fs.formation_id = ff.id AND ff.histo_destruction IS NULL
--          JOIN formation_enquete_question eq ON er.question_id = eq.id AND eq.histo_destruction IS NULL
--          JOIN formation_enquete_categorie ec ON eq.categorie_id = ec.id AND eq.histo_destruction IS NULL
-- WHERE er.histo_destruction IS NULL
-- ORDER BY fs.formation_id DESC, ec.libelle, eq.libelle;


drop view if exists v_extract_formation_presence;

create or replace view v_extract_formation_presence as
with pre_these as (select t.id,
                          t.doctorant_id,
                          t.ecole_doct_id,
                          t.unite_rech_id,
                          rank() OVER (PARTITION BY doctorant_id) as rank,
                          case when rank() OVER (PARTITION BY doctorant_id) > 1 then 'Ce doctorant a plusieurs thèses !' else null end rem
                   from these t
                   where t.histo_destruction is null
                     and t.etat_these in ('E', 'S'))
select f.id                                                                                            as formation_id,
       f.libelle                                                                                       as formation_libelle,
       fsess.id                                                                                        as session_id,
       (select string_agg(nom_usuel || ' ' || prenom1, ', ')
        from formation_formateur ff
                 join individu i on ff.individu_id = i.id
        where ff.session_id = fsess.id)                                                                as session_intervenant,
       case fsess.modalite
           when 'P' then 'Présentiel'
           when 'D' then 'Distanciel'
           else 'Mixte' end                                                                            as session_modalite,
       fsess.type                                                                                      as session_type,
       eorga.source_code                                                                               as session_etab_orga,
       (select sum(extract(epoch from (sea.fin - sea.debut)) / 3600)
        from formation_seance sea
        where sea.session_id = fsess.id
          and sea.histo_destruction is null)                                                           as session_nb_heures,
       d.id                                                                                            as doctorant_id,
       di.nom_usuel                                                                                    as doctorant_nom_usuel,
       di.nom_patronymique                                                                             as doctorant_nom_patronymique,
       array_to_string(ARRAY [di.prenom1, di.prenom2, di.prenom3], ' ')                                as doctorant_prenoms,
       e.source_code                                                                                   as doctorant_etablissement,
       (select string_agg(distinct s.code, ', ')
        from ecole_doct ed
                 join structure s on s.id = ed.structure_id
                 join pre_these t on ed.id = t.ecole_doct_id and t.doctorant_id = d.id)                as these_ed,
       (select string_agg(distinct s.code, ', ')
        from unite_rech ur
                 join structure s on s.id = ur.structure_id
                 join pre_these t on ur.id = t.unite_rech_id and t.rank = 1 and t.doctorant_id = d.id) as these_ur,
       'D' || (select count(distinct annee_univ)
               from these_annee_univ tau
                        join pre_these t on t.id = tau.these_id and t.rank = 1 and t.doctorant_id = d.id
               where tau.histo_destruction is null)                                                           as these_annee_doctorat,
       (select string_agg(distinct annee_univ::varchar, ', ')
        from these_annee_univ tau
                 join pre_these t on t.id = tau.these_id and t.rank = 1 and t.doctorant_id = d.id
        where tau.histo_destruction is null)                                                           as these_annees_inscription,
       fi.histo_creation                                                                               as session_date_inscription,
       fi.liste,
       case fi.liste
           when 'P' then 'Liste principale'
           when 'C' then 'Liste complémentaire'
           else 'Non classé' end                                                                       as session_statut_inscription,
       fs.id                                                                                           as seance_id,
       extract(epoch from (fs.fin - fs.debut)) / 3600                                                  as seance_nb_heures,
       to_char(fs.debut, 'DD/MM/YYYY'::text)                                                           as seance_debut,
       to_char(fs.fin, 'DD/MM/YYYY'::text)                                                             as seance_fin,
       annee_univ_compute_from_date(fs.debut)                                                          as seance_debut_annee_univ,
       fs.debut                                                                                        as seance_debut_ts,
       fs.fin                                                                                          as seance_fin_ts,
       case when fi.liste = 'P' then
         case when fp.temoin = 'O' then 'Présent·e' else 'Absent·e' end
       else null end                                                                                   as seance_statut_presence,
       (SELECT sum(extract(epoch from (fs.fin - fs.debut)) / 3600)
        FROM formation_presence fp
        join formation_seance fs on fp.seance_id = fs.id and fs.histo_destruction is null
        WHERE fp.inscription_id = fi.id
          AND fp.histo_destruction IS NULL
          AND fp.temoin = 'O')                                                                         as session_nb_heures_suivies_par_doctorant
from formation_inscription fi
         join formation_session fsess on fi.session_id = fsess.id and fsess.histo_destruction is null
         join formation_formation f on fsess.formation_id = f.id and f.histo_destruction is null
         join formation_seance fs on fsess.id = fs.session_id and fs.histo_destruction is null
         join doctorant d on fi.doctorant_id = d.id and d.histo_destruction is null
         join individu di on di.id = d.individu_id and di.histo_destruction is null
         join etablissement e on d.etablissement_id = e.id
         join etablissement eorga on fsess.site_id = eorga.id
         left join formation_presence fp on fi.id = fp.inscription_id and fp.seance_id = fs.id and fp.histo_destruction is null
--order by f.id, fsess.id, fs.id, di.nom_patronymique, di.prenom1
;
