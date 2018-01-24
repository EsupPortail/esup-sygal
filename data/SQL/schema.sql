
-- liste des tables ayant un lien vers la table THESE
select cc.table_name, cc.column_name, cc.constraint_name
from all_cons_columns cc
join all_constraints c on c.constraint_name = cc.constraint_name and c.constraint_type = 'R'
where column_name = 'THESE_ID';
