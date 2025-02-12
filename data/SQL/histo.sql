
alter table discipline_sise add histo_creation timestamp default ('now'::text)::timestamp not null;
alter table discipline_sise add histo_createur_id bigint not null default 1 constraint discipline_sise_hcfk references utilisateur;
alter table discipline_sise add histo_modification timestamp;
alter table discipline_sise add histo_modificateur_id bigint constraint discipline_sise_hmfk references utilisateur;
alter table discipline_sise add histo_destruction timestamp;
alter table discipline_sise add histo_destructeur_id bigint constraint discipline_sise_hdfk references utilisateur;
