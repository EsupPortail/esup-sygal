--
-- COMUE éventuelle
--
-- NB : Ce script crée une COMUE *uniquement si* vous l'avez demandé dans la config de préparation des scripts.
--

insert into structure (id, source_code, sigle, libelle, type_structure_id, source_id, code, histo_createur_id, histo_modificateur_id)
select
  nextval('structure_id_seq'), 'COMUE',
  '{ETAB_COMUE_SIGLE}',   --> sigle ou abbréviation à personnaliser
  '{ETAB_COMUE_LIBELLE}', --> libellé à personnaliser
  null, 1, 'COMUE', 1, 1
where {ETAB_COMUE} = 1
;

insert into etablissement (id, structure_id, domaine, source_id, source_code, est_comue, est_membre, histo_createur_id, histo_modificateur_id)
select
  nextval('etablissement_id_seq'), s.id,
  '{ETAB_COMUE_DOMAINE}', --> domaine à personnaliser
  1, 'COMUE', true, false, 1, 1
from structure s
where s.source_code = 'COMUE'
;
