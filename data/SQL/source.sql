
alter table version_diplome add source_id bigint not null constraint version_diplome_source_fk references source;
alter table version_diplome add source_code varchar(64) not null;
