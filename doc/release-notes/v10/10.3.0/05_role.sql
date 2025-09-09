--
-- 10.3.0
--

--
-- Suppression des rôles sensés pointer sur une structure mais qui ne le font pas.
--

alter table utilisateur
    drop constraint utilisateur_role_id_fk;

alter table utilisateur
    add constraint utilisateur_last_role_id_fk
        foreign key (last_role_id) references role
            on delete set null;

delete
from individu_role
where role_id in (select id
                  from role
                  where code in ('GEST_UR',
                                 'RESP_UR',
                                 'GEST_ED',
                                 'RESP_ED',
                                 'A'
                                 'ADMIN',
                                 'BDD',
                                 'BU',
                                 'D',
                                 'DOCTORANT',
                                 'GEST_FORM',
                                 'HDR_CANDIDAT',
                                 'HDR_GARANT',
                                 'HDR_GEST',
                                 'K',
                                 'M',
                                 'OBSERV',
                                 'P',
                                 'R')
                    and structure_id is null);

delete
from role_privilege
where role_id in (select id
                  from role
                  where code in ('GEST_UR',
                                 'RESP_UR',
                                 'GEST_ED',
                                 'RESP_ED',
                                 'A'
                                 'ADMIN',
                                 'BDD',
                                 'BU',
                                 'D',
                                 'DOCTORANT',
                                 'GEST_FORM',
                                 'HDR_CANDIDAT',
                                 'HDR_GARANT',
                                 'HDR_GEST',
                                 'K',
                                 'M',
                                 'OBSERV',
                                 'P',
                                 'R')
                    and structure_id is null);

delete
from role
where code in ('GEST_UR',
               'RESP_UR',
               'GEST_ED',
               'RESP_ED',
               'A'
               'ADMIN',
               'BDD',
               'BU',
               'D',
               'DOCTORANT',
               'GEST_FORM',
               'HDR_CANDIDAT',
               'HDR_GARANT',
               'HDR_GEST',
               'K',
               'M',
               'OBSERV',
               'P',
               'R')
  and structure_id is null;
