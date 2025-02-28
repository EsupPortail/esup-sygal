Tests réception du Flux IA
==========================

Envoi initial A.1
------------------------

[inscr-admin-PEGASE_INSA-409145484.json](json/inscr-admin-PEGASE_INSA-409145484.json)

- individu 1214542 créé :

| id | type | civilite | nom\_usuel | nom\_patronymique | prenom1 | prenom2 | prenom3 | email | date\_naissance | nationalite | source\_code | source\_id | histo\_createur\_id | histo\_creation | histo\_modificateur\_id | histo\_modification | histo\_destructeur\_id | histo\_destruction | supann\_id | pays\_id\_nationalite | id\_ref | npd\_force | est\_substituant\_modifiable | synchro\_undelete\_enabled | synchro\_update\_on\_deleted\_enabled | apatride |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1214542 | doctorant | Mme | Pascal | Pascal | Émilie | Alphonse |  | sophie.pires@noos.fr | 2000-01-01 00:00:00.000000 | Française | PEGASE\_INSA::409145484 | 8 | 1 | 2024-10-04 15:03:24.000000 | null | null | null | null | null | null | null | null | true | true | false | false |

- doctorant 84328 créé :

| id | etablissement\_id | individu\_id | source\_code | source\_id | histo\_createur\_id | histo\_creation | histo\_modificateur\_id | histo\_modification | histo\_destructeur\_id | histo\_destruction | ine | source\_code\_sav | code\_apprenant\_in\_source | npd\_force | est\_substituant\_modifiable | synchro\_undelete\_enabled | synchro\_update\_on\_deleted\_enabled |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 84328 | 5 | 1214542 | PEGASE\_INSA::409145484 | 8 | 1 | 2024-10-04 15:03:24.000000 | 1 | 2024-10-04 16:47:47.000000 | null | null | 409145484 | null | 7810177 | null | true | true | false |

- inscription_administrative créée :

| id | source\_id | source\_code | doctorant\_id | ecole\_doct\_id | date\_inscription | date\_annulation | principale | statut\_inscription | chemin | code\_structure\_etablissement\_du\_chemin | formation | cesure | mobilite | origine | regime\_inscription\_libelle | no\_candidat | periode\_code | periode\_libelle | periode\_annee\_universitaire | histo\_creation | histo\_createur\_id | histo\_modification | histo\_modificateur\_id | histo\_destruction | histo\_destructeur\_id |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 4 | 8 | PEGASE\_INSA::ETAB97@7810166@LICENCE\_MAI&gt;LICENCE-MAI-L1@PER-2019 | 84328 | 6 | 2022-09-01 | null | true | VALIDE | LICENCE\_MAI&gt;LICENCE-MAI-L1 | ETAB97 | ut Excepteur | SANS | SANS | ECANDIDAT | sdfsfjmsdqfjmjklh | 1234 | PER-2019 | Lorem ut adipisicing do | 2022 | 2024-10-04 15:15:34 | 1 | null | null | null | null |



Envoi A.2 : A avec code apprenant modifié 7810166 => 7810177
--------------------

[inscr-admin-PEGASE_INSA-409145484_modif_code_apprenant.json](json/inscr-admin-PEGASE_INSA-409145484_modif_code_apprenant.json)

- individu non impacté

- doctorant mis à jour : code_apprenant_in_source, histo_modif

- une 2e inscription est créée pour ce doctorant
  - raison : le code apprenant a changé donc le source_code de l'ia aussi => la synchro crée un nouvel enregistrement

| id | source\_id | source\_code | doctorant\_id | ecole\_doct\_id | date\_inscription | date\_annulation | principale | statut\_inscription | chemin | code\_structure\_etablissement\_du\_chemin | formation | cesure | mobilite | origine | regime\_inscription\_libelle | no\_candidat | periode\_code | periode\_libelle | periode\_annee\_universitaire | histo\_creation | histo\_createur\_id | histo\_modification | histo\_modificateur\_id | histo\_destruction | histo\_destructeur\_id |
|:---| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 6  | 8 | PEGASE\_INSA::ETAB97@7810177@LICENCE\_MAI&gt;LICENCE-MAI-L1@PER-2019 | 84328 | 6 | 2022-09-01 | null | true | VALIDE | LICENCE\_MAI&gt;LICENCE-MAI-L1 | ETAB97 | ut Excepteur | SANS | SANS | ECANDIDAT | sdfsfjmsdqfjmjklh | 1234 | PER-2019 | Lorem ut adipisicing do | 2022 | 2024-10-04 17:03:10 | 1 | null | null | null | null |
| 4  | 8 | PEGASE\_INSA::ETAB97@7810166@LICENCE\_MAI&gt;LICENCE-MAI-L1@PER-2019 | 84328 | 6 | 2022-09-01 | null | true | VALIDE | LICENCE\_MAI&gt;LICENCE-MAI-L1 | ETAB97 | ut Excepteur | SANS | SANS | ECANDIDAT | sdfsfjmsdqfjmjklh | 1234 | PER-2019 | Lorem ut adipisicing do | 2022 | 2024-10-04 15:15:34 | 1 | null | null | null | null |



Envoi A.3 : A avec INE modifié 409145484 => 409145555
----------------

>>>>>> À REVOIR CAR L'INE N'EST PLUS LE SOURCE_CODE <<<<<<<<<<<<<<<

[inscr-admin-PEGASE_INSA-409145484_modif_ine.json](json/inscr-admin-PEGASE_INSA-409145484_modif_ine.json)

- ~~un 2e individu 1214562 est créé !~~
  - raison : l'INE a été choisi comme source_code pour l'import/synchro des individus
  - il est substitué car en doublon (1214542, 1214562 subtitués par 1214563) !

| id | type | civilite | nom\_usuel | nom\_patronymique | prenom1 | prenom2 | prenom3 | email | date\_naissance | nationalite | source\_code | source\_id | histo\_createur\_id | histo\_creation | histo\_modificateur\_id | histo\_modification | histo\_destructeur\_id | histo\_destruction | supann\_id | pays\_id\_nationalite | id\_ref | npd\_force | est\_substituant\_modifiable | synchro\_undelete\_enabled | synchro\_update\_on\_deleted\_enabled | apatride |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1214562 | doctorant | Mme | Pascal | Pascal | Émilie | Alphonse |  | sophie.pires@noos.fr | 2000-01-01 00:00:00.000000 | Française | PEGASE\_INSA::409145555 | 8 | 1 | 2024-10-04 17:07:47.000000 | null | null | 1 | 2024-10-04 17:07:47.330217 | null | null | null | null | true | false | true | false |

- un 2e doctorant 84348 est créé ! :-(

| id | etablissement\_id | individu\_id | source\_code | source\_id | histo\_createur\_id | histo\_creation | histo\_modificateur\_id | histo\_modification | histo\_destructeur\_id | histo\_destruction | ine | source\_code\_sav | code\_apprenant\_in\_source | npd\_force | est\_substituant\_modifiable | synchro\_undelete\_enabled | synchro\_update\_on\_deleted\_enabled |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 84348 | 5 | 1214563 | PEGASE\_INSA::409145555 | 8 | 1 | 2024-10-04 17:07:47.000000 | null | null | null | null | 409145555 | null | 7810166 | null | true | true | false |

- inscription mise à jour : histo_modif, doctorant_id 84328 => 84348

