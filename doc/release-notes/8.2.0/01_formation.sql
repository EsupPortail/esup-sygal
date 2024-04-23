-- Module Formation

--Ajout d'un paramètre pour spécifier le délai pour une annulation d'inscription
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur,
                                                ordre)
VALUES (20, 2, 'DELAI_ANNULATION_INSCRIPTION', 'Delai annulation', null, 'Number', '21', 20) ON CONFLICT DO NOTHING;


--Ajout d'un privilège pour voir le lieu des séances d'une session
INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (SELECT 'voir_lieu', 'Voir le lieu de la session', 8)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
         JOIN categorie_privilege cp ON cp.CODE = 'formation_session'
WHERE NOT EXISTS (SELECT 1
                  FROM PRIVILEGE p
                  WHERE p.CODE = d.code);
--
-- Accord du privilège aux profils ADMIN_TECH, MAISON DU DOCTORAT, FORMATEUR, RESPONSABLE_ED, DOCTORANT.
--
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'formation_session', 'voir_lieu')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH',
                                           'BDD',
                                           'FORMATEUR',
                                           'RESP_ED',
                                           'DOCTORANT'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
;

--
-- Accord de privilèges au profil Formateur pour accéder à l'index de ses formations.
--
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'formation', 'index_formateur')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'FORMATEUR'
    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
;

--Ajout d'une macro pour afficher la dénomination du doctorant (civilité+nom Patronymique+prénom)
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('Doctorant#DenominationPatronymique',
        '<p>Retourne la dénomination du doctorant (civilité+nom Patronymique+prénom)</p>',
        'doctorant', 'getDenominationPatronymique')
    ON CONFLICT DO NOTHING;

--Changement en conséquence du template
UPDATE public.unicaen_renderer_template SET document_corps = '<h1 style="text-align: center;">Attestation de suivi de formation</h1><p></p><p>Bonjour ,</p><p>Je, sousigné·e, certifie que <strong>VAR[Doctorant#DenominationPatronymique]</strong> a participé à la formation <strong>VAR[Formation#Libelle]</strong> qui s''est déroulée sur la période du VAR[Session#Periode] (Durée : VAR[Session#Duree] heures).</p><p>VAR[Doctorant#DenominationPatronymique] a suivi VAR[Inscription#DureeSuivie] heure·s de formation.</p><p style="text-align: right;">Le·la responsable du module<br />VAR[Session#Responsable]<br /><br /></p><p style="text-align: right;">VAR[Signature#EtablissementFormation]</p>'
where code = 'FORMATION_ATTESTATION';

-- Template mail lors de la création d'une formation spécifique à destination des doctorants de l'ED
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('FORMATION_FORMATION_SPECIFIQUE_OUVERTE',
        '<p>Mail envoyé au doctorant·e appartenant aux structures valides déclarées lorsqu''une formation spécifique passe à l''état Inscription ouverte</p>', 'mail',
        'Nouvelle formation spécifique ouverte dans votre ED', e'<p>Bonjour,</p>
<p>La formation spécifique <strong>VAR[Formation#Libelle]</strong> vient d''ouvrir. Si vous voulez plus d''informations, rendez-vous dans l''application ESUP-SyGAL, onglet <em>''mes formations''.</em></p>
<p>En vous souhaitant une bonne journée,<br/>VAR[Formation#Responsable]</p>',
        null, 'Formation\Provider\Template') ON CONFLICT DO NOTHING;