#!/bin/bash

#############################################################################################
#        Script de lancement de l'import et de la synchro pour un établissement.
#############################################################################################
#
# Usage :
#   $ run-import.sh "UCN"
#
#############################################################################################

usage() {
  cat << EOF
Script de lancement de l'import et de la synchro pour un établissement.
Usage: $0 <ETAB>
EOF
  exit 0;
}

[[ -z "$1" ]] && usage

ROOT_DIR=$(cd `dirname $0` && pwd)/..
ETAB="$1"

php "$ROOT_DIR"/public/index.php import-all --etablissement="$ETAB" --synchronize=0 --breakOnServiceNotFound=0 && \
php "$ROOT_DIR"/public/index.php run synchro --all
# >> "/tmp/cron_sygal_import_$1.log" 2>&1 && \
