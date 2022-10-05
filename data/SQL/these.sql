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


/**
 * Rattachement à une thèse destination de tout ce qui a été saisi sur une thèse source.
 */
create or replace function transfert_these(fromTheseId bigint, toTheseId bigint) returns void
    language plpgsql
as $$
BEGIN
    -- select 'update '||rpad(table_name, 35)||' set '||column_name||' = toTheseId where '||column_name||' = fromTheseId ;' from information_schema.columns
-- where column_name ilike '%these_id%' and
--         table_name not ilike 'v\_%' and
--         table_name not ilike 'src_%' and
--         table_name not ilike 'tmp_%';

    update soutenance_proposition   set histo_destruction = now(), histo_destructeur_id = 1 where these_id = toTheseId ;

    update attestation              set these_id = toTheseId where these_id = fromTheseId ;
    update diffusion                set these_id = toTheseId where these_id = fromTheseId ;
    update fichier_these            set these_id = toTheseId where these_id = fromTheseId ;
    update metadonnee_these         set these_id = toTheseId where these_id = fromTheseId ;
    update rapport                  set these_id = toTheseId where these_id = fromTheseId ;
    update rdv_bu                   set these_id = toTheseId where these_id = fromTheseId ;
    update soutenance_intervention  set these_id = toTheseId where these_id = fromTheseId ;
    update soutenance_proposition   set these_id = toTheseId where these_id = fromTheseId ;
    update validation                          set these_id = toTheseId where these_id = fromTheseId ;
END;
$$;


-- Rattachement à une thèse destination de tout ce qui est rattaché à une thèse source
select transfert_these(from_id, to_id) ;
