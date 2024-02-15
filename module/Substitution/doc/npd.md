NPD
===

Procédure pour tester une nouvelle formule/fonction de calcul du NPD.
---------------------------------------------------------------------------------------------------------------

### 1) Création de la nouvelle fonction de calcul du NPD sous un autre nom normalisé : substit_npd_xxxx_new()

Exemple doctorant :

```postgresql
create or replace function substit_npd_doctorant_new(doctorant doctorant) returns varchar
language plpgsql
as
$$declare
    v_npd_individu varchar(256);
begin
    --
    -- Fonction de calcul du "NPD".
    --
    -- Important : Le NPD d'un doctorant "inclut" celui de l'individu lié. Cela permet de garantir qu'un doctorant
    -- ne peut être considéré comme un doublon si son individu lié ne l'est pas lui-même.
    --
    -- ATTENTION ! Modifier le calcul du NPD n'est pas anodin car cela peut remettre en question les
    -- substitutions existantes (table 'xxxx_substit').
    -- Cf. procédures "substit_create_view_for_substit_npd_function()" et "substit_migrate_to_substit_npd_function()".
    --
    
    select substit_npd_individu(i.*) into v_npd_individu from individu i where id = doctorant.individu_id;
    
    -- NB : bricolage bidon pour simuler que certains NPD ne sont plus les mêmes pour certains doctorants
    return v_npd_individu || ',' || doctorant.ine || case when doctorant.id % 23 = 0 then '/' else '' end;
end
$$;
```


### 2) Sauvegarde de la fonction de calcul du NPD existante : substit_npd_xxxx => substit_npd_xxxx_old

Exemple doctorant :

```postgresql
alter function substit_npd_doctorant(doctorant) rename to substit_npd_doctorant_old;
```


### 3) La nouvelle fonction devient la fonction officielle : substit_npd_xxxx_new => substit_npd_xxxx

Exemple doctorant :

```postgresql
alter function substit_npd_doctorant_new(doctorant) rename to substit_npd_doctorant;
```


### 4) Création de la vue/requête listant les substitutions pour lesquelles tous les substitués n'ont pas le même NPD

Exemple doctorant :

```postgresql
create or replace view v_substit_check_distinct_npds_doctorant as
    with substitutions_avec_des_npd_differents as (
        select to_id, array_length(array_agg(distinct coalesce(d.npd_force, substit_npd_doctorant(d))), 1) nb_npd_differents
        from substit_doctorant subs
        join doctorant d on subs.from_id = d.id
        group by to_id
        having array_length(array_agg(distinct coalesce(d.npd_force, substit_npd_doctorant(d))), 1) > 1
    )
    select substit_npd_doctorant_old(d), substit_npd_doctorant(d), d.npd_force, nb_npd_differents, subs.from_id, subs.to_id, subs.npd
    from substitutions_avec_des_npd_differents c
    join substit_doctorant subs on subs.to_id = c.to_id
    join doctorant d on subs.from_id = d.id
    order by subs.to_id, subs.from_id ;

comment on view v_substit_check_distinct_npds_doctorant is
    'Requête listant les substitutions pour lesquelles tous les substitués n''ont pas le même NPD (que celui-ci soit
calculé ou forcé). Le nombre de NPD distincts est compté et seules les substitutions ayant plus de 2 NPD de
substitués distincts sont retenues.

Utile pour tester la nouvelle formule de calcul de NPD : est-ce que si l''on
change la fonction de calcul du NPD, les substitutions existantes peuvent être conservées telles quelles ?
Une substitution peut être conservée telle quelle si tous ses substitués ont le même NPD malgré le changement
de la formule de calcul.

Pré-requis :
- la nouvelle fonction de calcul du NPD doit exister *sous un autre nom que celle existante* :
  "substit_npd_xxxx()" (exemple pour les doctorants : "substit_npd_doctorant()").

Le résultat de cette requête doit être épluché à la main pour savoir si une substitution doit être modifiée,
conservée ou supprimée.';
```


### 5) Interrogation de la vue listant les substitutions existantes éventuellement remise en cause

Exemple doctorant :

```postgresql
select * from v_substit_check_distinct_npds_doctorant ;
```


### 6) Examen manuel du résultat de la requête précédente pour décider du devenir des substitutions.


### 7) Restauration de l'ancienne fonction de calcul du NPD

