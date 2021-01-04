#!/usr/bin/env bash

# PRE-REQUIS :
#   La variable ROOT_DIR doit être spécifiée au préalable avec `export ROOT_DIR=...`

if [ -z ${ROOT_DIR} ]; then
  echo 'La variable ROOT_DIR doit être spécifiée au préalable avec `export ROOT_DIR=...`'
  exit 1
fi


PHP_VERSION="7.3"
REPO="git@git.unicaen.fr:dsi/sygal.git"
APPDIR='/var/www/sygal'
BAREREPODIR="/var/www/sygal.git"

echo "ROOT_DIR = ${ROOT_DIR}"
echo "PHP_VERSION = ${PHP_VERSION}"
echo

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
  HOST=$1
  git push --force ${HOST}:${BAREREPODIR} --tags # NB: à faire à part et en 1er
  git push --force ${HOST}:${BAREREPODIR} --tags ${BRANCH}
  ssh ${HOST} "cd ${APPDIR} && bash install.sh"
}

function deploy_common_config() {
  HOST=$1
  scp ${ROOT_DIR}/config/php/fpm/pool.d/*.conf     ${HOST}:/etc/php/${PHP_VERSION}/fpm/pool.d/
  scp ${ROOT_DIR}/config/php/fpm/conf.d/*.ini      ${HOST}:/etc/php/${PHP_VERSION}/fpm/conf.d/
  scp ${ROOT_DIR}/config/php/cli/conf.d/*.ini      ${HOST}:/etc/php/${PHP_VERSION}/cli/conf.d/
  scp ${ROOT_DIR}/config/apache/000-default.conf   ${HOST}:/etc/apache2/sites-available/000-default.conf
}

function deploy_resources() {
  HOST=$1
  scp ${ROOT_DIR}/config/app/logo-etablissement.png ${HOST}:${APPDIR}/public/
}

function generate_version_file() {
  HOST=$1
  VERSION_CMD="git --git-dir ${BAREREPODIR} describe"
  DATE_CMD="git --git-dir ${BAREREPODIR} log --pretty='format:%ad' --date=format:'%d/%m/%Y %H:%M:%S' -1"
  ver_num=$(ssh ${HOST} ${VERSION_CMD})
  ver_date=$(ssh ${HOST} ${DATE_CMD})
  ssh ${HOST} "cd ${APPDIR} && ./create-version-config-file --number ${ver_num} --date ${ver_date}"
}

function reload_services() {
  HOST=$1
  ssh ${HOST} "service php${PHP_VERSION}-fpm reload && service apache2 reload"
}