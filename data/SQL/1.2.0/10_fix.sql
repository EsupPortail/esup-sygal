
--
-- Index manquants
--
create index FICHIER_NATURE_ID_index on FICHIER (NATURE_ID ASC)
/
create index ACTEUR_INDIVIDU_ID_idx on ACTEUR (INDIVIDU_ID ASC)
/
create index ACTEUR_THESE_ID_idx on ACTEUR (THESE_ID ASC)
/
create index ACTEUR_ROLE_ID_idx on ACTEUR (ROLE_ID ASC)
/
create index ACTEUR_SOURCE_ID_idx on ACTEUR (SOURCE_ID ASC)
/
create index ACTEUR_HISTO_MODIF_ID_idx on ACTEUR (HISTO_MODIFICATEUR_ID ASC)
/
create index ACTEUR_HISTO_DESTRUCT_ID_idx on ACTEUR (HISTO_DESTRUCTEUR_ID ASC)
/
create index ACTEUR_ACTEUR_ETAB_ID_idx on ACTEUR (ACTEUR_ETABLISSEMENT_ID ASC)
/
create index ACTEUR_ETABLISSEMENT_ID_idx on THESE (ETABLISSEMENT_ID ASC)
/
create index ACTEUR_DOCTORANT_ID_idx on THESE (DOCTORANT_ID ASC)
/
create index ACTEUR_ECOLE_DOCT_ID_idx on THESE (ECOLE_DOCT_ID ASC)
/
create index ACTEUR_UNITE_RECH_ID_idx on THESE (UNITE_RECH_ID ASC)
/

--
-- Contraintes manquantes
--
alter table ACTEUR add constraint ACTEUR_ROLE_ID_fk foreign key (ROLE_ID) references ROLE on delete cascade
/
