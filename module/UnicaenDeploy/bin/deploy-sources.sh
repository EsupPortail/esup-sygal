#!/usr/bin/env bash

#
# cript de déploiement d'application sur un serveur.
#
# Usage examples :
#   $ bash ./deploy.sh
#

DIR=$(cd `dirname $0` && pwd)

export ROOT_DIR=${DIR}
source ${DIR}/_include.sh

usage() {
  cat << EOF
Script de déploiement des sources d'une application sur un serveur.

  Usage : [DEPLOY=<0/1>] [RELOAD=<0/1>] [BRANCH=<branch>] DESTINATION=<host> $0

  Ex: DESTINATION=root@host1.domain.fr $0
      => déploie les sources (branche master) sur host1 et reload les services
  Ex: DEPLOY=0 RELOAD=1 BRANCH=master DESTINATION=root@host2.domain.fr $0
      => ne déploie pas les sources mais reload les services sur host2
EOF
  exit 0;
}

[ -z ${DESTINATION} ] && usage
[ -z ${BRANCH} ] && BRANCH="master"
[ -z ${DEPLOY} ] && DEPLOY=1
[ -z ${RELOAD} ] && RELOAD=1
[ -z ${TMPDIR} ] && TMPDIR="/tmp/unicaen-deploy-$RAND"

if [ ${DEPLOY} = 1 ]; then
  ## App
  echo "[App]"
  fetch_sources ${TMPDIR}
  echo "> Deploying '${BRANCH}' branch on ${DESTINATION}..."
  deploy ${DESTINATION}
  echo

  # Génération du fichier de version
  echo "[Version]"
  echo "Génération du fichier de version sur ${DESTINATION}..."
  generate_version_file ${DESTINATION}
  echo
fi

if [ ${RELOAD} = 1 ]; then
  ## Services
  echo "[Services]"
  echo "> Reloading services on ${DESTINATION}..."
  reload_services ${DESTINATION}
  echo
fi