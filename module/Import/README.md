# Module Import

## Interface web

### Exemples 

Import de toutes les données du service "variable" de l'Université de Caen :

    /ws-import/import/variable/UCN

Import d'une seule donnée du service "variable" de l'Université de Caen :

    /ws-import/import/variable/UCN/ETB_LIB

Import de toutes les données de tous les services de l'Université de Caen : À ÉVITER

    /ws-import/import-all/UCN

## Interface CLI

### Exemples 

Import de toutes les données du service "variable" de l'Université de Caen :

    php public/index.php import --service=variable --etablissement=UCN

Import d'une seule donnée du service "variable" de l'Université de Caen :

    php public/index.php import --service=variable --etablissement=UCN --source-code=ETB_LIB

Import de toutes les données de tous les services de l'Université de Caen :

    php public/index.php import-all --etablissement=UCN

