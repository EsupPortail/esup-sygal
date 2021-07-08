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

function run() {
  service=$1
  (
    set -x ;
    /usr/bin/php ${APP_DIR}/public/index.php import --synchronize=0 --etablissement=${ETAB} --service=${service} && \
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
