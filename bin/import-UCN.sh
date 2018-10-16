#!/bin/bash

##########################################################################################
#        Script de lancement de l'import de toutes les données d'un établissement.
##########################################################################################

ETAB="UCN"

php public/index.php import --service=structure        --etablissement=${ETAB} && echo && \
php public/index.php import --service=ecole-doctorale  --etablissement=${ETAB} && echo && \
php public/index.php import --service=unite-recherche  --etablissement=${ETAB} && echo && \
php public/index.php import --service=etablissement    --etablissement=${ETAB} && echo && \
php public/index.php import --service=individu         --etablissement=${ETAB} && echo && \
php public/index.php import --service=doctorant        --etablissement=${ETAB} && echo && \
php public/index.php import --service=these            --etablissement=${ETAB} && echo && \
php public/index.php import --service=role             --etablissement=${ETAB} && echo && \
php public/index.php import --service=acteur           --etablissement=${ETAB} && echo && \
php public/index.php import --service=variable         --etablissement=${ETAB} && echo
