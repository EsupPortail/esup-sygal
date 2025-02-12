--
-- 9.3.1
--

call unicaen_indicateur_delete_matviews();

-- select matviews__check_column_dropable(column_name := 'code_sise_disc');
alter table these drop column code_sise_disc;

call unicaen_indicateur_recreate_matviews();
