--
-- séquences *_XX
--
select 'drop sequence ' || sequence_name || ';'
from all_sequences
where sequence_owner = 'OTH'
and sequence_name like '%\_XX%' ESCAPE '\';

--
-- tables S_* et *_XX
--
select 'drop table ' || table_name || ';' from all_tables
where owner = 'OTH'
and table_name like 'S\_%' ESCAPE '\' or table_name like '%\_XX' ESCAPE '\';
