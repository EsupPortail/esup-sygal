--
-- Préparation des séquences :
--   - currval = max(table.id)
--

create or replace function init_sequences_values_fdsfdgfd654gs6fd54() returns void
language plpgsql as
$$
declare
    v_suffix varchar = '_id_seq';
    v_sql text;
begin
    for v_sql in
        with seqs as (
            -- le nom de la table est déduite du nom de la sequence, ex : sequence "utilisateur_id_seq" => table "utilisateur"
            select sequence_name, substr(sequence_name, 0, strpos(sequence_name, v_suffix)) table_name
            from information_schema.sequences s
            where sequence_name like '%' || v_suffix
        )
        select
            'select setval(''' || seqs.sequence_name || ''', coalesce(max(id),1)) ' ||
            'from ' || seqs.table_name || ';'
        from seqs
             join information_schema.tables t on t.table_name = seqs.table_name -- la table doit exister
        loop
            execute v_sql;
        end loop;
end
$$;

select init_sequences_values_fdsfdgfd654gs6fd54();
