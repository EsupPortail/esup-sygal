--
-- 9.0.0
--

--
-- Nouvelle colonne dans profil.
--

alter table profil add these_dep boolean default false not null;
comment on column profil.these_dep is 'Indique si ce profil s''adresse à un rôle thèse-dépendant ';

update profil
set these_dep = true
where role_id in (
                  'A', -- Rapporteur Absent
                  'D', -- Directeur de thèse
                  'DOCTORANT', -- Doctorant
                  'K', -- Co-directeur
                  'M', -- Membre
                  'P', -- Président du jury
                  'R' -- Rapporteur
    );


--
-- Profils devant être typés 'etablissement' (certains l'étaient déjà)
--
update profil
set structure_type = (select id from type_structure where code = 'etablissement')
where role_id in (
                  'A', -- Rapporteur Absent
                  'ADMIN', -- Administrateur
                  'BDD', -- Bureau des doctorats
                  'BU', -- Bibliothèque universitaire
                  'D', -- Directeur de thèse
                  'DOCTORANT', -- Doctorant
                  'GEST_FORM', -- Gestionnaire de formation
                  'K', -- Co-directeur
                  'M', -- Membre
                  'OBSERV', -- Observateur
                  'P', -- Président du jury
                  'R' -- Rapporteur
    );


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
-- Vraie contrainte d'unicité dans la table role.
--

drop index if exists role_code_structure_id_uindex;
alter table role add constraint role_code_structure_uindex unique(code, structure_id);


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
        s.id,
        s.type_structure_id
    from structure s
             join type_structure ts on s.type_structure_id = ts.id
             join profil p on p.structure_type = s.type_structure_id
    where s.id = p_structure_id
    on conflict on constraint role_code_structure_uindex do update
        set these_dep = excluded.these_dep,
            libelle = excluded.libelle
    ;

    --
    -- Création des profil_to_role manquants
    --
    insert into profil_to_role(profil_id, role_id)
    select p.id, r.id
    from role r
             join profil p on p.role_id = r.code
    where r.structure_id = p_structure_id
      and not exists (select * from profil_to_role p2r where p2r.role_id = r.id)
    order by code;

    --
    -- Attribution automatique des privilèges aux rôles, d'après ce qui est spécifié dans :
    --   - PROFIL_TO_ROLE (profils appliqués à chaque rôle) et
    --   - PROFIL_PRIVILEGE (privilèges accordés à chaque profil).
    --
    insert into role_privilege (role_id, privilege_id)
    select p2r.role_id, pp.privilege_id
    from profil_to_role p2r
             join role r on p2r.role_id = r.id and r.structure_id = p_structure_id
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

-- Création/màj des rôles typés 'ecole-doctorale' pour chaque ED
select e.id ecole_doct_id, e.structure_id, role__create_roles_from_profils_for_structure(s.id)
from ecole_doct e
         join structure s on e.structure_id = s.id and s.histo_destruction is null
         join type_structure ts on s.type_structure_id = ts.id and ts.code = 'ecole-doctorale'
where e.histo_destruction is null;

-- Création/màj des rôles typés 'ecole-doctorale' pour chaque UR
select e.id unite_rech_id, e.structure_id, role__create_roles_from_profils_for_structure(s.id)
from unite_rech e
         join structure s on e.structure_id = s.id and s.histo_destruction is null
         join type_structure ts on s.type_structure_id = ts.id and ts.code = 'unite-recherche'
where e.histo_destruction is null;

/* verifs
select * from role where structure_id in (
    select s.id
    from etablissement e
    join structure s on e.structure_id = s.id and s.histo_destruction is null
    join type_structure ts on s.type_structure_id = ts.id and ts.code = 'etablissement'
    where e.histo_destruction is null and e.est_etab_inscription = true
)
order by histo_creation desc;

select * from role where structure_id in (
    select s.id
    from ecole_doct e
    join structure s on e.structure_id = s.id and s.histo_destruction is null
    join type_structure ts on s.type_structure_id = ts.id and ts.code = 'ecole-doctorale'
    where e.histo_destruction is null
)
order by histo_creation desc;

select * from role where structure_id in (
    select s.id
    from unite_rech e
    join structure s on e.structure_id = s.id and s.histo_destruction is null
    join type_structure ts on s.type_structure_id = ts.id and ts.code = 'unite-recherche'
    where e.histo_destruction is null
)
order by histo_creation desc;
*/
