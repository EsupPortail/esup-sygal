#!/usr/bin/env bash

#
# Déploiement de la branche 'test' de l'application sur le serveur de PREPROD.
#
# Usage examples :
#   $ bash ./deploy.sh
#

DIR=$(cd `dirname $0` && pwd)

export ROOT_DIR=${DIR}
source ${DIR}/_include.sh

usage() {
  cat << EOF
Script de déploiement de la config d'une application sur un serveur.

  Usage : [DEPLOY=<0/1>] [RELOAD=<0/1>] [BRANCH=<branch>] HOST=<host> $0

  Ex: HOST=root@host1.domain.fr $0
      => déploie les sources (branche master) sur host1 et reload les services
  Ex: DEPLOY=0 RELOAD=1 BRANCH=master HOST=root@host2.domain.fr $0
      => ne déploie pas les sources mais reload les services sur host2
EOF
  exit 0;
}

[ -z ${HOST} ] && usage
[ -z ${APPDIR} ] && usage
[ -z ${RELOAD} ] && RELOAD=1

## Resources
echo "[Resources]"
echo "> Copying resources to ${HOST}..."
deploy_resources ${HOST}
echo

## Config
echo "[Config]"
echo "> Copying config files to ${HOST}..."
deploy_common_config ${HOST}
scp ${DIR}/config/app/*local.php            ${HOST}:${APPDIR}/config/autoload/
scp ${DIR}/config/cron/sygal                ${HOST}:/etc/cron.d/sygal           # >>>>> ATTENTION : PAS FORCÉMENT SUR TOUS LES HOST
echo

if [ ${RELOAD} = 1 ]; then
  ## Services
  echo "[Services]"
  echo "> Reloading services on ${HOST}..."
  reload_services ${HOST}
  echo
fi