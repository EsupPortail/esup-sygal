--
-- 9.4.0
--

-- modif vue extraction

drop view if exists v_formation_export ;
create or replace view v_formation_export as
SELECT m.libelle as module_libelle,
       f.id AS formation_id,
       f.libelle AS formation_titre,
       sess.id AS session_id,
       ( SELECT string_agg((if.nom_patronymique::text || ' '::text) || if.prenom1::text, ','::text || chr(10)) AS string_agg
         FROM formation_formateur ff
                  JOIN individu if ON if.id = ff.individu_id
         WHERE ff.histo_destruction IS NULL AND sess.id = ff.session_id) AS session_intervenants,
       sess.modalite AS session_modalite,
       sess.type AS session_type,
       ( SELECT string_agg(COALESCE(fsvs.sigle, fsvs.code)::text, ','::text || chr(10)) AS string_agg
         FROM formation_session_structure_valide fsv
                  JOIN structure fsvs ON fsv.structure_id = fsvs.id
         WHERE sess.id = fsv.session_id AND fsv.histo_destruction IS NULL) AS session_structures_valides,
       ssr.sigle AS session_etab_orga,
       COALESCE((sir.nom_patronymique::text || ' '::text) || sir.prenom1::text, (fir.nom_patronymique::text || ' '::text) || fir.prenom1::text) AS session_responsable,
       ( SELECT count(fi.id) AS count
         FROM formation_inscription fi
         WHERE sess.id = fi.session_id AND fi.histo_destruction IS NULL AND fi.liste IS NULL) AS session_nb_nonclasses,
       ( SELECT count(fi.id) AS count
         FROM formation_inscription fi
         WHERE sess.id = fi.session_id AND fi.histo_destruction IS NULL AND fi.liste::text = 'P'::text) AS session_nb_liste_princ,
       ( SELECT count(fi.id) AS count
         FROM formation_inscription fi
         WHERE sess.id = fi.session_id AND fi.histo_destruction IS NULL AND fi.liste::text = 'C'::text) AS session_nb_liste_compl,
       sea.id AS seance_id,
       EXTRACT(year FROM sea.debut) AS seance_annee_debut,
       to_char(sea.debut, 'DD/MM/YYYY HH24:MI'::text) AS seance_debut,
       to_char(sea.fin, 'DD/MM/YYYY HH24:MI'::text) AS seance_fin,
       date_part('hour'::text, sea.fin - sea.debut) + date_part('min'::text, sea.fin - sea.debut) / 60::double precision AS seance_nb_h,
       ( SELECT count(fp.id) AS count
         FROM formation_presence fp
         WHERE fp.seance_id = sea.id AND fp.histo_destruction IS NULL AND fp.temoin::text = 'O'::text) AS seance_nb_presents,
       (( SELECT count(fi.id) AS count
          FROM formation_inscription fi
          WHERE sess.id = fi.session_id AND fi.histo_destruction IS NULL AND fi.liste::text = 'P'::text)) - (( SELECT count(fp.id) AS count
                                                                                                               FROM formation_presence fp
                                                                                                               WHERE sea.id = fp.seance_id AND fp.histo_destruction IS NULL AND fp.temoin::text = 'O'::text)) AS seance_nb_absents,
       ( SELECT string_agg(tmp.str, ','::text || chr(10)) AS string_agg
         FROM ( SELECT (des.sigle::text || ':'::text) || count(*) AS str
                FROM formation_presence fp
                         JOIN formation_inscription fi ON fp.inscription_id = fi.id AND fi.histo_destruction IS NULL
                         JOIN doctorant d ON fi.doctorant_id = d.id
                         JOIN etablissement de ON d.etablissement_id = de.id
                         JOIN structure des ON de.structure_id = des.id
                WHERE fp.temoin::text = 'O'::text AND fp.seance_id = sea.id
                GROUP BY des.sigle, fp.temoin
                ORDER BY des.sigle) tmp) AS seance_nb_presents_etab
FROM formation_seance sea
         JOIN formation_session sess ON sea.session_id = sess.id AND sess.histo_destruction IS NULL
         JOIN formation_formation f ON sess.formation_id = f.id AND f.histo_destruction IS NULL
         JOIN formation_module m ON m.id = f.module_id AND m.histo_destruction IS NULL
         JOIN structure ssr ON ssr.id = sess.site_id
         LEFT JOIN individu fir ON fir.id = f.responsable_id
         LEFT JOIN individu sir ON sir.id = sess.responsable_id
WHERE sea.histo_destruction IS NULL
--ORDER BY f.id desc, f.libelle, sea.debut
;
