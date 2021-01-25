Troubleshooting
===============

Utilisateur introuvable dans la page "Admin > Utilisateurs"
-----------

Ex: Marie LEGAY-MELEUX et Anne-Marie ROLLAND-LE CHEVREL
https://redmine.unicaen.fr/Etablissement/issues/26539

### Cause
L'individu référencé par l'utilisateur avait :
- pour source Apogée (id=3) car importé d'Apogée
- pour type 'acteur'
- était historisé car avait disparu ensuite des acteurs dans Apogée.
=> cela expliquait qu'il ne remontait pas dans la requête en BDD

### Solution
- Déhistoriser l'individu
- Mettre type à NULL
- Changer sa source à SYGAL (id=1)
- Renseigner le supannId si besoin


Rôle utilisateur non visible sur la page utilisateur mais visibles dans la fiche structure
------------------------------------------------------------------------------------------

### Cause
La personne existe sans doute en double dans la table INDIVIDU et le rôle est porté par 
la "version historisée" de l'individu.

### Solution
Suivre la procédure suivante.

```sql
--
-- Etape 1 : identifier les INDIVIDU_ROLE ayant un INDIVIDU historisé.
--
select distinct *
from individu i
where exists (
        select *
        from INDIVIDU_ROLE ir
                 join role r on ir.ROLE_ID = r.ID and r.STRUCTURE_ID is not null
        where i.ID = ir.INDIVIDU_ID
    )
  and i.HISTO_DESTRUCTION is not null

--
-- Etape 2 : lister tous les individus présents dans les résultats de l'étape 1.
--
with tmp as (
    select distinct *
    from individu i
    where exists (
            select *
            from INDIVIDU_ROLE ir
                     join role r on ir.ROLE_ID = r.ID and r.STRUCTURE_ID is not null
            where i.ID = ir.INDIVIDU_ID
        )
      and i.HISTO_DESTRUCTION is not null
)
select i.ID,
       i.SUPANN_ID,
       i.SOURCE_CODE,
       i.SOURCE_ID,
       i.HISTO_DESTRUCTION,
       i.TYPE,
       i.CIVILITE,
       i.NOM_USUEL,
       i.NOM_PATRONYMIQUE,
       i.PRENOM1,
       i.PRENOM2,
       i.PRENOM3,
       i.EMAIL,
       i.DATE_NAISSANCE,
       i.NATIONALITE,
       i.HISTO_CREATION,
       i.HISTO_MODIFICATION,
       i.ETABLISSEMENT_ID
from individu i
     join tmp
         on upper(tmp.NOM_USUEL) = upper(i.NOM_USUEL) and upper(tmp.PRENOM1) = upper(i.PRENOM1)
             --and i.SOURCE_ID = 5 -- URN
order by i.NOM_USUEL, i.PRENOM1
;

--
-- Etape 3 : pour chaque individu en double/triple dans les résultats précédents,
-- chercher la ligne correspondant au "bon individu".
--

--
-- Etape 4 : pour un individu, remplacer dans INDIVIDU_ROLE les mauvais (id d') individus par le bon.
-- Ex :
--
update INDIVIDU_ROLE set INDIVIDU_ID = 802407 where INDIVIDU_ID in (863864,
    9195,
        802407
    ) ;
```

Pour mémoire :
```sql
--
-- ACTEUR pointant vers un individu historisé.
--
select *
from individu i
where exists (
        select id
        from acteur ir
        where i.ID = ir.INDIVIDU_ID
        and ir.HISTO_DESTRUCTION is null
    )
  and i.HISTO_DESTRUCTION is not null
;
```
