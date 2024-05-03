#!/usr/bin/env bash

#############################################################################################
#        Script de lancement de l'import et de la synchro pour un établissement.
#############################################################################################
#
# Variables d'env attendues :
#   ETAB    : Code de l'établissement à traiter (ex: "UCN"). OBLIGATOIRE.
#   SERVICE : Code du service à traiter, (ex: "variable"). OPTIONNEL.
#

usage() {
  cat << EOF
Script de lancement de l'import et de la synchro pour un établissement.
Usage: ETAB=<etab> [SERVICE=<service>] $0
EOF
  exit 0;
}

echo "Etablissement : $ETAB"
echo "Service : $SERVICE"

[[ -z "$ETAB" ]] && usage

CURR_DIR=$(cd `dirname $0` && pwd)
APP_DIR=$(cd ${CURR_DIR}/.. && pwd)

echo "Répertoire courant : $CURR_DIR"
echo "Répertoire de l'appli : $APP_DIR"

# Avorte si le script est déjà en cours d'exécution (avec les mêmes arguments)
LOCK_FILE=/run/$(basename $0)_${ETAB}_${SERVICE}.lock
echo "Lock file : $LOCK_FILE"
exec 200>$LOCK_FILE
flock -n 200
if [ $? -eq 1 ]; then
  echo "Script $(realpath $0) déjà en cours d'exécution ($LOCK_FILE). Stop !"
  exit 1
fi

echo

function run() {
  service=$1
  (
    set -x ;
    /usr/bin/php ${APP_DIR}/public/index.php run import  --name=${service}-${ETAB} && \
    /usr/bin/php ${APP_DIR}/public/index.php run synchro --name=${service}-${ETAB}
  )
}

# Import (appel web service) puis Synchro
if [ -z "$SERVICE" ]; then
  # tous les services
  SERVICES[0]='structure'
  SERVICES[1]='etablissement'
  SERVICES[2]='ecole-doctorale'
  SERVICES[3]='unite-recherche'
  #SERVICES[4]='composante-enseignement'
  SERVICES[4]='individu'
  SERVICES[5]='doctorant'
  SERVICES[6]='these'
  SERVICES[7]='these-annee-univ'
  SERVICES[8]='role'
  SERVICES[9]='acteur'
  SERVICES[10]='origine-financement'
  SERVICES[11]='financement'
  SERVICES[12]='titre-acces'
  SERVICES[13]='variable'
  for SERVICE in ${SERVICES[@]}; do
    run ${SERVICE}
  done
else
  # seul service spécifié
  run ${SERVICE}
fi

# Refresh de la vue matérialisée utilisée pour la recherche des thèses
/usr/bin/php ${APP_DIR}/public/index.php run-sql-query --sql="refresh materialized view MV_RECHERCHE_THESE;"

# Vidage du cache de résultat Doctrine
#rm -rf ${APP_DIR}/data/DoctrineModule/cache/*
${APP_DIR}/vendor/bin/doctrine-module orm:clear-cache:result