Exemple doctorant :

```postgresql
alter function substit_npd_doctorant(doctorant) rename to substit_npd_doctorant_new;
alter function substit_npd_doctorant_old(doctorant) rename to substit_npd_doctorant;
```



Lancement de la migration des substitutions existantes vers le nouveau calcul du NPD
---------------------------------------------------------------------------------------------------------------

### 1) Remplacement de la fonction de calcul du NPD existante

Exemple doctorant :

```postgresql
create or replace function substit_npd_doctorant(doctorant doctorant) returns varchar
language plpgsql
as
$$declare
    v_npd_individu varchar(256);
begin
    --
    -- Fonction de calcul du "NPD".
    --
    -- Important : Le NPD d'un doctorant "inclut" celui de l'individu lié. Cela permet de garantir qu'un doctorant
    -- ne peut être considéré comme un doublon si son individu lié ne l'est pas lui-même.
    --
    -- ATTENTION ! Modifier le calcul du NPD n'est pas anodin car cela peut remettre en question les
    -- substitutions existantes (table 'xxxx_substit').
    -- Cf. procédures "substit_create_view_for_substit_npd_function()" et "substit_migrate_to_substit_npd_function()".
    --

    select substit_npd_individu(i.*) into v_npd_individu from individu i where id = doctorant.individu_id;

    return v_npd_individu || ',' || normalized_string(trim(doctorant.ine));
end
$$;
```


### 2) Création de la procédure de migration de toutes les substitutions existantes

