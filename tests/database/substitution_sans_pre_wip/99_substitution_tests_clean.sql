-- ------------------------------------------------------------------------------------------------------------------
-- Clean
-- ------------------------------------------------------------------------------------------------------------------

-- select 'drop function if exists '||routine_name||';' from information_schema.routines where routine_name ilike 'test_substit%';

drop function if exists clean_substit_test_functions;

create function clean_substit_test_functions() returns void
    language plpgsql
as
$$declare
    v_routine_name varchar;
begin
    for v_routine_name in select routine_name from information_schema.routines where routine_name ilike 'test_substit%' loop
        execute format('drop function %I', v_routine_name);
    end loop;
end$$;

select clean_substit_test_functions();

drop function clean_substit_test_functions();
