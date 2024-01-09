--
-- Substitutions : vérifs
--

--
-- Vérif que toutes les substitutions possibles existent (d'après v_xxxx_doublon) :
--
select 'individu' t, v.id from v_individu_doublon v left join substit_individu ss on v.id = from_id where to_id is null union all
select 'doctorant' t, v.id from v_doctorant_doublon v left join substit_doctorant ss on v.id = from_id where to_id is null union all
select 'structure' t, v.id from v_structure_doublon v left join substit_structure ss on v.id = from_id where to_id is null union all
select 'etablissement' t, v.id from v_etablissement_doublon v left join substit_etablissement ss on v.id = from_id where to_id is null union all
select 'ecole_doct' t, v.id from v_ecole_doct_doublon v left join substit_ecole_doct ss on v.id = from_id where to_id is null union all
select 'unite_rech' t, v.id from v_unite_rech_doublon v left join substit_unite_rech ss on v.id = from_id where to_id is null
;


--
-- Vérif des remplacements de FK :
--
-- 1/ Générer la requête.
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_individu ss on from_id = r.',v.fk_column,' union all') from v_substit_foreign_keys_individu v union all
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_doctorant ss on from_id = r.',v.fk_column,' union all') from v_substit_foreign_keys_doctorant v union all
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_structure ss on from_id = r.',v.fk_column,' union all') from v_substit_foreign_keys_structure v union all
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_etablissement ss on from_id = r.',v.fk_column,' union all') from v_substit_foreign_keys_etablissement v union all
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_ecole_doct ss on from_id = r.',v.fk_column,' union all') from v_substit_foreign_keys_ecole_doct v union all
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_unite_rech ss on from_id = r.',v.fk_column,' union all') from v_substit_foreign_keys_unite_rech v
;

-- 2/ Copier-coller la requête générée et la lancer.
-- Il doit y avoir 0 ligne.
-- Sauf pour 'individu_role' et 'validation' où la contrainte d'unicité a pu empêcher le remplacement.
select 'acteur' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from acteur r join substit_individu ss on from_id = r.individu_id union all
select 'doctorant' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from doctorant r join substit_individu ss on from_id = r.individu_id union all
select 'formation_formateur' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from formation_formateur r join substit_individu ss on from_id = r.individu_id union all
select 'formation_formation' t, r.id, 'responsable_id' fk_name, responsable_id fk_value_current, ss.to_id fk_value_required from formation_formation r join substit_individu ss on from_id = r.responsable_id union all
select 'formation_session' t, r.id, 'responsable_id' fk_name, responsable_id fk_value_current, ss.to_id fk_value_required from formation_session r join substit_individu ss on from_id = r.responsable_id union all
select 'individu_role' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from individu_role r join substit_individu ss on from_id = r.individu_id union all
select 'mail_confirmation' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from mail_confirmation r join substit_individu ss on from_id = r.individu_id union all
select 'rapport_activite_validation' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from rapport_activite_validation r join substit_individu ss on from_id = r.individu_id union all
select 'rapport_validation' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from rapport_validation r join substit_individu ss on from_id = r.individu_id union all
select 'individu_compl' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from individu_compl r join substit_individu ss on from_id = r.individu_id union all
select 'utilisateur' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from utilisateur r join substit_individu ss on from_id = r.individu_id union all
select 'validation' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from validation r join substit_individu ss on from_id = r.individu_id union all
select 'z_doctorant_compl' t, r.id, 'doctorant_id' fk_name, doctorant_id fk_value_current, ss.to_id fk_value_required from z_doctorant_compl r join substit_doctorant ss on from_id = r.doctorant_id union all
select 'doctorant_mission_enseignement' t, r.id, 'doctorant_id' fk_name, doctorant_id fk_value_current, ss.to_id fk_value_required from doctorant_mission_enseignement r join substit_doctorant ss on from_id = r.doctorant_id union all
select 'formation_inscription' t, r.id, 'doctorant_id' fk_name, doctorant_id fk_value_current, ss.to_id fk_value_required from formation_inscription r join substit_doctorant ss on from_id = r.doctorant_id union all
select 'these' t, r.id, 'doctorant_id' fk_name, doctorant_id fk_value_current, ss.to_id fk_value_required from these r join substit_doctorant ss on from_id = r.doctorant_id union all
select 'formation_formation' t, r.id, 'type_structure_id' fk_name, type_structure_id fk_value_current, ss.to_id fk_value_required from formation_formation r join substit_structure ss on from_id = r.type_structure_id union all
select 'formation_session_structure_valide' t, r.id, 'structure_id' fk_name, structure_id fk_value_current, ss.to_id fk_value_required from formation_session_structure_valide r join substit_structure ss on from_id = r.structure_id union all
select 'formation_session' t, r.id, 'type_structure_id' fk_name, type_structure_id fk_value_current, ss.to_id fk_value_required from formation_session r join substit_structure ss on from_id = r.type_structure_id union all
select 'role' t, r.id, 'structure_id' fk_name, structure_id fk_value_current, ss.to_id fk_value_required from role r join substit_structure ss on from_id = r.structure_id union all
select 'structure_document' t, r.id, 'structure_id' fk_name, structure_id fk_value_current, ss.to_id fk_value_required from structure_document r join substit_structure ss on from_id = r.structure_id union all
select 'acteur' t, r.id, 'acteur_etablissement_id' fk_name, acteur_etablissement_id fk_value_current, ss.to_id fk_value_required from acteur r join substit_etablissement ss on from_id = r.acteur_etablissement_id union all
select 'doctorant' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from doctorant r join substit_etablissement ss on from_id = r.etablissement_id union all
select 'formation_formation' t, r.id, 'site_id' fk_name, site_id fk_value_current, ss.to_id fk_value_required from formation_formation r join substit_etablissement ss on from_id = r.site_id union all
select 'formation_session' t, r.id, 'site_id' fk_name, site_id fk_value_current, ss.to_id fk_value_required from formation_session r join substit_etablissement ss on from_id = r.site_id union all
select 'individu_compl' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from individu_compl r join substit_etablissement ss on from_id = r.etablissement_id union all
select 'etablissement_rattach' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from etablissement_rattach r join substit_etablissement ss on from_id = r.etablissement_id union all
select 'structure_document' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from structure_document r join substit_etablissement ss on from_id = r.etablissement_id union all
select 'these' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from these r join substit_etablissement ss on from_id = r.etablissement_id union all
select 'variable' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from variable r join substit_etablissement ss on from_id = r.etablissement_id union all
select 'these' t, r.id, 'ecole_doct_id' fk_name, ecole_doct_id fk_value_current, ss.to_id fk_value_required from these r join substit_ecole_doct ss on from_id = r.ecole_doct_id union all
select 'acteur' t, r.id, 'acteur_uniterech_id' fk_name, acteur_uniterech_id fk_value_current, ss.to_id fk_value_required from acteur r join substit_unite_rech ss on from_id = r.acteur_uniterech_id union all
select 'individu_compl' t, r.id, 'unite_id' fk_name, unite_id fk_value_current, ss.to_id fk_value_required from individu_compl r join substit_unite_rech ss on from_id = r.unite_id union all
select 'etablissement_rattach' t, r.id, 'unite_id' fk_name, unite_id fk_value_current, ss.to_id fk_value_required from etablissement_rattach r join substit_unite_rech ss on from_id = r.unite_id union all
select 'these' t, r.id, 'unite_rech_id' fk_name, unite_rech_id fk_value_current, ss.to_id fk_value_required from these r join substit_unite_rech ss on from_id = r.unite_rech_id
;

-- select * from validation where individu_id in (1016708,863476,   1201547,1201434) order by these_id;
-- select * from type_validation where id = 6;




--=============== cas particuliers =================--

with doublons as (
    select * from v_individu_doublon
    where upper(nom_patronymique) in (
        'FRANCK',                       -- Xavier Franck :https://sygal.normandie-univ.fr/utilisateur/voir/33019 : signalé par Emilie
        'FATYEYEVA',                    -- Kateryana Fatyeyeva : https://sygal.normandie-univ.fr/utilisateur/voir/58883 : idem
        'BLAISOT',                      -- Jean-Bernard Blaisot : https://sygal.normandie-univ.fr/utilisateur/voir/67663 : idem
        'CHETELAT',                     -- Gaelle Chetelat : https://sygal.normandie-univ.fr/utilisateur/voir/40481 : signalé par Véro
        'JEAN-MARIE', 'JEAN MARIE',     -- Laurence Jean-Marie : https://sygal.normandie-univ.fr/utilisateur/voir/73423 : idem
        'VILLEDIEU'                     -- Marie Villedieu : https://sygal.normandie-univ.fr/utilisateur/voir/134803 : idem
        --'HOCHAN', 'VIEILLE', 'BERNAUDIN', 'BRANDLE DE MOTTA', 'DEMOULIN', 'DURET'
        )
    order by nom_patronymique
)
select i.*, ir.*
from individu i
join doublons d on d.id = i.id
join individu_role ir on i.id = ir.individu_id
--where i.id in ('863784','39729')
;


--
-- individu_role
select i.nom_patronymique, i.prenom1, ir.*
from individu_role ir
join substit_individu sub on sub.to_id = ir.individu_id
join individu i on ir.individu_id = i.id
where upper(i.nom_patronymique) in (
--     'FRANCK',                       -- Xavier Franck :https://sygal.normandie-univ.fr/utilisateur/voir/33019 : signalé par Emilie
--     'FATYEYEVA',                    -- Kateryana Fatyeyeva : https://sygal.normandie-univ.fr/utilisateur/voir/58883 : idem
    'BLAISOT',                      -- Jean-Bernard Blaisot : https://sygal.normandie-univ.fr/utilisateur/voir/67663 : idem
--     'CHETELAT',                     -- Gaelle Chetelat : https://sygal.normandie-univ.fr/utilisateur/voir/40481 : signalé par Véro
    'JEAN-MARIE', 'JEAN MARIE'     -- Laurence Jean-Marie : https://sygal.normandie-univ.fr/utilisateur/voir/73423 : idem
--     'VILLEDIEU'                     -- Marie Villedieu : https://sygal.normandie-univ.fr/utilisateur/voir/134803 : idem
    --'HOCHAN', 'VIEILLE', 'BERNAUDIN', 'BRANDLE DE MOTTA', 'DEMOULIN', 'DURET'
);

--
-- acteur
select i.nom_patronymique, i.prenom1, ir.*
from acteur ir
         join substit_individu sub on sub.to_id = ir.individu_id
         join individu i on ir.individu_id = i.id
where upper(i.nom_patronymique) in (
    'BLAISOT',                      -- Jean-Bernard Blaisot : https://sygal.normandie-univ.fr/utilisateur/voir/67663 : signalé par Emilie
--     'FRANCK',                       -- Xavier Franck :https://sygal.normandie-univ.fr/utilisateur/voir/33019 : idem
--     'FATYEYEVA',                    -- Kateryana Fatyeyeva : https://sygal.normandie-univ.fr/utilisateur/voir/58883 : idem
--     'CHETELAT',                     -- Gaelle Chetelat : https://sygal.normandie-univ.fr/utilisateur/voir/40481 : signalé par Véro
    'JEAN-MARIE', 'JEAN MARIE'     -- Laurence Jean-Marie : https://sygal.normandie-univ.fr/utilisateur/voir/73423 : idem
--     'VILLEDIEU'                     -- Marie Villedieu : https://sygal.normandie-univ.fr/utilisateur/voir/134803 : idem
    --'HOCHAN', 'VIEILLE', 'BERNAUDIN', 'BRANDLE DE MOTTA', 'DEMOULIN', 'DURET'
    );


select * from utilisateur where individu_id in (1184130, 1185160);
select * from doctorant where individu_id in (1184130, 1185160);
select * from these where doctorant_id = 25812;



select * from doctorant where source_code in ('UCN::20008144','UCN::21313055','UCN::20406116','UCN::20407482','UCN::20408648' );
select * from substit_doctorant where from_id in (select id from doctorant where source_code in ('UCN::20008144','UCN::21313055','UCN::20406116','UCN::20407482','UCN::20408648'));

select * from doctorant where ine = '2493251751B';


with logs as (
    select substitue_id, substituant_id from substit_log where type = 'individu' and operation = 'FK_REPLACE_PROBLEM'
),
     tmp as (
         select 'x ' f, ir.*, sub.to_id, i.nom_usuel
         from individu_role ir
                  join individu i on ir.individu_id = i.id
                  join substit_individu sub on sub.from_id = ir.individu_id --and sub.histo_destruction is null
         where individu_id in (select substitue_id from logs)
         union all
         select 'done', ir.*, null, i.nom_usuel
         from individu_role ir
                  join individu i on ir.individu_id = i.id
         where individu_id in (select substituant_id from logs)
     )
select * from tmp order by role_id, f;