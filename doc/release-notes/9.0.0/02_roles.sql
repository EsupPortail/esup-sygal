--
-- 9.0.0
--

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
-- Troncature préalable du structure.code nécessaire (cf. role__create_roles_from_profils_for_structure()).
--
update structure set code = substring(code, 1, 16) where length(code) > 16;

--
-- Création des rôles typés 'etablissement', en se basant sur les profils
--
select e.id etablissement_id, e.structure_id, role__create_roles_from_profils_for_structure(s.id)
from etablissement e
         join structure s on e.structure_id = s.id and s.histo_destruction is null
         join type_structure ts on s.type_structure_id = ts.id and ts.code = 'etablissement'
where e.histo_destruction is null and e.est_etab_inscription = true;

--
-- Création des rôles typés 'ecole-doctorale', en se basant sur les profils
--
select e.id ecole_doct_id, e.structure_id, role__create_roles_from_profils_for_structure(s.id)
from ecole_doct e
         join structure s on e.structure_id = s.id and s.histo_destruction is null
         join type_structure ts on s.type_structure_id = ts.id and ts.code = 'ecole-doctorale'
where e.histo_destruction is null;

--
-- Création des rôles typés 'ecole-doctorale', en se basant sur les profils
--
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
