#!/usr/bin/env bash

#############################################################################################
#        Script de lancement de l'import et de la synchro pour un établissement.
#############################################################################################
#
# Variables d'env requises :
#   ETAB : code de l'établissement à traiter, ex: "UCN".
#
# Variables d'env possibles :
#   SERVICE : code du service à traiter, ex: "variable".
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

# Import (appel web service) puis Synchro
if [ -z "$SERVICE" ]; then
  # tous les services
  set -x
  /usr/bin/php ${APP_DIR}/public/index.php import-all --etablissement=${ETAB} --synchronize=0 --breakOnServiceNotFound=0 && \
  /usr/bin/php ${APP_DIR}/public/index.php run synchro
else
  # seul service spécifié
  set -x
  /usr/bin/php ${APP_DIR}/public/index.php import --etablissement=${ETAB} --service=${SERVICE} --synchronize=0 && \
  /usr/bin/php ${APP_DIR}/public/index.php run synchro --name=${SERVICE}
fi

# Refresh de la vue matérialisée utilisée pour la recherche des thèses
/usr/bin/php ${APP_DIR}/public/index.php run-sql-query --sql="begin DBMS_MVIEW.REFRESH('MV_RECHERCHE_THESE'); end;"
