--
-- 9.x ?
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
       (SELECT count(fi.id) AS count
        FROM formation_inscription fi
        WHERE sess.id = fi.session_id
          AND fi.histo_destruction IS NULL
          AND fi.liste::text = 'C'::text)                                           AS session_nb_liste_compl,
       sea.id                                                                       AS seance_id,
       EXTRACT(year FROM sea.debut)                                                 AS seance_annee_debut,
       to_char(sea.debut, 'DD/MM/YYYY HH24:MI'::text)                               AS seance_debut,
       to_char(sea.fin, 'DD/MM/YYYY HH24:MI'::text)                                 AS seance_fin,
       date_part('hour'::text, sea.fin - sea.debut) +
       date_part('min'::text, sea.fin - sea.debut) / 60::double precision           AS seance_nb_h,
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
with pre_these as (select 'NB : un doctorant peut avoir 2 thèses' as rem,
                          t.id,
                          t.doctorant_id,
                          t.ecole_doct_id,
                          t.unite_rech_id,
                          rank() OVER (PARTITION BY doctorant_id) as rank
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
        from unite_rech ed
                 join structure s on s.id = ed.structure_id
                 join pre_these t on ed.id = t.ecole_doct_id and t.rank = 1 and t.doctorant_id = d.id) as these_ur,
       'D' || (select count(distinct annee_univ)
        from these_annee_univ tau
                 join pre_these t on t.id = tau.these_id and t.rank = 1 and t.doctorant_id = d.id
        where tau.histo_destruction is null)                                                           as these_annee_doctorat,
       (select string_agg(distinct annee_univ::varchar, ', ')
        from these_annee_univ tau
                 join pre_these t on t.id = tau.these_id and t.rank = 1 and t.doctorant_id = d.id
        where tau.histo_destruction is null)                                                           as these_annees_inscription,
       fi.histo_creation                                                                               as session_date_inscription,
       fs.id                                                                                           as seance_id,
       fs.debut                                                                                        as seance_debut,
       case when fp.temoin = 'O' then 'Présent·e' else 'Absent·e' end                                  as seance_statut_presence
from formation_inscription fi
         join formation_session fsess on fi.session_id = fsess.id and fsess.histo_destruction is null
         join formation_formation f on fsess.formation_id = f.id and f.histo_destruction is null
         join formation_seance fs on fsess.id = fs.session_id and fs.histo_destruction is null
         join doctorant d on fi.doctorant_id = d.id and d.histo_destruction is null
         join individu di on di.id = d.individu_id and di.histo_destruction is null
         join etablissement e on d.etablissement_id = e.id
         join etablissement eorga on fsess.site_id = eorga.id
         left join formation_presence fp
                   on fi.id = fp.inscription_id and fp.seance_id = fs.id and fp.histo_destruction is null
-- where f.id = 87
order by f.id, fsess.id, fs.id, di.nom_patronymique, di.prenom1
;
