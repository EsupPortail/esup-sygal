INSERT INTO THESARD    (
    ID,
    CIVILITE_ID,
    NOM_USUEL,
    PRENOM,
    NOM_PATRONYMIQUE,
    DATE_NAISSANCE,
    TEL,
    EMAIL,
    SOURCE_ID,
    SOURCE_CODE,
    histo_createur_id,
    histo_modificateur_id
)
values (
    thesard_id_seq.nextval,
    1,
    'HOCHON',
    'Paule',
    'TERGEIST',
    sysdate,
    '0102030405',
    'paule.hochon@mail.fr',
    1,
    thesard_id_seq.currval,
    1,
    1
);


INSERT INTO THESE
(
    ID,
    THESARD_ID,
    NOM,
    HISTO_CREATEUR_ID,
    HISTO_MODIFICATEUR_ID
)
VALUES
(
    THESE_id_seq.nextval,
    thesard_id_seq.currval,
    'Test',
    1,
    1
);


-- selection des doctorants dont la thèse a plus d'un acteur
with c as (
    select a.these_id, count(a.id) nb, t.titre
    from acteur a
        join these t on a.these_id = t.id
    group by a.these_id, t.titre
)
select c.nb, td.*
from thesard td
    join these t on t.thesard_id = td.id
    join c on c.these_id = t.id
where c.nb > 1
order by c.nb desc, nom_usuel
;


-- selection des thesard ayant plus d'une thèse
select d.id, d.source_code, d.nom_usuel, t.titre
from thesard d
    join these t on t.thesard_id = d.id
where d.id in (
    select thesard_id
    from these
    --where etat_these = 'E'
    group by thesard_id
    having count(id) > 1
);



-- Rattachement à une thèse destination de tout ce qui est rattaché à une thèse source
update rapport          set these_id = 49033 where these_id = 28284;
update attestation      set these_id = 49033 where these_id = 28284;
update DIFFUSION        set these_id = 49033 where these_id = 28284;
update fichier_these    set these_id = 49033 where these_id = 28284;
update METADONNEE_THESE set these_id = 49033 where these_id = 28284;
update RDV_BU           set these_id = 49033 where these_id = 28284;
update VALIDATION       set these_id = 49033 where these_id = 28284;
