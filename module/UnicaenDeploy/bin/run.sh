#!/usr/bin/env bash

#
# cript de déploiement d'application sur un serveur.
#
# Usage examples :
#   $ bash ./deploy.sh
#

DIR=$(cd `dirname $0` && pwd)

function reload_services() {
  DESTINATION=$1
  ssh ${DESTINATION} "service php${PHP_VERSION}-fpm reload && service apache2 reload"
}

usage() {
  cat << EOF
Script de déploiement d'une application sur un serveur.

  Usage : [DEPLOY=<0/1>] [RELOAD=<0/1>] [BRANCH=<branch>] DESTINATION=<host> $0

  Ex: DESTINATION=root@host1.domain.fr $0
      => déploie les sources (branche master) sur host1 et reload les services
  Ex: DEPLOY=0 RELOAD=1 BRANCH=master DESTINATION=root@host2.domain.fr $0
      => ne déploie pas les sources mais reload les services sur host2
EOF
  exit 0;
}

  ## Services
  echo "[Services]"
  echo "> Reloading services on ${DESTINATION}..."
  reload_services ${DESTINATION}
  echo
fi

############################### Config ##################################

[ -z ${APPDIR} ] && usage

## Resources
echo "[Resources]"
echo "> Copying resources to ${DESTINATION}..."
deploy_resources ${DESTINATION}
echo

## Config
echo "[Config]"
echo "> Copying config files to ${DESTINATION}..."
deploy_common_config ${DESTINATION}
scp ${DIR}/config/app/*local.php            ${DESTINATION}:${APPDIR}/config/autoload/
scp ${DIR}/config/cron/sygal                ${DESTINATION}:/etc/cron.d/sygal           # >>>>> ATTENTION : PAS FORCÉMENT SUR TOUS LES DESTINATION
echo

if [ ${RELOAD} = 1 ]; then
  ## Services
  echo "[Services]"
  echo "> Reloading services on ${DESTINATION}..."
  reload_services ${DESTINATION}
  echo
fi
