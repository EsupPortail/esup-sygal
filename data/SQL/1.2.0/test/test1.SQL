insert into API_LOG(ID, REQ_URI, REQ_START_DATE, REQ_END_DATE, REQ_STATUS, REQ_RESPONSE, REQ_ETABLISSEMENT, REQ_TABLE)
select API_LOG_ID_SEQ.nextval, 'xxxxx', sysdate, sysdate, 'test', 'hello!!', 'UCN', 'TEST' from dual
/

declare
    d date;
begin
    select sysdate into d from dual;
    insert into API_LOG(ID, REQ_URI, REQ_START_DATE, REQ_END_DATE, REQ_STATUS, REQ_RESPONSE, REQ_ETABLISSEMENT, REQ_TABLE)
    select API_LOG_ID_SEQ.nextval, 'yyyyy', d, d, 'test', 'hello!!', 'UCN', 'TEST' from dual;
end;
/

create index TEST_index on FICHIER (HISTO_MODIFICATION ASC)
/
drop index TEST_index
/