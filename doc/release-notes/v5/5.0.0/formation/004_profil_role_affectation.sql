-- PROFIL --------------------------------------------------------------------------------------------------------------

INSERT INTO profil (id, libelle, role_id)
VALUES (nextval('profil_id_seq'), 'Formateur·trice', 'FORMATEUR');
INSERT INTO profil (id, libelle, role_id)
VALUES (nextval('profil_id_seq'), 'Gestionnaire de formation', 'GEST_FORMATION');

-- ROLE ----------------------------------------------------------------------------------------------------------------

INSERT INTO role (id, code, libelle, source_code, source_id, role_id, is_default, ldap_filter, attrib_auto, these_dep, type_structure_dependant_id, ordre_affichage, histo_createur_id)
select nextval('role_id_seq'), 'FORMATEUR', 'Formateur·trice', 'SYGAL::FORMATEUR', 1, 'Formateur·trice', false, null, false, false, 1, 'formation_aab', 1;
INSERT INTO role (id, code, libelle, source_code, source_id, role_id, is_default, ldap_filter, attrib_auto, these_dep, type_structure_dependant_id, ordre_affichage, histo_createur_id)
select nextval('role_id_seq'), 'GEST_FORMATION', 'Gestionnaire de formation', 'SYGAL::GEST_FORMATION', 1, 'Gestionnaire de formation', false, null, false, false, 1, 'formation_aaa', 1;

-- AFFECTATION DES RÔLES AUX PROFILS -----------------------------------------------------------------------------------

insert into PROFIL_TO_ROLE (PROFIL_ID, ROLE_ID)
with data(PROFIL_CODE, ROLE_ROLE_ID) as (
    select 'FORMATEUR', 'Formateur·trice' union
    select 'GEST_FORMATION', 'Gestionnaire de formation'
)
select pr.id, r.id
from data
join PROFIL pr on pr.ROLE_ID = data.PROFIL_CODE
join role r on r.ROLE_ID = data.ROLE_ROLE_ID
where not exists (
    select * from PROFIL_TO_ROLE where PROFIL_ID = pr.id and ROLE_ID = r.id
);

-- AFFECTATION DES PRIVILÈGES AUX PROFILS ADMIN TECH / GESTIONNAIRE DE FORMATION ---------------------------------------

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation'::text, 'index'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
join PROFIL on profil.ROLE_ID in ('ADMIN_TECH','GEST_FORMATION')
join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
    select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_module'::text, 'index'::text union
    select 'formation_module'::text, 'afficher'::text union
    select 'formation_module'::text, 'ajouter'::text union
    select 'formation_module'::text, 'modifier'::text union
    select 'formation_module'::text, 'historiser'::text union
    select 'formation_module'::text, 'supprimer'::text union
    select 'formation_module'::text, 'catalogue'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH','GEST_FORMATION')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_formation'::text, 'index'::text union
    select 'formation_formation'::text, 'afficher'::text union
    select 'formation_formation'::text, 'ajouter'::text union
    select 'formation_formation'::text, 'modifier'::text union
    select 'formation_formation'::text, 'historiser'::text union
    select 'formation_formation'::text, 'supprimer'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH','GEST_FORMATION')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_session'::text, 'index'::text union
    select 'formation_session'::text, 'afficher'::text union
    select 'formation_session'::text, 'ajouter'::text union
    select 'formation_session'::text, 'modifier'::text union
    select 'formation_session'::text, 'historiser'::text union
    select 'formation_session'::text, 'supprimer'::text union
    select 'formation_session'::text, 'gerer_inscription'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH','GEST_FORMATION')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_seance'::text, 'index' union
    select 'formation_seance'::text, 'afficher' union
    select 'formation_seance'::text, 'ajouter' union
    select 'formation_seance'::text, 'modifier' union
    select 'formation_seance'::text, 'historiser' union
    select 'formation_seance'::text, 'supprimer' union
    select 'formation_seance'::text, 'renseigner_presence'
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH','GEST_FORMATION')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    );

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_inscription'::text, 'index'::text union
    select 'formation_inscription'::text, 'afficher'::text union
    select 'formation_inscription'::text, 'ajouter'::text union
    select 'formation_inscription'::text, 'historiser'::text union
    select 'formation_inscription'::text, 'supprimer'::text union
    select 'formation_inscription'::text, 'gerer_liste'::text union
    select 'formation_inscription'::text, 'generer_convocation'::text union
    select 'formation_inscription'::text, 'generer_attestation'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH','GEST_FORMATION')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_enquete'::text, 'question_afficher'::text union
    select 'formation_enquete'::text, 'question_ajouter'::text union
    select 'formation_enquete'::text, 'question_modifier'::text union
    select 'formation_enquete'::text, 'question_historiser'::text union
    select 'formation_enquete'::text, 'question_supprimer'::text union
    select 'formation_enquete'::text, 'reponse_repondre'::text union
    select 'formation_enquete'::text, 'reponse_resultat'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH','GEST_FORMATION')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
);

-- AFFECTATION DES PRIVILÈGES AUX PROFILS DOCTORANT --------------------------------------------------------------------

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation'::text, 'index_doctorant'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('DOCTORANT')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_module'::text, 'afficher'::text union
    select 'formation_module'::text, 'catalogue'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('DOCTORANT')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_formation'::text, 'afficher'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('DOCTORANT')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_inscription'::text, 'ajouter'::text union
    select 'formation_inscription'::text, 'historiser'::text union
    select 'formation_inscription'::text, 'supprimer'::text union
    select 'formation_inscription'::text, 'generer_convocation'::text union
    select 'formation_inscription'::text, 'generer_attestation'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('DOCTORANT')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    );

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_enquete'::text, 'reponse_repondre'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('DOCTORANT')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    );

-- AFFECTATION DES PRIVILÈGES AUX PROFILS FORMATEUR --------------------------------------------------------------------

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'index_formateur'::text, 'index'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('FORMATEUR')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_module'::text, 'afficher'::text union
    select 'formation_module'::text, 'catalogue'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('FORMATEUR')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_formation'::text, 'afficher'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('FORMATEUR')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_session'::text, 'afficher'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('FORMATEUR')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_seance'::text, 'afficher' union
    select 'formation_seance'::text, 'renseigner_presence'
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('FORMATEUR')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    );

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'formation_enquete'::text, 'reponse_resultat'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('FORMATEUR')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    );

-- REAPPLICATION AUX ROLES ---------------------------------------------------------------------------------------------

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (
        select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id
    )
;







