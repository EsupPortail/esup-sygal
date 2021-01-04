#!/usr/bin/env bash

#
# cript de déploiement d'application sur un serveur.
#
# Usage examples :
#   $ bash ./deploy.sh
#

DIR=$(cd `dirname $0` && pwd)

export ROOT_DIR=${DIR}

#PHP_VERSION="7.3"
#REPO="git@git.unicaen.fr:dsi/sygal.git"
#APPDIR='/var/www/sygal'
#BAREREPODIR="/var/www/sygal.git"

function fetch_sources() {
  TMPDIR=$1
  if [ ! -d ${TMPDIR} ]; then
    echo "> Cloning ${REPO} repo to ${TMPDIR}..."
    git clone --quiet ${REPO} ${TMPDIR}
  fi
  cd ${TMPDIR}
  git fetch --all && git checkout --force ${BRANCH} && git pull
}

function deploy() {
  DESTINATION=$1
  git push --force ${DESTINATION}:${BAREREPODIR} --tags # NB: à faire à part et en 1er
  git push --force ${DESTINATION}:${BAREREPODIR} --tags ${BRANCH}
  ssh ${DESTINATION} "cd ${APPDIR} && bash install.sh"
}

function deploy_common_config() {
  DESTINATION=$1
  scp ${ROOT_DIR}/config/php/fpm/pool.d/*.conf     ${DESTINATION}:/etc/php/${PHP_VERSION}/fpm/pool.d/
  scp ${ROOT_DIR}/config/php/fpm/conf.d/*.ini      ${DESTINATION}:/etc/php/${PHP_VERSION}/fpm/conf.d/
  scp ${ROOT_DIR}/config/php/cli/conf.d/*.ini      ${DESTINATION}:/etc/php/${PHP_VERSION}/cli/conf.d/
  scp ${ROOT_DIR}/config/apache/000-default.conf   ${DESTINATION}:/etc/apache2/sites-available/000-default.conf
}

function deploy_resources() {
  DESTINATION=$1
  scp ${ROOT_DIR}/config/app/logo-etablissement.png ${DESTINATION}:${APPDIR}/public/
}

function generate_version_file() {
  DESTINATION=$1
  VERSION_CMD="git --git-dir ${BAREREPODIR} describe"
  DATE_CMD="git --git-dir ${BAREREPODIR} log --pretty='format:%ad' --date=format:'%d/%m/%Y %H:%M:%S' -1"
  ver_num=$(ssh ${DESTINATION} ${VERSION_CMD})
  ver_date=$(ssh ${DESTINATION} ${DATE_CMD})
  ssh ${DESTINATION} "cd ${APPDIR} && ./create-version-config-file --number ${ver_num} --date ${ver_date}"
}

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

############################### Sources ##################################

[ -z ${PHP_VERSION} ] && usage
[ -z ${DESTINATION} ] && usage
[ -z ${BAREREPODIR} ] && usage
[ -z ${APPDIR} ] && usage
[ -z ${BRANCH} ] && BRANCH="master"
[ -z ${DEPLOY} ] && DEPLOY=1
[ -z ${RELOAD} ] && RELOAD=1
[ -z ${TMPDIR} ] && TMPDIR="/tmp/unicaen-deploy-$RAND"

echo "ROOT_DIR = ${ROOT_DIR}"
echo "PHP_VERSION = ${PHP_VERSION}"
echo "BAREREPODIR = ${BAREREPODIR}"
echo "APPDIR = ${APPDIR}"
echo "DESTINATION = ${DESTINATION}"
echo "BRANCH = ${BRANCH}"
echo "DEPLOY = ${DEPLOY}"
echo "RELOAD = ${RELOAD}"
echo "TMPDIR = ${TMPDIR}"
echo


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
