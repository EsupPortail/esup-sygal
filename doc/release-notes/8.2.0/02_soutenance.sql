
alter table soutenance_qualite alter column histo_modificateur_id drop not null;
alter table soutenance_qualite alter column histo_modification drop not null;
alter table soutenance_qualite_sup alter column histo_modificateur_id drop not null;
alter table soutenance_qualite_sup alter column histo_modification drop not null;
