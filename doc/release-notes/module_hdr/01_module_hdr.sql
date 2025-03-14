--
-- Single table inheritance
--

create table candidat_hdr
(
    id                    bigserial                                                    not null
        primary key,
    etablissement_id      bigint references etablissement (id)                         not null,
    individu_id           bigint REFERENCES individu (id)                              not null,
    ine                   varchar(64),
    source_code           varchar(64)                                                  not null,
    source_id             bigint references source (id)                                not null,
    histo_createur_id     bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

CREATE TABLE hdr
(
    id     bigserial not null
        primary key,
    candidat_id bigint REFERENCES candidat_hdr (id),
    ecole_doct_id                        bigint REFERENCES ecole_doct (id),
    unite_rech_id                        bigint REFERENCES unite_rech (id),
    etablissement_id              bigint REFERENCES etablissement (id),
    etat_hdr varchar(20),
    cnu varchar(20),
    resultat                                               smallint,
    date_abandon                                           timestamp,
    discipline_sise_id                                     bigint references discipline_sise(id),
    date_fin_confid                                        timestamp,
    source_code                                            varchar(64)                                                    not null,
    source_id                                              bigint                                                         not null references source(id),
    histo_createur_id     bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

create table fichier_hdr
(
    id bigserial not null primary key,
    fichier_id   bigint  REFERENCES fichier(id)    on delete cascade                                              not null,
    hdr_id  bigint REFERENCES hdr(id) on delete cascade not null,
    est_annexe   boolean default false                                     not null
);

insert into soutenance_etat(id, code, libelle) values (nextval('soutenance_etat_id_seq'),'EN_COURS_SAISIE', 'En cours de saisie');
update soutenance_etat set code = 'EN_COURS_EXAMEN' where code = 'EN_COURS';


ALTER table soutenance_proposition
    add column type VARCHAR(255) NOT NULL default 'SOUTENANCE_THESE_PROPOSITION';
ALTER TABLE soutenance_proposition
    ALTER COLUMN these_id DROP NOT NULL;
ALTER table soutenance_proposition
    add column hdr_id bigint REFERENCES hdr (id);
ALTER table soutenance_proposition
    add column titre varchar(2048);
ALTER TABLE soutenance_proposition
    ADD CONSTRAINT chk_soutenance_proposition_type
        CHECK (
            (these_id IS NOT NULL AND hdr_id IS NULL)
                OR (these_id IS NULL AND hdr_id IS NOT NULL)
            );

ALTER TABLE soutenance_justificatif ADD COLUMN fichier_hdr_id bigint REFERENCES fichier_hdr(id);


ALTER TABLE soutenance_justificatif RENAME COLUMN fichier_id TO fichier_these_id;
ALTER TABLE soutenance_justificatif
    ALTER COLUMN fichier_these_id DROP NOT NULL;
ALTER TABLE soutenance_justificatif DROP CONSTRAINT justificatif_fichier_fk;
ALTER TABLE soutenance_justificatif
    ADD CONSTRAINT justificatif_fichier_these_fk FOREIGN KEY (fichier_these_id) REFERENCES fichier_these (id);
ALTER TABLE soutenance_justificatif
    ADD CONSTRAINT chk_soutenance_justificatif_type
        CHECK (
            (fichier_these_id IS NOT NULL AND fichier_hdr_id IS NULL)
                OR (fichier_these_id IS NULL AND fichier_hdr_id IS NOT NULL)
            );

ALTER TABLE soutenance_avis ADD COLUMN fichier_hdr_id bigint REFERENCES fichier_hdr(id);

-- GESTION DES RôLES

alter table role add hdr_dep boolean default false not null;

alter table profil add hdr_dep boolean default false not null;
comment on column profil.hdr_dep is 'Indique si ce profil s''adresse à un rôle hdr-dépendant ';

INSERT INTO profil (id, libelle, role_id, structure_type, ordre, hdr_dep) VALUES (nextval('profil_id_seq'), 'Candidat HDR', 'HDR_CANDIDAT', (select id from type_structure where code = 'etablissement'), 300, true);
INSERT INTO profil (id, libelle, role_id, structure_type, ordre, hdr_dep) VALUES (nextval('profil_id_seq'), 'Garant HDR', 'HDR_GARANT', (select id from type_structure where code = 'etablissement'),  300, true);
INSERT INTO profil (id, libelle, role_id, structure_type, ordre, hdr_dep) VALUES (nextval('profil_id_seq'), 'Gestionnaire HDR', 'HDR_GEST', (select id from type_structure where code = 'etablissement'),  300, false);


--
-- Correction de la signature de privilege__update_role_privilege().
--

drop function privilege__update_role_privilege;

create function privilege__update_role_privilege() returns void
    language plpgsql
as $$
BEGIN
    -- création des 'role_privilege' manquants d'après le contenu de 'profil_to_role' et de 'profil_privilege'
insert into role_privilege (role_id, privilege_id)
select p2r.role_id, pp.privilege_id
from profil_to_role p2r
         join profil pr on pr.id = p2r.profil_id
         join profil_privilege pp on pp.profil_id = pr.id
where not exists(
    select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id
);

-- ATTENTION : ne pas faire de suppression des 'role_privilege' en trop d'après le contenu de 'profil_to_role' et de 'profil_privilege'
-- parce que des rôles ne sont associés à aucun profil (ex: "Authentifié") et ces derniers se verraient retirer tous leurs privilèges !
END;
$$;


--
-- role__create_roles_from_profils_for_structure() :
--   - suppression du ménage systématique des attributions de privilèges car des rôles ne sont pas liés à un profil
--   - update du role s'il existe déjà.
--

create or replace function role__create_roles_from_profils_for_structure(p_structure_id bigint) returns void
    language plpgsql
as
$$begin
    --
    -- Procédure de création des rôles manquants à partir des profils structure-dépendants,
    -- pour la structure spécifiée.
    --

    insert into role (
        id,
        code,
        libelle,
        source_code,
        source_id,
        role_id,
        histo_createur_id,
        these_dep,
        hdr_dep,
        structure_id,
        type_structure_dependant_id
    )
select
    nextval('role_id_seq'),
    p.role_id,
    p.libelle,
    s.source_code || '::' || p.role_id,
    app_source_id(),
    p.libelle || ' ' || case when ts.code = 'etablissement' then s.source_code else s.code end,
    app_utilisateur_id(),
    p.these_dep,
    p.hdr_dep,
    s.id,
    s.type_structure_id
from structure s
         join type_structure ts on s.type_structure_id = ts.id
         join profil p on p.structure_type = s.type_structure_id
where s.id = p_structure_id
    on conflict on constraint role_code_structure_uindex do update
                                                                set hdr_dep = excluded.hdr_dep,
                                                                these_dep = excluded.these_dep,
                                                                libelle = excluded.libelle
;

--
-- Création des profil_to_role manquants
--
insert into profil_to_role(profil_id, role_id)
select p.id, r.id
from role r
         join profil p on p.role_id = r.code
where not exists (select * from profil_to_role p2r where p2r.role_id = r.id)
order by code;

--
-- Attribution automatique des privilèges aux rôles, d'après ce qui est spécifié dans :
--   - PROFIL_TO_ROLE (profils appliqués à chaque rôle) et
--   - PROFIL_PRIVILEGE (privilèges accordés à chaque profil).
--
insert into role_privilege (role_id, privilege_id)
select p2r.role_id, pp.privilege_id
from profil_to_role p2r
         join profil pr on pr.id = p2r.profil_id
         join profil_privilege pp on pp.profil_id = pr.id
where not exists ( select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
order by pr.role_id
;

-- ATTENTION : ne pas faire de suppression des 'role_privilege' en trop d'après le contenu de 'profil_to_role' et de 'profil_privilege'
-- parce que des rôles ne sont associés à aucun profil (ex: "Authentifié") et ces derniers se verraient retirer tous leurs privilèges !
end
$$;


--
-- Création/màj des rôles typés 'structure', en se basant sur les profils
--

-- Troncature préalable du structure.code nécessaire (cf. role__create_roles_from_profils_for_structure()).
update structure set code = substring(code, 1, 16) where length(code) > 16;

-- Création/màj des rôles typés 'etablissement' pour chaque établissement d'inscription
select e.id etablissement_id, e.structure_id, role__create_roles_from_profils_for_structure(s.id)
from etablissement e
         join structure s on e.structure_id = s.id and s.histo_destruction is null
         join type_structure ts on s.type_structure_id = ts.id and ts.code = 'etablissement'
where e.histo_destruction is null and e.est_etab_inscription = true;

update role set hdr_dep = true where code IN ('HDR_CANDIDAT', 'HDR_GARANT', 'HDR_GESTIONNAIRE', 'M', 'P', 'R', 'A', 'B');
update role set ordre_affichage = 'aaa' where code IN ('HDR_GARANT');
update role set attrib_auto = true where code IN ('HDR_CANDIDAT', 'HDR_GARANT');


INSERT INTO unicaen_privilege_categorie (ID, CODE, LIBELLE, ORDRE)
SELECT nextval('categorie_privilege_id_seq'),
       'hdr',
       'HDR',
       12000 WHERE NOT EXISTS (SELECT 1
                               FROM unicaen_privilege_categorie
                               WHERE CODE = 'hdr');

INSERT INTO unicaen_privilege_categorie (ID, CODE, LIBELLE, ORDRE)
SELECT nextval('categorie_privilege_id_seq'),
       'candidat-hdr',
       'Candidat HDR',
       12000 WHERE NOT EXISTS (SELECT 1
                               FROM unicaen_privilege_categorie
                               WHERE CODE = 'candidat-hdr');

INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre) VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'candidat-hdr'), 'modifier-email-contact', 'Modifier l''email de contact du candidat', 10);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre) VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'candidat-hdr'), 'afficher-email-contact', 'Visualiser l''email de contact du candidat', 20);

INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'telechargement-fichier', 'Téléchargement de fichier déposé', 3060);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'consultation-fiche', 'Consultation de la fiche d''identité de l''HDR', 3025);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'recherche', 'Recherche de HDR', 3010);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'fichier-divers-televerser',
        'Téléverser un fichier comme le PV ou le rapport de soutenance, la demande de confidentialité, etc.', 100);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'fichier-divers-consulter',
        'Télécharger/consulter un fichier comme le PV ou le rapport de soutenance, la demande de confidentialité, etc.',
        150);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'export-csv', 'Export des HDR au format CSV', 3020);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'consultation-de-toutes-les-hdr', 'Consultation de toutes les HDR', 1000);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'consultation-de-ses-hdr', 'Consultation de ses HDR', 1100);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'modification-de-toutes-les-hdr', 'Modification de toutes les HDR', 1200);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'modification-de-ses-hdr', 'Modification de ses HDR', 1300);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'hdr'), 'donner-resultat', 'Attribution du résultat de l''HDR suite à la soutenance', 3060);

--
-- Accord de privilèges à des profils.
--
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'hdr', 'telechargement-fichier'
                           union
                           select 'hdr', 'consultation-fiche'
                           union
                           select 'hdr', 'recherche'
                           union
                           select 'hdr', 'fichier-divers-televerser'
                           union
                           select 'hdr', 'fichier-divers-consulter'
                           union
                           select 'hdr', 'export-csv'
                           union
                           select 'hdr', 'consultation-de-toutes-les-hdr'
                           union
                           select 'hdr', 'consultation-de-ses-hdr'
                           union
                           select 'hdr', 'modification-de-ses-hdr'
                           union
                           select 'hdr', 'modification-de-toutes-les-hdr'
                           union
                           select 'hdr', 'donner-resultat'
                           union
                           select 'candidat-hdr', 'modifier-email-contact'
                           union
                           select 'candidat-hdr', 'afficher-email-contact')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH'
    )
         join unicaen_privilege_categorie cp on cp.CODE = data.categ
         join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'acteur', 'modifier-acteur-de-toutes-theses' union

    select 'droit', 'privilege-visualisation' union

    select 'faq', 'modification' union

    select 'fichier-commun', 'telecharger' union

    select 'gestion-president', 'modifier-mail-president' union
    select 'gestion-president', 'gestion-president' union
    select 'gestion-president', 'notifier-president' union

    select 'hdr', 'modification-de-toutes-les-hdr' union
    select 'hdr', 'modification-de-ses-hdr' union
    select 'hdr', 'fichier-divers-televerser' union
    select 'hdr', 'telechargement-fichier' union
    select 'hdr', 'consultation-de-ses-hdr' union
    select 'hdr', 'recherche' union
    select 'hdr', 'consultation-fiche' union
    select 'hdr', 'export-csv' union
    select 'hdr', 'fichier-divers-consulter' union
    select 'hdr', 'donner-resultat' union

    select 'individu', 'individucompl_supprimer' union
    select 'individu', 'modifier' union
    select 'individu', 'lister' union
    select 'individu', 'consulter' union
    select 'individu', 'ajouter' union
    select 'individu', 'individucompl_modifier' union
    select 'individu', 'individucompl_afficher' union
    select 'individu', 'individucompl_index' union

    select 'page-information', 'modifier-information' union

    select 'soutenance', 'association-membre-individu' union
    select 'soutenance', 'index-global' union
    select 'soutenance', 'engagement-impartialite-annuler' union
    select 'soutenance', 'proposition-modification_gestion' union
    select 'soutenance', 'proposition-validation-bdd' union
    select 'soutenance', 'avis-annuler' union
    select 'soutenance', 'presoutenance-visualisation' union
    select 'soutenance', 'engagement-impartialite-visualiser' union
    select 'soutenance', 'declaration-honneur-revoquer' union
    select 'soutenance', 'proposition-revoquer-structure' union
    select 'soutenance', 'avis-notifier' union
    select 'soutenance', 'modification-date-rapport' union
    select 'soutenance', 'avis-visualisation' union
    select 'soutenance', 'engagement-impartialite-notifier' union
    select 'soutenance', 'proposition-sursis' union
    select 'soutenance', 'proposition-visualisation' union
    select 'soutenance', 'qualite-modification' union
    select 'soutenance', 'index-structure' union
    select 'soutenance', 'proposition-modification' union
    select 'soutenance', 'proposition-presidence' union
    select 'soutenance', 'proposition-supprimer' union

    select 'soutenance_justificatif', 'justificatif_index' union
    select 'soutenance_justificatif', 'justificatif_ajouter' union
    select 'soutenance_justificatif', 'justificatif_retirer' union

    select 'soutenance_intervention', 'intervention_modifier' union
    select 'soutenance_intervention', 'intervention_afficher' union
    select 'soutenance_intervention', 'intervention_index' union

    select 'structure', 'consultation-de-toutes-les-structures' union
    select 'structure', 'modification-de-ses-structures' union
    select 'structure', 'consultation-de-ses-structures' union
    select 'structure', 'modification-de-toutes-les-structures' union

    select 'unicaen-auth-token', 'modifier' union
    select 'unicaen-auth-token', 'lister' union
    select 'unicaen-auth-token', 'creer' union
    select 'unicaen-auth-token', 'tester' union
    select 'unicaen-auth-token', 'envoyer' union
    select 'unicaen-auth-token', 'prolonger' union

    select 'utilisateur', 'create_from_individu' union
    select 'utilisateur', 'modification' union
    select 'utilisateur', 'consultation'
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'HDR_GEST'
    )
         join unicaen_privilege_categorie cp on cp.CODE = data.categ
         join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'hdr', 'telechargement-fichier'
                           union
                           select 'hdr', 'consultation-fiche'
                           union
                           select 'hdr', 'recherche'
                           union
                           select 'hdr', 'fichier-divers-consulter'
                           union
                           select 'hdr', 'export-csv'
                           union
                           select 'hdr', 'consultation-de-ses-hdr')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'RESP_ED',
                                           'GEST_UR',
                                           'RESP_UR'
    )
         join unicaen_privilege_categorie cp on cp.CODE = data.categ
         join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'hdr', 'telechargement-fichier'
                           union
                           select 'hdr', 'consultation-fiche'
                           union
                           select 'hdr', 'fichier-divers-consulter'
                           union
                           select 'hdr', 'consultation-de-ses-hdr'
                           union
                           select 'soutenance', 'index-acteur'
                           union
                           select 'soutenance', 'proposition-visualisation'
                           union
                           select 'soutenance', 'proposition-modification'
                           union
                           select 'soutenance', 'proposition-validation-acteur'
                           union
                           select 'soutenance', 'presoutenance-visualisation'
                           union
                           select 'soutenance', 'engagement-impartialite-visualiser'
                           union
                           select 'soutenance', 'avis-visualisation'
                           union
                           select 'soutenance_justificatif', 'justificatif_ajouter'
                           union
                           select 'soutenance_justificatif', 'justificatif_retirer'
                           union
                           select 'soutenance_intervention', 'intervention_afficher'
                           union
                           select 'candidat-hdr', 'modifier-email-contact'
                           union
                           select 'candidat-hdr', 'afficher-email-contact'
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'HDR_CANDIDAT'
    )
         join unicaen_privilege_categorie cp on cp.CODE = data.categ
         join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

-- INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
-- with data(categ, priv) as (select 'hdr', 'telechargement-fichier'
--                            union
--                            select 'hdr', 'consultation-fiche'
--                            union
--                            select 'hdr', 'recherche'
--                            union
--                            select 'hdr', 'fichier-divers-consulter'
--                            union
--                            select 'hdr', 'consultation-de-ses-hdr'
--                            union
--                            select 'hdr', 'export-csv'
--                            union
--                            select 'soutenance', 'index-global'
--                            union
--                            select 'soutenance', 'index-acteur'
--                            union
--                            select 'soutenance', 'proposition-visualisation'
--                            union
--                            select 'soutenance', 'proposition-modification'
--                            union
--                            select 'soutenance', 'proposition-validation-acteur'
--                            union
--                            select 'soutenance', 'presoutenance-visualisation'
--                            union
--                            select 'soutenance', 'engagement-impartialite-visualiser'
--                            union
--                            select 'soutenance', 'avis-visualisation'
--                            union
--                            select 'soutenance_justificatif', 'justificatif_ajouter'
--                            union
--                            select 'soutenance_justificatif', 'justificatif_retirer')
-- select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
-- from data
--          join PROFIL on profil.ROLE_ID in (
--     'HDR_GARANT'
--     )
--          join unicaen_privilege_categorie cp on cp.CODE = data.categ
--          join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
-- where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);
select privilege__grant_privileges_to_profiles('hdr', ARRAY['telechargement-fichier',
    'consultation-fiche',
    'recherche',
    'fichier-divers-consulter',
    'consultation-de-ses-hdr',
    'export-csv'], ARRAY['HDR_GARANT']);
select privilege__grant_privileges_to_profiles('soutenance', ARRAY['index-global',
    'index-acteur',
    'proposition-visualisation',
    'proposition-modification',
    'proposition-validation-acteur',
    'presoutenance-visualisation',
    'engagement-impartialite-visualiser',
    'avis-visualisation'], ARRAY['HDR_GARANT']);
select privilege__grant_privileges_to_profiles('soutenance_justificatif', ARRAY['justificatif_ajouter',
    'justificatif_retirer'], ARRAY['HDR_GARANT']);

-- INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
-- with data(categ, priv) as (select 'hdr', 'telechargement-fichier'
--                            union
--                            select 'hdr', 'consultation-fiche'
--                            union
--                            select 'hdr', 'fichier-divers-consulter'
--                            union
--                            select 'hdr', 'consultation-de-toutes-les-hdr'
--                            union
--                            select 'hdr', 'consultation-de-ses-hdr'
--                            union
--                            select 'hdr', 'export-csv')
-- select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
-- from data
--          join PROFIL on profil.ROLE_ID in (
--     'OBSERV'
--     )
--          join unicaen_privilege_categorie cp on cp.CODE = data.categ
--          join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
-- where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);
select privilege__grant_privileges_to_profiles('hdr', ARRAY['telechargement-fichier',
    'consultation-fiche',
    'fichier-divers-consulter',
    'consultation-de-toutes-les-hdr',
    'consultation-de-ses-hdr',
    'export-csv'], ARRAY['OBSERV']);

-- INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
-- with data(categ, priv) as (select 'hdr', 'telechargement-fichier'
--                            union
--                            select 'hdr', 'export-csv')
-- select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
-- from data
--          join PROFIL on profil.ROLE_ID in (
--     'M'
--     )
--          join unicaen_privilege_categorie cp on cp.CODE = data.categ
--          join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
-- where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);
select privilege__grant_privileges_to_profiles('hdr', ARRAY['telechargement-fichier', 'export-csv'], ARRAY['M']);


-- INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
-- with data(categ, priv) as (select 'hdr', 'consultation-de-ses-hdr'
--                            union
--                            select 'hdr', 'telechargement-fichier'
--                            union
--                            select 'hdr', 'consultation-fiche')
-- select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
-- from data
--          join PROFIL on profil.ROLE_ID in (
--     'P'
--     )
--          join unicaen_privilege_categorie cp on cp.CODE = data.categ
--          join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
-- where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);
--
--
-- INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
-- with data(categ, priv) as (select 'hdr', 'consultation-de-ses-hdr'
--                            union
--                            select 'hdr', 'telechargement-fichier'
--                            union
--                            select 'hdr', 'consultation-fiche'
--                            union
--                            select 'hdr', 'export-csv')
-- select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
-- from data
--          join PROFIL on profil.ROLE_ID in (
--     'R'
--     )
--          join unicaen_privilege_categorie cp on cp.CODE = data.categ
--          join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
-- where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);
select privilege__grant_privileges_to_profiles('hdr', ARRAY['consultation-de-ses-hdr', 'telechargement-fichier', 'consultation-fiche'], ARRAY['R', 'P']);

-- insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
-- select p2r.ROLE_ID, pp.PRIVILEGE_ID
-- from PROFIL_TO_ROLE p2r
--          join profil pr on pr.id = p2r.PROFIL_ID
--          join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
-- where not exists (select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
-- ;

UPDATE unicaen_parametre_categorie
SET code = 'SOUTENANCE_THESE' where code = 'SOUTENANCE';

insert into unicaen_parametre_categorie (
    code, libelle, ordre
) VALUES ('SOUTENANCE_HDR', 'Gestion des paramètres du module Soutenance HDR',1100 );


INSERT INTO public.unicaen_parametre_parametre (
    categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre
)
SELECT
    c.id,
    p.code,
    p.libelle,
    p.description,
    p.valeurs_possibles,
    p.valeur,
    p.ordre
FROM (VALUES ('NB_MIN_RAPPORTEUR', 'Nombre minimal de rapporteurs', NULL, 'Number', '3', 300),
             ('RATIO_MIN_RANG_A', 'Ratio minimal de membres de rang A', NULL, 'String', '0.5', 500),
             ('DOC_REDACTION_ANGLAIS', 'Formulaire de demande de rédaction en anglais', NULL, 'String', NULL, 2400),
             ('NB_MIN_MEMBRE_JURY', 'Nombre minimal de membres dans le jury', NULL, 'Number', '5', 10),
             ('RATIO_MIN_EXTERIEUR', 'Ratio minimal de membres extérieurs', NULL, 'String', '0.5', 600),
             ('NB_MAX_MEMBRE_JURY', 'Nombre maximal de membres dans le jury', NULL, 'Number', '10', 20),
             ('DELAI_RETOUR', 'Delai avant le retour des rapports', NULL, 'Number', '14', 1100),
             ('EQUILIBRE_FEMME_HOMME', 'Équilibre Femme/Homme dans le jury',
              '<p>N''est que indicatif car ne peut &ecirc;tre <em>enforced</em> dans certaines disciplines.</p>',
              'String', '0', 400),
             ('DOC_DELEGATION_SIGNATURE', 'Formulaire de délégation de signature', NULL, 'String',
              '/fichier/telecharger/permanent/DEMANDE_DELEGATION_SIGNATURE', 2200),
             ('DOC_DELOCALISATION', 'Formulaire de délocalisation de la soutenance', NULL, 'String',
              '/fichier/telecharger/permanent/DEMANDE_DELOCALISATION_SOUTENANCE', 2100),
             ('DOC_CONFIDENTIALITE', 'Formulaire de demande de confidentialité', NULL, 'String',
              '/fichier/telecharger/permanent/DEMANDE_DE_CONFIDENTIALITE', 2500),
             ('DELAI_INTERVENTION', 'Délai permettant aux garants d''intervenir [-j jour:+j jour]', NULL, 'Number',
              '21', 1200)
     )
         AS p(code, libelle, description, valeurs_possibles, valeur, ordre)
         CROSS JOIN (
    SELECT id
    FROM unicaen_parametre_categorie
    WHERE code = 'SOUTENANCE_HDR'
) AS c;
INSERT INTO unicaen_parametre_parametre (categorie_id, code, libelle, valeurs_possibles, valeur, ordre)
select cat.id, 'RATIO_MAX_EMERITES', 'Ratio maximal d''émérites', 'String', '0', 600
from unicaen_parametre_categorie cat where cat.code = 'SOUTENANCE_HDR';


-- Ajout d'un proposition_id pour soutenance_intervention pour avoir le même fonctionnement que les autres tables
-- 1. Ajouter la colonne proposition_id sans la contrainte NOT NULL
ALTER TABLE soutenance_intervention
    ADD COLUMN proposition_id BIGINT
        REFERENCES soutenance_proposition (id);

-- 2. Mettre à jour la colonne proposition_id
UPDATE soutenance_intervention
SET proposition_id = sp.id
    FROM soutenance_proposition sp
WHERE soutenance_intervention.these_id = sp.these_id
  AND sp.these_id IS NOT NULL;

-- 3. Ajouter la contrainte NOT NULL une fois les données mises à jour
ALTER TABLE soutenance_intervention
    ALTER COLUMN proposition_id SET NOT NULL;

ALTER TABLE soutenance_intervention
DROP COLUMN these_id;


--
-- Templates RENDERER
--

--Mise à jour du code des templates existants pour la thèse

UPDATE unicaen_renderer_template
SET code = REGEXP_REPLACE(code, '^SOUTENANCE_', 'SOUTENANCE_THESE_')
WHERE --code LIKE 'SOUTENANCE_%';
      code IN ('SOUTENANCE_AVIS_DEFAVORABLE',
               'SOUTENANCE_AVIS_FAVORABLE',
               'SOUTENANCE_CONVOCATION_DOCTORANT',
               'SOUTENANCE_CONVOCATION_MEMBRE',
               'SOUTENANCE_ENGAGEMENT_IMPARTIALITE',
               'SOUTENANCE_FEU_VERT',
               'SOUTENANCE_TOUS_AVIS_RENDUS',
               'SOUTENANCE_TOUS_AVIS_RENDUS_DIRECTION',
               'SOUTENANCE_VALIDATION_ACTEUR_DIRECT',
               'SOUTENANCE_VALIDATION_ANNULEE',
               'SOUTENANCE_VALIDATION_DEMANDE_ED',
               'SOUTENANCE_VALIDATION_DEMANDE_ETAB',
               'SOUTENANCE_VALIDATION_DEMANDE_UR'
          );

UPDATE unicaen_renderer_template
SET code = 'SOUTENANCE_THESE_' || code
WHERE --code not LIKE 'SOUTENANCE_%' and namespace = 'Soutenance\Provider\Template';
      code IN ('ANNULATION_ENGAGEMENT_IMPARTIALITE',
               'CONNEXION_RAPPORTEUR',
               'DEMANDE_ADRESSE_EXACTE',
               'DEMANDE_ENGAGEMENT_IMPARTIALITE',
               'DEMANDE_PRERAPPORT',
               'DEMANDE_RAPPORT_SOUTENANCE',
               'PROPOSITION_REFUS',
               'PROPOSITION_SUPPRESSION',
               'REFUS_ENGAGEMENT_IMPARTIALITE',
               'SERMENT_DU_DOCTEUR',
               'SIGNATURE_ENGAGEMENT_IMPARTIALITE',
               'TRANSMETTRE_DOCUMENTS_DIRECTION',
               'VALIDATION_SOUTENANCE_AVANT_PRESOUTENANCE',
               'VALIDATION_SOUTENANCE_ENVOI_PRESOUTENANCE'
      );

INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_TOUS_AVIS_RENDUS_GARANT', '<p>Courrier électronique adressé aux encadrants d''une HDR lorsque tous les avis sont rendus.</p>', 'mail', 'Tous les avis de soutenance de l''HDR de VAR[Candidat#Denomination] ont été rendus.', e'<p>Bonjour,</p>
<p>Les rapporteurs de l''HDR de VAR[Candidat#Denomination] ont rendu leur rapport de pré-soutenance.</p>
<p>Vous pouvez les consulter ceux-ci sur la page de la proposition de soutenance : VAR[Url#SoutenanceProposition]<br /><br />Vous avez reçu ce mail car :</p>
<ul>
<li>tous les avis de soutenance de l''HDR de VAR[Candidat#Denomination] ont été rendus ;</li>
<li>vous êtes garant de l''HDR de VAR[Candidat#Denomination]. <br /><br /></li>
</ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_ENGAGEMENT_IMPARTIALITE', '<p>Texte associé à l''engagement d''impartialité</p>', 'texte', 'Engagement d''impartialité', '<p>En signant cet engagement d''impartialité, je, sous-signé <strong>VAR[SoutenanceMembre#Denomination]</strong>, atteste ne pas avoir de liens d''intérêt, qu''ils soient de nature professionnelle, familiale, personnelle ou patrimoniale avec le candidat ou son garant, ne pas avoir pris part aux travaux de l''HDR et ne pas avoir de publication cosignée avec le  dans les cinq dernières années et ne pas avoir participé au comité de suivi de l''HDR de VAR[Candidat#Denomination].</p><p>By signing, I certify that I have no personal or family connection with the HDR student or his/her HDR supervisor and that I have not taken part in the work of the HDR and not co-authored  publications with the HDR student for the last five years.<br /><br /></p>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ( 'SOUTENANCE_HDR_VALIDATION_ANNULEE', '<p>Annulation de la validation</p>', 'mail', 'Votre validation de la proposition de soutenance HDR de VAR[Candidat#Denomination] a été annulée', '<p>Bonjour,</p><p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL</p><p>Suite à la modification de la proposition de soutenance, votre validation (faite le VAR[Validation#Date]) a été annulée. Si la nouvelle proposition vous convient, veuillez valider la proposition de soutenance à nouveau.<br /><br />Pour consulter, les modifications faites connectez-vous à  ESUP SyGAL et visualisez la proposition de soutenance en utilisant le lien suivant : VAR[Url#SoutenanceProposition].</p><p><span style="text-decoration: underline;">NB :</span> La proposition de soutenance sera envoyée automatiquement à votre unité de recherche, une fois que tous les intervenants directs auront validé celle-ci (c.-à-d. , garant).<br /><br />-- Justification ----------------------------------------------------------------------</p><p>Vous avez reçu ce mail car :</p><ul><li>vous avez validé la proposition de soutenance de VAR[Candidat#Denomination] ;</li><li>une modification de la proposition a été faite ou demandée.</li></ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ( 'SOUTENANCE_HDR_VALIDATION_ACTEUR_DIRECT', '<p>Mail de validation d''une proposition par un des acteurs directs</p>', 'mail', 'Une validation de votre proposition de soutenance HDR vient d''être faite', '<p>Bonjour,</p><p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL.</p><p>VAR[Validation#Auteur] vient de valider la proposition de soutenance de HDR.</p><p><br />Pour consulter cette proposition, connectez-vous à ESUP SyGAL et visualisez la proposition de soutenance en utilisant le lien suivant : VAR[Url#SoutenanceProposition].</p><p><span style="text-decoration: underline;">NB :</span> La proposition de soutenance sera envoyée automatiquement à votre unité de recherche, une fois que tous les intervenants directs auront validé celle-ci (c.-à-d. candidat, garant).</p><p>-- Justification ----------------------------------------------------------------------</p><p>Vous avez reçu ce mail car :</p><ul><li>un des acteurs directs de l''HDR de VAR[Candidat#Denomination] vient de valider la proposition de soutenance  ;</li><li>vous êtes un des acteurs directs de l''HDR de VAR[Candidat#Denomination].</li></ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ( 'SOUTENANCE_HDR_VALIDATION_DEMANDE_UR', null, 'mail', 'Demande de validation d''une proposition de la soutenance HDR de VAR[Candidat#Denomination]', '<p>Bonjour,</p><p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL.</p><p>Une proposition de soutenance vient d''être faite pour l''HDR suivante :</p><table style="width: 473.433px;"><tbody><tr><td style="width: 547px;"><strong>Candidat·e</strong></td><td style="width: 467.433px;">VAR[Candidat#Denomination]</td></tr></tbody></table><p>Pour examiner cette proposition et statuer sur celle-ci merci de vous rendre dans l''application ESUP SyGAL : VAR[Url#SoutenanceProposition].<br /><br />-- Justification ----------------------------------------------------------------------</p><p> Vous avez reçu ce mail car :</p><ul><li>tous les acteurs directs de l''HDR de VAR[Candidat#Denomination] ont validé la proposition de soutenance ;</li><li>vous êtes un·e responsable de l''unité de recherche encadrant l''HDR.</li></ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ( 'SOUTENANCE_HDR_VALIDATION_DEMANDE_ETAB', null, 'mail', 'Demande de validation d''une proposition de la soutenance HDR de VAR[Candidat#Denomination]', e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP SyGAL.</p>
<p>Une proposition de soutenance vient d\'être faite pour l''HDR suivante :</p>
<table style="width: 473.433px;">
<tbody>
<tr>
<td style="width: 547px;"><strong>Candidat·e</strong></td>
<td style="width: 467.433px;">VAR[Candidat#Denomination]</td>
</tr>
</tbody>
</table>
<p>Pour examiner cette proposition et statuer sur celle-ci merci de vous rendre dans l\'application ESUP SyGAL : VAR[Url#SoutenanceProposition].<br><br>-- Justification ----------------------------------------------------------------------</p>
<p> Vous avez reçu ce mail car :</p>
<ul>
<li>vous êtes un·e gestionnaire encadrant l''HDR.</li>
</ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_VALIDATION_DEMANDE_ED', null, 'mail', 'Demande de validation d''une proposition de la soutenance HDR de VAR[Candidat#Denomination]', '<p>Bonjour,</p><p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL.</p><p>Une proposition de soutenance vient d''être faite pour l''HDR suivante :</p><table style="width: 473.433px;"><tbody><tr><td style="width: 547px;"><strong>Candidat·e</strong></td><td style="width: 467.433px;">VAR[Candidat#Denomination] (VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle])</td></tr></tbody></table><p>Pour examiner cette proposition et statuer sur celle-ci merci de vous rendre dans l''application ESUP SyGAL : VAR[Url#SoutenanceProposition].<br /><br />-- Justification ----------------------------------------------------------------------</p><p> Vous avez reçu ce mail car :</p><ul><li>l''unité de recherche de l''HDR de VAR[Candidat#Denomination] ont validé la proposition de soutenance ;</li></ul>', null, 'Soutenance\Provider\Template', 'default');
                                                                                                                                                             INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_CONVOCATION_MEMBRE', null, 'mail', 'Convocation pour la soutenance de HDR de VAR[Candidat#Denomination]', e'<p>Bonjour,</p>
<p>Par décision en date du VAR[Validation#Date], le chef de l\'établissement VAR[Etablissement#Libelle] vous a désigné·e pour participer au jury devant examiner les travaux de VAR[Candidat#Denomination] en vue de l\'obtention du diplôme : HDR en VAR[HDR#Discipline].</p>
<p>Les travaux sont dirigés par VAR[HDR#Encadrement]</p>
<p>La soutenance aura lieu le VAR[Soutenance#Date] à l\'adresse suivante :<br />VAR[Soutenance#Adresse]</p>
                                                                                                                                                                <p>La soutenance VAR[Soutenance#ModeSoutenance].</p>
                                                                                                                                                                <p>Vous pouvez accéder aux rapports de pré-soutenance grâce aux liens suivants :<br />VAR[Url#TableauPrerapports]<br /><br />Je vous prie d\'agréer, l\'expression de mes salutations distinguées.<br /><br />P.S.: Vous pouvez obtenir une version imprimable de cette convocation à l\'adresse suivante : VAR[Url#ConvocationMembre]<br /><br />
<em>-- Justification -----------------------------------------------------------------</em></p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la proposition de soutenance de VAR[Candidat#Denomination] a été validée; </li>
<li>vous avez été désigné comme membre du jury pour l''HDR de VAR[Candidat#Denomination].</li>
</ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_AVIS_FAVORABLE', null, 'mail', 'VAR[Acteur#Denomination] vient de rendre un avis favorable pour l''HDR de VAR[Candidat#Denomination]', e'<p>Bonjour,</p>
<p>Le rapporteur VAR[Acteur#Denomination] vient de rendre un avis favorable pour l''HDR de VAR[Candidat#Denomination] (VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle]).</p>
<p>Vous pouvez consulter le rapport de pré-soutenance en allant sur la page de gestion de la soutenance VAR[Url#SoutenanceProposition] ou en utilisant le lien suivant : VAR[Url#PrerapportSoutenance]</p>
<p>Vous avez reçu ce courrier électronique car :</p>
<ul>
<li>un avis favorable a été rendu par un rapporteur</li>
<li>vous êtes soit :
<ul>
<li>un·e gestionnaire HDR de l\'établissement d\'inscription du candidat</li>
<li>un·e responsable de l\'unité de recherche accueillant l''HDR de VAR[Candidat#Denomination]</li>
<li>garant de l''HDR de VAR[Candidat#Denomination]</li>
</ul>
</li>
</ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_CONVOCATION_CANDIDAT', null, 'mail', 'Convocation pour la soutenance de HDR de VAR[Candidat#Denomination]', e'<p>Bonjour,</p>
                                                                                                                                                                <p>Par décision en date du VAR[Validation#Date], vous avez été autorisé·e à présenter en soutenance vos travaux en vue de l\'obtention du diplôme : HDR en VAR[HDR#Discipline].<br><br>La soutenance aura lieu le VAR[Soutenance#Date] à l\'adresse suivante : <br>VAR[Soutenance#Adresse]<br><br>La soutenance VAR[Soutenance#ModeSoutenance].<br><br>Vous pouvez accéder aux pré-rapports de soutenance sur la page de la proposition de soutenance : VAR[Url#SoutenanceProposition]<br><br>Nous vous prions d\'agréer, l\'expression de nos salutations distinguées.<br><br><span style="text-decoration: underline;">P.S.:</span> Vous pouvez obtenir une version imprimable de cette convocation à l\'adresse suivante : VAR[Url#ConvocationCandidat]<br><br></p>
<p><em>-- Justification -------------------------------------------------------------------------------</em></p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>votre proposition de soutenance a été validé par votre gestionnaire HDR; </li>
<li>vous êtes le candidat associé à la proposition de soutenance. </li>
</ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_FEU_VERT', null, 'mail', 'La soutenance HDR de VAR[Candidat#Denomination] a été acceptée par votre établissement.', e'<p>Bonjour,</p>
<p>La soutenance de VAR[Candidat#Denomination] (VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle]) a été acceptée par votre établissement.<br>Conformément aux informations que vous avez fournies celle-ci se tiendra le VAR[Soutenance#Date] dans VAR[Soutenance#Lieu].</p>
<p>Vous pouvez consulter les rapports de pré-soutenance en bas de la page de la proposition de soutenance : VAR[Url#SoutenanceProposition]</p>
<p><em>-- Justification ---------------------------------------------------------------------------</em></p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la/le gestionnaire HDR vient de donner son feu vert pour la soutenance</li>
<li>vous êtes soit :<br>
<ul>
<li>un·e acteur·trice direct·e de l''HDR de  VAR[Candidat#Denomination] ;</li>
<li>un·e responsable de l\'unité de recherche encadrant l''HDR.</li>
</ul>
</li>
</ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_ANNULATION_ENGAGEMENT_IMPARTIALITE', '<p>Courrier électronique envoyé lors de l''annulation d''un engagement d''impartialité</p>', 'mail', 'Annulation de la signature de l''engagement d''impartialité de VAR[SoutenanceMembre#Denomination] pour l''HDR de VAR[Candidat#Denomination]', '<p>Bonjour,</p><p><br />Votre signature de l''engagement d''impartialité de l''HDR de <strong>VAR[Candidat#Denomination]</strong> vient d''être annulée.</p> <p>-- Justification ----------------------------------------------------------------------</p> <p>Vous avez reçu ce mail car :</p><ul><li>vous avez signé l''engagement d''impartialité pour l''HDR de VAR[Candidat#Denomination];  </li><li>la signature a été annulée. </li></ul>', null, 'Soutenance\Provider\Template', 'default');
                                                                                                                                                             INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_CONNEXION_RAPPORTEUR', '<p>Courrier électronique envoyé aux rapporteur·trices  pour la connexion à SyGAL</p>', 'mail', 'Connexion en tant que rapporteur de l''HDR de VAR[Candidat#Denomination]', e'<p><em>-- Version française----------------------------------------------------</em><br /><br />Bonjour,</p>
<p>Vous venez d\'être désigné comme rapporteur pour l''HDR de VAR[Candidat#Denomination]. Afin de pouvoir vous connecter à l\'application ESUP-SyGAL et ainsi pouvoir rendre votre avis de pré-soutenance, vous pouvez utiliser l\'adresse suivante : VAR[Url#RapporteurDashboard].<br /><br />Vous avez jusqu\'au <strong>VAR[Soutenance#DateRetourRapport]</strong> pour effectuer ce dépôt.</p>
<p> <br />Cordialement,</p>
<p><br /><em>-- English version -----------------------------------------------------</em><br /><br />Dear Mrs/Mr,</p>
<p>You have been appointed as an external referee for the PhD HDR presented by VAR[Candidat#Denomination]. In order to have access to ESUP-SyGAL  web-based application for submitting your report, you can use the following link : VAR[Url#RapporteurDashboard].</p>
<p>You have until the <strong>VAR[Soutenance#DateRetourRapport]</strong> to upload your report.</p>
<p>For these reasons, I am in favor of VAR[Candidat#Denomination]\'s HDR defense ; and thus for VAR[Candidat#Denomination] to be able to claim the title of candidate from VAR[Etablissement#Libelle] and Normandie Université.<br /><br /></p>
                                                                                                                                                                <p>Best regards,<br /><br /><br /></p>
                                                                                                                                                                <p> </p>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_DEMANDE_ADRESSE_EXACTE', '<p>Courrier électronique envoyé au candidat ainsi qu''aux encadrants de l''HDR afin de leur demander qu''ils renseignent l''adresse exacte de la soutenance</p>', 'mail', 'Renseignement de l''adresse exacte de la soutenance HDR ', e'<p>Bonjour,</p>
                                                                                                                                                                <p><em>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.</em></p>
<p>Merci de bien vouloir renseigner l\'adresse exacte de la soutenance</p>
                                                                                                                                                                <p>Vous pouvez dès à présent le faire à partir de la page de gestion de la soutenance : VAR[Url#SoutenanceProposition]</p>
                                                                                                                                                                <p> </p>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_DEMANDE_ENGAGEMENT_IMPARTIALITE', '<p>Courrier électronique notifiant un·e futur·e rapporteur·e pour la signature de l''engagement d''impartialité.<br />Envoyé lors de l''appariement membre/acteur</p>', 'mail', 'Demande de signature de l''engagement d''impartialité de l''HDR de VAR[Candidat#Denomination]', '<p>-- Version française ---------------------------------------------------------------</p><p>Bonjour,</p><p>Afin de pouvoir devenir rapporteur de l''HDR de <strong>VAR[Candidat#Denomination]</strong>, il est nécessaire de signer électroniquement l''engagement d''impartialité dans l''application <em>ESUP-SyGAL</em> :<strong> VAR[Url#RapporteurDashboard]</strong>.<br /><br />Vous accéderez ainsi à une page "tableau de bord de la soutenance" listant les membres du jury.<br />Cliquez ensuite sur "accès à l’engagement d’impartialité".<br />Puis après avoir pris connaissance des conditions relatives à cet engagement, vous pourrez signer ou non cet engagement d’impartialité.<br />Si vous signez, vous pourrez alors télécharger le PDF du manuscrit de HDR.</p><p>Cordialement<br /><br />-- English version ------------------------------------------------------------------<br /><br />Dear Mrs/Mr,</p><p>Before being officially registered as an external referee for the PhD HDR presented by <strong>VAR[Candidat#Denomination]</strong>, you have to sign the "impartiality commitment" available in your dashborad : <strong>VAR[Url#RapporteurDashboard]</strong>.<br /><br />You will then be connected to a web page entitled "index of the PhD defense" listing the PhD jury members.<br />Click then on "access to the impartiality commitment".<br />Then, after reading the requirements regarding the impartiality commitment of an external referee, you sign it or not.<br />If you sign it, you will be able to download the PDF version of the PhD manuscript.</p><p>Best regards,</p><p>-- Justification ----------------------------------------------------------------------<br /><br />Vous avez reçu ce mail car :</p><ul><li>vous avez été désigné rapporteur pour l''HDR de VAR[Candidat#Denomination]</li><li>la signature a été annulée<br /><br /></li></ul>', null, 'Soutenance\Provider\Template', 'default');
                                                                                                                                                             INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_DEMANDE_PRERAPPORT', '<p>Courrier électronique envoyé au rapport pour leur demander leur pré-rapport de soutenance</p>', 'mail', 'Demande de l''avis de soutenance de l''HDR de VAR[Candidat#Denomination]', e'<p><em>-- Version française----------------------------------------------------</em><br /><br />Bonjour,</p>
<p>Vous pouvez commencer à examiner les travaux HDR de <strong>VAR[Candidat#Denomination]</strong>.</p>
<p>Par la suite, vous devez établir un rapport incluant votre avis sur la soutenance et votre signature au moins 14 jours avant la date de soutenance, puis le déposer sur la plateforme ESUP-SyGAL à l’adresse suivante : VAR[Url#RapporteurDashboard].</p>
<p>Si votre avis est négatif, pourriez-vous indiquer brièvement les raisons de ce choix puis déposer votre rapport.</p>
<p><span style="text-decoration: underline;">Rappel :</span> <strong>Le rapport est attendu pour le VAR[Soutenance#DateRetourRapport].</strong><br />Au delà de cette date, vous ne pourrez plus rendre votre rapport.</p>
<p>Cordialement,</p>
<p><em>-- English version -----------------------------------------------------</em><br /><br />Dear Mrs/Mr,</p>
<p>Since you have signed the "impartiality commitment", you can start the evaluation of the PhD HDR presented by <strong>VAR[Candidat#Denomination]</strong>.<br />Then, you must give your opinion about the PhD HDR and upload your signed PhD report at least 14 days before the date of the PhD defense at : VAR[Url#RapporteurDashboard].</p>
<p>In case of a negative opinion, please indicate briefly the main reasons for the rejection and upload your signed PhD report.</p>
<p><strong>Keep in mind that your report must be uploaded before VAR[Soutenance#DateRetourRapport].</strong><br />After the deadline, you won\'t be able to upload your report.</p>
                                                                                                                                                                <p>Best regards,</p>
                                                                                                                                                                <p> </p>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_DEMANDE_RAPPORT_SOUTENANCE', '<p>Courrier électronique de demande du rapport de présoutenance à un·e rapporteur·trice</p>', 'mail', 'Demande de rapport de pré-soutenance pour l''HDR de VAR[Candidat#Denomination]', e'<p><em>-- Version française -----------------------------------------------------------</em><br /><br />Bonjour,</p>
                                                                                                                                                                <p>Vous venez d\'être désigné comme rapporteur pour l''HDR de VAR[Candidat#Denomination].</p>
<p>Ce courrier électronique a pour but de vous rappeler que vous devez rendre votre avis de soutenance pour la date du <strong>VAR[Soutenance#DateRetourRapport]</strong>.<br />Pour rendre votre avis, connectez-vous à l\'application ESUP-SyGAL en utilisant l\'adresse suivante : VAR[Url#RapporteurDashboard].</p>
<p> Cordialement,</p>
<p><em>--  English version -----------------------------------------------------------</em></p>
<p><br />Dear Mrs/Mr,</p>
<p>You have been appointed as an external referee for the PhD HDR presented by VAR[Candidat#Denomination].</p>
<p>This mail is a reminder that you have until <strong>VAR[Soutenance#DateRetourRapport]</strong> to submit your report.<br />To do so you can use the following link : VAR[Url#RapporteurDashboard].</p>
<p>For these reasons, I am in favor of VAR[Candidat#Denomination]\'s HDR defense ; and thus for VAR[Candidat#Denomination] to be able to claim the title of candidate from VAR[Etablissement#Libelle] and Normandie Université.</p>
                                                                                                                                                                <p>Best regards,</p>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_PROPOSITION_REFUS', '<p>Courrier électronique envoyé aux acteurs directs de l''HDR lors du refus de la proposition</p>', 'mail', 'Votre proposition de soutenance HDR a été réfusée', e'<p>Bonjour,</p>
                                                                                                                                                                <p>VAR[Individu#getDenomination] (VAR[Role#getLibelle] <em>VAR[Etablissement#Libelle]</em>) a refusé votre proposition de soutenance.<br />Le motif du refus est le suivant :</p>
                                                                                                                                                                <table>
                                                                                                                                                                <tbody>
                                                                                                                                                                <tr>
                                                                                                                                                                <td>VAR[String#ToString]</td>
                                                                                                                                                                </tr>
                                                                                                                                                                </tbody>
                                                                                                                                                                </table>
                                                                                                                                                                <p>Suite à ce refus toutes les validations associées à cette proposition ont été annulées.<br /><br />Vous avez reçu ce mail car :</p>
                                                                                                                                                                <ul>
                                                                                                                                                                <li>la proposition de soutenance de HDR de VAR[Candidat#Denomination] a été refusée ;</li>
                                                                                                                                                                <li>vous êtes un des acteurs directs de l''HDR.<br /><br /></li>
                                                                                                                                                                </ul>', 'td {border: 1px solid black;}', 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_PROPOSITION_SUPPRESSION', '<p>Suppression des informations concernant la proposition de soutenance</p>', 'mail', 'Votre proposition de soutenance HDR a été supprimée', e'<p>Bonjour,</p>
                                                                                                                                                                <p>Ceci est un mail envoyé automatiquement par l\'application ESUP SyGAL</p>
<p>Votre proposition de soutenance vient d\'être <strong>supprimée.</strong></p>
                                                                                                                                                                <p>Veuillez vous rapprocher de la/le gestionnaire de votre HDR afin d\'avoir plus d\'informations à ce sujet.</p>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_TRANSMETTRE_DOCUMENTS_GARANT', '<p>Courrier électronique envoyé au garant de l''HDR pour transmission de documents avant soutenance</p>', 'mail', 'Transmission des documents pour la soutenance HDR de VAR[Candidat#Denomination]', e'<p>Bonjour,</p>
                                                                                                                                                                <p>La soutenance de VAR[Candidat#Denomination] est imminente.<br />Vous retrouverez ci-dessous les liens pour télécharger les documents utiles pour la soutenance.</p>
                                                                                                                                                                <p>Document pour la soutenance :<br />- Procès verbal : VAR[Url#ProcesVerbal]<br />- Rapport de soutenance : VAR[Url#RapportSoutenance]<br />- Rapport technique (en cas de visioconférence) : VAR[Url#RapportTechnique]</p>
                                                                                                                                                                <p><br />Bonne journée,<br />L\'équipe SyGAL</p>
<p> </p>', null, 'Soutenance\Provider\Template', 'default');
                                                                                                                                                             INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_SIGNATURE_ENGAGEMENT_IMPARTIALITE', '<p>Courrier électronique envoyé vers les gestionnaires HDR lors de la signature de l''engagement d''impartialité par un rapporteur</p>', 'mail', 'Signature de l''engagement d''impartialité de l''HDR de VAR[Candidat#Denomination] par VAR[SoutenanceMembre#Denomination]', e'<p>Bonjour,</p>
<p><strong>VAR[SoutenanceMembre#Denomination]</strong> vient de signer l\'engagement d\'impartialité de l''HDR de <strong>VAR[Candidat#Denomination]</strong>.</p>
<p>-- Justification ----------------------------------------------------------------------<br><br>Vous avez reçu ce mail car :</p>
<ul>
<li>le rapporteur VAR[SoutenanceMembre#Denomination] vient de signer l\'engagement d\'impartialité;</li>
<li>vous êtes un gestionnaire HDR de l\'établissement d\'inscription du candidat . <br><br></li>
</ul>', null, 'Soutenance\Provider\Template', 'default');
                                                                                                                                                             INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_VALIDATION_SOUTENANCE_AVANT_PRESOUTENANCE', '<p>Courrier électronique indiquant aux acteurs directs et aux structures que le dossiers est complet et part pour saisie en pré-soutenance</p>', 'mail', 'Validation de proposition de soutenance HDR de VAR[Candidat#Denomination]', e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l\'application ESUP-SyGAL.<br><br>La proposition de soutenance de HDR suivante a été validée par tous les acteurs et structures associées :</p>
                                                                                                                                                                <table>
                                                                                                                                                                <tbody>
                                                                                                                                                                <tr>
                                                                                                                                                                <th>Candidat :</th>
                                                                                                                                                                <td>VAR[Candidat#Denomination] (VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle])</td>
                                                                                                                                                                </tr>
                                                                                                                                                                </tbody>
                                                                                                                                                                </table>
                                                                                                                                                                <p>Pour examiner cette proposition merci de vous rendre dans l\'application ESUP-SyGAL : VAR[Url#SoutenanceProposition].</p>
<p>-----------------------</p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la proposition de soutenance vient d\'être validée par tous les acteurs directs et toutes les structures concernées ;</li>
                                                                                                                                                                <li>vous êtes soit :
                                                                                                                                                                <ul>
                                                                                                                                                                <li>un des acteurs directs de l''HDR de VAR[Candidat#Denomination]</li>
                                                                                                                                                                <li>un·e responsable de l\'unité de recherche encadrant l''HDR,</li>
<li>un·e gestionnaire HDR de l\'établissement d\'inscription du candidat. <br><br></li>
</ul>
</li>
</ul>', 'table { width:100%; } th { text-align:left; }', 'Soutenance\Provider\Template', 'default');
                                                                                                                                                             INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_REFUS_ENGAGEMENT_IMPARTIALITE', '<p>Courrier électronique envoyé lors du refus de l''engagement d''impartialité</p>', 'mail', 'Refus de l''engagement d''impartialité de l''HDR de VAR[Candidat#Denomination] par VAR[SoutenanceMembre#Denomination]', e'<p>Bonjour,</p>
<p><strong>VAR[SoutenanceMembre#Denomination]</strong> vient de refuser l\'engagement d\'impartialité de l''HDR de <strong>VAR[Candidat#Denomination]</strong>.</p>
<p>-- Justification ----------------------------------------------------------------------</p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>le rapporteur VAR[SoutenanceMembre#Denomination] vient de refuser de signer l\'engagement d\'impartialité;</li>
<li>vous êtes :
<ul>
<li>soit un des acteurs directs de l''HDR de VAR[Candidat#Denomination],</li>
<li>soit un gestionnaire HDR de l\'établissement d\'inscription du candidat.<br>            <br><br></li>
</ul>
</li>
</ul>', null, 'Soutenance\Provider\Template', 'default');
                                                                                                                                                             INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_AVIS_DEFAVORABLE', null, 'mail', 'VAR[Acteur#Denomination] vient de rendre un avis défavorable pour l''HDR de VAR[Candidat#Denomination]', e'<p>Bonjour,</p>
<p>Le rapporteur VAR[Acteur#Denomination] vient de rendre un avis défavorable pour l''HDR de VAR[Candidat#Denomination] (VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle]).<br>VAR[Acteur#Denomination] motive ce refus avec le justification suivante : VAR[Avis#Justification]</p>
<p> </p>
<p>Vous pouvez consulter le rapport de pré-soutenance en allant sur la page de gestion de la soutenance VAR[Url#SoutenanceProposition] ou en utilisant le lien suivant : VAR[Url#PrerapportSoutenance]</p>
<p>Vous avez reçu ce courrier électronique car :</p>
<ul>
<li>un avis défavorable a été rendu par un rapporteur</li>
<li>vous êtes soit :
<ul>
<li>un·e gestionnaire HDR de l\'établissement d\'inscription du candidat</li>
<li>un·e responsable de l\'unité de recherche accueillant l''HDR de VAR[Candidat#Denomination]</li>
                                                                                                                                                                <li>garant de l''HDR de VAR[Candidat#Denomination]</li>
                                                                                                                                                                </ul>
                                                                                                                                                                </li>
                                                                                                                                                                </ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_TOUS_AVIS_RENDUS', '<p>Courrier électronique vers les <em>aspects HDR</em>  indiquant que tous les avis ont été rendus</p>', 'mail', 'Tous les avis de soutenance de l''HDR de VAR[Candidat#Denomination] ont été rendus.', e'<p>Bonjour,</p>
                                                                                                                                                                <p>Les rapporteurs de l''HDR de VAR[Candidat#Denomination] ont rendu leur rapport de pré-soutenance.</p>
                                                                                                                                                                <p>Vous pouvez les consulter sur la page de gestion de la pré-soutenance de cette HDR : VAR[Url#SoutenancePresoutenance]<br><br>Vous avez reçu ce mail car :</p>
                                                                                                                                                                <ul>
                                                                                                                                                                <li>tous les avis de soutenance de l''HDR de VAR[Candidat#Denomination] ont été rendus ;</li>
                                                                                                                                                                <li>vous êtes gestionnaire HDR de l\'établissement d\'inscription du candidat</li>
                                                                                                                                                                </ul>', null, 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_VALIDATION_SOUTENANCE_ENVOI_PRESOUTENANCE', '<p>Courrier électronique pour indiquer que l''HDR peut débuter le circuit de pré-soutenance</p>', 'mail', 'Vous pouvez maintenant procéder au renseignement des informations liées à la soutenance HDR de VAR[Candidat#Denomination]', e'<p>Bonjour,</p>
                                                                                                                                                                <p>La proposition de soutenance de l''HDR suivante a été totalement validée :</p>
                                                                                                                                                                <table>
                                                                                                                                                                <tbody>
                                                                                                                                                                <tr>
                                                                                                                                                                <th>Candidat :</th>
                                                                                                                                                                <td>VAR[Candidat#Denomination]</td>
                                                                                                                                                                </tr>
                                                                                                                                                                </tbody>
                                                                                                                                                                </table>
                                                                                                                                                                <p>Vous pouvez maintenance procéder à la saisie des informations liées à la soutenance : VAR[Url#SoutenancePresoutenance]</p>
                                                                                                                                                                <p>---------------------------------</p>
                                                                                                                                                                <p>Vous avez reçu ce mail car :</p>
                                                                                                                                                                <ul>
                                                                                                                                                                <li>la proposition de soutenance de HDR de VAR[Candidat#Denomination] a été complètement validée</li>
                                                                                                                                                                <li>vous êtes gestionnaire HDR de l\'établissement d\'inscription du candidat.</li>
                                                                                                                                                                </ul>', 'table { width:100%; } th { text-align:left; }', 'Soutenance\Provider\Template', 'default');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('SOUTENANCE_HDR_DEMANDE_SAISIE_INFOS_SOUTENANCE', '<p>Courrier électronique envoyé au candidat afin de lui demander qu''ils renseignent les informations concernant sa proposition de soutenance</p>', 'mail', 'Vous pouvez dès à présent renseigner vos informations de soutenance', e'<p>Bonjour,</p>
<p><em>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</em></p>
<p>La/le gestionnaire HDR a saisi l''ensemble des informations concernant votre HDR.</p>
<p>Vous pouvez donc dès à présent renseigner les informations concernant votre soutenance, à partir de la page de gestion de celle-ci : VAR[Url#SoutenanceProposition]</p>', null, 'Soutenance\Provider\Template', 'default');

-- Macros
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Candidat#Denomination', '<p>Retourne la dénomination du candidat</p>', 'candidat', '__toString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Candidat#DenominationPatronymique', '<p>Retourne la dénomination du candidat (civilité+nom Patronymique+prénom)</p>', 'candidat', 'getDenominationPatronymique');

INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('HDR#Discipline', '<p>Affiche le libellé de la discipline associée à l''HDR</p>', 'hdr', 'getLibelleDiscipline');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('HDR#Encadrement', null, 'hdr', 'toStringEncadrement');

INSERT INTO public.unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Url#ConvocationCandidat', NULL, 'Url', 'getSoutenanceConvocationCandidat');




------------------------------------------------- Versions de diplômes -------------------------------------------------

create table version_diplome(
    id bigserial primary key,
    source_id bigint default 1 not null constraint version_diplome_source_fk references source,
    source_code varchar(64) not null,
    etablissement_id bigint not null constraint version_diplome_etab_fk references etablissement,
    code varchar(32) not null,
    libelle_court varchar(64) not null,
    libelle_long varchar(128) not null,
    these_compatible boolean default false not null,
    hdr_compatible boolean default false not null
);

create unique index version_diplome_source_code_un on version_diplome (source_code);

alter table version_diplome add histo_creation timestamp default ('now'::text)::timestamp not null;
alter table version_diplome add histo_createur_id bigint not null default 1 constraint version_diplome_hcfk references utilisateur;
alter table version_diplome add histo_modification timestamp;
alter table version_diplome add histo_modificateur_id bigint constraint version_diplome_hmfk references utilisateur;
alter table version_diplome add histo_destruction timestamp;
alter table version_diplome add histo_destructeur_id bigint constraint version_diplome_hdfk references utilisateur;

----

-- ULHN
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HAI','HAI','HDR AI','HDR AI',true from source s where s.code = 'ULHN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HLSH','HLSH','HDR Lettres Sciences Hu','HDR Lettres Sciences Hu',true from source s where s.code = 'ULHN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HST','HST','HDR SCIENCES','HDR SCIENCES',true from source s where s.code = 'ULHN::apogee';
-- UCN
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HARTM','HARTM','HDR ARTS SPECT PLAST MUSI','HABILITATION A DIRIGER DES RECHERCHES EN ARTS DU SPECTACLE, ARTS PLASTIQUES, MUSICOLOGIE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HCHIM','HCHIM','HDR CHIMIE','HABILITATION A DIRIGER DES RECHERCHES DE CHIMIE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HGENC','HGENC','HDR GENIE CIVIL','HABILITATION A DIRIGER DES RECHERCHES DE GENIE CIVIL',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HGENP','HGENP','HDR GENIE PROCEDES','HABILITATION A DIRIGER DES RECHERCHES DE GENIE PROCEDES',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HGEO','HGEO','HDR GEOGRAPHIE','HABILITATION A DIRIGER DES RECHERCHES DE GEOGRAPHIE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HHIST','HHIST','HDR HISTOIRE','HABILITATION A DIRIGER DES RECHERCHES D''HISTOIRE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HINFO','HINFO','HDR INFORMATIQUE','HABILITATION A DIRIGER DES RECHERCHES D''INFORMATIQUE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HLLA','HLLA','HDR LANG. LITT. ANCIENNES','HABILITATION A DIRIGER DES RECHERCHES DE LANGUES ET LITTERATURES ANCIENNES',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HLLE','HLLE','HDR LANG. LITT. ETRANGERE','HABILITATION A DIRIGER DES RECHERCHES DE LANGUES ET LITTERATURES ETRANGERES',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HLLF','HLLF','HDR LANG. LITT. FRANCAISE','HABILITATION A DIRIGER DES RECHERCHES DE LANGUES ET  LITTERATURES FRANCAISES',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HMATH','HMATH','HDR MATHEMATIQUES','HABILITATION A DIRIGER DES RECHERCHES DE MATHEMATIQUES',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HMED','HMED','HDR MEDECINE','HABILITATION A DIRIGER DES RECHERCHES DE MEDECINE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HPHARM','HPHARM','HDR PHARMACIE','HABILITATION A DIRIGER DES RECHERCHES DE PHARMACIE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HPHILO','HPHILO','HDR PHILOSOPHIE','HABILITATION A DIRIGER DES RECHERCHES DE PHILOSOPHIE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HPHYS','HPHYS','HDR PHYSIQUE','HABILITATION A DIRIGER DES RECHERCHES en PHYSIQUE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HPSYCH','HPSYCH','HDR PSYCHOLOGIE','HABILITATION A DIRIGER DES RECHERCHES DE PSYCHOLOGIE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HSCGES','HSCGES','HDR SCIENCES DE GESTION','HABILITATION A DIRIGER DES RECHERCHES DE SCIENCES DE GESTION',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HSCLANG','HSCLANG','HDR SCIENCES DU LANGAGE','HABILITATION A DIRIGER DES RECHERCHES DE SCIENCES DU LANGAGE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HSCECO','HSCECO','HDR SCIENCES ECONOMIQUES','HABILITATION A DIRIGER DES RECHERCHES DE SCIENCES ECONOMIQUES',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HSCED','HSCED','HDR SCIENCES EDUCATION','HABILITATION A DIRIGER DES RECHERCHES DE SCIENCES DE L''EDUCATION',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HSCJUR','HSCJUR','HDR SCIENCES JURIDIQUES','HABILITATION A DIRIGER DES RECHERCHES DE SCIENCES JURIDIQUES',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HSCPOL','HSCPOL','HDR SCIENCES POLITIQUES','HABILITATION A DIRIGER DES RECHERCHES DE SCIENCES POLITIQUES',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HSCTERR','HSCTERR','HDR SCIENCES TERRE','HABILITATION A DIRIGER DES RECHERCHES EN SCIENCES TERRE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HSCVIE','HSCVIE','HDR SCIENCES VIE','HABILITATION A DIRIGER DES RECHERCHES DE SCIENCES VIE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HSOCIO','HSOCIO','HDR SOCIOLOGIE','HABILITATION A DIRIGER DES RECHERCHES en SOCIOLOGIE',true from source s where s.code = 'UCN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'HSTAPS','HSTAPS','HDR STAPS','HABILITATION A DIRIGER DES RECHERCHES DE STAPS',true from source s where s.code = 'UCN::apogee';
-- URN
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'DDW90','DDW90','HDR Droit','Habilitation à Diriger des Recherches en Droit',true from source s where s.code = 'URN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'LLW90','LLW90','HDR Lettres','Habilitation à Diriger des Recherches en Lettres',true from source s where s.code = 'URN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'MMW90','MMW90','HDR Médecine','Habilitation à Diriger des Recherches en Médecine',true from source s where s.code = 'URN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'SSW90','SSW90','HDR Sciences','Habilitation à Diriger des Recherches en Sciences',true from source s where s.code = 'URN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'KKW90','KKW90','HDR Sciences APS','Habilitation à Diriger des Recherches en Sciences et Techniques des Activités Physiques et Sportives',true from source s where s.code = 'URN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'DGS91','DGS91','HDR Sciences de Gestion','Habilitation à Diriger des Recherches en Sciences de Gestion',true from source s where s.code = 'URN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'DDW91','DDW91','HDR Sciences Economiques','Habilitation à Diriger des Recherches en Sciences Economiques',true from source s where s.code = 'URN::apogee';
insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'PPW90','PPW90','HDR SHS','Habilitation à Diriger des Recherches en Sciences Humaines et Sociales',true from source s where s.code = 'URN::apogee';

/*
-- Requête Apogée UCN : génération des INSERTs des versions de diplomes pour HDR
select 'insert into version_diplome(etablissement_id,source_id,source_code,code,libelle_court,libelle_long,hdr_compatible) select s.etablissement_id,s.id,'''||
       cod_dip||''','''||cod_dip||''','''||replace(lic_vdi,'''','''''')||''','''||replace(lib_web_vdi,'''','''''')||''',true ' ||
       'from source s where s.code = ''UCN::apogee'';' sql
from VERSION_DIPLOME
where tem_ses_uni = 'O' and tem_res_ths_vdi = 'O'
  and to_char(sysdate, 'YYYY') >= daa_deb_val_vdi
  and to_char(sysdate, 'YYYY') <= daa_fin_val_vdi
  and dur_ann_vdi = 1
order by lic_vdi;
*/

delete from unicaen_renderer_macro where variable_name = 'hdr' and methode_name = 'getLibelleDiscipline';
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('HDR#VersionDiplome', '<p>Affiche le libellé de la version de diplôme associée à l''HDR</p>', 'hdr', 'getLibelleVersionDiplome');

alter table HDR add version_diplome_id bigint constraint hdr_version_diplome_fk references version_diplome(id) on delete no action;