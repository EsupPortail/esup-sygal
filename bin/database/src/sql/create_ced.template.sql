--
-- Collège des écoles doctorales (CED) éventuel.
--
-- NB : Ce script crée un CED *uniquement si* vous l'avez demandé dans la config de préparation des scripts.
--

insert into structure (id, source_code, code, sigle, libelle,
                       type_structure_id, source_id, histo_createur_id)
select nextval('structure_id_seq'), 'CED', 'CED',
       '{ETAB_CED_SIGLE}',   --> sigle ou abbréviation à personnaliser
       '{ETAB_CED_LIBELLE}', --> libellé à personnaliser
       1, 1, 1
    from type_structure ts
    where {ETAB_CED} = 1
;

insert into etablissement (id, structure_id, source_id, source_code, est_ced, histo_createur_id)
    select nextval('etablissement_id_seq'), s.id, 1, 'CED', true, 1
    from structure s
    where s.source_code = 'CED'
;
