--
-- COMUE or not COMUE ?
--
-- NB : Ce script vous concerne uniquement si vous avez besoin de déclarer une structure particulière
-- pour votre COMUE.
--

insert into structure (id, source_code, sigle, libelle, type_structure_id, source_id, code, histo_createur_id, histo_modificateur_id)
select
  nextval('structure_id_seq'), 'COMUE',
  '',   --> sigle ou abbréviation à personnaliser
  '', --> libellé à personnaliser
  null, 1, 'COMUE', 1, 1
where 0 = 1
;

insert into etablissement (id, structure_id, domaine, source_id, source_code, est_comue, est_membre, histo_createur_id, histo_modificateur_id)
select
  nextval('etablissement_id_seq'), s.id,
  '', --> domaine à personnaliser
  1, 'COMUE', 1, 0, 1, 1
from structure s
where s.source_code = 'COMUE'
;
