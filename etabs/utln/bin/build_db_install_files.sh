#!/usr/bin/env bash
set -e

#
# Script de génération des fichiers permettant de créer une base de données pour SyGAL.
#
#                                     U T L N
#

# Usage
# ----------
# PGDATABASE='sygal' PGUSER='user' PGHOST='host.domain.fr' PGPORT=5432 PGPASSWORD='xxxxxx' ./build.sh

usage() {
  echo "Usage :"
  echo "    PGDATABASE='sygal' PGUSER='user' PGHOST='host.domain.fr' PGPORT=5432 PGPASSWORD='xxxxxx' $(basename $0)"
  exit 0
}

THIS_DIR=$(cd $(dirname $0) && pwd)
APP_ROOT_DIR=$(realpath ${THIS_DIR}/../../..)
BUILD_SCRIPT=${APP_ROOT_DIR}/bin/database/build_db_install_files.sh

ETAB_DIR=${APP_ROOT_DIR}/etabs/utln
CONFIG_FILE=${ETAB_DIR}/bin/build_db_sygal_dev.conf
OUTPUT_DIR=${ETAB_DIR}/tmp/build_$(date '+%Y%m%d_%H%M%S')

#echo "THIS_DIR = ${THIS_DIR}"
#echo "APP_ROOT_DIR = ${APP_ROOT_DIR}"
#echo "BUILD_SCRIPT = ${BUILD_SCRIPT}"
#echo "ETAB_DIR = ${ETAB_DIR}"
#echo "CONFIG_FILE = ${CONFIG_FILE}"
#echo "OUTPUT_DIR = ${OUTPUT_DIR}"

mkdir ${OUTPUT_DIR}

${BUILD_SCRIPT} -c ${CONFIG_FILE} -o ${OUTPUT_DIR}

if [ -f ${OUTPUT_DIR}/build_db_files.conf.dist ]; then
  echo ""
  echo "Copie du fichier de config ${ETAB_DIR}/conf/build_db_files.UTLN.conf dans le build..."
  echo "> ${OUTPUT_DIR}/build_db_files.conf"
  rm -f ${OUTPUT_DIR}/build_db_files.conf
  cp ${ETAB_DIR}/conf/build_db_files.UTLN.conf ${OUTPUT_DIR}/build_db_files.conf
fi
