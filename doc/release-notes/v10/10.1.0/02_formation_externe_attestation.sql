-- Création de la table
CREATE TABLE formation_formation_externe
(
    id                    bigserial                                                    not null
        primary key,
    libelle               varchar(1024)                                                not null,
    type                  varchar(1)                                                   not null,
    duree                 int                                                          not null,
    date_debut            timestamp                                                    not null,
    date_fin              timestamp                                                    not null,
    attestation_id        bigint REFERENCES fichier (id)                               not null,
    doctorant_id          integer REFERENCES doctorant (id) on delete cascade          not null,
    histo_createur_id     bigint                                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp,

    CONSTRAINT fk_formation_formation_externe_attestation
        FOREIGN KEY (attestation_id)
            REFERENCES fichier (id)
            ON DELETE CASCADE
);

-- Création de la nature du fichier associée à l'attestation
INSERT INTO nature_fichier (id, code, libelle) VALUES (nextval('nature_fichier_id_seq'::regclass), 'FORMATION_ATTESTATION_FORMATION_EXTERIEURE', 'Attestation de suivi d''une formation externe');


-- Création/attribution des privilèges
INSERT INTO unicaen_privilege_categorie (ID, CODE, LIBELLE, ORDRE)
SELECT nextval('categorie_privilege_id_seq'),
       'formation_formationexterne',
       'Formation Externe',
       5007 WHERE NOT EXISTS (SELECT 1
                               FROM unicaen_privilege_categorie
                               WHERE CODE = 'formation_formationexterne');

INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'formation_formationexterne'), 'ajouter', 'Ajouter une formation suivie à l''extérieur', 1);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'formation_formationexterne'), 'modifier', 'Modifier une formation suivie à l''extérieur', 2);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'formation_formationexterne'), 'historiser', 'Historiser une formation suivie à l''extérieur', 3);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'formation_formationexterne'), 'supprimer', 'Supprimer une formation suivie à l''extérieur', 4);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'formation_formationexterne'), 'telecharger-attestation', 'Télécharger une attestation de suivi d''une formation externe', 5);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'formation_formationexterne'), 'gerer-validation', 'Gérer les validations (valider/refuser/révoquer) d''une formation externe', 5);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'formation_formationexterne'), 'notifier-formation-ajoutee', 'Notifier les personnes ayant le droit de valider les formations externes, que des nouvelles ont été ajoutées', 5);


--------------- Privilèges concernant le tableau de bord de gestion des formations
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'formation'), 'index_gestion', 'Accès à l''index de gestion concernant un doctorant', 3);
----------------

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'formation_formationexterne', 'ajouter'
                            union
                               select 'formation_formationexterne', 'modifier'
                               union
                               select 'formation_formationexterne', 'historiser'
                               union
                               select 'formation_formationexterne', 'supprimer'
                               union
                               select 'formation_formationexterne', 'telecharger-attestation'
                               union
                               select 'formation_formationexterne', 'notifier-formation-ajoutee'
                            )
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH',
                                           'DOCTORANT'
    )
         join unicaen_privilege_categorie cp on cp.CODE = data.categ
         join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'formation_formationexterne', 'telecharger-attestation'
                           union
                           select 'formation_formationexterne', 'modifier')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'BDD',
                                          'GEST_ED',
                                          'RESP_ED'
    )
         join unicaen_privilege_categorie cp on cp.CODE = data.categ
         join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'formation', 'index_gestion'
                           union
                           select 'formation_formationexterne', 'gerer-validation')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                          'BDD',
                                          'ADMIN_TECH',
                                          'GEST_ED',
                                          'RESP_ED'
    )
         join unicaen_privilege_categorie cp on cp.CODE = data.categ
         join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
;


-- Gestion des validations
create table validation_formation_externe
(
    id                    bigint                                                       not null
        constraint validation_formation_externe_pkey
            primary key,
    formation_externe_id              bigint                                                       not null
        constraint validation_formation_externe_fk
            references public.formation_formation_externe,
    individu_id           bigint
        constraint validation_formation_externe_individu_id_fk
            references public.individu,
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_createur_id     bigint    default 1                                          not null
        constraint validation_formation_externe_hcfk
            references public.utilisateur,
    histo_modification    timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint    default 1                                          not null
        constraint validation_formation_externe_hmfk
            references public.utilisateur,
    histo_destruction     timestamp,
    histo_destructeur_id  bigint
        constraint validation_formation_externe_hdfk
            references public.utilisateur,
    validation_id         bigint                                                       not null
        constraint validation_formation_externe_validation_fk
            references public.validation
);

create index validation_formation_externe_hcfk_idx
    on public.validation_formation_externe (histo_createur_id);
create index validation_formation_externe_hdfk_idx
    on public.validation_formation_externe (histo_destructeur_id);
create index validation_formation_externe_hmfk_idx
    on public.validation_formation_externe (histo_modificateur_id);
create index validation_formation_externe_individu_idx
    on public.validation_formation_externe (individu_id);
create index validation_formation_externe_idx
    on public.validation_formation_externe (formation_externe_id);
create index validation_formation_externe_validation_idx
    on public.validation_formation_externe (validation_id);


create sequence validation_formation_externe_id_seq owned by validation_formation_externe.id;
alter table validation_formation_externe alter column id set default nextval('validation_formation_externe_id_seq');

-- ajout des validations
insert into type_validation (id, code, libelle) VALUES (nextval('type_validation_id_seq'), 'VALIDATION_FORMATION_EXTERNE', 'Validation de la formation suivi à l''extérieur par un doctorant');
insert into type_validation (id, code, libelle) VALUES (nextval('type_validation_id_seq'), 'REFUS_VALIDATION_FORMATION_EXTERNE', 'Refus de la formation suivi à l''extérieur par un doctorant');

-- Templates/Macro Renderer
INSERT INTO public.unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Url#IndexGestionFormation', '<p>Retourne l''URL de l''index de gestion des formations d''un doctorant</p>', 'Url', 'getUrlIndexGestionFormation');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('FORMATION_EXTERNE_AJOUTEE', '<p>Mail envoyé aux personnes ayant le droit de valider des formations externes déposées par le doctorant</p>', 'mail', 'Des formations externes déposées par VAR[Doctorant#Denomination] ont été soumises à validation', e'<p>Bonjour,</p>
<p>De nouvelles déclarations de formations externes ont été enregistrées par <strong>VAR[Doctorant#Denomination]</strong>.</p>
<p>Vous êtes invité·e à vous connecter à l\'application pour les examiner et, si nécessaire, les valider : VAR[Url#IndexGestionFormation]</p>
<p>Cordialement, </p>
                                                                                                                                                                <p><em>L’équipe SyGAL</em></p>', null, 'Formation\Provider\Template', 'default');