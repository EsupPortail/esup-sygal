--
-- Ajout des privilèges liés à la suppression d'une proposition de soutenance
--
insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (
    select 34, 'proposition-supprimer', 'Supprimer l''ensemble des informations d''une proposition'
)
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d join CATEGORIE_PRIVILEGE cp on cp.CODE = 'soutenance';

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'soutenance', 'proposition-supprimer')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH',
                                           'GEST_ED'
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
-- Ajout d'un template pour la notification des acteurs de la thèse lors de la suppression des informations d'une proposition
--
INSERT INTO public.unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                              document_css, namespace)
VALUES ('PROPOSITION_SUPPRESSION',
        '<p>Suppression des informations concernant la proposition de soutenance</p>', 'mail',
        'Votre proposition de soutenance a été supprimée', e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL</p>
<p>Votre proposition de soutenance vient d''être <strong>supprimée.</strong></p>
<p>Veuillez vous rapprocher de la/le gestionnaire de votre école doctorale afin d''avoir plus d''informations à ce sujet.</p>',
        null, 'Soutenance\Provider\Template');