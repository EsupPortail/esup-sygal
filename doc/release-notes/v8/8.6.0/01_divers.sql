--
-- 8.6.0
--

-- nouvelle colonne individu.apatride
alter table individu add column apatride boolean default false not null;


CREATE or replace FUNCTION privilege__update_role_privilege() RETURNS void
    LANGUAGE plpgsql
AS $$
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