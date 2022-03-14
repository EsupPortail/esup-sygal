#!/bin/bash

set -e

usage() {
  cat << EOF
Script de génération du fichier de version.
Usage :
  $0 <gitdir>
  <gitdir> : Chemin du répertoire des métadonnées git (.git), ou du dépôt git intermédiaire. Obligatoire.
Exemples :
  $0 /workspace/sygal/.git
EOF
  exit 0;
}

CURR_DIR=$(cd `dirname $0` && pwd)
APPDIR=$(cd ${CURR_DIR}/.. && pwd)

GITDIR=$1

[[ -z "$GITDIR" ]] && usage

function generate_version_file() {
  GITDIR=$1
  VERSION_CMD="git --git-dir ${GITDIR} describe"
  DATE_CMD="git --git-dir ${GITDIR} log --pretty='format:%ad' --date=format:'%d/%m/%Y %H:%M:%S' -1"
  echo "==> Génération du fichier de version..."
  ver_num=$(eval ${VERSION_CMD})
  ver_date=$(eval ${DATE_CMD})
  cd ${APPDIR} && ./create-version-config-file --number ${ver_num} --date ${ver_date}
}

generate_version_file $(realpath $GITDIR)