```postgresql
drop function if exists substit_migrate_to_substit_npd_function;

create or replace function substit_migrate_to_substit_npd_function(type character varying,
                                                                   simulate bool = true) returns void
language plpgsql
as
$$declare
    --
    -- Procédure permettant de "migrer" toutes les substitutions existantes (du type spécifié) vers une nouvelle
    -- façon de calculer le NPD (spécifiée par le nom de la nouvelle fonction de calcul).
    --
    -- *
    -- ATTENTION ! Si vous utilisez cette procédure, cela veut dire que vous DECIDEZ que toutes les substitutions
    -- existantes ont toujours lieu d'être malgré le nouveau calcul de NPD. Par exemple, si la nouvelle formule de
    -- calcul fait que les NPD du genre 'hochon_paule_19851231,123W1K03024' deviennent
    -- 'hochon_paule_19851231,123w1k03024' (i.e. simple changement de casse de la partie finale), vous
    -- savez que cela ne remet pas en cause les substitutions existantes et vous pouvez utilisez cette procédure.
    -- Avant d'utilisez cette procédure, utilisez la vue "v_substit_check_distinct_npds_doctorant"
    -- listant les substitutions existantes éventuellement remises en cause par le nouveau calcul du NPD.
    -- *
    --
    -- La stratégie de cette procédure est de parcourir les substitutions une à une (table substit_xxxx) pour :
    --   - corriger leur NPD (substit_xxxx.npd) avec le nouveau NPD calculé à partir du 1er substitué *sans NPD forcé*
    --     rencontré ;
    --   - en cas de substitution manuelle, corriger le NPD forcé du substitué avec ce nouveau NPD calculé ;
    --   - loguer chaque correction de NPD effectuée (raise notice).
    --
    -- NB : Les triggers liés aux tables 'substit_xxxx' et 'xxxx' sont désactivés au début de la procédure
    -- puis réactivés à la fin.
    --
    -- Exemples d'utilisation :
    --   - Mode simulation :
    --          select substit_migrate_to_substit_npd_function('doctorant', 'substit_npd_doctorant', true);
    --   - Pour de vrai (par défaut) :
    --          select substit_migrate_to_substit_npd_function('doctorant', 'substit_npd_doctorant');
    --
    -- Exemple de log :
    --      Substitution doctorant 48153 => 82371 : substit_doctorant.npd 'terieur_alain_19950909,4567001817T' corrigé en 'terieur_alain_19950909,4567001817t'
    --      Substitution doctorant 58347 => 82371 : substit_doctorant.npd 'terieur_alain_19950909,4567001817T' corrigé en 'terieur_alain_19950909,4567001817t'
    --      Substitution doctorant 33663 => 82409 : substit_doctorant.npd 'hochon_paule_19851231,123W1K03024' corrigé en 'hochon_paule_19851231,123w1k03024'
    --      Substitution doctorant 33241 => 82409 manuelle : doctorant.npd_force 'hochon_paule_19851231,123W1K03024' corrigé en 'hochon_paule_19851231,123w1k03024'
    --      Substitution doctorant 33241 => 82409 : substit_doctorant.npd 'hochon_paule_19851231,123W1K03024' corrigé en 'hochon_paule_19851231,123w1k03024'
    --

    v_cursor_substit_xxxx refcursor;
    v_substit_xxxx record;
    to_id_prec bigint = null;
    v_npd varchar(256);
begin
    -- désactivation des triggers
    execute format('alter table %s disable trigger substit_trigger_%s', type, type);
    execute format('alter table substit_%s disable trigger substit_trigger_on_substit_%s', type, type);
    
        -- Parcours des substitutions existantes
        --   NB : pour chaque substitution (to_id), tri des substitués pour avoir en dernier ceux dont le NPD est forcé.
        open v_cursor_substit_xxxx for execute
            format('select d.npd_force, subs.*
                    from substit_%s subs
                    join %s d on subs.from_id = d.id
                    order by to_id, d.npd_force nulls first', type, type);
        fetch next from v_cursor_substit_xxxx into v_substit_xxxx;
        while found loop
            if v_substit_xxxx.to_id = to_id_prec then
                ----  On est au sein de la même substitution (to_id) -------------------------------------------------------

                if v_substit_xxxx.npd_force is not null then
                    -- Cas d'un substitué dont le NPD est forcé (on ne peut donc pas calculer le NPD à partir de lui).
                    -- Le NPD forcé du substitué doit être corrigé : valeur du NPD caclulé du 1er substitué rencontré.
                    if simulate = false then
                        execute format('update %s set npd_force = %L where id = %s', type, v_npd, v_substit_xxxx.from_id);
                    end if;
                    raise notice 'Substitution % % => % manuelle : %.npd_force ''%'' corrigé en ''%''', type, v_substit_xxxx.from_id, v_substit_xxxx.to_id, type, v_substit_xxxx.npd_force, v_npd;
                end if;
            else
                ---- On est passé à une autre substitution (to_id) ---------------------------------------------------------

                if v_substit_xxxx.npd_force is not null then
                    -- Le premier substitué rencontré possède un NPD forcé : problème !
                    -- Si on arrive ici c'est que, pour la substitution courante, on n'a rencontré aucun substitué sans NPD forcé
                    -- donc on ne dispose d'aucune valeur de NPD nous permettant de corriger le NPD forcé du substitué.
                    raise exception 'Cas non supporté : la substitution to_id = %s ne possède aucun substitué sans NPD forcé', v_substit_xxxx.to_id;
                end if;

                -- Calcul du NPD à partir de ce 1er substitué n'ayant pas de NPD forcé.
                execute format('select substit_npd_%s(d) from %s d where d.id = %s', type, type, v_substit_xxxx.from_id) into v_npd;
            end if;

            -- Correction du NPD de la substitution courante avec le NPD calculé, si nécessaire.
            if v_substit_xxxx.npd <> v_npd then
                if simulate = false then
                    execute format('update substit_%s subs set npd = %L where id = %s', type, v_npd, v_substit_xxxx.id);
                end if;
                raise notice 'Substitution % % => % : substit_%.npd ''%'' corrigé en ''%''', type, v_substit_xxxx.from_id, v_substit_xxxx.to_id, type, v_substit_xxxx.npd, v_npd;
            end if;

            to_id_prec = v_substit_xxxx.to_id;
            fetch next from v_cursor_substit_xxxx into v_substit_xxxx;
        end loop;
    close v_cursor_substit_xxxx;

    -- réactivation des triggers
    execute format('alter table %s enable trigger substit_trigger_%s', type, type);
    execute format('alter table substit_%s enable trigger substit_trigger_on_substit_%s', type, type);

    raise notice 'Procédure terminée (simulation %)', case when simulate = true then 'ACTIVÉE' else 'DESACTIVÉE' end;
end
$$;
```


### 3) Simulation

Exemple doctorant :

```postgresql
select substit_migrate_to_substit_npd_function('doctorant');
```


### 4) Pour de vrai

Exemple doctorant :

```postgresql
select substit_migrate_to_substit_npd_function('doctorant', false); rollback;
```
