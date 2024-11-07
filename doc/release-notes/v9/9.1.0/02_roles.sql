--
-- 9.1.0
--

--
-- On delete no action sur individu_role.role_id.
--

alter table individu_role drop constraint individu_role_role_id_fk;
alter table individu_role add constraint individu_role_role_id_fk foreign key (role_id) references role;

