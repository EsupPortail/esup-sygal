CREATE TABLE formation_justificatif
(
    id                    bigserial                                    not null
        primary key,
    fichier_id            bigint REFERENCES fichier (id)               not null,
    inscription_id        bigint REFERENCES formation_inscription (id) not null,
    histo_createur_id     bigint                                       not null REFERENCES utilisateur (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp
);

CREATE TABLE formation_justificatif_seance
(
    justificatif_id       bigint                  NOT NULL,
    seance_id             bigint                  NOT NULL,
    histo_createur_id     bigint                  NOT NULL REFERENCES utilisateur (id),
    histo_creation        timestamp DEFAULT now() NOT NULL,
    histo_modificateur_id bigint REFERENCES utilisateur (id),
    histo_modification    timestamp,
    histo_destructeur_id  bigint REFERENCES utilisateur (id),
    histo_destruction     timestamp,
    PRIMARY KEY (justificatif_id, seance_id),

    CONSTRAINT fk_justificatif
        FOREIGN KEY (justificatif_id)
            REFERENCES formation_justificatif (id)
            ON DELETE CASCADE,

    CONSTRAINT fk_seance
        FOREIGN KEY (seance_id)
            REFERENCES formation_seance (id)
            ON DELETE CASCADE
);

-- Création de la nature du fichier associée au justificatif d'absence
INSERT INTO nature_fichier (id, code, libelle) VALUES (nextval('nature_fichier_id_seq'::regclass), 'JUSTIFICATIF_ABSENCE', 'Justificatif d''absence à une formation');



-- Privilèges
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'formation_inscription'), 'acceder_justificatif_absence', 'Accéder aux justificatifs d''absence à une formation', 8);
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'formation_inscription'), 'gerer_justificatif_absence', 'Gérer les justificatifs d''absence à une formation (ajout/suppression)', 9);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'formation_inscription', 'acceder_justificatif_absence')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH',
                                           'BDD',
                                           'DOCTORANT'
    )
         join unicaen_privilege_categorie cp on cp.CODE = data.categ
         join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'formation_inscription', 'gerer_justificatif_absence')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH',
                                           'DOCTORANT'
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

-- Template/Macro Renderer
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Url#JustificationAbsences', '<p>Renvoi vers le tableau de bord permettant de déposer des justificatifs d''absences à une formation</p>', 'Url', 'getUrlJustificationAbsenceFormation');
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('FORMATION_SESSION_ABSENCES_INJUSTIFIEES', '<p>Mail envoyé à l''étudiant ayant été absent au moins à une séance d''une formation suivie</p>', 'mail', 'Justification de vos absences à la formation VAR[Formation#Libelle]', '<p>Bonjour VAR[Doctorant#Denomination],<br><br>Vous vous êtes inscrit-es à la formation <strong>VAR[Formation#Libelle]</strong> pour laquelle vous avez reçu une convocation. <br>Les séances de cette formation se sont tenues : <br>VAR[Session#SeancesTable]<br><br>Cependant, vous avez été absent à une ou plusieurs séances, et nous n''avons pas reçu de message de votre part pour nous en informer. Nous vous rappelons que <strong>toute inscription</strong> à une formation <strong>vous</strong> <strong>engage à la suivre</strong>. <br><br>En cas d’indisponibilité, il est de votre devoir de vous désinscrire <strong>jusqu’à J-5</strong> ou de prévenir la personne chargée de la gestion de cette formation. Si vous aviez pu nous prévenir à l''avance, cela aurait libéré une place pour qu''un-e autre doctorant-e puisse assister à celle-ci. <br><br>Nous vous prions de bien vouloir justifier votre absence à réception de ce mail dans SyGAL (VAR[Url#JustificationAbsences]), qui sera transmise à la direction de votre école doctorale.</p>
<p>Bien cordialement,<br>VAR[Formation#Responsable]</p>', null, 'Formation\Provider\Template', 'default');