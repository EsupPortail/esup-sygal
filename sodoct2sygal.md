# Transformation de SoDoct en SYGAL

## Base de données 

### Création des tables

|Fait|Nom|Commentaires|
|----|---|------------|
|x|ACTEUR|
|x|ATTESTATION|
|x|CATEGORIE_PRIVILEGE|
|x|CONTENU_FICHIER|
|x|DIFFUSION|
|x|ECOLE_DOCT|
|x|ECOLE_DOCT_IND|
|x|ETAB|renommée ETABLISSEMENT|
|x|FAQ|
|x|FICHIER|
|x|IMPORT_OBS_NOTIF|
|x|IMPORT_OBS_RESULT_NOTIF|
|x|IMPORT_OBSERV|
|x|IMPORT_OBSERV_RESULT|
|x|INDIVIDU|
|x|METADONNEE_THESE|
|x|NOTIF|Module Notification|
|x|NOTIF_RESULT|Module Notification|
| |MV_RECHERCHE_THESE|Aïe, utilise des vues materialisées!|
|x|NATURE_FICHIER|
|x|PRIVILEGE|
|x|RDV_BU|
|x|ROLE_PRIVILEGE|pointe sur une table ROLE qui a changé!|
|x|SOURCE|
|x|THESARD|renommée DOCTORANT|
|x|THESARD_COMPL|renommée DOCTORANT_COMPL|
|x|THESE|
|x|TYPE_VALIDATION|
|x|UNITE_RECH|
|x|UNITE_RECH_IND|
|x|USER_ROLE|devenue ROLE|
|x|USER_ROLE_LINKER|renommé UTILISATEUR_ROLE|
|x|UTILISATEUR|
|x|VALIDATION|
|x|VALIDITE_FICHIER|
|x|VARIABLE|
|x|VERSION_FICHIER|
|x|WF_ETAPE|
|-----|-----|-----|
| |MV_ACTEUR|?|
| |MV_INDIVIDU|?|
| |MV_THESARD|?|
| |MV_THESE|?|
| |MV_USER_ROLE|?|
| |MV_VARIABLE|?|
| |ENV|Abandonnée|
| |PARAMETRE|semble inutilisée|
| |SYNC_LOG|créée auto par import|
| |WF_ETAPE_DEP|Pas utilisée|

### Création des vues

|Fait|Nom|Commentaires|
|----|---|------------|
|x|V_SITU_ARCHIVAB_VA|
|x|V_SITU_ARCHIVAB_VAC|
|x|V_SITU_ARCHIVAB_VO|
|x|V_SITU_ARCHIVAB_VOC|
|x|V_SITU_ATTESTATIONS|
|x|V_SITU_ATTESTATIONS_VOC|
|x|V_SITU_AUTORIS_DIFF_THESE|
|x|V_SITU_AUTORIS_DIFF_THESE_VOC|
|x|V_SITU_DEPOT_PV_SOUT|
|x|V_SITU_DEPOT_RAPPORT_SOUT|
|x|V_SITU_DEPOT_VA|
|x|V_SITU_DEPOT_VAC|
|x|V_SITU_DEPOT_VC_VALID_DIR|Créée mais à réécrire pour prendre en compte l'étab|
|x|V_SITU_DEPOT_VC_VALID_DOCT|
|x|V_SITU_DEPOT_VO|
|x|V_SITU_DEPOT_VOC|
|x|V_SITU_RDV_BU_SAISIE_BU|
|x|V_SITU_RDV_BU_SAISIE_DOCT|
|x|V_SITU_RDV_BU_VALIDATION_BU|
|x|V_SITU_SIGNALEMENT_THESE|
|x|V_SITU_VERIF_VA|
|x|V_SITU_VERIF_VAC|
|x|V_SITU_VERSION_PAPIER_CORRIGEE|
|x|V_WF_ETAPE_PERTIN|
|x|V_WORKFLOW|

### Peuplement des tables

|Fait | Nom                     | Commentaires
|-----|-------------------------|------------------------------------------------------|
|  *  | ACTEUR                  | Données SoDoct reprises. Tester import...
|  *  | ATTESTATION             | Données SoDoct reprises. 
|  x  | CATEGORIE_PRIVILEGE     | 
|     | CONTENU_FICHIER         | 
|  *  | DIFFUSION               | Données SoDoct reprises. 
|  *  | ECOLE_DOCT              | Données SoDoct reprises. 
|     | ECOLE_DOCT_IND          | 
|  x  | ETABLISSEMENT           | 
|  x  | FAQ                     | Données SoDoct reprises. 
|     | FICHIER                 | 
|     | IMPORT_OBS_NOTIF        | 
|     | IMPORT_OBS_RESULT_NOTIF | 
|  *  | IMPORT_OBSERV           | 
|  *  | IMPORT_OBSERV_RESULT    | 
|  *  | INDIVIDU                | Données SoDoct reprises. Tester import...
|  *  | METADONNEE_THESE        | Données SoDoct reprises. 
|     | NOTIF                   | Module Notification | 
|     | NOTIF_RESULT            | Module Notification | 
|  x  | MV_RECHERCHE_THESE      | Exploitait des VM dans SoDoct! | 
|  x  | NATURE_FICHIER          | Données SoDoct reprises. 
|  x  | PRIVILEGE               | Données SoDoct reprises. 
|  *  | RDV_BU                  | Données SoDoct reprises. 
|  x  | ROLE_PRIVILEGE          | 
|  x  | SOURCE                  | 
|  *  | DOCTORANT               | Données SoDoct reprises. Tester import...
|  *  | DOCTORANT_COMPL         | Données SoDoct reprises.
|  x  | THESE                   | Données SoDoct reprises. Tester import...
|  x  | TYPE_VALIDATION         | Données SoDoct reprises. 
|  *  | UNITE_RECH              | Données SoDoct reprises. 
|     | UNITE_RECH_IND          | 
|  x  | ROLE                    | Données SoDoct reprises. Tester import...
|  *  | UTILISATEUR_ROLE        | 
|  *  | UTILISATEUR             | Données SoDoct reprises. 
|     | VALIDATION              | Données SoDoct reprises. 
|     | VALIDITE_FICHIER        | 
|  x  | VARIABLE                | Import
|  x  | VERSION_FICHIER         | Données SoDoct reprises. 
|  x  | WF_ETAPE                | Données SoDoct reprises. 
