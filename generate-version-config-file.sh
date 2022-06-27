#!/usr/bin/env bash

DIR=$(cd `dirname $0` && pwd)

cd ${DIR}

DIST_FILE_PATH=${DIR}/config/autoload/auto.version.local.php.dist
TARGET_FILE_PATH=${DIR}/config/autoload/auto.version.local.php

ver_num=$(git describe) && \
ver_date=$(git log --pretty='format:%ad' --date=format:'%d/%m/%Y %H:%M:%S' -1) && \

cp -f ${DIST_FILE_PATH} ${TARGET_FILE_PATH} && \
sed -i "s#_VERSION_NUMBER_#${ver_num}#; s#_VERSION_DATE_#${ver_date}#; s#_GENERATION_DATE_#$(date)#" ${TARGET_FILE_PATH} && \

echo "Fichier de version généré : ${TARGET_FILE_PATH} (version = '${ver_num}', date = '${ver_date}')"
